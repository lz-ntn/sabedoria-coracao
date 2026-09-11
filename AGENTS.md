# AGENTS.md — Sabedoria de Coração

Ecossistema de saberes ancestrais: 6 sites estáticos + 1 API PHP puro + biblioteca compartilhada. PHP 8.3+, sem framework, MySQL 8.0 (local) / TiDB Serverless (prod), Docker, Render. Repo git próprio (branch `main`): `github.com/lz-ntn/sabedoria-coracao`.

> **⚠️ A partir de 2026-09-11 o Portal Saberes foi FUNDIDO no Caminho Saberes** (decisão do vida real). Os artigos do
> Portal vivem agora como `licoes` com `tipo='artigo'` exibidos na seção **Biblioteca** do SPA. Os serviços/containers
> do Portal (`api/portal-saberes`) não são mais deployados, mas o código e o banco `portal_saberes` são mantidos para
> referência e para o script de importação. NÃO edite o Portal; evoluir tudo dentro do Caminho.

```
sites/{portal,aprender,meditacao,viver,cristianismo,curso}   HTML/CSS/JS estáticos
api/caminho-saberes   Plataforma SPA (v2.0.0) — lições (tipo='licao') + Biblioteca (tipo='artigo')
api/portal-saberes    Código do antigo CMS Wiki (v1.0.0) — RETIDO: não deploya, serve só de referência/importação
core/                 Biblioteca compartilhada sabedoria/core (namespace Core\)
render.yaml           Blueprint Render (6 Static Sites)
```

## Estrutura de conteúdo no Caminho

- `licoes` ganhou colunas na migration `003_portal_merge.sql`: `tipo` (`licao`|`artigo`), `resumo`, `tags`, `imagem`,
  `fonte`, `autor_id`, `publicado_em`, `views`; tabela nova `discussoes` (comentários em camisetas); FULLTEXT `ft_conteudo`;
  views `vw_artigos_publicos` e `vw_licoes_cronograma`.
- **APIs:** `api/biblioteca.php` (lista/busca/detalhe de artigos `tipo='artigo'`), `api/discussoes.php` (comentários),
  `api/progresso.php` (agora filtra `tipo='licao'`); `stats.php`, `favoritos.php`, `quiz.php`, `newsletter.php`.
- Admin (`api/caminho-saberes/admin/index.php`) tem abas **Lições** e **Artigos** + CRUD de categorias + ação de importação.
- **Seed e imports:** `php database/seed_biblioteca.php` (8 artigos de exemplo, idempotente).
  `php database/import_saberes_wiki.php` (52 saberes do JSON → lições; cria 5 categorias novas: Práticas, Cosmologia,
  Jornada, Vida Verdadeira, Tradições). `php database/import_jesus_artigos.php` (22 artigos Markdown → artigos).
  `php database/import_blavatsky.php` (5 lições HTML → artigos). Todos aceitam paths via variáveis de ambiente.
- Consumo no SPA: seção `#biblioteca` + modal de artigo; **LGPD segue obrigatória** antes de criar cookie/usuário.
- **Estado atual:** 95 itens (60 lições + 35 artigos) em 11 categorias: Gnose, Epigenética, Práticas, Hermetismo,
  Kundalini, Cosmologia, Teosofia, Jornada, Coração, Vida Verdadeira, Tradições.

## ⚠️ Gotcha crítico: `core/src/` existe em 2 cópias ativas

Há **duas** cópias de `core/src/` em uso: `core/src/` (raiz) e `api/caminho-saberes/core/src/` (embutida). A cópia de
`api/portal-saberes/core/src/` é legado (não roda) — não precisa sincronizar. Em geral, **sincronize as 2**: edite a raiz e
copie para `api/caminho-saberes/core/src/`.

