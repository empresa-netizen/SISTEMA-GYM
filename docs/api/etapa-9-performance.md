# Etapa 9 — Performance, Filas e Segurança

## Filas (`QUEUE_CONNECTION=database`)

Tabela: `jobs` (+ `failed_jobs` já existente).

| Job | Disparado por |
|-----|----------------|
| `ProcessExamUpload` | Upload de foto/exame (`MemberCrmController@storePhoto`) |
| `NotifyFeedInteraction` | Like/comentário no feed |
| `SendPaymentReminderEmail` | Notificar cliente (assunto com “pagament…”) ou `php artisan payments:send-reminders` |
| `SendClientNotificationEmail` | Notificar cliente (e-mail genérico) |
| `InAppAlert` (Notification) | Qualquer `->notify(InAppAlert)` — agora `ShouldQueue` |
| `Common` (Mailable) | `commonEmailSend()` via `Mail::queue` |

Worker local:

```bash
docker compose exec app php artisan queue:work --tries=3
```

## Cache do Dashboard

- API: `Cache::remember('dashboard:api:v1:{tenant}', 900, …)`
- Web KPIs: `dashboard:web:stats:{tenant}` (15 min)
- Invalidação: `DashboardCacheObserver` em Member, Invoice, InvoicePayment, Event, Workout, Conversation, ClientFeedback

## Rate Limiting

| Limiter | Limite | Onde |
|---------|--------|------|
| `api-login` | 5/min por IP | `POST /api/v1/login`, `/auth/login` |
| `api-authenticated` | 100/min por user/IP | rotas V1 com Sanctum |
| `api` | 100/min | middleware group `api` (fallback) |

## Redis (produção)

Local/MVP: MySQL `database` queue + `file` cache.  
Produção com tráfego real: subir Redis e apontar `QUEUE_CONNECTION=redis` + `CACHE_DRIVER=redis` sem mudar Jobs/Observers.
