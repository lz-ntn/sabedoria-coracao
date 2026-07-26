#!/bin/sh

echo "=== Starting migration ==="
php -d output_buffering=0 database/migrate.php
echo "=== Migration exit code: $? ==="

echo "=== Starting PHP server on port ${PORT:-10000} ==="
exec php -S 0.0.0.0:${PORT:-10000} -t .
