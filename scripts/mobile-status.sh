#!/usr/bin/env bash

check() {
  local name=$1 url=$2
  local code
  code=$(curl -s -o /dev/null -w '%{http_code}' "$url" 2>/dev/null || echo "000")
  if [[ "$code" =~ ^[23] ]] || [[ "$code" == "200" ]]; then
    echo "✅ $name — $url"
  else
    echo "❌ $name — $url (offline ou compilando)"
  fi
}

echo "Prime Mobile — status"
echo "====================="
check "API"         "http://localhost:8088/health"
check "App Aluno"   "http://127.0.0.1:8086"
check "App Pro"     "http://127.0.0.1:8089"
check "Web Laravel" "http://localhost:8000/login"
echo ""
docker compose -f "$(cd "$(dirname "$0")/.." && pwd)/docker-compose.mobile.yml" ps --format 'table {{.Name}}\t{{.Status}}' 2>/dev/null || true
