@extends('layouts.master')

@section('title', $membershipPlan->name)

@section('content')
@php
    $durationTypes = [
        'daily' => 'Diário',
        'weekly' => 'Semanal',
        'monthly' => 'Mensal',
        'quarterly' => 'Trimestral',
        'half_yearly' => 'Semestral',
        'yearly' => 'Anual',
        'lifetime' => 'Vitalício',
    ];
    $durationLabel = $durationTypes[$membershipPlan->duration_type] ?? ucfirst($membershipPlan->duration_type);
@endphp

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">{{ $membershipPlan->name }}</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-price-tag-3-line"></i>
                    Plano de consultoria
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('membership-plans.edit', $membershipPlan->id) }}" class="mg-btn-primary">
                <i class="ri-pencil-line"></i> Editar
            </a>
            <a href="{{ route('membership-plans.index') }}" class="mg-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <div class="mg-product-show">
        <div class="mg-panel mg-panel--compact mg-product-show__price">
            <div class="mg-panel-value mg-panel-value--sm mb-2">R$ {{ number_format($membershipPlan->price, 2, ',', '.') }}</div>
            <p class="text-muted mb-3">{{ $membershipPlan->duration_value }} {{ strtolower($durationLabel) }}</p>
            @if($membershipPlan->is_active)
                <span class="mg-chip mg-chip--success">Ativo</span>
            @else
                <span class="mg-chip mg-chip--danger">Inativo</span>
            @endif
            @if($membershipPlan->personal_training)
                <p class="small text-muted mt-3 mb-0"><i class="ri-user-star-line me-1"></i> Inclui treino personalizado</p>
            @endif
        </div>

        <div class="mg-panel mg-panel--compact">
            <div class="mg-panel-label mb-3">Detalhes</div>
            <dl class="mg-detail-grid mb-0">
                <dt>Nome</dt><dd>{{ $membershipPlan->name }}</dd>
                <dt>Preço</dt><dd>R$ {{ number_format($membershipPlan->price, 2, ',', '.') }}</dd>
                <dt>Duração</dt><dd>{{ $membershipPlan->duration_value }} {{ strtolower($durationLabel) }}</dd>
                <dt>Status</dt>
                <dd>
                    @if($membershipPlan->is_active)
                        <span class="mg-chip mg-chip--success">Ativo</span>
                    @else
                        <span class="mg-chip mg-chip--danger">Inativo</span>
                    @endif
                </dd>
                <dt>Treino personalizado</dt>
                <dd>{{ $membershipPlan->personal_training ? 'Sim' : 'Não' }}</dd>
                <dt>Descrição</dt><dd>{{ $membershipPlan->description ?? '—' }}</dd>
                <dt>Benefícios</dt>
                <dd>
                    @if($membershipPlan->features)
                        <ul class="mb-0 ps-3">
                            @foreach($membershipPlan->features as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    @else
                        —
                    @endif
                </dd>
            </dl>
        </div>
    </div>
</div>
@endsection
