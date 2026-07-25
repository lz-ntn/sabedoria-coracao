# 🕉️ O Caminho — Saberes Ancestrais (PHP + MySQL)

**Versão 2.0** — Projeto full-stack refatorado: Node.js/Express → **PHP + MySQL**

## 📋 Índice

1. [O que é este projeto?](#-o-que-é-este-projeto)
2. [Tecnologias](#-tecnologias)
3. [Como rodar no XAMPP](#-como-rodar-no-xampp)
4. [Como publicar no InfinityFree](#-como-publicar-no-infinityfree)
5. [Estrutura do Projeto](#-estrutura-do-projeto)
6. [API Endpoints](#-api-endpoints)
7. [Conceitos Aprendidos](#-conceitos-aprendidos)

---

## 🎯 O que é este projeto?

Um **site completo** sobre saberes ancestrais (Gnose, Epigenética, Hermetismo, Kundalini, Teosofia, Coração) com:

- ✅ **Frontend**: HTML + CSS + JavaScript moderno
- ✅ **Backend**: PHP 8+ com MySQL
- ✅ **Painel Admin** para acompanhar dados
- ✅ **Quiz interativo** com perguntas sobre os temas
- ✅ **Sistema de progresso** (lições concluídas)
- ✅ **Newsletter** (cadastro de emails)
- ✅ **Favoritos** (salvar lições preferidas)
- ✅ **Timer de meditação** integrado
- ✅ **Modo escuro/claro**
- ✅ **Responsivo** (funciona em celular)

---

## 🛠️ Tecnologias

| Tecnologia | Versão | Para que serve |
|------------|--------|----------------|
| **PHP** | 8.0+ | Lógica do servidor, API REST |
| **MySQL** | 5.7+ | Banco de dados relacional |
| **Apache** | 2.4+ | Servidor web (XAMPP) |
| **HTML5** | - | Estrutura da página |
| **CSS3** | - | Estilos, animações, responsividade |
| **JavaScript** | ES6+ | Interatividade, chamadas AJAX |
| **PDO** | - | Conexão segura com MySQL |

---

## 🚀 Como rodar no XAMPP

### Passo 1: Baixar e instalar o XAMPP

1. Acesse [https://www.apachefriends.org](https://www.apachefriends.org)
2. Baixe o XAMPP para seu sistema (Windows, Linux, macOS)
3. Instale com as opções padrão (Apache + MySQL)

### Passo 2: Copiar o projeto

```bash
# Caminho no Windows
C:\xampp\htdocs\caminho-saberes\

# Caminho no Linux
/opt/lampp/htdocs/caminho-saberes/

# Caminho no macOS
/Applications/XAMPP/htdocs/caminho-saberes/
```

**Alternativa:** Copie a pasta `caminho-saberes/` para dentro da pasta `htdocs` do XAMPP.

### Passo 3: Iniciar o XAMPP

1. Abra o **XAMPP Control Panel**
2. Clique em **Start** no Apache
3. Clique em **Start** no MySQL
4. Verifique se os dois estão verdes ✅

### Passo 4: Instalar o banco de dados

1. Abra o navegador
2. Acesse: **http://localhost/caminho-saberes/install.php**
3. Clique no botão **"Instalar Banco de Dados"**
4. ✅ Pronto! O banco foi criado com todas as tabelas e dados iniciais

### Passo 5: Acessar o site

- **Site**: http://localhost/caminho-saberes/
- **Admin**: http://localhost/caminho-saberes/admin/
- **Login admin**: `admin` / `admin123`

### Passo 6: Verificar se está funcionando

```bash
# Testar a API
curl http://localhost/caminho-saberes/api/stats.php

# Deve retornar um JSON com estatísticas
```

---

## 🌐 Como publicar no InfinityFree

### O que é InfinityFree?

[InfinityFree](https://infinityfree.com/) é uma hospedagem **100% gratuita** com:
- PHP 8.3 ✅
- MySQL (via iFastNet) ✅
- SSL grátis ✅
- Subdomínio grátis (ex: `seuprojeto.infinityfreeapp.com`) ✅
- Sem anúncios obrigatórios ✅
- 5 GB de espaço ✅

### Passo 1: Criar conta

1. Acesse [https://infinityfree.com](https://infinityfree.com)
2. Clique em **"Get Free Hosting"**
3. Preencha com seu email e crie uma senha
4. Confirme o email de ativação

### Passo 2: Criar o site

1. No painel, clique em **"Create Account"** (ou "+ Add Account")
2. Escolha um **subdomínio** (ex: `caminho-saberes`)
3. Selecione o domínio gratuito (ex: `caminho-saberes.infinityfreeapp.com`)
4. Anote o **usuário FTP** e a **senha** que serão gerados

### Passo 3: Criar o banco MySQL

1. No painel do InfinityFree, vá em **"MySQL Databases"**
2. Clique em **"Create MySQL Database"**
3. Anote os dados:
   - **Database Name** (ex: `if0_12345678_caminho`)
   - **Username** (ex: `if0_12345678`)
   - **Password** (a que você escolheu)
   - **Server** (ex: `sql123.infinityfree.com`)

### Passo 4: Configurar o projeto

Edite o arquivo `config/database.php` com os dados do InfinityFree:

```php
define('DB_HOST', 'sql123.infinityfree.com');  // 👈 Host do MySQL
define('DB_PORT', '3306');
define('DB_NAME', 'if0_12345678_caminho');     // 👈 Nome do banco
define('DB_USER', 'if0_12345678');             // 👈 Usuário
define('DB_PASS', 'sua_senha_aqui');           // 👈 Senha
define('DB_CHARSET', 'utf8mb4');
```

E edite `config/app.php`:

```php
define('APP_URL', 'https://caminho-saberes.infinityfreeapp.com');
define('APP_ENV', 'production');
```

### Passo 5: Enviar os arquivos via FTP

Você pode usar **FileZilla** (gratuito) ou o gerenciador de arquivos do próprio InfinityFree:

1. **FileZilla** (recomendado):
   - Host: `ftp.infinityfree.com`
   - Usuário: seu usuário FTP
   - Senha: sua senha FTP
   - Porta: 21

2. Envie **TODOS** os arquivos da pasta `caminho-saberes/` para a raiz do servidor

### Passo 6: Instalar o banco remoto

1. Acesse: **https://caminho-saberes.infinityfreeapp.com/install.php**
2. Clique em **"Instalar Banco de Dados"**
3. ⚠️ **IMPORTANTE:** Após instalar, delete o arquivo `install.php` por segurança!

### Passo 7: Pronto! 🎉

- **Site**: https://caminho-saberes.infinityfreeapp.com/
- **Admin**: https://caminho-saberes.infinityfreeapp.com/admin/
- **Login admin**: `admin` / `admin123`

---

## 📁 Estrutura do Projeto

```
caminho-saberes/                    # Raiz do projeto
├── index.php                       # Página principal (SPA)
├── install.php                     # Instalador do banco (DELETE após uso!)
├── .htaccess                       # Configurações do Apache
│
├── config/                         # Configurações
│   ├── database.php                # Conexão com MySQL
│   └── app.php                     # Constantes da aplicação
│
├── database/                       # Banco de dados
│   └── schema.sql                  # Script SQL completo
│
├── api/                            # API RESTful
│   ├── progresso.php               # CRUD progresso
│   ├── newsletter.php              # CRUD newsletter
│   ├── favoritos.php               # CRUD favoritos
│   ├── quiz.php                    # Quiz + resultados
│   └── stats.php                   # Estatísticas
│
├── includes/                       # Código PHP compartilhado
│   ├── Database.php                # Classe de conexão PDO
│   └── functions.php               # Funções auxiliares
│
├── admin/                          # Painel administrativo
│   └── index.php                   # Dashboard + login
│
└── assets/                         # Recursos frontend
    ├── css/
    │   └── estilo.css              # Estilos do site
    └── js/
        ├── api.js                  # Cliente JS da API
        └── app.js                  # Lógica JavaScript
```

---

## 🔌 API Endpoints

Todas as rotas da API retornam JSON.

### Progresso

| Método | URL | Descrição |
|--------|-----|-----------|
| `GET` | `/api/progresso.php` | Buscar progresso do usuário |
| `POST` | `/api/progresso.php` | Marcar lição como concluída |
| `DELETE` | `/api/progresso.php?licao_id=5` | Desmarcar lição |
| `DELETE` | `/api/progresso.php?reset=1` | Resetar todo progresso |

**POST body:**
```json
{ "licao_id": 5, "categoria": "gnose" }
```

### Newsletter

| Método | URL | Descrição |
|--------|-----|-----------|
| `GET` | `/api/newsletter.php` | Listar inscritos |
| `POST` | `/api/newsletter.php` | Inscrever email |

**POST body:**
```json
{ "email": "usuario@email.com" }
```

### Favoritos

| Método | URL | Descrição |
|--------|-----|-----------|
| `GET` | `/api/favoritos.php` | Listar favoritos |
| `POST` | `/api/favoritos.php` | Adicionar favorito |
| `DELETE` | `/api/favoritos.php?licao_id=5` | Remover favorito |

**POST body:**
```json
{ "licao_id": 5 }
```

### Quiz

| Método | URL | Descrição |
|--------|-----|-----------|
| `GET` | `/api/quiz.php` | Buscar perguntas |
| `GET` | `/api/quiz.php?categoria=gnose` | Filtrar por categoria |
| `POST` | `/api/quiz.php` | Salvar resultado |

### Estatísticas

| Método | URL | Descrição |
|--------|-----|-----------|
| `GET` | `/api/stats.php` | Estatísticas gerais |

---

## 🎓 Conceitos Aprendidos

### 1. PHP + MySQL (Backend)

**PDO (PHP Data Objects)** — Conexão segura com o banco:

```php
// Antes (inseguro - SQL injection)
$query = "SELECT * FROM usuarios WHERE email = '$email'";

// Depois (seguro - prepared statement)
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
```

### 2. API RESTful

Padrão de URL que representa recursos:

```
GET    /api/progresso     → "Quero ver meu progresso"
POST   /api/progresso     → "Quero salvar um progresso"
DELETE /api/progresso     → "Quero remover um progresso"
```

### 3. JSON

Formato universal para troca de dados entre frontend e backend:

```json
{
  "success": true,
  "message": "Lição marcada como concluída"
}
```

### 4. Session vs Cookie

- **Cookie**: Armazena UUID do usuário no navegador (1 ano)
- **Session**: Armazena dados temporários no servidor (login admin)

### 5. MVC Simplificado

```
Model (Database.php)  →  Dados (MySQL)
View (index.php)      →  Interface (HTML)
Controller (api/*.php)→  Lógica entre Model e View
```

### 6. Segurança

| Prática | Onde | Para que |
|---------|------|----------|
| Prepared Statements | `includes/Database.php` | Prevenir SQL Injection |
| htmlspecialchars() | `includes/functions.php` | Prevenir XSS |
| password_hash() | `database/schema.sql` | Hash de senhas |
| .htaccess | Raiz | Bloquear arquivos sensíveis |
| DELETE install.php | - | Evitar reinstalação maliciosa |

---

## 🧪 Testando a API

Use o terminal para testar:

```bash
# Testar conexão
curl http://localhost/caminho-saberes/api/stats.php

# Salvar progresso
curl -X POST http://localhost/caminho-saberes/api/progresso.php \
  -H "Content-Type: application/json" \
  -d '{"licao_id": 1, "categoria": "gnose"}'

# Buscar progresso
curl http://localhost/caminho-saberes/api/progresso.php

# Buscar perguntas do quiz
curl http://localhost/caminho-saberes/api/quiz.php
```

---

## ❓ Solução de Problemas

| Problema | Causa | Solução |
|----------|-------|---------|
| `Erro 500` no install.php | MySQL não rodando | Inicie o MySQL no XAMPP |
| `PDOException` | Dados do banco errados | Verifique `config/database.php` |
| `Error 403` | Permissão de pasta | Chmod 755 na pasta (Linux) |
| AJAX não funciona | URL da API errada | Verifique `APP_URL` no `config/app.php` |
| CSS quebrado | Cache do navegador | Ctrl+F5 para limpar cache |
| Admin não loga | Senha errada | Admin: `admin` / Senha: `admin123` |

---

## 📚 Próximos Passos

- [ ] Adicionar **autenticação real** (JWT ou OAuth)
- [ ] Migrar para **SQLite** (mais simples, sem servidor)
- [ ] Adicionar **upload de áudios** para as lições
- [ ] Criar **páginas de erro customizadas** (404, 500)
- [ ] Adicionar **cache com Redis** ou APCu
- [ ] Implementar **modo offline** (Service Worker)
- [ ] Adicionar **testes automatizados** (PHPUnit)

---

## 📄 Licença

Projeto educacional — livre para uso, estudo e modificação.

---

> **Nota:** Versão 2.0 — Projeto originalmente em Node.js/Express refatorado para PHP + MySQL.
> Criado como material didático para estudos de desenvolvimento web full-stack.
