#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
SCHEDULE="${BACKUP_CRON_SCHEDULE:-0 23 * * *}"
LOG_FILE="${BACKUP_LOG_FILE:-${PROJECT_DIR}/storage/logs/database-backup.log}"
COPY_DIR="${BACKUP_COPY_DIR:-}"
MARKER="# e-klinik-database-backup"

command -v crontab >/dev/null 2>&1 || { echo "crontab tidak ditemukan." >&2; exit 1; }
[[ -n "$COPY_DIR" ]] || { echo "BACKUP_COPY_DIR wajib diisi dengan folder pada perangkat kedua." >&2; exit 1; }
[[ -d "$COPY_DIR" ]] || { echo "BACKUP_COPY_DIR tidak tersedia; pasang drive/NAS terlebih dahulu." >&2; exit 1; }
[[ -w "$COPY_DIR" ]] || { echo "BACKUP_COPY_DIR tidak dapat ditulis." >&2; exit 1; }
mkdir -p -- "$(dirname -- "$LOG_FILE")"

quote_shell() { printf "'%s'" "${1//\'/\'\\\'\'}"; }

COMMAND="cd $(quote_shell "$PROJECT_DIR") && BACKUP_COPY_DIR=$(quote_shell "$COPY_DIR") ./backup-db.sh >> $(quote_shell "$LOG_FILE") 2>&1"
CURRENT="$(crontab -l 2>/dev/null || true)"
FILTERED="$(printf '%s\n' "$CURRENT" | grep -vF "$MARKER" || true)"
{
    printf '%s\n' "$FILTERED"
    printf '%s %s %s\n' "$SCHEDULE" "$COMMAND" "$MARKER"
} | sed '/^[[:space:]]*$/d' | crontab -

echo "Backup otomatis terpasang: $SCHEDULE"
echo "Log: $LOG_FILE"
echo "Salinan kedua: $COPY_DIR"
