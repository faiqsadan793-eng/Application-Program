#!/bin/bash
# ==============================================================================
# Script Auto-Backup Database E-Klinik (Home Server)
# ==============================================================================
BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
FILENAME="$BACKUP_DIR/backup_eklinik_$TIMESTAMP.sql.gz"

mkdir -p "$BACKUP_DIR"

echo "[$TIMESTAMP] Starting database backup..."
docker compose exec -T db mariadb-dump -u root -prootklinik123 db_eklinik_ta | gzip > "$FILENAME"

if [ -f "$FILENAME" ]; then
    echo "[$TIMESTAMP] Backup successfully saved to: $FILENAME ($(du -h "$FILENAME" | cut -f1))"
else
    echo "[$TIMESTAMP] Backup failed!"
    exit 1
fi

# Hapus backup yang umurnya lebih dari 14 hari agar harddisk server tidak penuh
find "$BACKUP_DIR" -type f -name "*.sql.gz" -mtime +14 -delete
echo "[$TIMESTAMP] Old backups (>14 days) cleaned up."
