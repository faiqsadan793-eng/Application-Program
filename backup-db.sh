#!/usr/bin/env bash
set -Eeuo pipefail

# Konfigurasi database dibaca di dalam container dari MYSQL_DATABASE,
# dan MYSQL_ROOT_PASSWORD yang berasal dari file .env proyek.
PROJECT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="${BACKUP_DIR:-${PROJECT_DIR}/backups}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-14}"
TIMESTAMP="$(date +'%Y%m%d_%H%M%S')"
FINAL_FILE="${BACKUP_DIR}/backup_eklinik_${TIMESTAMP}.sql.gz"
TEMP_SQL="${BACKUP_DIR}/.backup_eklinik_${TIMESTAMP}.sql.tmp"
TEMP_GZIP="${FINAL_FILE}.tmp"

cleanup() { rm -f -- "$TEMP_SQL" "$TEMP_GZIP"; }
fail() { echo "[$TIMESTAMP] Backup gagal: $1" >&2; exit 1; }
trap cleanup EXIT

case "$RETENTION_DAYS" in
    ''|*[!0-9]*) fail "BACKUP_RETENTION_DAYS harus berupa angka 0 atau lebih besar." ;;
esac

command -v docker >/dev/null 2>&1 || fail "perintah docker tidak ditemukan."
command -v gzip >/dev/null 2>&1 || fail "perintah gzip tidak ditemukan."
mkdir -p -- "$BACKUP_DIR"
[[ ! -e "$FINAL_FILE" ]] || fail "file tujuan sudah ada; jalankan ulang satu detik kemudian."

echo "[$TIMESTAMP] Membuat backup database dari konfigurasi Docker Compose..."

# File final hanya diterbitkan setelah dump dan kompresinya tervalidasi.
if ! docker compose --project-directory "$PROJECT_DIR" exec -T db sh -eu -c '
    : "${MYSQL_DATABASE:?MYSQL_DATABASE tidak tersedia}"
    : "${MYSQL_ROOT_PASSWORD:?MYSQL_ROOT_PASSWORD tidak tersedia}"
    MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mariadb-dump \
        --user=root \
        --single-transaction \
        --quick \
        --routines \
        --events \
        --triggers \
        --default-character-set=utf8mb4 \
        "$MYSQL_DATABASE"
' > "$TEMP_SQL"; then
    fail "mariadb-dump tidak berhasil. Backup lama tetap dipertahankan."
fi

if [[ ! -s "$TEMP_SQL" ]] || ! grep -q '^-- MariaDB dump' "$TEMP_SQL" || ! grep -q '^-- Dump completed on' "$TEMP_SQL"; then
    fail "hasil dump kosong atau tidak lengkap. Backup lama tetap dipertahankan."
fi

gzip -c -- "$TEMP_SQL" > "$TEMP_GZIP" || fail "kompresi gzip tidak berhasil. Backup lama tetap dipertahankan."
gzip -t -- "$TEMP_GZIP" || fail "hasil kompresi rusak. Backup lama tetap dipertahankan."
mv -- "$TEMP_GZIP" "$FINAL_FILE"
rm -f -- "$TEMP_SQL"

# Backup lama baru dibersihkan jika backup terbaru sudah valid.
find "$BACKUP_DIR" -type f -name 'backup_eklinik_*.sql.gz' -mtime "+$RETENTION_DAYS" -delete

echo "[$TIMESTAMP] Backup berhasil dan tervalidasi: $FINAL_FILE ($(du -h "$FINAL_FILE" | cut -f1))"
echo "Jalankan ./verify-backup.sh \"$FINAL_FILE\" untuk membuktikan proses restore."
