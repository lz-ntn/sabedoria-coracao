# AGENTS.md — Sabedoria de Coração

## Visão Geral

Ecossistema digital de saberes ancestrais: 6 sites estáticos + 2 aplicações PHP + biblioteca compartilhada.

```
sabedoria-deploy/
├── sites/                    # Sites estáticos (HTML/CSS/JS)
│   ├── portal/               → Portal Saberes Ancestrais (landing)
│   ├── aprender/             → SPA Interativa "Saberes de Coração"
│   ├── meditacao/            → Timer de Meditação + Chakras
│   ├── viver/                → "Viver sem Filtros" (reflexões)
│   ├── cristianismo/         → Cristianismo Primitivo
│   └── curso/                → Curso "Jornada da Consciência"
├── api/
│   ├── portal-saberes/       → CMS Wiki (PHP puro, v1.0.0)
│   └── caminho-saberes/      → Plataforma SPA (PHP puro, v2.0.0)
├── core/                     → Biblioteca compartilhada sabedoria/core
│   └── src/ {Config, Csrf, RateLimiter, functions}
├── render.yaml               → Blueprint Render (6 Static Sites)
└── AGENTS.md                 → Este arquivo
```

**Tech Stack:** PHP 8.3+ (puro, sem framework), MySQL/TiDB Serverless, Docker, Render, Railway

---

## Decisões de Arquitetura

| Decisão | Escolha | Motivo |
|---------|---------|--------|
| Framework | Nenhum (PHP puro) | Simplicidade, aprendizado, sem dependências pesadas |
| Banco | TiDB Serverless | MySQL compatível, tier gratuito generoso, SSL nativo |
| Deploy estático | Render Static Sites | Gratuito, CDN global, auto-deploy via git |
| Deploy PHP | Render PHP runtime | Integração com Blueprint, deploy simplificado |
| Biblioteca compartilhada | Composer path repo | Reuso de código entre as APIs sem publicar no Packagist |
| Autenticação | Sessões PHP + bcrypt | Simples e suficiente para o porte |
| Identificação usuário | UUID em cookie (caminho-saberes) | Sem login obrigatório, tracking anônimo |

---

## Plataforma de Deploy: Recomendação

### 🏆 Recomendado: Render + TiDB Serverless

**Por que Render?**
- Plano gratuito generoso (750h/mês por serviço)
- Static Sites com deploy automático via git
- PHP runtime nativo (gerencia Nginx + PHP-FPM por trás dos panos)
- Blueprint (infra como código) via `render.yaml`
- CDN global, SSL automático, health checks

**Por que TiDB Serverless?**
- MySQL 100% compatível
- 5GB de armazenamento gratuitos
- SSL/TLS nativo
- Sem necessidade de gerenciar servidor
- Ideal para projetos em crescimento

### Alternativas

| Plataforma | Prós | Contras | Indicação |
|------------|------|---------|-----------|
| **Railway** | Docker nativo, cron jobs, MySQL grátis | Free tier hiberna por inatividade | Bom para APIs com Docker |
| **Vercel** | Excelente para estáticos + Serverless | Sem PHP | Só para `sites/meditacao` (já configurado) |
| **VPS (DigitalOcean)** | Controle total, PHP-FPM real | Custo mensal, manutenção | Futuro: quando escalar |

**Evolução futura prevista:**
1. **Fase 1 (atual):** Render + TiDB — deploy inicial, validação
2. **Fase 2 (crescimento):** Docker + Nginx + PHP-FPM em Railway ou VPS
3. **Fase 3 (escala):** Separação em repositórios independentes, CI/CD, testes

---

## Passo a Passo: Configurar Deploy no Render + TiDB

### 1. Criar Contas

