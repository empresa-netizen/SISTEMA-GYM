#!/usr/bin/env bash

check() {
  local name=$1 url=$2
  local code
  code=$(curl --max-time 5 -s -o /dev/null -w '%{http_code}' "$url" 2>/dev/null || echo "000")
  if [[ "$code" =~ ^[23] ]] || [[ "$code" == "200" ]]; then
    echo "✅ $name — $url"
  else
    echo "❌ $name — $url (offline ou compilando)"
  fi
}

echo "MGTEAM Mobile — status"
echo "====================="
check "Web Laravel" "http://localhost:8000/login"
check "API Laravel" "http://localhost:8000/api/health"
check "App Aluno"   "http://127.0.0.1:8086"
echo ""
docker compose -f "$(cd "$(dirname "$0")/.." && pwd)/docker-compose.mobile.yml" ps --format 'table {{.Name}}\t{{.Status}}' 2>/dev/null || true
