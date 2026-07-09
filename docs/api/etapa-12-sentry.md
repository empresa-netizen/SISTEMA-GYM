# Etapa 12 — Observabilidade com Sentry

## Instalação

- Pacote: `sentry/sentry-laravel` (^4.26)
- Config: `config/sentry.php`
- Handler: `Integration::handles($exceptions)` em `bootstrap/app.php`
- Contexto: middleware `App\Http\Middleware\SentryContext` (id, email, username + tags)
- Browser: `resources/views/partials/sentry-browser.blade.php` no layout master

## Variáveis

```env
SENTRY_LARAVEL_DSN=https://...@o....ingest.sentry.io/...
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_SEND_DEFAULT_PII=false
```

Sem DSN preenchido, o SDK não envia eventos (seguro para local).

## Smoke test

1. Crie um projeto em [sentry.io](https://sentry.io/) e cole o DSN no `.env`
2. `php artisan config:clear`
3. Login API + chame:

```bash
curl -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/debug-sentry
```

Esperado: HTTP 500 JSON `server_error` + evento no painel Sentry com user context.

4. Em produção, remova ou restrinja `GET /api/v1/debug-sentry` a um role admin.
