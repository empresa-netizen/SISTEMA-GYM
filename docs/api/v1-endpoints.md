# API V1 — Contratos Mobile (Sanctum)

Base URL local: `http://localhost:8000/api/v1`  
Auth: `Authorization: Bearer {access_token}`  
Header recomendado: `Accept: application/json`

## Autenticação

| Método | Path | Auth | Descrição |
|--------|------|------|-----------|
| POST | `/login` | — | Emite Personal Access Token |
| POST | `/auth/login` | — | Alias do login |
| POST | `/logout` | Bearer | Revoga token atual |
| POST | `/auth/logout` | Bearer | Alias do logout |
| GET | `/auth/me` | Bearer | Usuário autenticado |
| POST | `/auth/refresh` | Bearer | Rotaciona token |

### Login body
```json
{
  "email": "coach@mgteam.app",
  "password": "password",
  "device_name": "ios-iphone"
}
```

### Login response
```json
{
  "message": "Autenticacao realizada com sucesso.",
  "token_type": "Bearer",
  "access_token": "...",
  "user": { "id": 1, "name": "...", "email": "...", "roles": [] }
}
```

## Negócio (todas com `auth:sanctum`)

| Método | Path | Resource |
|--------|------|----------|
| GET | `/dashboard` | KPIs + recent (Feed/InvoicePayment/Event) |
| GET | `/members` | `MemberResource` paginado |
| GET | `/members/{id}` | `MemberResource` |
| PATCH | `/members/{id}` | `MemberResource` |
| GET | `/members/{id}/workouts` | `WorkoutResource` paginado |
| GET | `/members/{id}/feedbacks` | `ClientFeedbackResource` paginado |
| GET | `/messages/conversations` | `ConversationResource` paginado |
| POST | `/messages/conversations/start/{member}` | `ConversationResource` |
| GET | `/messages/conversations/{id}` | `ConversationResource` + messages |
| POST | `/messages/conversations/{id}/messages` | `MessageResource` |
| POST | `/messages/conversations/{id}/read` | ack |
| GET | `/prescriptions` | workouts + diets paginados |
| GET | `/prescriptions/member/{member}` | member + workouts + diets |
| POST | `/prescriptions/diet` | `DietPrescriptionResource` |
| GET | `/finance/dashboard` | saldos + recent payments |
| GET | `/finance/invoices` | `InvoiceResource` paginado |
| GET | `/finance/invoices/{id}` | `InvoiceResource` |
| GET | `/finance/payments` | `InvoicePaymentResource` paginado |
| GET | `/events` | `EventResource` paginado |
| POST | `/events` | `EventResource` |
| GET | `/events/{id}` | `EventResource` |
| PATCH | `/events/{id}` | `EventResource` |
| GET | `/feed` | `FeedPostResource` + `summary` |
| POST | `/feed` | `FeedPostResource` |
| GET | `/feed/feedbacks` | `ClientFeedbackResource` paginado |
| GET | `/catalog/overview` | contagens |
| GET | `/catalog/exercises` | `ExerciseResource` paginado |
| GET | `/catalog/diet-menus` | `DietMenuResource` paginado |
| GET | `/catalog/membership-plans` | `MembershipPlanResource` paginado |

## Erros JSON padrão

| Status | Shape |
|--------|--------|
| 401 | `{ "message": "Nao autenticado.", "error": "unauthenticated" }` |
| 404 | `{ "message": "Recurso nao encontrado.", "error": "not_found" }` |
| 422 | `{ "message": "Os dados enviados sao invalidos.", "errors": { ... } }` |
| 403 | `{ "message": "...", "error": "http_error" }` |

Paginação Laravel Resource: `data`, `links`, `meta`.
