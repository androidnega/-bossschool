#!/usr/bin/env bash
# Dev stack reachable on your LAN.
#
# Styling is loaded from the Tailwind Play CDN (see
# resources/views/layouts/partials/head-assets.blade.php), so Vite is NOT
# required in normal dev. We still start the Laravel queue listener and
# the log tailer alongside php artisan serve.
#
# Starts (in order):
#   1. (optional) Vite dev server  — only if WITH_VITE=1 is set
#   2. Laravel queue listener
#   3. Laravel log tailer (pail)
#   4. PHP artisan serve  (foreground — Ctrl+C stops everything)
#
# All background processes are tracked and killed cleanly on exit.
#
# Usage:
#   ./start.sh
#   PORT=8080 ./start.sh        # change Laravel port (default 8000)
#   NO_QUEUE=1 ./start.sh       # skip queue:listen
#   NO_PAIL=1  ./start.sh       # skip pail (log tail)
#   WITH_VITE=1 ./start.sh      # also run vite (rarely needed — CDN handles CSS)

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

PORT="${PORT:-8000}"
LOG_DIR="${LOG_DIR:-storage/logs}"
mkdir -p "$LOG_DIR"

# ─────────────────────────── helpers ────────────────────────────────────────

c_reset=$'\033[0m'
c_dim=$'\033[2m'
c_blue=$'\033[38;5;111m'
c_purple=$'\033[38;5;141m'
c_pink=$'\033[38;5;211m'
c_orange=$'\033[38;5;215m'

# Track background PIDs so we can clean them up on exit.
PIDS=()

kill_tree() {
  # Kill <pid> and all of its descendants. macOS-compatible (no GNU pkill flags).
  local pid="${1:-}" sig="${2:-TERM}"
  [[ -z "$pid" ]] && return 0
  # Find descendants recursively via `ps`.
  local children child
  children="$(pgrep -P "$pid" 2>/dev/null || true)"
  for child in $children; do
    kill_tree "$child" "$sig"
  done
  kill -"$sig" "$pid" 2>/dev/null || true
}

cleanup() {
  local pid
  trap - INT TERM EXIT
  echo ""
  echo "${c_dim}Stopping dev stack…${c_reset}"
  for pid in "${PIDS[@]:-}"; do
    kill_tree "$pid" TERM
  done
  sleep 0.5
  for pid in "${PIDS[@]:-}"; do
    kill_tree "$pid" KILL
  done
  # Vite writes public/hot; remove it so the next non-dev request doesn't
  # try to reach a dead dev server.
  rm -f public/hot 2>/dev/null || true
}
trap cleanup INT TERM EXIT

detect_lan_ip() {
  local iface ip
  if command -v ipconfig >/dev/null 2>&1; then
    for iface in en0 en1 en2 en3 en4; do
      ip="$(ipconfig getifaddr "$iface" 2>/dev/null || true)"
      if [[ -n "$ip" ]]; then printf '%s' "$ip"; return 0; fi
    done
  fi
  if command -v ip >/dev/null 2>&1; then
    ip="$(ip -4 route get 1.1.1.1 2>/dev/null | awk '{for (i=1;i<=NF;i++) if ($i=="src") {print $(i+1); exit}}')"
    if [[ -n "$ip" ]]; then printf '%s' "$ip"; return 0; fi
  fi
  if command -v hostname >/dev/null 2>&1; then
    hostname -I 2>/dev/null | awk '{ print $1; exit }'
  fi
}

prefix() {
  local label="$1" color="$2" pad=" "
  # Read line by line from stdin and prepend a coloured label.
  while IFS= read -r line; do
    printf '%s%-6s%s%s%s\n' "$color" "$label" "$c_reset" "$pad" "$line"
  done
}

start_bg() {
  # start_bg <label> <color> <cmd…>
  local label="$1" color="$2"; shift 2
  ( "$@" 2>&1 | prefix "$label" "$color" ) &
  local pid=$!
  PIDS+=("$pid")
  printf '%sstart%s %-6s pid=%s\n' "$c_dim" "$c_reset" "$label" "$pid"
}

# ─────────────────────────── banner ─────────────────────────────────────────

LAN_IP="$(detect_lan_ip || true)"
LAN_IP="$(printf '%s' "$LAN_IP" | tr -d '[:space:]')"

echo ""
echo "  App (this machine):  http://127.0.0.1:${PORT}"
if [[ -n "$LAN_IP" ]]; then
  echo "  App (LAN / share):   http://${LAN_IP}:${PORT}"
else
  echo "  App (LAN / share):   (could not detect IP — check Wi-Fi / Ethernet)"
fi
echo ""
echo "  Styling: Tailwind Play CDN (no build step required)."
echo "  Ctrl+C stops queue, logs and the PHP server together."
echo ""

# ─────────────────────────── pre-flight ─────────────────────────────────────

# Styling now comes from the Tailwind Play CDN, so a leftover Vite hot file
# from a previous run would make Laravel try to hit a dead dev server. Clear it.
rm -f public/hot 2>/dev/null || true

# ─────────────────────────── start services ─────────────────────────────────

# 1. Vite — opt-in only (WITH_VITE=1). The app loads Tailwind from the CDN,
#    so this is not needed for day-to-day dev. Useful only if someone is
#    deliberately editing resources/css/* with the old Vite pipeline.
if [[ -n "${WITH_VITE:-}" ]]; then
  if [[ ! -d node_modules ]]; then
    echo "${c_dim}node_modules missing — running npm install…${c_reset}"
    npm install
  fi
  start_bg "vite"  "$c_purple" npm run dev -- --host

  for _ in $(seq 1 40); do
    [[ -f public/hot ]] && break
    sleep 0.25
  done

  if [[ ! -f public/hot ]]; then
    echo "${c_orange}warn${c_reset}  Vite hot file did not appear within 10s — continuing anyway."
  fi
fi

# 2. Queue + 3. Pail (optional)
if [[ -z "${NO_QUEUE:-}" ]]; then
  start_bg "queue" "$c_pink"   php artisan queue:listen --tries=1 --timeout=0
fi
if [[ -z "${NO_PAIL:-}" ]]; then
  start_bg "logs"  "$c_orange" php artisan pail --timeout=0
fi

# 4. Laravel dev server — foreground. When it exits (or Ctrl+C), trap fires
#    and tears down the background processes.
echo ""
php artisan serve --host=0.0.0.0 --port="${PORT}" 2>&1 | prefix "server" "$c_blue"
