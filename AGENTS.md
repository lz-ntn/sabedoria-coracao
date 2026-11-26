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

## Fonte da verdade e deploys (decisão 22/11/2026)

- **Este repo (`lz-ntn/sabedoria-coracao`) é o canônico** — único lugar para dev e deploy. Atalho limpo: `~/vidaReal/novoComeco/sabedoria-de-coracao/sabedoria-deploy`. URLs oficiais: `sabedoria-{aprender,viver,meditacao,portal,cristianismo,curso}.onrender.com` + API `caminho-saberes`.
- **Projeto distinto:** `lz-ntn/saberes-de-coracao` (SPA antigo, código na partição Manjaro) é servido por `saberesdecoracao.up.railway.app` **e** `saberes-de-coracao.onrender.com` com bytes idênticos (hash `bf5832de…`). Não confundir com este repo — os domínios oficiais aqui são `sabedoria-*`, não `saberes-*`.
- Railway foi **desativado em 22/11/2026**: removido o domínio público do service `saberes-de-coracao` (projeto `Saberes de Coração`); a URL hoje responde 404. Projeto/service foram mantidos (reversível). Render é a produção oficial.
- Em 22/11/2026 as cópias antigas foram **arquivadas por rename reversível**: pastas na partição Manjaro e `/opt/lampp/htdocs/{caminho,portal}-saberes` ganharam sufixo `_LEGADO_ARCHIVADO_2026-11-22`; `api/portal-saberes/railway.json` → `railway.json.LEGADO`. Detalhes em `~/vidaReal/novoComeco/sabedoria-de-coracao/local.md`.

## Estrutura de conteúdo no Caminho

- `licoes` ganhou colunas na migration `003_portal_merge.sql`: `tipo` (`licao`|`artigo`), `resumo`, `tags`, `imagem`,
  `fonte`, `autor_id`, `publicado_em`, `views`; tabela nova `discussoes` (comentários em camisetas);
  views `vw_artigos_publicos` e `vw_licoes_cronograma`. **Sem FULLTEXT** (o TiDB Serverless exige réplica columnar paga);
  a busca em `biblioteca.php` usa `LIKE` em `titulo/conteudo/tags/resumo`.
- **APIs:** `api/biblioteca.php` (lista/busca/detalhe de artigos `tipo='artigo'`), `api/discussoes.php` (comentários),
  `api/progresso.php` (agora filtra `tipo='licao'`); `stats.php`, `favoritos.php`, `quiz.php`, `newsletter.php`.
- Admin (`api/caminho-saberes/admin/index.php`) tem abas **Lições** e **Artigos** + CRUD de categorias + ação de importação.
- **Home dinâmica:** seção `#inicio` em `index.php` consome `/api/stats.php` (contadores animados via `HomeDinamica` em `assets/js/app.js`) e `/api/biblioteca.php?per_page=3&ordenar=recente` (cards "Adicionados recentemente"). Busca rápida da home replica o termo em `#biblioteca-search` e rola até a Biblioteca. `abrirArtigo()` é exposto em `window` pelo script inline do `index.php` para os cards recentes. A contagem animada sempre escreve o valor final por `setTimeout` — sem isso, aba em segundo plano/congelamento do `requestAnimationFrame` deixa o número no meio da contagem.
- **Paginação da Biblioteca:** `carregarBiblioteca(opcoes)` usa `{ reset: true }` para busca/filtro/entrada na seção e **nenhuma opção** para Anterior/Próxima. `bibliotecaPage` é sempre a página exibida (atribuída de `data.page`, nunca `data.page + 1`). Antes, o `+1` fazia "Próxima" saltar para a última página e "Anterior" não voltar; o guard `!append && !hasMore` travava os filtros depois de uma busca sem resultado. Testado com Playwright: 1→2→1, busca vazia→busca "gnose" (9 cards), zero erros JS.
- **Nunca fixar prefixo da API no JS** (26/11/2026): `assets/js/api.js` tinha `baseURL: '/caminho-saberes/api'` e em produção (app serve na raiz) progresso, quiz, favoritos, newsletter e discussões davam **404** — o console mostrava `Usando localStorage como fallback` e a tela parecia travada. Agora `baseURL` é getter de `window.APP_DATA.url_api`. Complementarmente, `url_api` no `index.php` é **relativo à origem** (`parse_url(APP_URL, PHP_URL_PATH) . '/api'`): injetar `APP_URL` absoluto (`http://localhost:8081`) causava **CORS** quando a página era aberta por `127.0.0.1` ou outro host. Regra: prefixo vem do servidor, nunca do cliente; URL relativa evita preflight. Validado com Playwright: `/api` nos 4 endpoints, zero respostas 4xx, console limpo.
- **Dados embutidos no deploy:** `api/caminho-saberes/data/` contém `dados-unificados.json`, `jesus-posts/` (23 Markdown) e `blavatsky/` (5 lições HTML). O Dockerfile `COPY . .` leva tudo para `/app/data/`, que é onde os importadores procuram primeiro — sem depender de caminhos do host.
- **Popular de uma vez:** `php database/popular.php` roda os 4 importadores em processos separados (seed, saberes_wiki, jesus, blavatsky). Todos são idempotentes (pulam slug existente). No admin: aba **📦 Popular Conteúdo** (`?acao=popular`) executa o mesmo script e mostra o log.
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

