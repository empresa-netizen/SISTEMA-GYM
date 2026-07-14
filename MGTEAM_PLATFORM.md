# Referencia Visual — Clone Local

Clone funcional do [Referencia Visual](referencia-visual-local) baseado no GymFlow (Laravel 12).

## Início rápido

```bash
cd SISTEMA-GYM
docker compose up -d
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan db:seed --force
```

**URL:** http://localhost:8000  
**Login:** `coach@mgteam.local` / `password`

## Plano da semana (D1–D7)

| Dia | Escopo | Status |
|-----|--------|--------|
| **D1** | Infra Docker, auth MGTEAM, landing, dashboard, biblioteca Vimeo | ✅ |
| **D2** | Clientes, planos, perfil, seed 3 pessoas | ✅ |
| **D3** | Financeiro: hub `/finance`, vendas, pagamentos, relatórios | ✅ |
| **D4** | Agenda: lista + calendário `/schedule`, eventos seed | ✅ |
| **D5** | Treinos prescritos, biblioteca exercícios, evolução (healths) | ✅ |
| **D6** | Configurações PT-BR, suporte + FAQ, central de ajuda | ✅ |
| **D7** | Busca global, polish UI, métricas tenant-scoped, docs | ✅ |

## Rotas principais

| Referencia visual | Clone local |
|----------------|-------------|
| `/dashboard` | `/dashboard` |
| `/schedule` | `/schedule` |
| `/customers/list` | `/members` |
| `/library/workout` | `/exercises` + `/workouts` |
| `/finance` | `/finance` |
| `/account/settings` | `/settings` |
| `/help` | `/support-tickets` |

## Módulos

- **Resumo** — métricas reais (receita, clientes, treinos, tickets)
- **Agenda** — calendário FullCalendar + CRUD eventos
- **Clientes** — DataTables, criar/editar/perfil
- **Treinos** — prescrição com embed Vimeo
- **Biblioteca** — 20 exercícios do JSON local
- **Evolução** — medições corporais (`/healths`)
- **Financeiro** — dashboard, transações, saques (stub), relatórios
- **Planos** — membership plans em R$
- **Configurações** — settings do tenant
- **Suporte** — tickets + FAQ
- **Busca** — `/search?q=` (clientes, treinos, faturas)

## Seeders

```bash
docker compose exec -T app php artisan db:seed --class=MgteamPaymentSeeder --force
docker compose exec -T app php artisan db:seed --class=MgteamEventSeeder --force
docker compose exec -T app php artisan db:seed --class=MgteamSupportSeeder --force
docker compose exec -T app php artisan db:seed --class=MgteamCrmSeeder --force
```

## Apps mobile (Jul/2026)

Stack Expo + mgteam-api via Docker:

```bash
chmod +x scripts/start-all.sh scripts/mobile-up.sh scripts/mobile-down.sh
./scripts/start-all.sh          # web Laravel + mobile
# ou só mobile:
./scripts/mobile-up.sh
```

| App | URL | Login demo |
|-----|-----|------------|
| **Profissional** | http://localhost:8089 | admin@mgteam.app / 123456 |
| **Aluno** | http://localhost:8086 | anabeatriz@gmail.com / 123456 |
| **API** | http://localhost:8088 | Swagger em `/api-docs` |
| **Hub web** | http://localhost:8000/apps | — |

Compose: `docker-compose.mobile.yml` (PostgreSQL coach-pro + seed automático).

## Fora do escopo (Semana 2+)

- MGTEAM Pay / saque real
- ~~Chat, feed comunidade, dietas~~ → **espelhados em Jul/2026** (MVP funcional)
- App mobile
- IA de prescrição
- Afiliados / carrinho abandonado (stubs)

### Módulos espelhados (coach-pro)

| Rota | Módulo |
|------|--------|
| `/messages` | Chat coach ↔ aluno |
| `/feed` | Timeline operacional |
| `/community` | Grupos e posts |
| `/feedbacks` | Feedbacks com fotos |
| `/library/diet` | Hub dieta + alimentos + cardápios |
| `/products/coupons` | Cupons de desconto |
| `/members/renewals` | Renovações 30 dias |
| `/members/pending` | Clientes sem treino |
| `/prescriptions` | Hub treinos + dietas prescritas |
| `/members/{id}/show?tab=` | Ficha 360° (7 abas CRM) |

### Ficha 360° do cliente (Jul/2026)

Abas em `/members/{id}/show`:

| Aba | Conteúdo |
|-----|----------|
| Visão geral | Resumo, últimos treinos e medições |
| Prescrições | Treinos + dietas (modal prescrever dieta) |
| Anamnese | Formulário objetivos/lesões/estilo de vida |
| Fotos | Upload evolução corporal |
| Feedbacks | Feedbacks do aluno |
| Diário | Logbook treino/alimentação/humor |
| Consultas | Eventos da agenda vinculados ao cliente |

Seeder CRM: `MgteamCrmSeeder` (anamnese, diário, dieta demo, eventos com `member_id`).

## Stack

Laravel 12 · PHP 8.3 · MySQL 8 · Docker · Bootstrap 5 · DataTables · FullCalendar

## Ecossistema irmão (CLAUDE / coach-pro)

O projeto anterior em `/Users/trabalho/CLAUDE` contém **API mobile + apps Expo** (não front web).
O front web Next.js está em `/Users/trabalho/codex/coach-pro`.

| Documento | Conteúdo |
|-----------|----------|
| `docs/ECOSYSTEM_BRIDGE.md` | O que portar de cada repo |
| `docs/ORQUESTRACAO.md` | Prompt mestre e subagentes |
| `docs/agents/*.md` | Playbooks copiados de `.claude/agents/` |
| `.cursor/rules/mg-coaching-clone.mdc` | Regras Cursor do clone |

**Não portar código React/Expo** — portar contratos API, tokens MGTEAM (`--mg-*` em `mg-app.css`) e padrões de tela.
