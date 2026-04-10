#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "=== Structure Evaluation ($(date '+%Y-%m-%d %H:%M:%S')) ==="
echo "[1] App PHP files"
find app -type f -name '*.php' | wc -l | tr -d ' '

echo "[2] Controller distribution"
find app -type f -name '*Controller.php' | sort

echo "[3] Domain-oriented controllers"
find app/Domain -type f -name '*Controller.php' 2>/dev/null | wc -l | tr -d ' '

echo "[4] FormRequest usage in API"
rg -n "extends FormRequest" app/Domain app/Http/Requests --glob '*.php' || true

echo "[5] Route -> Controller namespace references"
rg -n "App\\\\Domain|App\\\\Http\\\\Controllers\\\\Api" routes --glob '*.php' || true

echo "=== End ==="
