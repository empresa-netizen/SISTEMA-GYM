# MGTEAM FITNESS & HEALTH — Plataforma Coach

Réplica fiel do [Prime Coaching](https://app.primecoaching.com.br) com marca **MGTEAM FITNESS & HEALTH**.

## Acesso local

| Serviço | URL | Login |
|---------|-----|-------|
| Painel web coach | http://localhost:8000 | `coach@mgteam.app` / `password` |
| App profissional | http://localhost:8089 | **mesmo login** (API Laravel) |
| App aluno | http://localhost:8086 | `anabeatriz@gmail.com` / `password` |
| API unificada | http://localhost:8000 | `/api/auth/*`, `/api/professional/*`, `/api/v1/*` |
| Health | http://localhost:8000/health | MySQL ok |

> **1 banco (MySQL) · 1 login · 1 API.** Express `:8088` é legado (`--profile legacy-api`). Ver `docs/AUTH_ARCHITECTURE.md`.

## Subir tudo

```bash
cd SISTEMA-GYM
./scripts/start-all.sh
```

## Módulos implementados

- **Resumo** — dashboard completo (faturamento, operacional, vendas, saúde, tendência)
- **Agenda** — calendário FullCalendar + eventos
- **Produtos** — planos, cupons, afiliados, recuperação de carrinho
- **Clientes** — ficha 360° (7 abas), renovações, engajamento, mensagens
- **Bibliotecas** — exercícios, treinos, dieta
- **Ferramentas** — anamnese, prescrições (treino/dieta/cardio/suplementação)
- **Financeiro** — transações, relatórios, MGTEAM Pay (saques em desenvolvimento)
- **Feed / Comunidade / Suporte**
- **Apps mobile** — hub com links Pro/Aluno/API
- **API v1** — Sanctum, documentada em `docs/API.md`

## Branding

Config central em `config/brand.php`:

- `BRAND_NAME=MGTEAM FITNESS & HEALTH`
- `BRAND_PAY=MGTEAM Pay`

## Referência visual

Compare sempre com `app.primecoaching.com.br` (tema escuro, rail sidebar, cards operacionais).
