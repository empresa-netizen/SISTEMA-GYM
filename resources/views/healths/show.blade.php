@extends('layouts.master')

@section('title', 'Medição')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">Medição de {{ $health->member?->name ?? 'cliente' }}</h1>
        <p class="prime-page-sub">{{ $health->measurement_date?->format('d/m/Y') ?? '—' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('healths.edit', $health) }}" class="btn btn-primary btn-sm"><i class="ri-pencil-line me-1"></i> Editar</a>
        <a href="{{ route('healths.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line"></i></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="prime-panel">
            <div class="prime-panel-label mb-2">RESUMO</div>
            @if($health->bmi)
                <div class="prime-panel-value prime-panel-value--sm mb-1">{{ $health->bmi }}</div>
                <span class="badge @if($health->bmi_category == 'Normal') bg-success @elseif($health->bmi_category == 'Overweight') bg-warning text-dark @elseif($health->bmi_category == 'Obese') bg-danger @else bg-info @endif">{{ $health->bmi_category }}</span>
            @else
                <p class="text-muted mb-0 small">IMC não calculado.</p>
            @endif
            @if($health->notes)
                <p class="small text-muted mt-3 mb-0">{{ $health->notes }}</p>
            @endif
        </div>
    </div>
    <div class="col-lg-8">
        <div class="prime-panel">
            <h2 class="prime-section-title h6 mb-3">Medidas</h2>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead><tr><th>Tipo</th><th>Valor</th></tr></thead>
                    <tbody>
                        @forelse($health->measurements as $key => $value)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                            <td><strong>{{ $value }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">Nenhuma medida registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
