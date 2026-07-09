# API v1 - Plataforma Coach (Prime/MGTeam)

Esta documentacao descreve a camada REST `v1` para consumo mobile.

## Base URL

- Local: `http://localhost:8000/api/v1`
- Producao: `https://seu-dominio.com/api/v1`

## Autenticacao

A API usa **Laravel Sanctum** com Bearer Token.

### Login

- **POST** `/auth/login`

Request:

```json
{
  "email": "coach@exemplo.com",
  "password": "password",
  "device_name": "iphone-16"
}
```

Response `200`:

```json
{
  "message": "Autenticacao realizada com sucesso.",
  "token_type": "Bearer",
  "access_token": "1|token...",
  "user": {
    "id": 1,
    "name": "Coach Prime",
    "email": "coach@exemplo.com",
    "parent_id": null,
    "tenant_id": 1,
    "roles": ["owner"]
  }
}
```

### Usuario autenticado

- **GET** `/auth/me`
- Header: `Authorization: Bearer {token}`

### Renovar token

- **POST** `/auth/refresh`
- Header: `Authorization: Bearer {token}`

### Logout

- **POST** `/auth/logout`
- Header: `Authorization: Bearer {token}`

---

## Dashboard

### Resumo geral

- **GET** `/dashboard`

Response `200`:

```json
{
  "data": {
    "kpis": {
      "members_total": 120,
      "members_active": 98,
      "events_upcoming": 6,
      "conversations_unread": 14,
      "feedback_pending": 9,
      "invoices_open": 22,
      "revenue_month": 18450.75
    },
    "recent": {
      "feed": [],
      "payments": [],
      "events": []
    }
  }
}
```

---

## Membros

### Listar membros

- **GET** `/members?q=ana&status=active&per_page=15`

### Detalhes do membro

- **GET** `/members/{member}`

### Atualizar membro

- **PATCH** `/members/{member}`

Request:

```json
{
  "phone": "+55 11 99999-9999",
  "status": "active",
  "notes": "Cliente focado em hipertrofia."
}
```

### Treinos do membro

- **GET** `/members/{member}/workouts`

### Feedbacks do membro

- **GET** `/members/{member}/feedbacks`

---

## Mensagens

### Listar conversas

- **GET** `/messages/conversations?q=maria`

### Iniciar conversa

- **POST** `/messages/conversations/start/{member}`

### Ver conversa com mensagens

- **GET** `/messages/conversations/{conversation}`

### Enviar mensagem

- **POST** `/messages/conversations/{conversation}/messages`

Request:

```json
{
  "content": "Oi! Como foi seu treino de hoje?"
}
```

### Marcar mensagens como lidas

- **POST** `/messages/conversations/{conversation}/read`

---

## Prescricoes

### Listar painel de prescricoes

- **GET** `/prescriptions?member_id=10`

Response `200`:

```json
{
  "data": {
    "workouts": [],
    "diets": []
  }
}
```

### Listar prescricoes por membro

- **GET** `/prescriptions/member/{member}`

### Criar prescricao alimentar

- **POST** `/prescriptions/diet`

Request:

```json
{
  "member_id": 10,
  "diet_menu_id": 4,
  "title": "Plano cutting julho",
  "notes": "Ajustar carbo em dias sem treino.",
  "status": "scheduled",
  "delivery_status": "PENDING",
  "scheduled_at": "2026-07-10 08:00:00"
}
```

---

## Financeiro

### Dashboard financeiro

- **GET** `/finance/dashboard`

### Listar faturas

- **GET** `/finance/invoices?status=unpaid&member_id=10&q=INV&per_page=20`

### Ver fatura

- **GET** `/finance/invoices/{invoice}`

### Listar pagamentos

- **GET** `/finance/payments?method=card&per_page=20`

---

## Eventos

### Listar eventos

- **GET** `/events?status=scheduled&member_id=10&from=2026-07-01&to=2026-07-31`

### Criar evento

- **POST** `/events`

Request:

```json
{
  "member_id": 10,
  "title": "Reavaliacao mensal",
  "description": "Consulta de acompanhamento",
  "start_time": "2026-07-12 09:00:00",
  "end_time": "2026-07-12 09:45:00",
  "location": "Sala 2",
  "max_participants": 1,
  "status": "scheduled"
}
```

### Detalhes de evento

- **GET** `/events/{event}`

### Atualizar evento

- **PATCH** `/events/{event}`

---

## Feed

### Listar feed

- **GET** `/feed?type=POST&per_page=20`

### Publicar item no feed

- **POST** `/feed`

Request:

```json
{
  "type": "POST",
  "title": "Parabens time!",
  "description": "Meta coletiva batida esta semana.",
  "meta": "Publicado pelo coach"
}
```

### Listar feedbacks de clientes

- **GET** `/feed/feedbacks?status=pending`

---

## Catalogo

### Visao geral

- **GET** `/catalog/overview`

### Listar exercicios

- **GET** `/catalog/exercises?q=agachamento&per_page=30`

### Listar cardapios

- **GET** `/catalog/diet-menus?status=published&per_page=30`

### Listar planos de assinatura

- **GET** `/catalog/membership-plans?active_only=1&per_page=30`

---

## Escopo multi-tenant

- Os endpoints aplicam escopo por tenant usando `parentId()` quando aplicavel.
- Em recursos com `parent_id`, a API valida acesso para impedir leitura/escrita cruzada entre tenants.

## Erros padrao

### Nao autenticado

Response `401`:

```json
{
  "message": "Unauthenticated."
}
```

### Validacao

Response `422`:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Mensagem de validacao"]
  }
}
```