- SQL numerados `001_*, 002_*, 003_*` em `api/caminho-saberes/database/` (a tabela `migrations` é criada pelo próprio `migrate.php`, não por `\Core\Migration`).
- Rodam no start do entrypoint e no `startCommand` do Render. Manual: `make migrate-caminho-saberes`, ou `docker-compose exec caminho-saberes php database/migrate.php`.
- **`migrate.php` é idempotente de verdade (corrigido em 22/11/2026):** checa `SELECT ... FROM migrations` **antes** de executar cada SQL e imprime `Já aplicada: ...`. Não depende de "engolir erro de coluna duplicada" como antes.
- **Um batch por execução:** todas as migrations novas entram no mesmo batch; as antigas ficaram nos batches 1/2/3 (histórico preservado, não reescrito).
- **Conferência de schema real:** antes de confiar na tabela `migrations`, o script checa `information_schema` — se `licoes` não existe reaplica `001`; se `tipo`/`resumo` faltam, reaplica `003` (cobre banco recriado no TiDB).
- **Deploy Render:** Web Service Docker com Root Directory `api/caminho-saberes` (não `api/portal-saberes`); exige env vars `APP_ENV`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_SSL_CA`, `DB_CHARSET`, `APP_URL`. URL: `https://saberes-de-coracao.onrender.com`.
- **SSL condicional:** `APP_ENV=development` → `--ssl=0` (MySQL local); fora de dev → `--ssl-mode=VERIFY_IDENTITY --ssl-ca=<CA>` (ou `REQUIRED` se não achar CA), via `db_ssl_options()`. O TiDB Serverless **exige** TLS.
- **Sempre faça backup antes de migrar:**
  ```bash
  docker exec sabedoria-deploy_mysql_1 mysqldump -uroot -proot \
    --databases portal_saberes caminho_saberes > ~/backup-sabedoria-$(date +%F).sql
  ```
- O banco `portal_saberes` **não deve ser dropado**: está vazio, mas `database/import_portal.php` e a aba de importação do admin conectam nele para trazer artigos legados. É mantido de propósito (ver topo deste arquivo).

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
- **Provisionamento sem Shell (Render Free):** `admin/setup.php` faz tudo por HTTP, em uma chamada, porque o plano free não libera Shell. Defina `ADMIN_SETUP_TOKEN` (e, se quiser login determinístico, `ADMIN_USER`/`ADMIN_PASSWORD`/`ADMIN_EMAIL`) no painel → redeploy → abra `/admin/setup.php?token=<segredo>`. A ação padrão (`tudo`) cria/redefine o admin, roda `database/popular.php` e devolve o acervo em JSON; `&acao=admin|popular|status` faz cada parte. Sem `ADMIN_PASSWORD`/`&senha=`, um admin **já existente não tem a senha trocada** (evita reset por acidente); `&forcar=1` gera senha nova e a mostra uma única vez. Token ausente ou errado devolve o mesmo 403 (não vira oráculo). **Depois de usar, apague `ADMIN_SETUP_TOKEN` e redeploy** — o endpoint volta a responder 403.
- **Título/resumo de artigo importado não pode vir do nome do arquivo cru:** `import_jesus_artigos.php` humaniza o slug quando falta frontmatter (`mensagem-dos-mestres` → "Mensagem dos Mestres", preposições minúsculas) e deriva o resumo do início do texto. Sem isso a home mostrava um card chamado `mensagem-dos-mestres` com resumo vazio.
- **LGPD:** tracking no caminho-saberes só cria cookie após aceite do banner (`lgpd_consent`); os endpoints tratam `$usuario_id = null`. Preserve esse contrato.
- `.htaccess` existe em ambas as APIs (segurança + URL rewriting).
- **Sintoma clássico de banco vazio** (tabela `xxx.categorias doesn't exist`): reimportar `schema-caminho.sql` (após `make clean` ou `compose down -v`, o volume `mysql-data` some).

## Comandos legados relevantes

- Conectar TiDB: `mysql -h <DB_HOST> -P 4000 -u <DB_USER> -p --ssl-mode=VERIFY_IDENTITY --ssl-ca=.../ca-certificates.crt`
- Início: 2026-07-26. Contato: ecossistema@saberesancestrais.com
