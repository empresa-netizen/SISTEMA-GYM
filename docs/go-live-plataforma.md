# Go-live — plataforma.mgteamoficial.site

## 1. Subdomínio (Hostinger)

A API MCP da Hostinger está retornando `ECONNRESET` neste momento. Crie o subdomínio pelo hPanel:

1. [hPanel](https://hpanel.hostinger.com/) → **Domains** → `mgteamoficial.site`
2. **Subdomains** → Create → prefixo `plataforma`
3. Document root sugerido (Laravel): `plataforma.mgteamoficial.site/public`  
   (ou pasta do addon apontando o vhost Nginx `root` para `/public`)
4. DNS: registro **A** `plataforma` → IP do hosting/VPS (TTL 300)
5. SSL: SSL/TLS no painel (Let's Encrypt) para `plataforma.mgteamoficial.site`

Quando a API Hostinger voltar, dá para criar via:
`hosting_createWebsiteSubdomainV1(username, domain=mgteamoficial.site, subdomain=plataforma)`.

## 2. Servidor

- PHP 8.3 FPM + extensões (mbstring, pdo_mysql, bcmath, gd, zip, pcntl, exif)
- MySQL 8
- Nginx (`deployment/nginx-plataforma.mgteamoficial.site.conf`)
- Supervisor (`deployment/supervisor-queue.conf.example`) apontando para o path real

## 3. Deploy do código

```bash
# No servidor (path exemplo)
cd /var/www/plataforma.mgteamoficial.site
git pull origin main
cp deployment/env.production.example .env   # preencher secrets
php artisan key:generate --force
chmod +x deploy.sh
./deploy.sh
chown -R www-data:www-data storage bootstrap/cache
```

Ou: secrets `DEPLOY_*` no GitHub + workflow **Deploy**.

## 4. Validação Dia 1

```bash
curl -s https://plataforma.mgteamoficial.site/api/health
curl -s -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"...","password":"..."}' \
  https://plataforma.mgteamoficial.site/api/v1/login
# Bearer → /api/v1/dashboard
# Com DSN: /api/v1/debug-sentry (bloqueado se APP_ENV=production)
```

## 5. Checklist

- [ ] `APP_ENV=production` / `APP_DEBUG=false`
- [ ] `APP_URL=https://plataforma.mgteamoficial.site`
- [ ] `SENTRY_LARAVEL_DSN` preenchido
- [ ] SMTP real
- [ ] Supervisor `queue:work` UP
- [ ] Backup MySQL diário
- [ ] HTTPS válido
