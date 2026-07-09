#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
DB_URL="postgresql://trabalho:123456@localhost:5433/coachpro?schema=public"
SEED_DIR="/Users/trabalho/codex/coach-pro"

log() { echo "▶ $*"; }

log "Parando processos locais antigos (se houver)..."
pkill -f "tsx watch src/index.ts" 2>/dev/null || true
pkill -f "expo start --web --host lan --port 8086" 2>/dev/null || true
pkill -f "expo start --web --host lan --port 8089" 2>/dev/null || true
rm -rf /tmp/prime-mobile 2>/dev/null || true

log "Subindo stack mobile via Docker..."
docker compose -f docker-compose.mobile.yml up -d mobile-db

log "Aguardando PostgreSQL..."
for i in $(seq 1 30); do
  docker exec prime_mobile_db pg_isready -U trabalho -d coachpro >/dev/null 2>&1 && break
  sleep 2
done

if ! docker exec prime_mobile_db psql -U trabalho -d coachpro -tAc "SELECT 1 FROM users LIMIT 1" 2>/dev/null | grep -q 1; then
  log "Seed inicial (coach-pro)..."
  if [[ -d "$SEED_DIR/node_modules" ]]; then
    (
      cd "$SEED_DIR"
      DATABASE_URL="$DB_URL" DIRECT_DATABASE_URL="$DB_URL" npx prisma db push --accept-data-loss
      DATABASE_URL="$DB_URL" DIRECT_DATABASE_URL="$DB_URL" npm run db:seed
    )
  else
    log "⚠️  Rode o seed manualmente se login falhar:"
    log "   cd $SEED_DIR && DATABASE_URL='$DB_URL' npm run db:seed"
  fi
fi

log "Subindo API + apps (primeira vez pode levar 3–5 min para instalar deps)..."
docker compose -f docker-compose.mobile.yml up -d mobile-api mobile-aluno mobile-pro

log "Aguardando serviços (até 5 min)..."
api_ok=0 aluno_ok=0 pro_ok=0
for i in $(seq 1 150); do
  a=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:8088/health 2>/dev/null || echo 000)
  s=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1:8086 2>/dev/null || echo 000)
  p=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1:8089 2>/dev/null || echo 000)
  [[ "$a" == "200" ]] && api_ok=1
  [[ "$s" =~ ^[23] ]] && aluno_ok=1
  [[ "$p" =~ ^[23] ]] && pro_ok=1
  if [[ $api_ok -eq 1 && $aluno_ok -eq 1 && $pro_ok -eq 1 ]]; then
    break
  fi
  if (( i % 15 == 0 )); then
    echo "   ...ainda compilando ($i/150) api:$a aluno:$s pro:$p"
    docker compose -f docker-compose.mobile.yml ps --format 'table {{.Name}}\t{{.Status}}' 2>/dev/null | head -5
  fi
  sleep 2
done

echo ""
if [[ $api_ok -eq 1 && $aluno_ok -eq 1 && $pro_ok -eq 1 ]]; then
  echo "✅ Apps mobile no ar (Docker)."
else
  echo "⚠️  Ainda iniciando — acompanhe:"
  echo "   docker compose -f docker-compose.mobile.yml logs -f mobile-aluno mobile-pro"
fi

echo ""
echo "  API:          http://localhost:8088/health"
echo "  Aluno:        http://localhost:8086"
echo "  Profissional: http://localhost:8089"
echo "  Hub Laravel:  http://localhost:8000/apps"
echo ""
echo "Login demo:"
echo "  Profissional → admin@mgteam.app / 123456"
echo "  Aluno        → anabeatriz@gmail.com / 123456"
echo ""
echo "Status:  ./scripts/mobile-status.sh"
echo "Logs:    docker compose -f docker-compose.mobile.yml logs -f"
echo "Parar:   ./scripts/mobile-down.sh"
