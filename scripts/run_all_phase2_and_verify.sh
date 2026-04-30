#!/usr/bin/env bash
set -euo pipefail

echo "================ Phase 2: Migración de datos y verificación ================="

DB_NAME="${DB_NAME:-hotel_management_system}"
HOST="${DB_HOST:-localhost}"
USER="${DB_USER:-root}"
PASS="${DB_PASS:-}"

BACKUP_DIR="backups"
DATE=$(date +%F_%H-%M-%S)
mkdir -p "$BACKUP_DIR"

echo "[INFO] Backend: host=$HOST, user=$USER, db=$DB_NAME"

echo "[STEP] Respaldo de la BD..."
mysqldump -h "$HOST" -u "$USER" -p"$PASS" "$DB_NAME" > "$BACKUP_DIR/hotel_management_system_backup_$DATE.sql"

echo "[STEP] Ejecutando migración de datos (Phase 2)..."
mysql -h "$HOST" -u "$USER" -p"$PASS" "$DB_NAME" < scripts/migrate_phase2.sql

echo "[STEP] Verificaciones post-migración..."
echo "--- Usuarios (email, apellido) ---"
mysql -h "$HOST" -u "$USER" -p"$PASS" -D "$DB_NAME" -e "SELECT email, apellido FROM usuarios ORDER BY email;" --table

echo "--- Clientes (email, apellido) ---"
mysql -h "$HOST" -u "$USER" -p"$PASS" -D "$DB_NAME" -e "SELECT email, apellido FROM clientes ORDER BY email;" --table

echo "--- Habitaciones (numero, imagen_url) ---"
mysql -h "$HOST" -u "$USER" -p"$PASS" -D "$DB_NAME" -e "SELECT numero, imagen_url FROM habitaciones ORDER BY numero;" --table

echo "[DONE] Phase 2 completada. Revisa la salida y confirma funcionamiento de las vistas y consultas."
