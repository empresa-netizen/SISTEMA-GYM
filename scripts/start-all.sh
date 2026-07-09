#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "▶ Laravel (Docker)..."
docker compose up -d

echo "▶ Banco (migrate + seed se vazio)..."
docker compose exec -T app php artisan migrate --force --no-interaction 2>/dev/null || true
USER_COUNT="$(docker compose exec -T app php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -1 | tr -d '[:space:]')"
if [[ "${USER_COUNT:-0}" == "0" ]]; then
  echo "   Nenhum usuário — rodando db:seed..."
  docker compose exec -T app php artisan db:seed --force --no-interaction
fi

echo "▶ Mobile stack..."
"$ROOT/scripts/mobile-up.sh"

echo "▶ Unificando credenciais web ↔ mobile..."
chmod +x "$ROOT/scripts/unify-demo-auth.sh" 2>/dev/null || true
"$ROOT/scripts/unify-demo-auth.sh" || true

echo ""
echo "════════════════════════════════════════"
echo "  MGTEAM FITNESS & HEALTH — tudo no ar"
echo "════════════════════════════════════════"
echo "  Web coach:  http://localhost:8000"
echo "  Login:      coach@mgteam.app / password"
echo "  Apps hub:   http://localhost:8000/apps"
echo "  App Pro:    http://localhost:8089  (API Laravel :8000)"
echo "  App Aluno:  http://localhost:8086  anabeatriz@gmail.com / password"
echo "  Health:     http://localhost:8000/health"
echo "  Docs auth:  docs/AUTH_ARCHITECTURE.md"
echo "════════════════════════════════════════"