- O autoload PSR-4 da API (`Core\` → `core/src/`) resolve para a **cópia embutida** — o path repo `sabedoria/core` gera o
  symlink `vendor/sabedoria/core -> ../../core/`, que aponta de volta para a cópia embutida (auto-loop; `realpath` =
  `api/caminho-saberes/core/src`).
- **Qual cópia roda?** Dev (`make up`, `docker-compose.yml`) monta `./core:/app/core` por cima da embutida → roda a **raiz**.
  Prod/Render (`Dockerfile COPY . .` sem mount) → roda a **cópia embutida** do Caminho.
- **Regra de ouro:** ao editar `core/src/*.php`, sincronize **as 2 cópias ativas** (`core/src/` e `api/caminho-saberes/core/src/`).

## Integração com o OPS Dashboard (`gestor.sh`)

Gerenciado pelo `gestor.sh` (vidaReal) como serviço `sabedoria`:

```bash
/home/lz-ntn/vidaReal/novoComeco/scripts/gestor.sh sabedoria start|stop|restart|logs
/home/lz-ntn/vidaReal/novoComeco/scripts/gestor.sh status docker   # saúde dos containers/portas
```

- `gestor.sh` usa `compose_dir="/home/lz-ntn/Área de trabalho/sabedoria-deploy"` (com fallback `/home/lz-ntn/sabedoria-deploy`).
- Containers monitorados: `sabedoria-deploy_mysql_1`, `sabedoria-deploy_static_1`, `sabedoria-deploy_caminho-saberes_1`.
  Portas vigiadas: 8081 (caminho), 8082 (static).

## Ambiente local (Docker)

```bash
make up       # docker compose up -d (dev; monta ./core por cima)
make clean    # docker compose down -v --remove-orphans (limpa volume MySQL)
```

- Caminho em `:8081:10000`, estáticos em `:8082:80`, MySQL interno `:3306` (sem porta exposta no host — evita conflito).
- MySQL local com `docker/init/001-create-databases.sql` que cria `portal_saberes` e `caminho_saberes` — `portal_saberes`
  é só para dados legados do import (`database/import_portal.php`); o serviço não roda mais.
- Entry points fazem loop de 30 tentativas aguardando MySQL ficar pronto antes de rodar migrations.
- SSL desativado em dev (`APP_ENV=development`); ativado em prod (CA bundle obrigatório para TiDB).
- Imagens: `php:8.3-cli` (dev, `php -S` via `docker-entrypoint.sh`) e `php:8.3-fpm` (`Dockerfile.fpm`, OPcache, `docker-compose.prod.yml`).

## Comandos úteis

```bash
make lint        # php -l em api/**/*.php (ignora vendor)
make fmt         # php-cs-fixer @PSR12 (se instalado; senão no-op)
make test        # no-op (PHPUnit NÃO configurado — não há testes)
make migrate-<svc>   # roda migrations no container (ex: make migrate-caminho-saberes)
make shell-<svc>     # shell no container (ex: make shell-caminho-saberes)
make deploy      # git push origin main (remoto: lz-ntn/sabedoria-coracao)
make clean       # remove volumes e containers ( MySQL data é perdido)
```
- **Não há testes.** CI (`.github/workflows/ci.yml`) roda só `php -l`, `composer validate` e PHPStan (`continue-on-error`, não instalado → skip).

## Migrations

- Sistema versionado `\Core\Migration` (tabela `migrations`), SQL numerados `001_*, 002_*` em `api/*/database/`.
- Rodam automaticamente no start do entrypoint. Manual: `make migrate-<svc>`, ou `docker compose exec <svc> php database/migrate.php`.
- `migrate.php` conecta com SSL (CA bundle) exigido pelo TiDB em prod.

## Banco (Config + SSL)

- `\Core\Config` carrega `.env` por `load($path)`; `Config::get('KEY', default)`; `Config::isDevelopment()` é `APP_ENV === 'development'` (default `production`).
- **Dev vs prod via `APP_ENV`.** Fora de dev, `\Core\Database` adiciona CA bundle via `db_ssl_options()` (`MYSQL_ATTR_SSL_CA`, candidatos em `functions.php`) — obrigatório para TiDB Serverless. Em dev, SSL é pulado.
- Padrões de DB: `MYSQLHOST/MYSQLPORT/MYSQLDATABASE` (Railway) se `DB_*` ausente.

## Deploy

- **Estáticos:** `render.yaml` = Blueprint com 6 Static Sites (`rootDir: sites/<nome>`, `staticPublishPath: .`), repo `github.com/lz-ntn/sabedoria-coracao`, projeto `Luz`.
- **APIs no Render:** Web Service `runtime: php`, build `composer install --no-dev --optimize-autoloader`, start `php database/migrate.php && php -S 0.0.0.0:10000 -t .` (ou seja, **Render usa `php -S`, não FPM**; FPM+Nginx só no `docker-compose.prod.yml`). Health checks: caminho `/api/health.php`.
- TiDB: `portal_saberes` e `caminho_saberes` (utf8mb4). Schemas `schema-*.sql` na raiz e em `api/*/database/001_core_schema.sql`.
- `.env.*.example` são modelos; `.env*` reais são gitignored.

## Convenções / pendências reais

- **Admin sem senha padrão:** schemas não criam mais admin com `admin123`. Crie/atualize via `php seed-admin.php` (na raiz de cada API) — gera aleatória ou usa `ADMIN_EMAIL`/`ADMIN_PASSWORD` do `.env`; bloqueado na web por `.htaccess` e checagem CLI.
- **LGPD:** tracking no caminho-saberes só cria cookie após aceite do banner (`lgpd_consent`); os endpoints tratam `$usuario_id = null`. Preserve esse contrato.
- `.htaccess` existe em ambas as APIs (segurança + URL rewriting).
- **Sintoma clássico de banco vazio** (tabela `xxx.categorias doesn't exist`): reimportar `schema-caminho.sql` (após `make clean` ou `compose down -v`, o volume `mysql-data` some).

## Comandos legados relevantes

- Conectar TiDB: `mysql -h <DB_HOST> -P 4000 -u <DB_USER> -p --ssl-mode=VERIFY_IDENTITY --ssl-ca=.../ca-certificates.crt`
- Início: 2026-07-26. Contato: ecossistema@saberesancestrais.com
