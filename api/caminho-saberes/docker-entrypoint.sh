#!/bin/sh
set -e

echo "=== Starting migration ==="
php database/migrate.php 2>&1
echo "=== Migration done ==="

echo "=== Starting PHP server on port ${PORT:-10000} ==="
exec php -S 0.0.0.0:${PORT:-10000} -t .
