# Saberia-deploy — Guia Completo de Administração

> Guia de operação, conteúdo e manutenção dos apps Portal Saberes (:8083),
> Caminho Saberes (:8081), static (:8082) e MySQL.
> Atualizado: 21/08/2026 (após restauração das tabelas)

---

## 1. Visão geral da stack

| Container | Função | Porta | Status check |
|---|---|---|---|
| `sabedoria-deploy_mysql_1` | Banco MySQL (root/root) | interna 3306 | healthy |
| `sabedoria-deploy_portal-saberes_1` | App PHP — Portal Saberes Ancestrais | **:8083** | http://localhost:8083 |
| `sabedoria-deploy_caminho-saberes_1` | App PHP — O Caminho / Saberes Ancestrais | **:8081** | http://localhost:8081 |
| `sabedoria-deploy_static_1` | Nginx — arquivos estáticos (`aprender/`, `cristianismo/`...) | **:8082** | http://localhost:8082 |

- Código-fonte: `/home/lz-ntn/Área de trabalho/sabedoria-deploy/`
- Apps montam os volumes `./api/<app>:/app` e `./core:/app/core` — editar o
  código local reflete no container na hora (ambiente dev).
- Monitorado pelo OPS Dashboard (`gestor.sh status docker`).

---

## 2. Painéis de administração

### Portal Saberes — `/admin/`
Acesse: `http://localhost:8083/admin/`

Seções disponíveis:
- **Artigos** — criar/editar artigos da wiki
- **Categorias** — organizar artigos por tema
- **Comentários** — moderar comentários
- **Mídia** — upload de imagens/arquivos
- **Páginas** — páginas institucionais fixas
- **Usuários** — contas com nível de acesso

Usuário padrão criado pelo schema:
```
email: admin@saberes.com   (senha: a que você definiu no primeiro acesso)
```

### Caminho Saberes — `/admin/`
Acesse: `http://localhost:8081/admin/`

Usuário na tabela `admin` (`usuario` + `senha` bcrypt). Se não souber a senha,
redefina gerando um hash novo:

```bash
# gerar hash bcrypt para uma senha nova
docker run --rm php:8.2-cli -r "echo password_hash('SUA_NOVA_SENHA', PASSWORD_BCRYPT), PHP_EOL;"
# atualizar no banco
docker exec -i sabedoria-deploy_mysql_1 mysql -uroot -proot caminho_saberes \
  -e "UPDATE admin SET senha='<COLE_O_HASH_AQUI>' WHERE usuario='admin';"
```

---

## 3. Populando conteúdo

### Portal Saberes
1. Entre em `/admin/` → **Categorias** → crie as categorias-base
   (ex.: História, Espiritualidade, Estudos...).
2. **Artigos** → Novo → escolha categoria, escreva o conteúdo, publique.
3. **Mídia** → envie imagens antes de referenciá-las nos artigos.

### Caminho Saberes
1. `/admin/` → **Categorias** → trilhas de aprendizado (o schema já vem
   com categorias de exemplo).
2. **Lições** → vincule à categoria, defina `nível`, `duração_min`, `ordem`.
3. Alunos veem progresso/favoritos/quiz na área pública.

---

## 4. Manutenção do dia a dia

```bash
# Status geral (containers, portas, túnel)
/home/lz-ntn/vidaReal/novoComeco/scripts/gestor.sh status

# Reiniciar um app após mudar código (volumes montam ao vivo; restart
# é necessário só se mexer em config/composer)
docker restart sabedoria-deploy_portal-saberes_1
docker restart sabedoria-deploy_caminho-saberes_1

# Logs de um app
docker logs sabedoria-deploy_portal-saberes_1 --tail 50

# Acessar o banco manualmente
docker exec -it sabedoria-deploy_mysql_1 mysql -uroot -proot portal_saberes
```

---

## 5. Banco de dados — backup e restauração

### Backup (faça antes de qualquer mudança!)
```bash
docker exec sabedoria-deploy_mysql_1 mysqldump -uroot -proot \
  --databases portal_saberes caminho_saberes > ~/backup-sabedoria-$(date +%F).sql
```

### Restaurar estrutura (o que foi feito em 21/08/2026)
Os bancos existiam mas estavam sem tabelas (`categorias` etc.). Os schemas
idempotentes recriam tudo com `IF NOT EXISTS`, preservando dados:

```bash
docker exec -i sabedoria-deploy_mysql_1 mysql -uroot -proot \
  < "/home/lz-ntn/Área de trabalho/sabedoria-deploy/schema-portal.sql"
docker exec -i sabedoria-deploy_mysql_1 mysql -uroot -proot \
  < "/home/lz-ntn/Área de trabalho/sabedoria-deploy/schema-caminho.sql"
```

⚠️ Se um dia os containers forem recriados do zero (`docker-compose down -v`),
o volume `mysql-data` some — nesse caso o `./docker/init/001-create-databases.sql`
recria os bancos vazios e **os schemas acima precisam ser importados de novo**.

### Sintoma clássico de banco vazio
```
Fatal error: Uncaught PDOException: SQLSTATE[42S02]:
Base table or view not found: 1146 Table 'xxx.categorias' doesn't exist
```
→ Solução: reimportar o schema correspondente (comando acima).

---

## 6. Problemas conhecidos resolvidos

| Data | Problema | Solução |
|---|---|---|
| 18/08/2026 | SSL forçado em dev derrubava os apps; conflito de porta 8080 | `migrate.php` sem SSL fora de produção; portal movido p/ :8083 |
| 21/08/2026 | Tabelas ausentes (`categorias`) após recriação do volume MySQL | Reimportar `schema-portal.sql` e `schema-caminho.sql` |
