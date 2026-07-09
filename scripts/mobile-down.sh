#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "▶ Parando stack mobile Docker..."
docker compose -f docker-compose.mobile.yml stop mobile-api mobile-aluno mobile-pro 2>/dev/null || true

echo "▶ Parando processos locais..."
pkill -f "tsx watch src/index.ts" 2>/dev/null || true
pkill -f "expo start --web --host lan --port 8086" 2>/dev/null || true
pkill -f "expo start --web --host lan --port 8089" 2>/dev/null || true

echo "✅ Apps mobile parados. (PostgreSQL continua rodando — use 'docker compose -f docker-compose.mobile.yml stop mobile-db' para parar o banco)"
