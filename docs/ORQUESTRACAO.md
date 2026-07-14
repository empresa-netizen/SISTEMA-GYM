# Orquestração — Clone Referencia Visual (Laravel)

Adaptado de `/Users/trabalho/CLAUDE/MODELO_ORQUESTRACAO_SUBAGENTES.md` para o escopo **web-only** deste repositório.

## Topologia ativa (SISTEMA-GYM)

- **Web clone:** `SISTEMA-GYM` → `http://localhost:8000` (Laravel 12, MySQL Docker)
- **Referência MGTEAM:** `referencia-visual-local`
- **Referência ecossistema:** `coach-pro` (:8081), `mgteam-api` (:8088), apps (:8086/:8089)

## Regras absolutas

1. **Dados reais** — seeders Mgteam*, sem mock silencioso em telas operacionais
2. **Tenancy** — filtrar por `parent_id` / `parentId()` em métricas e queries
3. **UI MGTEAM** — coach usa shell `mg-*`; super-admin mantém Velzon
4. **PT-BR** — copy, datas `d/m/Y`, moeda `R$`
5. **Docker** — `docker compose exec -T app php artisan ...`

## Pipeline para nova feature (web)

1. **Schema** — migration + model + seeder se necessário
2. **Backend** — Controller, Policy, FormRequest
3. **Frontend** — Blade em `resources/views/mgteam/` ou módulo com `mg-page-title`
4. **Rotas** — `routes/web.php`, sidebar em `mg-sidebar.blade.php`
5. **Validar** — browser em `localhost:8000`

## Subagentes Cursor sugeridos

| Tarefa | Subagente |
|--------|-----------|
| Explorar código / rotas MGTEAM | `explore` |
| Implementar feature multi-arquivo | `generalPurpose` |
| Revisar diff | `bugbot` |
| Shell / Docker / artisan | `shell` |

## Prompt mestre (copiar)

```text
Você trabalha no clone Referencia Visual em Laravel:
/Users/trabalho/Documents/CURSOR COACH/SISTEMA-GYM

Objetivo: [FEATURE]

Regras:
- Shell MGTEAM para usuários coach (não super-admin)
- Métricas com parent_id
- Comparar com referencia-visual-local quando for UI
- Usar docs/ECOSYSTEM_BRIDGE.md se tocar domínio do ecossistema CLAUDE/coach-pro
- Não commitar sem pedido explícito

Entregue: código + validação no browser se possível.
```
