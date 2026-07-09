# Ponte: CLAUDE / coach-pro ↔ SISTEMA-GYM

Este documento explica o que existe em cada repositório e **o que pode (e não pode) ser portado** para o clone Laravel sem perder funcionalidade.

## Mapa dos projetos

| Projeto | Caminho | Stack | Papel |
|---------|---------|-------|-------|
| **SISTEMA-GYM** (este) | `Documents/CURSOR COACH/SISTEMA-GYM` | Laravel 12, Blade, MySQL | Clone web Prime Coaching (D1–D7) |
| **coach-pro** | `/Users/trabalho/codex/coach-pro` | Next.js 15, Prisma, PostgreSQL | Web SaaS completo (referência de telas) |
| **mgteam-api** | `/Users/trabalho/CLAUDE/mgteam-api` | Express, JWT, Prisma | API REST para apps mobile |
| **mgteam-pro-app** | `/Users/trabalho/CLAUDE/mgteam-pro-app` | Expo 57, RN Web | App do coach |
| **mgteam-app** | `/Users/trabalho/CLAUDE/mgteam-app` | Expo 57, RN Web | App do aluno |

## O que a pasta CLAUDE contém

A pasta `/Users/trabalho/CLAUDE` **não é o front web**. Ela contém:

- API mobile (`mgteam-api`) — ~70 endpoints, schema Prisma 31 modelos
- Apps Expo (coach + aluno) — UI em React Native `StyleSheet`, não HTML/CSS
- Orquestração de agentes (`.claude/agents/`, `AGENTS.md`, `BLACKBOARD.json`)
- Design tokens mobile em `mgteam-*/constants/colors.ts` (MGTEAM aqua `#14B8A6`)

O **front web** do ecossistema anterior está em **coach-pro** (`localhost:8081`).

## Estratégia de adaptação (sem perder nada)

### ✅ Portar direto para SISTEMA-GYM

| Origem | Destino | Motivo |
|--------|---------|--------|
| `CLAUDE/AGENTS.md` (regras de domínio) | `.cursor/rules/prime-coaching-clone.mdc` | Efeito dominó, no-mock, contratos |
| `CLAUDE/.claude/agents/*.md` | `docs/agents/*.md` | Playbook para subagentes Cursor |
| `CLAUDE/MODELO_ORQUESTRACAO_SUBAGENTES.md` | `docs/ORQUESTRACAO.md` | Prompt mestre reutilizável |
| `CLAUDE/mgteam-api/prisma/schema.prisma` | Referência para migrations Laravel | Modelo de domínio |
| `CLAUDE/mgteam-api/src/routes/*.ts` | Referência para API futura | Contratos REST |
| `mgteam-*/constants/colors.ts` | `public/css/prime-app.css` (`--mgteam-*`) | Tokens visuais |
| `coach-pro/src/app/(dashboard)/**` | `resources/views/prime/**` | Layout/UX das telas web |

### ⚠️ Não portar código literal

| Origem | Por quê |
|--------|---------|
| Componentes `.tsx` / `.tsx` Expo | Stack diferente (Blade ≠ React) |
| Server Actions Next.js | Laravel usa Controllers + Eloquent |
| Prisma queries | Eloquent + migrations MySQL |
| `mockData.ts` dos apps | Política no-mock do ecossistema |

### 🔄 Reaproveitar padrão visual

1. **Prime original** (`app.primecoaching.com.br`) — referência principal do clone (azul escuro, rail sidebar)
2. **coach-pro** — estrutura de páginas (finance tabs, clientes CRM, biblioteca)
3. **MGTEAM tokens** — variáveis CSS opcionais para futura unificação de marca

## Agentes e subagentes no Cursor

Subagentes do ecossistema CLAUDE mapeados para Cursor:

| Agente CLAUDE | Quando usar no SISTEMA-GYM |
|---------------|----------------------------|
| `database-specialist` | Migrations, seeders, tenancy `parent_id` |
| `frontend-specialist` | Blade `resources/views/prime/**`, CSS |
| `api-specialist` | Rotas API Laravel (se expor mobile) |
| `backend-specialist` | Controllers, models, policies |
| `mobile-*` | Somente se conectar mgteam-api ao Laravel |
| `devops-specialist` | Docker, `docker-compose.yml` |

Prompt mestre: ver `docs/ORQUESTRACAO.md`.

## Roadmap de integração (pós D7)

| Fase | Escopo |
|------|--------|
| **Semana 1** ✅ | Clone web Laravel D1–D7 |
| **Semana 2** | Chat, feed, dietas — espelhar `coach-pro` + schema Prisma |
| **Semana 3** | API Laravel compatível com `mgteam-api` OU bridge Express |
| **Semana 4** | Apps mobile apontando para API unificada ✅ (stack local Jul/2026) |

## Apps mobile no ar (local)

```bash
cd "/Users/trabalho/Documents/CURSOR  COACH/SISTEMA-GYM"
./scripts/mobile-up.sh    # sobe tudo
./scripts/mobile-down.sh  # para tudo
```

| Serviço | URL |
|---------|-----|
| App Profissional | http://localhost:8089 |
| App Aluno | http://localhost:8086 |
| API (mgteam-api) | http://localhost:8088 |
| Hub no Laravel | http://localhost:8000/apps |

**Arquitetura:** PostgreSQL no Docker (`prime_mobile_db` :5433) + API e apps Expo.

> **Auth:** credenciais demo unificadas com o painel Laravel via `./scripts/unify-demo-auth.sh`.
> Login profissional: `coach@mgteam.app` / `password` (web + app).
> Ver `docs/AUTH_ARCHITECTURE.md` para o alvo (1 banco + 1 API).

## Comandos úteis

```bash
# Clone Laravel (este projeto)
cd "/Users/trabalho/Documents/CURSOR  COACH/SISTEMA-GYM"
docker compose up -d

# Ecossistema anterior (referência)
cd /Users/trabalho/codex/coach-pro && docker compose up -d    # :8081
cd /Users/trabalho/CLAUDE/mgteam-api && docker compose up -d  # :8088
```
