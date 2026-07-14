<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $prescription->title }} — Dieta</title>
    <style>
        :root {
            --ink: #1a1f2c;
            --muted: #5b6475;
            --line: #d8dde6;
            --accent: #0f766e;
            --bg: #f7f8fa;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
            line-height: 1.45;
        }
        .sheet {
            max-width: 820px;
            margin: 24px auto;
            background: #fff;
            padding: 32px 36px 40px;
            border: 1px solid var(--line);
        }
        .toolbar {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .toolbar button, .toolbar a {
            appearance: none;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }
        .toolbar .primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        h1 { margin: 0 0 6px; font-size: 26px; }
        .meta { color: var(--muted); font-size: 14px; margin-bottom: 22px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 18px 0 26px;
        }
        .stat {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 12px;
            background: #fafbfc;
        }
        .stat strong { display: block; font-size: 20px; }
        .stat span { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        h2 { font-size: 16px; margin: 28px 0 10px; border-bottom: 1px solid var(--line); padding-bottom: 6px; }
        p { margin: 0 0 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { border-bottom: 1px solid var(--line); padding: 8px 6px; text-align: left; }
        th { color: var(--muted); font-weight: 600; font-size: 11px; text-transform: uppercase; }
        td.num, th.num { text-align: right; }
        .note {
            margin-top: 18px;
            padding: 12px 14px;
            background: #f0fdfa;
            border-left: 3px solid var(--accent);
            font-size: 13px;
            color: #134e4a;
        }
        .foot { margin-top: 28px; color: var(--muted); font-size: 12px; }
        @media (max-width: 640px) {
            .sheet { margin: 0; border: 0; padding: 20px 16px 32px; }
            .grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media print {
            body { background: #fff; }
            .sheet { border: 0; margin: 0; max-width: none; padding: 0; }
            .toolbar { display: none !important; }
        }
    </style>
</head>
<body>
<div class="sheet">
    <div class="toolbar">
        <button type="button" class="primary" onclick="window.print()">Imprimir / PDF</button>
        <a href="javascript:history.back()">Voltar</a>
    </div>

    <p class="meta">MGTEAM · Plano alimentar</p>
    <h1>{{ $prescription->title }}</h1>
    <p class="meta">
        Aluno: <strong>{{ $member->name ?? $prescription->member?->name }}</strong>
        · Cardápio: {{ $summary['menu_name'] ?? 'Personalizado' }}
        · {{ $summary['meals_count'] ?? 0 }} refeições
        · Emitido em {{ now()->format('d/m/Y H:i') }}
    </p>

    <div class="grid">
        <div class="stat">
            <strong>{{ number_format((float) ($summary['prescribed']['calories'] ?? $summary['menu_kcal']), 0, ',', '.') }}</strong>
            <span>Kcal prescritas</span>
        </div>
        <div class="stat">
            <strong>{{ number_format((float) ($summary['prescribed']['protein'] ?? 0), 1, ',', '.') }}g</strong>
            <span>Proteína</span>
        </div>
        <div class="stat">
            <strong>{{ number_format((float) ($summary['prescribed']['carbs'] ?? 0), 1, ',', '.') }}g</strong>
            <span>Carboidrato</span>
        </div>
        <div class="stat">
            <strong>{{ number_format((float) ($summary['prescribed']['fat'] ?? 0), 1, ',', '.') }}g</strong>
            <span>Gordura</span>
        </div>
    </div>

    @if(!empty($summary['menu_description']))
        <h2>Descrição do cardápio</h2>
        <p>{{ $summary['menu_description'] }}</p>
    @endif

    @if($prescription->notes)
        <h2>Observações do coach</h2>
        <p>{{ $prescription->notes }}</p>
    @endif

    @if(!empty($summary['meals']) && count($summary['meals']) > 0)
        <h2>Refeições prescritas</h2>
        @foreach($summary['meals'] as $meal)
            <h3 style="font-size:14px;margin:18px 0 8px;">
                {{ $meal['name'] }}
                @if(!empty($meal['time_label']))
                    <span style="color:var(--muted);font-weight:400">· {{ $meal['time_label'] }}</span>
                @endif
                <span style="color:var(--muted);font-weight:400;font-size:12px">
                    · {{ number_format((float) $meal['macros']['calories'], 0, ',', '.') }} kcal
                </span>
            </h3>
            <table>
                <thead>
                <tr>
                    <th>Alimento</th>
                    <th class="num">Porção (g)</th>
                    <th class="num">Kcal</th>
                    <th class="num">P</th>
                    <th class="num">C</th>
                    <th class="num">G</th>
                </tr>
                </thead>
                <tbody>
                @forelse($meal['foods'] as $food)
                    <tr>
                        <td>{{ $food['name'] }}</td>
                        <td class="num">{{ number_format((float) $food['quantity_in_grams'], 0, ',', '.') }}</td>
                        <td class="num">{{ number_format((float) $food['macros']['calories'], 0, ',', '.') }}</td>
                        <td class="num">{{ number_format((float) $food['macros']['protein'], 1, ',', '.') }}</td>
                        <td class="num">{{ number_format((float) $food['macros']['carbs'], 1, ',', '.') }}</td>
                        <td class="num">{{ number_format((float) $food['macros']['fat'], 1, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Nenhum alimento nesta refeição.</td></tr>
                @endforelse
                </tbody>
            </table>
        @endforeach
    @else
        <h2>Alimentos de referência (catálogo do tenant)</h2>
        <table>
            <thead>
            <tr>
                <th>Alimento</th>
                <th>Grupo</th>
                <th class="num">Kcal</th>
                <th class="num">P</th>
                <th class="num">C</th>
                <th class="num">G</th>
            </tr>
            </thead>
            <tbody>
            @forelse($summary['foods'] as $food)
                <tr>
                    <td>{{ $food->name }}</td>
                    <td>{{ $food->food_group ?: '—' }}</td>
                    <td class="num">{{ number_format((float) $food->calories, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $food->protein, 1, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $food->carbs, 1, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $food->fat, 1, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Nenhum alimento cadastrado no catálogo.</td></tr>
            @endforelse
            </tbody>
        </table>
    @endif

    <div class="note">
        Macros calculados por porção: (macro do catálogo / 100g) × quantidade em gramas.
        Total prescrito: {{ number_format((float) ($summary['prescribed']['calories'] ?? $summary['menu_kcal']), 0, ',', '.') }} kcal.
    </div>

    <p class="foot">Documento gerado pelo MGTEAM · use Imprimir / Salvar como PDF no navegador.</p>
</div>
</body>
</html>
