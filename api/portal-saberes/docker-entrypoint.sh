#!/bin/sh
set -e

echo "=== Waiting for MySQL ==="
for i in $(seq 1 30); do
  if php -r "
    \$ca = getenv('DB_SSL_CA');
    if (\$ca === false || \$ca === '') {
      foreach (['/app/core/ssl/ca-certificates.crt', '/etc/ssl/certs/ca-certificates.crt', '/etc/ssl/ca-bundle.pem', '/etc/pki/tls/certs/ca-bundle.crt'] as \$c) {
        if (file_exists(\$c)) { \$ca = \$c; break; }
      }
    }
    \$ssl = [];
    if (getenv('APP_ENV') !== 'development' && \$ca !== false && \$ca !== '' && file_exists(\$ca)) {
      \$ssl[PDO::MYSQL_ATTR_SSL_CA] = \$ca;
    }
    \$pdo = new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306};charset=utf8mb4', '${DB_USER:-root}', '${DB_PASS:-root}', \$ssl);
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
