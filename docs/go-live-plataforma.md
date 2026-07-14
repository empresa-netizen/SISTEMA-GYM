# Go-live — plataforma.mgteamoficial.site

**Status (2026-07-10):** no ar em shared hosting Hostinger.

| Item | Valor |
|------|--------|
| URL | https://plataforma.mgteamoficial.site |
| Docroot | `/home/u953703540/domains/mgteamoficial.site/public_html/plataforma` |
| PHP | 8.3.30 (LiteSpeed) |
| DB | `u953703540_plataforma` |
| Login demo | `coach@mgteam.app` / `password` |

## Validado

- `GET /api/health` → `{"status":"ok","database":"ok"}`
- `POST /api/v1/login` → Bearer token
- `GET /api/v1/dashboard` → KPIs
- `/` e `/login` → 200 (MGTEAM)

## Crons (hPanel)

```
* * * * * cd .../plataforma && php artisan schedule:run
* * * * * cd .../plataforma && php artisan queue:work --stop-when-empty --max-time=50
```

(`symlink` e `exec` estão desabilitados no shared — queue via cron, storage sem `storage:link`.)

## Redeploy

1. Build zip local (vendor `--no-dev` + `public/build` + `.env` de produção).
2. MCP `hosting_deployStaticWebsite` com `domain=plataforma.mgteamoficial.site`.
3. Se precisar migrate: rodar Artisan via script one-shot ou File Manager + cron.

## Pendências opcionais

- [ ] SMTP real (`MAIL_*`)
- [ ] `SENTRY_LARAVEL_DSN`
- [ ] Trocar senha demo em produção
- [ ] Reativar VPS `srv1712804` se quiser Nginx/Supervisor dedicados (hoje está `suspended`)
- [ ] Remover `public/install-once.php` / `debug-boot.php` se ainda existirem no File Manager

## Notas

- VPS Hostinger não foi usado (suspenso).
- Shared hosting: deploy por archive MCP; sem SSH/rsync.
- Não commitar `.env` de produção nem senha do banco.
