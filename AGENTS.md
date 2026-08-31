# AGENTS.md — Sabedoria de Coração

Ecossistema de saberes ancestrais: 6 sites estáticos + 2 APIs PHP puro + biblioteca compartilhada. PHP 8.3+, sem framework, MySQL/TiDB Serverless, Docker, Render.

```
sites/{portal,aprender,meditacao,viver,cristianismo,curso}   HTML/CSS/JS estáticos
api/portal-saberes    CMS Wiki (v1.0.0)
api/caminho-saberes   Plataforma SPA (v2.0.0)
core/                 Biblioteca compartilhada sabedoria/core (namespace Core\)
render.yaml           Blueprint Render (6 Static Sites)
```

## ⚠️ Gotcha crítico: a biblioteca `core/` tem DUAS cópias

`Database.php` e `Migration.php` existem **somente na raiz** `core/src/`. Cada API tem uma **cópia comprometida própria** `api/*/core/src/` que contém apenas `Config, Csrf, functions, RateLimiter`.

- O composer de cada API mapeia `Core\` → seu próprio `api/*/core/src/` (path repo `sabedoria/core: @dev` → symlink `vendor/sabedoria/core -> ../../core/`, que é gitignored).
- Código que usa `\Core\Database` ou `\Core\Migration` só resolve em **dev**, onde `docker-compose.yml` monta `./core:/app/core` por cima da cópia embutida. Na imagem de produção (`Dockerfile` com `COPY . .`), Database/Migration não estão nas fontes `Core\` autoloadadas.
- **Ao editar `core/src/*.php`, você precisa sincronizar manualmente para `api/<nome>/core/src/`** (exceto Database/Migration, que só existem na raiz).

## Ambiente local (Docker)

```bash
make up       # docker compose up -d (dev; usa ./core montado)
```
- Portal em `http://localhost:8083:10000`, Caminho em `8081:10000`, sites estáticos em `8082:80`. (Não 8080/8081 como docs antigas diziam.)
- `composer.yaml` Images: `php:8.3-cli` (dev, roda `php -S` via `docker-entrypoint.sh`) e `php:8.3-fpm` (`Dockerfile.fpm`, OPcache, ambiente prod-like via `docker-compose.prod.yml`).
- O entrypoint espera MySQL, roda `database/migrate.php`, depois `php -S 0.0.0.0:${PORT:-10000}`.

## Comandos úteis

```bash
make lint        # php -l em api/**/*.php (ignora vendor)
make fmt         # php-cs-fixer @PSR12 (se instalado; senão no-op)
make test        # phpunit se instalado (NÃO configurado — sem testes)
make migrate-<svc>   # roda migrations no container
make deploy      # git push origin main
```
- **Não há testes configurados** (Makefile tem `test` no-op; PHPUnit não instalado). CI (`.github/workflows/ci.yml`) roda apenas `php -l`, `composer validate`, e PHPStan opcional (`continue-on-error`, não instalado → skip).

## Migrations

- Sistema versionado `\Core\Migration` com tabela `migrations`. SQL numerados `001_*, 002_*` em `api/*/database/`.
- O entrypoint roda migrations automaticamente no start. Manualmente: `make migrate-portal-saberes` / `make migrate-caminho-saberes`, ou `docker compose exec <svc> php database/migrate.php`.
- `migrate.php` conecta exigindo SSL com CA bundle do sistema (exigência TiDB).

## Banco (Config + SSL)

- `\Core\Config` carrega `.env` automaticamente e expõe `Config::get('KEY', default)`.
- **Dev vs prod é definido por `APP_ENV`** (`Config::isDevelopment()`). Em produção (não-dev), `\Core\Database` adiciona CA bundle do sistema (`MYSQL_ATTR_SSL_CA`) — obrigatório para TiDB Serverless.
- Padrões de DB: usa `MYSQLHOST/MYSQLPORT/MYSQLDATABASE` (Railway) se `DB_*` ausente.

## Deploy

- **Estáticos:** `render.yaml` = Blueprint com 6 Static Sites (`rootDir: sites/<nome>`, `staticPublishPath: .`). Repo `github.com/lz-ntn/sabedoria-coracao`.
- **APIs no Render:** Web Service, root dir `api/<nome>`, build `composer install --no-dev --optimize-autoloader`, health checks (`/healthcheck` portal, `/api/health` caminho).
- Databases TiDB: `portal_saberes` e `caminho_saberes` (utf8mb4). Schemas: `schema-*.sql` na raiz e em `api/*/database/001_core_schema.sql`.
- `.env.*.example` são os modelos; `.env*` reais são gitignored.

## Convenções / pendências reais

- **Admin = sem senha padrão**: schemas não criam mais admin com `admin123`. Crie/atualize via `php seed-admin.php` (raiz de cada API) — gera senha aleatória ou usa `ADMIN_EMAIL`/`ADMIN_PASSWORD` do `.env`; bloqueado na web por `.htaccess` e por checagem CLI.
- **Health checks**: `api/portal-saberes/healthcheck.php` (`/healthcheck.php`) e `api/caminho-saberes/api/health.php` (`/api/health.php`) — usados pelo Render `healthCheckPath`. Retornam 503 se o banco cair.
- LGPD: UUID tracking no caminho-saberes só cria cookie após aceite do banner (`lgpd_consent`); endpoints tratam `$usuario_id = null`. Preservar esse contrato.
- `.htaccess` existe em ambas as APIs (segurança + URL rewriting). `docker-entrypoint` usa `php -S` mesmo em "produção" dev; Nginx+FPM real fica no `docker-compose.prod.yml` via `Dockerfile.fpm`.

## Comandos legados relevantes

- Conectar TiDB: `mysql -h <DB_HOST> -P 4000 -u <DB_USER> -p --ssl-mode=VERIFY_IDENTITY --ssl-ca=.../ca-certificates.crt`
- Início do projeto: 2026-07-26. Contato: ecossistema@saberesancestrais.com
