# Arquitetura de Auth & Dados — MGTEAM FITNESS & HEALTH

## Diagnóstico: por que o login do web ≠ app?

Hoje existem **dois sistemas paralelos**, nascidos de stacks diferentes:

| Camada | Painel web | Apps mobile |
|--------|------------|-------------|
| App | Laravel Blade `:8000` | Expo RN `:8086` / `:8089` |
| API | Sessão cookie + Sanctum `/api/v1` | Express JWT `:8088` |
| Banco | **MySQL** (`mazer`) | **PostgreSQL** (`coachpro` :5433) |
| Usuário coach demo (antes) | `coach@mgteam.local` | `admin@mgteam.app` |
| Aluno demo | `ana.silva@cliente.com` | `anabeatriz@gmail.com` |

Por isso o mesmo e-mail/senha **não funcionava** nos dois lados: não era bug de formulário — eram **identidades e bancos diferentes**.

Isso é o oposto do ideal para um SaaS coach (web + mobile).

---

## O que a indústria recomenda (2025–2026)

Fontes: Better Auth / multi-app session, B2B SaaS auth checklists, padrões OAuth 2.1 + PKCE.

### Ideal para rapidez + completude + funcionalidade

1. **Uma identidade** — mesma tabela `users` para web, app coach e app aluno.
2. **Um banco** (ou um schema compartilhado) — fonte única da verdade.
3. **Uma API** — web e mobile consomem o mesmo backend.
4. **Dois formatos de sessão, mesma conta**:
   - Web: cookie HttpOnly (sessão Laravel)
   - Mobile: Bearer token (Sanctum / JWT curto + refresh)
5. **RBAC no servidor** — `owner` / `coach` / `member` (aluno), nunca só no front.
6. **Tenant no token** — `tenant_id` / `parent_id` em toda query.
7. **Credenciais fortes** — hash Argon2/bcrypt, rate limit no login, MFA opcional.

### Anti-padrões (o que tínhamos)

- Dois bancos com usuários diferentes
- Dois logins demo documentados
- App mobile falando com API Express enquanto o painel fala com MySQL Laravel
- Dados do aluno no web que não existem no app (e vice-versa)

---

## Arquitetura-alvo MGTEAM — STATUS

```
┌─────────────┐   cookie    ┌──────────────────────┐
│ Web Coach   │────────────▶│                      │
│ :8000       │             │  Laravel (fonte)     │
└─────────────┘             │  + Sanctum API       │──▶ MySQL (único)
┌─────────────┐   Bearer    │  /api/auth/*         │
│ App Pro/Aluno│───────────▶│  /api/professional/* │
│ :8086/:8089 │             │  /api/v1/*           │
└─────────────┘             └──────────────────────┘
```

**Concluído (Jul/2026):**
- ✅ Credenciais unificadas (`coach@mgteam.app` / `password`)
- ✅ Apps Expo apontam para `http://localhost:8000` (não mais :8088)
- ✅ Camada compatível Express em Laravel (`/api/auth/*`, `/api/professional/*`)
- ✅ Express+Postgres em profile `legacy-api` (desligado por padrão)
- ✅ Seed único com vendas/clientes no MySQL

**Ainda opcional:**
- MFA / refresh rotation
- Portar 100% dos endpoints Express avançados (upload fotos, CRUD catálogo)

---

## Login unificado (agora)

| Perfil | E-mail | Senha | Onde entra |
|--------|--------|-------|------------|
| Profissional | `coach@mgteam.app` | `password` | Web `:8000` **e** App Pro `:8089` |
| Aluno | `anabeatriz@gmail.com` | `password` | App Aluno `:8086` |

Rodar após seed ou reset:

```bash
./scripts/unify-demo-auth.sh
```

---

## Checklist de completude

| Item | Status |
|------|--------|
| Mesmo e-mail/senha web + app pro | ✅ script unify |
| Mesmo aluno demo nos dois lados | ✅ |
| Documentação clara de 1 login | ✅ |
| Apps apontando para Laravel API | ⏳ próximo |
| Um único banco em produção | ⏳ próximo |
| MFA / refresh rotation | ⏳ futuro |
