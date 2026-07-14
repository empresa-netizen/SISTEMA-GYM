@extends('layouts.master')

@section('title', $expense->title)

@section('content')
@php
    $paymentLabels = [
        'cash' => 'Dinheiro',
        'card' => 'Cartão',
        'bank_transfer' => 'Transferência bancária',
        'cheque' => 'Cheque',
        'other' => 'Outro',
    ];
    $paymentLabel = $paymentLabels[$expense->payment_method] ?? ucfirst(str_replace('_', ' ', $expense->payment_method));
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">{{ $expense->title }}</h1>
        <p class="mg-page-sub">{{ $expense->expense_number }} · {{ $expense->type->name ?? 'Sem categoria' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-primary btn-sm"><i class="ri-pencil-line me-1"></i> Editar</a>
        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line"></i></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="mg-panel text-center">
            <div class="mg-panel-value mg-panel-value--sm mb-2 text-danger">R$ {{ number_format($expense->amount, 2, ',', '.') }}</div>
            <span class="badge bg-info-subtle text-info">{{ $expense->type->name ?? 'Sem categoria' }}</span>
            <p class="small text-muted mt-3 mb-0">{{ $expense->expense_date->format('d/m/Y') }}</p>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="mg-panel" style="height:auto">
            <div class="mg-panel-label mb-3">DETALHES</div>
            <dl class="mg-detail-grid mb-0">
                <dt>Número</dt><dd>{{ $expense->expense_number }}</dd>
                <dt>Título</dt><dd>{{ $expense->title }}</dd>
                <dt>Tipo</dt><dd>{{ $expense->type->name ?? 'Sem categoria' }}</dd>
                <dt>Valor</dt><dd>R$ {{ number_format($expense->amount, 2, ',', '.') }}</dd>
                <dt>Data</dt><dd>{{ $expense->expense_date->format('d/m/Y') }}</dd>
                <dt>Forma de pagamento</dt><dd>{{ $paymentLabel }}</dd>
                <dt>Descrição</dt><dd>{{ $expense->description ?? '—' }}</dd>
                <dt>Observações</dt><dd>{{ $expense->notes ?? '—' }}</dd>
                @if($expense->receipt)
                    <dt>Comprovante</dt>
                    <dd>
                        <a href="{{ URL::asset('storage/' . $expense->receipt) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="ri-download-line me-1"></i> Ver comprovante
                        </a>
                    </dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