- [Render](https://render.com) — login com GitHub
- [TiDB Cloud](https://tidbcloud.com) — login com GitHub/Google

### 2. Criar Cluster TiDB Serverless

1. No [TiDB Cloud](https://tidbcloud.com), clique **"Create Cluster"** → **"Serverless"**
2. Escolha região **us-east-1** (próxima do Render free tier)
3. Após criar (~2 min), clique em **"Connect"**
4. Anote as credenciais:
   - `DB_HOST` (ex: `gateway01.us-east-1.prod.aws.tidbcloud.com`)
   - `DB_PORT`: `4000`
   - `DB_USER` (ex: `xxxxx.root`)
   - `DB_PASS`
5. Crie os databases via terminal ou painel:
   ```sql
   CREATE DATABASE portal_saberes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE DATABASE caminho_saberes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

### 3. Configurar Render — Sites Estáticos

1. No [Render Dashboard](https://dashboard.render.com), clique **"New +"** → **"Blueprint"**
2. Conecte o repositório GitHub
3. O Render vai ler o `render.yaml` raiz e criar 6 Static Sites:
   - `sabedoria-aprender` → `sites/aprender`
   - `sabedoria-viver` → `sites/viver`
   - `sabedoria-meditacao` → `sites/meditacao`
   - `sabedoria-portal` → `sites/portal`
   - `sabedoria-cristianismo` → `sites/cristianismo`
   - `sabedoria-curso` → `sites/curso`
4. O auto-deploy será ativado automaticamente

### 4. Configurar Render — APIs PHP

**Portal Saberes:**
1. **"New +"** → **"Web Service"**
2. Conecte o repositório, selecione `api/portal-saberes` como **Root Directory**
3. **Runtime:** `PHP`
4. **Build Command:** `composer install --no-dev --optimize-autoloader`
5. **Start Command:** `php database/migrate.php && php -S 0.0.0.0:10000 -t .`
6. **Health Check Path:** `/healthcheck`
7. Adicione as **Environment Variables** (copie do TiDB):

| Variável | Valor |
|----------|-------|
| `APP_ENV` | `production` |
| `DB_HOST` | `gateway01.us-east-1.prod.aws.tidbcloud.com` |
| `DB_PORT` | `4000` |
| `DB_NAME` | `portal_saberes` |
| `DB_USER` | (seu usuário TiDB) |
| `DB_PASS` | (sua senha TiDB) |
| `DB_CHARSET` | `utf8mb4` |
| `APP_URL` | `https://portal-saberes.onrender.com` |

**Caminho Saberes:**
1. Mesmo processo, **Root Directory:** `api/caminho-saberes`
2. **Start Command:** `php -S 0.0.0.0:10000 -t .`
3. **Health Check Path:** `/api/health`
4. Variáveis de ambiente (iguais, mudando `DB_NAME` para `caminho_saberes` e `APP_URL`)

### 5. Importar Schemas SQL

Conecte ao TiDB via MySQL client:

```bash
mysql -h <DB_HOST> -P 4000 -u <DB_USER> -p --ssl-mode=VERIFY_IDENTITY \
  --ssl-ca=/etc/ssl/certs/tidb-ca.pem
```

```sql
USE portal_saberes;
SOURCE api/portal-saberes/database/schema.sql;

USE caminho_saberes;
SOURCE api/caminho-saberes/database/schema.sql;
```

### 6. Verificar Deploy

- Acesse cada URL gerada pelo Render
- Teste health checks: `https://portal-saberes.onrender.com/healthcheck`
- Teste API: `https://caminho-saberes.onrender.com/api/health`

---

## Problemas Conhecidos (Pendências)

### 🔴 Críticos

- [ ] **`chmod -R 777 /app`** nos Dockerfiles → mudar para permissões restritivas
- [ ] **`php -S` em produção** → substituir por Nginx + PHP-FPM quando possível
- [ ] **SSL verification desabilitado** → `PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT = false` — habilitar com CA cert

### 🟡 Melhorias

- [ ] **Database.php duplicada** → extrair classe para `core/` e reutilizar
- [ ] **Senha admin hardcoded** (`admin123`) → gerar hash único no primeiro deploy
- [ ] **Newsletter não funcional** (só placeholder no frontend) → implementar endpoint
- [ ] **UUID tracking sem consentimento** → adicionar aviso LGPD/GDPR
- [ ] **Sem `.htaccess`** (mencionado no README mas não encontrado) → criar com regras de URL amigável e segurança
- [ ] **Sem controle de versão de migrations** → criar tabela `migrations` para trackear

### 🔵 Futuro

- [ ] Adicionar PHPUnit/Pest para testes
- [ ] Configurar GitHub Actions para CI/CD
- [ ] Separar repositórios por serviço
- [ ] Adicionar Redis/Valkey para cache
- [ ] Monitoramento com Sentry ou similar

---

## Changelog

| Data | Mudança | Responsável |
|------|---------|-------------|
| 2026-07-26 | Início do projeto — análise e documentação | AGENTS |
| 2026-07-26 | **Dockerfiles:** `chmod -R 777` substituído por `chown app:app` + permissões 755/775; criado usuário não-root `app`; adicionado `USER app` | AGENTS |
| 2026-07-26 | **SSL:** Verificação SSL ativada via CA cert TiDB (cis-2025.pem) em ambos os Dockerfiles | AGENTS |
| 2026-07-26 | **Database:** Classe `Database` extraída para `core/src/Database.php` (namespace `Core`); APIs usam `class Database extends \Core\Database` | AGENTS |
| 2026-07-26 | **Migrations:** Sistema versionado `\Core\Migration` com tabela `migrations`; SQL renomeados (`001_core_schema.sql`, `002_*.sql`); `migrate.php` criado no caminho-saberes | AGENTS |
| 2026-07-26 | **.htaccess:** Criado para ambas APIs com regras de segurança (bloqueio de dirs sensíveis, headers, URL rewriting) | AGENTS |
| 2026-07-26 | **Newsletter:** Endpoint `/api/newsletter.php` no portal-saberes; formulário no index.php agora funcional (fetch API); migration `003_newsletter.sql` | AGENTS |
| 2026-07-26 | **LGPD:** Banner de consentimento no caminho-saberes; UUID tracking só cria cookie após aceite; endpoints tratam `$usuario_id = null` | AGENTS |
| 2026-07-26 | **Railway:** `railway.json` corrigido (remove referência a `router.php` inexistente) | AGENTS |
| 2026-07-26 | **core/composer.json:** Adicionadas dependências `ext-pdo`, `ext-pdo_mysql`, `ext-json`, `ext-mbstring` | AGENTS |
| 2026-07-26 | **SSL fix Render:** URL `truststore.pki.pingcap.com` não resolvia no build; removido curl CA custom e passou a usar CA bundle do sistema (`ca-certificates.crt`) | AGENTS |
| 2026-07-26 | **migrate.php:** Adicionado SSL com CA bundle do sistema para conexão com TiDB Serverless (exigência do TiDB) | AGENTS |
| 2026-07-26 | **Infra:** `docker-compose.yml` (dev), `docker-compose.prod.yml` (Nginx+FPM), `Makefile`, `.github/workflows/ci.yml`, `.env.*.example` | AGENTS |
| 2026-07-26 | **Dockerfile.fpm:** Criados para ambas APIs usando `php:8.3-fpm` com OPcache + php.ini production | AGENTS |
| 2026-07-26 | **Nginx:** Configuração com virtual hosts pra portal e caminho, static sites, bloqueio de arquivos sensíveis | AGENTS |

---

## Comandos Úteis

```bash
# Testar conexão TiDB
mysql -h <DB_HOST> -P 4000 -u <DB_USER> -p --ssl-mode=VERIFY_IDENTITY \
  --ssl-ca=/etc/ssl/certs/tidb-ca.pem -e "SHOW DATABASES;"

# Rodar localmente com Docker (dev)
docker compose up -d
# Acessar: http://localhost:8080 (portal), http://localhost:8081 (caminho)

# Rodar localmente com Nginx + PHP-FPM (prod-like)
docker compose -f docker-compose.prod.yml up -d

# Makefile
make up        # docker compose up -d
make logs      # logs em tempo real
make lint      # verificar sintaxe PHP
make deploy    # git push

# Instalar dependências Composer local
cd api/portal-saberes && composer install
```

---

## Estrutura de Deploy

```
sabedoria-deploy/
├── docker-compose.yml              # Ambiente de desenvolvimento
├── docker-compose.prod.yml         # Ambiente produção-like (Nginx + PHP-FPM)
├── Makefile                        # Comandos facilitados
├── .github/workflows/ci.yml        # CI: PHP lint + Composer validate
├── .env.portal.example             # Env vars para portal-saberes
├── .env.caminho.example            # Env vars para caminho-saberes
└── docker/
    ├── nginx/
    │   ├── nginx.conf              # Config principal do Nginx
    │   ├── static.conf             # Para servir sites estáticos
    │   └── sites-enabled/
    │       ├── portal.conf         # VHost para portal-saberes
    │       └── caminho.conf        # VHost para caminho-saberes
    └── php/
        └── opcache.ini             # Otimização OPcache
```

---

## Contato

ecossistema@saberesancestrais.com
