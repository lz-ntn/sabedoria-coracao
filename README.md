# 🌐 Sabedoria de Coração — Ecossistema Digital

Repositório principal para deploy de todo o ecossistema **Sabedoria de Coração**.

## 📁 Estrutura

```
sabedoria-deploy/
├── sites/                          # Sites estáticos (HTML/CSS/JS)
│   ├── portal/                     → Portal Saberes Ancestrais
│   ├── aprender/                   → SPA Interativa
│   ├── meditacao/                  → Timer de Meditação
│   ├── viver/                      → Viver sem Filtros
│   ├── cristianismo/               → Cristianismo Primitivo
│   └── curso/                      → Curso Jornada da Consciência
├── api/                            # Sites PHP
│   ├── portal-saberes/             → CMS Wiki (Portal Saberes)
│   └── caminho-saberes/            → Plataforma SPA (Caminho Saberes)
├── core/                           → Biblioteca compartilhada sabedoria/core
├── render.yaml                     → Blueprint Render (infra como código)
└── README.md                       → Você está aqui
```

## 🚀 Deploy Rápido

### 1. Pré-requisitos
- Conta [GitHub](https://github.com)
- Conta [Render](https://render.com)
- Cluster [TiDB Serverless](https://tidbcloud.com) (MySQL grátis)

### 2. Enviar para o GitHub
```bash
git init
git add .
git commit -m "feat: ecossistema Sabedoria de Coração"
git remote add origin https://github.com/seu-usuario/sabedoria-coracao.git
git branch -M main
git push -u origin main
```

### 3. Sites Estáticos (Render Dashboard)
Crie 6 Static Sites no Render apontando para as pastas em `sites/`.

### 4. Sites PHP (Render Blueprint)
Conecte o repositório como Blueprint — o `render.yaml` configura tudo.

### 5. Banco de Dados (TiDB)
Crie os databases `portal_saberes` e `caminho_saberes` no TiDB.
Importe os schemas SQL de cada projeto.

## 📖 Documentação Completa
Veja o guia detalhado no vault Obsidian:
`04-Criacao/Projetos/26-01 Sabedoria de Coração/GUIA-DEPLOY.md`

## 📬 Contato
ecossistema@saberesancestrais.com
