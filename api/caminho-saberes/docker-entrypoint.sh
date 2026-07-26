#!/bin/sh
set -e

echo "=== Waiting for MySQL ==="
for i in $(seq 1 30); do
  if php -r "
    \$pdo = new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306};charset=utf8mb4', '${DB_USER:-root}', '${DB_PASS:-root}');
    echo 'ok';
  " 2>/dev/null; then
    echo " MySQL is ready!"
    break
  fi
  echo "Attempt $i/30: MySQL not ready yet..."
  sleep 2
done

echo "=== Starting migration ==="
php -d output_buffering=0 database/migrate.php
echo "=== Migration exit code: $? ==="

echo "=== Starting PHP server on port ${PORT:-10000} ==="
exec php -S 0.0.0.0:${PORT:-10000} -t .
