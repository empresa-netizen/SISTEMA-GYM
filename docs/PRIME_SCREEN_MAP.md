# Mapa de telas — Prime Coaching → MGTEAM

Fonte: `https://app.primecoaching.com.br` (conta real) · Clone: `http://localhost:8000`

## Clientes (submenu expandido)

| Prime URL | Label | Local |
|-----------|-------|-------|
| `/customers/actives` | Ativos | `/members` |
| `/customers/feedbacks` | Feedbacks | `/feedbacks` |
| `/customers/logbooks` | Diário de registros | `/members/logbook` |
| `/customers/messages` | Mensagens | `/messages` |
| `/customers/list` | Todos os clientes | `/members/pending` |
| `/customers/scheduled-prescriptions` | Prescrições Agendadas | `/prescriptions` |
| `/customers/renewal-estimates` | Estimativa de Renovações | `/members/renewals` |
| `/customers/engagement` | Engajamento | `/members/engagement` |
| `/customers/dropouts` | Desistências | `/members/dropouts` |
| `/tools/import/customers` | Importar clientes | (pendente) |

## Perfil do cliente (`/customers/actives/{id}/…`)

| Aba Prime | Path | Local `?tab=` |
|-----------|------|---------------|
| Progresso | `/progress` | `progress` |
| Agendamentos | `/schedule` | `appointments` |
| Anamnese | `/anamnese` | `anamnesis` |
| Avaliações | `/reviews` | `reviews` |
| Dietas | `/diet` | `diet` |
| Treinos | `/workout` | `workout` |
| Cardio | `/cardio` | `cardio` |
| Exames | `/exams` | `exams` |
| Feedbacks | `/feed` | `feedbacks` |
| Fotos | `/photos` | `photos` |
| Notas | `/notes` | `notes` |

## Outros módulos (rail)

| Prime | Local |
|-------|-------|
| `/dashboard` | `/dashboard` |
| Agenda | `/schedule` |
| Produtos → … | `/membership-plans`, `/events`, `/products/*` |
| Bibliotecas | `/exercises`, `/workouts`, `/library/diet` |
| Ferramentas | `/tools/anamnesis`, `/healths` |
| Financeiro | `/finance` |
| Apps | `/apps` |
| Feed | `/feed` |
| Comunidade | `/community` |
| Suporte | `/support-tickets` |
| Minha conta | `/reports` / settings |
| Configurações | `/settings` |

## Meta

Espelhar **todas** as telas do menu + abas do perfil com paridade visual (não só 10–20).
