# Documentação Didática — O Caminho: Saberes Ancestrais

## Índice

1. [Sugestões de Melhoria](#-sugestões-de-melhoria)
2. [Visão Geral do Projeto](#-visão-geral-do-projeto)
3. [Arquitetura](#-arquitetura)
4. [Banco de Dados](#-banco-de-dados)
5. [Backend (PHP)](#-backend-php)
6. [API REST](#-api-rest)
7. [Frontend (HTML + CSS + JS)](#-frontend)
8. [Fluxo de Dados Completo](#-fluxo-de-dados-completo)
9. [Segurança](#-segurança)
10. [Glossário](#-glossário)

---

## Sugestões de Melhoria

### Prioridade Alta

| Melhoria | Descrição | Por que fazer |
|----------|-----------|---------------|
| **Autoload com Composer (PSR-4)** | Substituir `require_once` manuais por autoloading | Facilita manutenção, permite usar bibliotecas externas |
| **Sistema de Rotas** | Criar um `Router.php` que mapeia URLs para controllers | Substitui a estrutura atual de arquivos soltos em `/api` |
| **Rate Limiter funcional** | Implementar o rate limit definido em `config/app.php` | Atualmente é apenas uma constante, não bloqueia requisições |
| **Proteção CSRF** | Tokens anti-CSRF nos formulários (newsletter, admin) | Previne ataques de falsificação de requisição |
| **Validação de email real** | Enviar email de confirmação para newsletter | Evita cadastros inválidos ou maliciosos |

### Prioridade Média

| Melhoria | Descrição |
|----------|-----------|
| **Testes automatizados** | PHPUnit para API, Jest para funções JS |
| **Logs centralizados** | Registrar erros, logins e ações importantes em arquivos datados |
| **Paginação no Admin** | Listar usuários, progresso e quizzes com paginação |
| **Modo Offline (PWA)** | Service Worker + Cache API para funcionar sem internet |
| **Exportar dados** | Progresso do usuário em CSV ou PDF |
| **Cache de queries** | APCu ou Redis para queries frequentes (categorias, lições) |

### Prioridade Baixa

| Melhoria | Descrição |
|----------|-----------|
| **Gráficos no Admin** | Chart.js para evolução visual dos dados |
| **Docker** | `docker-compose.yml` para ambiente de desenvolvimento padronizado |
| **API Versioning** | URLs como `/api/v1/` para evolução futura |
| **Sistema de Tags** | Tags nas lições para busca mais refinada |
| **Notas do usuário** | Campo de anotações em cada lição |

---

## Visão Geral do Projeto

```
O Caminho — Saberes Ancestrais
├── Frontend (HTML + CSS + JS)  →  O que o usuário vê
├── Backend (PHP)                →  A lógica do servidor
├── API (REST JSON)              →  A ponte entre frontend e backend
└── Banco de Dados (MySQL)      →  Onde os dados ficam guardados
```

### O que o site faz?

1. **Exibe conteúdo educativo** sobre 6 áreas do conhecimento (Gnose, Epigenética, Hermetismo, Kundalini, Teosofia, Coração)
2. **Permite marcar lições como estudadas** e acompanhar o progresso
3. **Oferece um quiz** para testar o conhecimento
4. **Guarda favoritos** do usuário
5. **Tem um timer de meditação**
6. **Tem painel admin** para acompanhar estatísticas

### Como o usuário é identificado?

Sem cadastro! O site usa um **cookie UUID** (identificador único) que dura 1 ano. Quando o usuário acessa o site pela primeira vez, um UUID é gerado e salvo no navegador. Todas as ações (progresso, favoritos, quiz) são associadas a esse UUID.

---

## Arquitetura

### Diagrama de Camadas

```
┌─────────────────────────────────────────────┐
│                Navegador                     │
│  (HTML + CSS + JavaScript)                   │
│  ├── app.js       → Lógica da SPA           │
│  ├── api.js        → Cliente HTTP (fetch)   │
│  └── estilo.css    → Visual                 │
└──────────────┬──────────────────────────────┘
               │  Requisições AJAX (JSON)
               ▼
┌─────────────────────────────────────────────┐
│              Apache (Servidor)               │
│                                              │
│  index.php  →  Renderiza a página inicial   │
│                                              │
│  /api/*.php  →  Endpoints REST              │
│  ├── progresso.php                           │
│  ├── favoritos.php                           │
│  ├── newsletter.php                          │
│  ├── quiz.php                                │
│  └── stats.php                               │
│                                              │
│  includes/  →  Código compartilhado          │
│  ├── Database.php   (Conexão PDO)           │
│  └── functions.php  (Funções úteis)         │
└──────────────┬──────────────────────────────┘
               │  Queries SQL (PDO)
               ▼
┌─────────────────────────────────────────────┐
│              MySQL (MariaDB)                 │
│  8 tabelas: categorias, licoes, usuarios,    │
│  progresso, favoritos, newsletter,           │
│  quiz_resultados, admin                      │
└─────────────────────────────────────────────┘
```

### MVC na prática

```
Model  →  includes/Database.php  (Classe que conecta e faz queries)
View   →  index.php  (HTML com PHP embutido)
Controller →  api/*.php  (Recebe requisição, usa Model, retorna JSON)
```

---

## Banco de Dados

### Conceito

Banco de dados é onde as informações ficam guardadas de forma organizada. Pense como uma **planilha gigante** com várias abas (tabelas).

### As 8 Tabelas

```
categorias          →  Os 6 temas principais (Gnose, Epigenética...)
    │
    ▼
licoes              →  19 lições, cada uma pertence a uma categoria
    │
    ▼
usuarios            →  Usuários identificados por UUID (cookie)
    │
    ├── progresso   →  Quais lições cada usuário concluiu
    ├── favoritos   →  Quais lições cada usuário favoritou
    └── quiz_resultados →  Resultados dos quizzes

newsletter          →  Emails de pessoas inscritas
admin               →  Login do painel administrativo
```

### Relacionamentos (FK - Foreign Keys)

```
licoes.categoria_id  →  categorias.id
progresso.usuario_id →  usuarios.id
progresso.licao_id   →  licoes.id
favoritos.usuario_id →  usuarios.id
favoritos.licao_id   →  licoes.id
quiz_resultados.usuario_id →  usuarios.id
```

Isso garante que não exista progresso de um usuário que não existe, ou favorito de uma lição que foi deletada.

### SQL Explicado

```sql
CREATE TABLE categorias (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE
);
```

- `INT` → Número inteiro
- `AUTO_INCREMENT` → A cada registro novo, o número sobe sozinho
- `PRIMARY KEY` → Chave principal, não pode repetir
- `VARCHAR(50)` → Texto de até 50 caracteres
- `NOT NULL` → Obrigatório
- `UNIQUE` → Não pode ter dois iguais

---

## Backend (PHP)

### O que é PHP?

PHP é uma linguagem que roda **no servidor**. Diferente do JavaScript (que roda no navegador), o PHP processa tudo antes de enviar o HTML para o usuário.

### Fluxo de uma requisição PHP

```
1. Usuário digita: http://localhost/caminho-saberes/
2. Apache recebe a requisição
3. Apache entrega para o PHP processar
4. PHP executa index.php:
   a. Inclui config/app.php (configurações)
   b. Inclui includes/Database.php (conexão com MySQL)
   c. Inclui includes/functions.php (funções auxiliares)
   d. Conecta ao MySQL e busca categorias e lições
   e. Gera HTML com os dados do banco
5. PHP devolve o HTML para o Apache
6. Apache envia o HTML para o navegador do usuário
```

### Database.php — Singleton Pattern

```php
class Database {
    private static $instance = null;  // Guarda a conexão
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();  // Cria conexão só 1 vez
        }
        return self::$instance;  // Reusa a conexão existente
    }
}
```

**Por que Singleton?** Para não abrir uma nova conexão com o MySQL a cada requisição. Uma conexão é criada na primeira chamada e reutilizada.

### PDO (PHP Data Objects)

PDO é a forma **segura** de conectar ao banco:

```php
// ❌ Inseguro (SQL Injection)
$sql = "SELECT * FROM usuarios WHERE email = '$email'";
// Se $email for: ' OR '1'='1
// Vira: SELECT * FROM usuarios WHERE email = '' OR '1'='1' ← RETORNA TUDO!

// ✅ Seguro (Prepared Statement)
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
// O MySQL trata '? OR '1'='1 como texto, não como comando SQL
```

### functions.php — Funções úteis

| Função | O que faz |
|--------|-----------|
| `json_response($data)` | Retorna JSON formatado para o frontend |
| `json_error($msg)` | Retorna JSON de erro |
| `validar_campos($fields, $data)` | Verifica se campos obrigatórios existem |
| `ler_corpo()` | Lê dados enviados (JSON ou formulário) |
| `esc_html($texto)` | Previne ataques XSS |
| `slugify($texto)` | "O Que é Gnose?" → "o-que-e-gnose" |
| `gerar_uuid()` | Gera identificador único universal |
| `obter_usuario_id($db)` | Pega ou cria UUID do usuário |

---

## API REST

### O que é uma API?

API (Application Programming Interface) é um **garçom** entre o frontend e o backend:

```
Frontend: "Quero uma lista de perguntas do quiz"  →  API  →  Backend: SELECT * FROM perguntas
Frontend: "Marque a lição 5 como concluída"       →  API  →  Backend: INSERT INTO progresso...
Frontend: ← JSON com a resposta                   ←  API  ←  Backend: { success: true }
```

### Métodos HTTP

| Método | O que significa | Exemplo |
|--------|----------------|---------|
| `GET` | **Pegar** dados | Listar favoritos |
| `POST` | **Criar** novo | Marcar lição |
| `DELETE` | **Remover** | Desmarcar lição |

### Endpoints

Cada arquivo em `/api/` é um endpoint:

```php
// /api/progresso.php
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Buscar progresso do usuário
}

if ($method === 'POST') {
    // Salvar progresso
}

if ($method === 'DELETE') {
    // Remover progresso
}
```

### Comunicação com JSON

O frontend envia dados assim:

```javascript
// frontend (api.js)
const response = await fetch('/api/progresso.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ licao_id: 5 })
});
const data = await response.json();  // { success: true, message: "..." }
```

O backend recebe e processa:

```php
// backend (progresso.php)
$data = json_decode(file_get_contents('php://input'), true);
$licao_id = $data['licao_id'];
$db->insert('progresso', ['usuario_id' => $id, 'licao_id' => $licao_id]);
json_response(['success' => true]);
```

---

## Frontend

### SPA (Single Page Application)

O site inteiro é uma **única página** (`index.php`). A navegação entre seções é feita com **âncoras** (#inicio, #conhecimento, #quiz) e JavaScript controla qual seção aparece.

### Estrutura do HTML

```
index.php
├── <header>        →  Logo + botões (busca, favoritos, tema)
├── <nav>           →  Menu de navegação (seções)
├── <main>
│   ├── #inicio     →  Introdução
│   ├── #cronograma →  Plano de estudos por nível
│   ├── #conhecimento →  Categorias + lições (accordion)
│   ├── #praticas   →  6 cards de práticas diárias
│   ├── #meditacao  →  Timer de meditação
│   ├── #jornada    →  3 visões filosóficas
│   ├── #quiz       →  Quiz interativo
│   └── #newsletter →  Formulário de email
├── <footer>        →  Informações finais
└── <script>        →  JS no final (performance)
```

### CSS — Modo escuro/claro

```css
:root {
    --bg: #f5f0eb;        /* Fundo claro */
    --text: #1a1a2e;      /* Texto escuro */
}

[data-theme="dark"] {
    --bg: #0a0a1a;        /* Fundo escuro */
    --text: #e0e0e0;      /* Texto claro */
}

body {
    background: var(--bg);
    color: var(--text);
}
```

O JavaScript alterna o atributo `data-theme` no `<html>` e salva a preferência no `localStorage`.

### JavaScript — api.js

```javascript
const API = {
    baseURL: '/caminho-saberes/api',

    async request(endpoint, options = {}) {
        const response = await fetch(this.baseURL + '/' + endpoint, {
            headers: { 'Content-Type': 'application/json' },
            ...options
        });
        return response.json();
    },

    async salvarProgresso(licaoId) {
        return this.request('progresso.php', {
            method: 'POST',
            body: JSON.stringify({ licao_id: licaoId })
        });
    }
};
```

### JavaScript — app.js

O `app.js` controla todas as interações:

| Função | Responsabilidade |
|--------|------------------|
| `Tema` | Alternar claro/escuro |
| `Accordion` | Abrir/fechar lições |
| `Tabs` | Navegar entre categorias |
| `ScrollSpy` | Destacar seção ativa no menu |
| `Timer` | Cronômetro de meditação |
| `Progresso` | Carregar/salvar/mostrar barras |
| `Busca` | Modal com filtro de lições |
| `Newsletter` | Enviar email |
| `Favoritos` | Adicionar/remover/listar |
| `Quiz` | Exibir perguntas, responder, ver resultado |

---

## Fluxo de Dados Completo

### Exemplo: Usuário marca uma lição como estudada

```
1. USUÁRIO clica em "Marcar como estudado"
       │
2. JavaScript (app.js) pega o ID da lição do atributo data-lesson
       │
3. Chama API.salvarProgresso(licao_id)
       │
4. api.js faz: fetch('POST /api/progresso.php', { licao_id: 5 })
       │
5. Apache recebe a requisição, executa progresso.php
       │
6. progresso.php:
   a. Inclui Database.php (conecta ao MySQL)
   b. Inclui functions.php (obter_usuario_id)
   c. Lê o UUID do cookie 'caminho_uuid'
   d. Busca ou cria usuário no banco
   e. Insere registro em progresso (usuario_id, licao_id)
   f. Retorna JSON: { success: true, message: "..." }
       │
7. JavaScript recebe { success: true }
       │
8. app.js atualiza a interface:
   - Muda o ícone da lição para ✅
   - Atualiza a barra de progresso da categoria
       │
9. USUÁRIO vê o feedback na tela
```

---

## Segurança

### As 4 camadas de proteção

```
1. Prepared Statements (PDO)  →  SQL Injection
2. htmlspecialchars()         →  XSS (Cross-Site Scripting)
3. password_hash() (bcrypt)   →  Senha do admin
4. .htaccess                  →  Bloqueio de arquivos
```

### .htaccess explicado

```apache
# Bloquear acesso a arquivos sensíveis
<FilesMatch "\.(sql|md|log|env|json)$">
    Require all denied
</FilesMatch>

# Não listar diretórios
Options -Indexes

# Cache de arquivos estáticos
<FilesMatch "\.(css|js|png|jpg)$">
    Header set Cache-Control "max-age=2592000, public"
</FilesMatch>
```

### Por que deletar install.php?

O `install.php` recria o banco de dados inteiro. Se um atacante acessá-lo, pode:
1. Resetar todo o banco
2. Mudar a senha do admin
3. Inserir dados maliciosos

---

## Glossário

| Termo | Significado |
|-------|-------------|
| **Apache** | Servidor web que entrega arquivos para o navegador |
| **XAMPP** | Pacote que já vem com Apache + MySQL + PHP prontos |
| **PDO** | Biblioteca do PHP para conectar em bancos de dados |
| **Singleton** | Padrão de projeto que cria apenas 1 instância de uma classe |
| **UUID** | Identificador único universal (ex: 550e8400-e29b-41d4-a716-446655440000) |
| **Cookie** | Pequeno arquivo que o navegador guarda no computador do usuário |
| **Session** | Dados temporários guardados no servidor |
| **JSON** | Formato leve para troca de dados (JavaScript Object Notation) |
| **REST** | Padrão de organização de URLs para APIs |
| **CRUD** | Create, Read, Update, Delete (criar, ler, atualizar, deletar) |
| **CORS** | Mecanismo de segurança para permitir requisições entre domínios |
| **XSS** | Ataque que insere scripts maliciosos no site |
| **SQL Injection** | Ataque que insere comandos SQL maliciosos |
| **bcrypt** | Algoritmo seguro para guardar senhas (lento de propósito) |
| **SPA** | Single Page Application (app de página única) |
| **AJAX** | Técnica de fazer requisições sem recarregar a página |
| **Hash** | Impressão digital de um dado (não dá pra voltar atrás) |
| **Debounce** | Técnica para não executar uma função muitas vezes seguidas |

---

> Documentação gerada para fins educacionais.
> Projeto: O Caminho — Saberes Ancestrais v2.0
