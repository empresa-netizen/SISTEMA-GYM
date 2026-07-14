#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "▶ Parando stack mobile Docker..."
docker compose -f docker-compose.mobile.yml stop mobile-aluno 2>/dev/null || true

echo "▶ Parando processos locais..."
pkill -f "expo start --web --host lan --port 8086" 2>/dev/null || true

echo "✅ App mobile parado."
