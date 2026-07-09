# Deploy — SISTEMA-GYM / MGTEAM

## Pré-requisitos no servidor

- PHP 8.2+ com extensões: `mbstring`, `pdo_mysql`, `bcmath`, `gd`, `zip`, `pcntl`
- Composer 2, Node 20 (apenas se o build de assets for no servidor)
- MySQL 8
- Supervisor (worker de fila)
- Nginx/Apache apontando para `public/`

## Secrets do GitHub Actions (Deploy)

| Secret | Descrição |
|--------|-----------|
| `DEPLOY_HOST` | Hostname ou IP do servidor |
| `DEPLOY_USER` | Usuário SSH |
| `DEPLOY_PATH` | Path absoluto do app (ex: `/var/www/SISTEMA-GYM`) |
| `DEPLOY_SSH_KEY` | Chave privada SSH (PEM) |

Sem esses secrets, o workflow `deploy.yml` valida o build e **pula** o rsync.

## Fluxo

1. Push em `main` → `ci.yml` (composer, npm build, migrate em MySQL de teste, tests)
2. `deploy.yml` → build de produção → rsync → `./deploy.sh --skip-assets` no servidor

Deploy manual: Actions → **Deploy** → Run workflow.

## Pós-deploy local/manual

```bash
chmod +x deploy.sh
./deploy.sh
```

Worker (Supervisor): veja `deployment/supervisor-queue.conf.example`.

## Assets

```bash
npm run build:prod   # NODE_ENV=production + minify + drop console
```

Manifest esperado: `public/build/manifest.json` (ou `public/build/.vite/manifest.json`).
O tema Velzon também copia CSS/JS/libs para `public/build/` consumidos via `URL::asset('build/...')`.
