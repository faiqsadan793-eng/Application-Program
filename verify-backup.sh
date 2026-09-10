#!/usr/bin/env bash
set -Eeuo pipefail

# Restore dilakukan ke database sementara terpisah, diperiksa, lalu dihapus.
PROJECT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_FILE="${1:-}"
TEST_DATABASE="eklinik_restore_test_$(date +'%Y%m%d_%H%M%S')_$$"
TEST_CREATED=0

fail() { echo "Verifikasi restore gagal: $1" >&2; exit 1; }

database_command() {
    docker compose --project-directory "$PROJECT_DIR" exec -T db sh -eu -c '
        : "${MYSQL_ROOT_PASSWORD:?MYSQL_ROOT_PASSWORD tidak tersedia}"
        MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mariadb --user=root "$@"
    ' sh "$@"
}

cleanup() {
    if [[ "$TEST_CREATED" -eq 1 ]]; then
        database_command --execute="DROP DATABASE IF EXISTS \`$TEST_DATABASE\`;" >/dev/null 2>&1 || true
    fi
}
trap cleanup EXIT

[[ -n "$BACKUP_FILE" ]] || fail "gunakan: ./verify-backup.sh /lokasi/file-backup.sql.gz"
[[ -f "$BACKUP_FILE" ]] || fail "file tidak ditemukan: $BACKUP_FILE"
[[ "$TEST_DATABASE" == eklinik_restore_test_* ]] || fail "nama database pengujian tidak aman."
command -v docker >/dev/null 2>&1 || fail "perintah docker tidak ditemukan."
command -v gzip >/dev/null 2>&1 || fail "perintah gzip tidak ditemukan."
gzip -t -- "$BACKUP_FILE" || fail "arsip gzip rusak."

echo "Membuat database pengujian terpisah: $TEST_DATABASE"
database_command --execute="CREATE DATABASE \`$TEST_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
TEST_CREATED=1

gzip -dc -- "$BACKUP_FILE" | database_command "$TEST_DATABASE" || fail "impor SQL ke database pengujian tidak berhasil."

CORE_TABLE_COUNT="$(database_command --batch --skip-column-names --execute="
    SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema = '$TEST_DATABASE'
      AND table_name IN ('users','dokters','pasiens','kunjungans','rekam_medises','transaksis');
")"
[[ "$CORE_TABLE_COUNT" == "6" ]] || fail "satu atau lebih tabel inti tidak ditemukan setelah restore."

docker compose --project-directory "$PROJECT_DIR" exec -T \
    -e "RESTORE_TEST_DATABASE=$TEST_DATABASE" db sh -eu -c '
        : "${MYSQL_ROOT_PASSWORD:?MYSQL_ROOT_PASSWORD tidak tersedia}"
        MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mariadb-check --user=root "$RESTORE_TEST_DATABASE"
    ' || fail "pemeriksaan integritas tabel hasil restore gagal."

echo "Jumlah baris pada tabel inti hasil restore:"
database_command --table --execute="
    SELECT 'users' AS tabel, COUNT(*) AS jumlah FROM \`$TEST_DATABASE\`.users
    UNION ALL SELECT 'dokters', COUNT(*) FROM \`$TEST_DATABASE\`.dokters
    UNION ALL SELECT 'pasiens', COUNT(*) FROM \`$TEST_DATABASE\`.pasiens
    UNION ALL SELECT 'kunjungans', COUNT(*) FROM \`$TEST_DATABASE\`.kunjungans
    UNION ALL SELECT 'rekam_medises', COUNT(*) FROM \`$TEST_DATABASE\`.rekam_medises
    UNION ALL SELECT 'transaksis', COUNT(*) FROM \`$TEST_DATABASE\`.transaksis;
"

echo "Verifikasi restore berhasil: 6 tabel inti tersedia dan seluruh tabel lolos pemeriksaan."
echo "Database pengujian $TEST_DATABASE akan dihapus otomatis."
