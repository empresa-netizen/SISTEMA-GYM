# Etapa 11 — Testes de Integração API V1 (Pest)

## Preferência

**Pest** para a suíte nova de contratos. Feature tests legados em PHPUnit permanecem intactos.

## Banco de testes

`phpunit.xml` aponta para MySQL dedicado **`mazer_testing`** (host `db` no Docker).  
Não usa o banco `mazer` de desenvolvimento. SQLite `:memory:` foi descartado por migrations legadas incompatíveis.

Criar o DB (já feito no Docker local):

```sql
CREATE DATABASE IF NOT EXISTS mazer_testing;
```

## Rodar

```bash
docker compose exec app php artisan test --testsuite=ApiV1
```

## Cobertura

| Arquivo | Cenários |
|---------|----------|
| `AuthApiTest` | 401 sem token, login inválido 401, payload 422, login 200 + UserResource, `/me`, logout revoga token |
| `MemberApiTest` | listagem + `assertJsonStructure` MemberResource + meta, show, isolamento multi-tenant |
| `FinanceApiTest` | invoices Resource + balance, show, dashboard KPIs |
| `FeedQueueApiTest` | `Queue::fake` + `assertPushed(NotifyFeedInteraction)` em like/comment |
