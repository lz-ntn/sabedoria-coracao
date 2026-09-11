# AGENTS.md — Sabedoria de Coração

Ecossistema de saberes ancestrais: 6 sites estáticos + 2 APIs PHP puro + biblioteca compartilhada. PHP 8.3+, sem framework, MySQL/TiDB Serverless, Docker, Render. Repo git próprio (branch `main`): `github.com/lz-ntn/sabedoria-coracao`.

```
sites/{portal,aprender,meditacao,viver,cristianismo,curso}   HTML/CSS/JS estáticos
api/portal-saberes    CMS Wiki (v1.0.0)
api/caminho-saberes   Plataforma SPA (v2.0.0)
core/                 Biblioteca compartilhada sabedoria/core (namespace Core\)
render.yaml           Blueprint Render (6 Static Sites)
```

## ⚠️ Gotcha crítico: `core/src/` existe em 3 cópias idênticas

Há **três** cópias de `core/src/` (Config, Csrf, Database, functions, Migration, RateLimiter), todas **byte-idênticas** e todas commitadas em git:
`core/src/`, `api/portal-saberes/core/src/`, `api/caminho-saberes/core/src/`.

- O autoload PSR-4 de cada API (`Core\` → `core/src/`) resolve para a **própria cópia embutida** da API — inclusive no `caminho`, onde o path repo `sabedoria/core` gera o symlink `vendor/sabedoria/core -> ../../core/`, que aponta de volta para a cópia embutida (auto-loop; `realpath` = `api/caminho-saberes/core/src`). O `vendor/sabedoria/core` é efetivamente morto — a cópia embutida sempre vence.
- **Qual cópia roda?** Dev (`make up`, `docker-compose.yml`) monta `./core:/app/core` por cima da embutida → roda a **raiz**. Prod/Render (`Dockerfile COPY . .` sem mount) → roda a **cópia embutida** de cada API (o Render builda a partir de `rootDir: api/<nome>`, então a raiz `./core` nem entra na imagem).
- **Regra de ouro:** ao editar `core/src/*.php`, sincronize **as 3 cópias** (`core/src/`, `api/portal-saberes/core/src/`, `api/caminho-saberes/core/src/`). Sem isso, dev e prod divergem.

## Integração com o OPS Dashboard (`gestor.sh`)

Gerenciado pelo `gestor.sh` (vidaReal) como serviço `sabedoria`:

```bash
/home/lz-ntn/vidaReal/novoComeco/scripts/gestor.sh sabedoria start|stop|restart|logs
/home/lz-ntn/vidaReal/novoComeco/scripts/gestor.sh status docker   # saúde dos containers/portas
```

- `gestor.sh` usa `compose_dir="/home/lz-ntn/Área de trabalho/sabedoria-deploy"` (com fallback `/home/lz-ntn/sabedoria-deploy`).
- Containers monitorados: `sabedoria-deploy_mysql_1`, `sabedoria-deploy_static_1`, `sabedoria-deploy_portal-saberes_1`, `sabedoria-deploy_caminho-saberes_1`. Portas vigiadas: 8083 (portal), 8081 (caminho), 8082 (static).

## Ambiente local (Docker)

```bash
make up       # docker compose up -d (dev; monta ./core por cima)
```
- Portal em `:8083:10000`, Caminho em `:8081:10000`, estáticos em `:8082:80`, MySQL interno `:3306`. (Não 8080 — o .env.portal.example ainda diz `APP_URL=http://localhost:8080`, mas o compose sobrescreve para 8083.)
- Imagens: `php:8.3-cli` (dev, `php -S` via `docker-entrypoint.sh`) e `php:8.3-fpm` (`Dockerfile.fpm`, OPcache, `docker-compose.prod.yml`).
- O entrypoint espera o MySQL, roda `database/migrate.php`, depois `php -S 0.0.0.0:${PORT:-10000} -t .`.

## Comandos úteis

```bash
make lint        # php -l em api/**/*.php (ignora vendor)
make fmt         # php-cs-fixer @PSR12 (se instalado; senão no-op)
make test        # no-op (PHPUnit NÃO configurado — não há testes)
make migrate-<svc>   # roda migrations no container (ex: migrate-caminho-saberes)
make deploy      # git push origin main (remoto: lz-ntn/sabedoria-coracao)
```
- **Não há testes.** CI (`.github/workflows/ci.yml`) roda só `php -l`, `composer validate` e PHPStan (`continue-on-error`, não instalado → skip).

## Migrations

- Sistema versionado `\Core\Migration` (tabela `migrations`), SQL numerados `001_*, 002_*` em `api/*/database/`.
- Rodam automaticamente no start do entrypoint. Manual: `make migrate-<svc>`, ou `docker compose exec <svc> php database/migrate.php`.
- `migrate.php` conecta com SSL (CA bundle) exigido pelo TiDB.

## Banco (Config + SSL)

- `\Core\Config` carrega `.env` por `load($path)`; `Config::get('KEY', default)`; `Config::isDevelopment()` é `APP_ENV === 'development'` (default `production`).
- **Dev vs prod via `APP_ENV`.** Fora de dev, `\Core\Database` adiciona CA bundle via `db_ssl_options()` (`MYSQL_ATTR_SSL_CA`, candidatos em `functions.php`) — obrigatório para TiDB Serverless. O `docker-entrypoint.sh` também usa `DB_SSL_CA` no readiness check.
- Padrões de DB: `MYSQLHOST/MYSQLPORT/MYSQLDATABASE` (Railway) se `DB_*` ausente.

## Deploy

- **Estáticos:** `render.yaml` = Blueprint com 6 Static Sites (`rootDir: sites/<nome>`, `staticPublishPath: .`), repo `github.com/lz-ntn/sabedoria-coracao`, projeto `Luz`.
- **APIs no Render:** Web Service `runtime: php`, build `composer install --no-dev --optimize-autoloader`, start `php database/migrate.php && php -S 0.0.0.0:10000 -t .` (ou seja, **Render usa `php -S`, não FPM**; FPM+Nginx só no `docker-compose.prod.yml`). Health checks: portal `/healthcheck.php`, caminho `/api/health.php`.
- TiDB: `portal_saberes` e `caminho_saberes` (utf8mb4). Schemas `schema-*.sql` na raiz e em `api/*/database/001_core_schema.sql`.
- `.env.*.example` são modelos; `.env*` reais são gitignored.

## Convenções / pendências reais

- **Admin sem senha padrão:** schemas não criam mais admin com `admin123`. Crie/atualize via `php seed-admin.php` (na raiz de cada API) — gera aleatória ou usa `ADMIN_EMAIL`/`ADMIN_PASSWORD` do `.env`; bloqueado na web por `.htaccess` e checagem CLI.
- **LGPD:** tracking no caminho-saberes só cria cookie após aceite do banner (`lgpd_consent`); os endpoints tratam `$usuario_id = null`. Preserve esse contrato.
- `.htaccess` existe em ambas as APIs (segurança + URL rewriting).
- **Sintoma clássico de banco vazio** (tabela `xxx.categorias doesn't exist`): reimportar `schema-portal.sql` / `schema-caminho.sql` (após `compose down -v`, o volume `mysql-data` some).

## Comandos legados relevantes

- Conectar TiDB: `mysql -h <DB_HOST> -P 4000 -u <DB_USER> -p --ssl-mode=VERIFY_IDENTITY --ssl-ca=.../ca-certificates.crt`
- Início: 2026-07-26. Contato: ecossistema@saberesancestrais.com
