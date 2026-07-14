#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

log() { echo "▶ $*"; }

LAN_IP="$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null || echo "localhost")"

log "Garantindo Laravel local no ar..."
docker compose up -d

log "Parando processos Expo locais antigos (se houver)..."
pkill -f "expo start --web --host lan --port 8086" 2>/dev/null || true

log "Subindo App do Aluno via Docker..."
docker compose -f docker-compose.mobile.yml up -d mobile-aluno

log "Aguardando serviços (até 5 min)..."
web_ok=0
student_ok=0
for i in $(seq 1 150); do
  web_code=$(curl --max-time 5 -s -o /dev/null -w '%{http_code}' http://localhost:8000/login 2>/dev/null || echo 000)
  student_code=$(curl --max-time 5 -s -o /dev/null -w '%{http_code}' http://127.0.0.1:8086 2>/dev/null || echo 000)

  [[ "$web_code" =~ ^[23] ]] && web_ok=1
  [[ "$student_code" =~ ^[23] ]] && student_ok=1

  if [[ $web_ok -eq 1 && $student_ok -eq 1 ]]; then
    break
  fi

  if (( i % 15 == 0 )); then
    echo "   ...ainda iniciando ($i/150) web:$web_code aluno:$student_code"
    docker compose -f docker-compose.mobile.yml ps --format 'table {{.Name}}\t{{.Status}}' 2>/dev/null | head -5
  fi

  sleep 2
done

echo ""
if [[ $web_ok -eq 1 && $student_ok -eq 1 ]]; then
  echo "✅ Web + App do Aluno no ar."
else
  echo "⚠️  Ainda iniciando — acompanhe:"
  echo "   docker compose -f docker-compose.mobile.yml logs -f mobile-aluno"
fi

echo ""
echo "  Web Laravel:  http://localhost:8000/login"
echo "  API local:    http://localhost:8000/api/v1"
echo "  App Aluno:    http://localhost:8086"
echo "  Celular Wi-Fi: http://$LAN_IP:8086"
echo "  Backend Wi-Fi: http://$LAN_IP:8000"
echo ""
echo "Login demo:"
echo "  Admin/Coach → admin@mgteam.app / 123456"
echo "  Aluno       → anabeatriz@gmail.com / 123456"
echo ""
echo "Status:  ./scripts/mobile-status.sh"
echo "Logs:    docker compose -f docker-compose.mobile.yml logs -f"
echo "Parar:   ./scripts/mobile-down.sh"
