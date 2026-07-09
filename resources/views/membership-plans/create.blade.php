@extends('layouts.master')

@section('title', 'Novo plano')

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
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Novo plano</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-price-tag-3-line"></i>
                    Configure preço, duração e benefícios
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('membership-plans.index') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <form action="{{ route('membership-plans.store') }}" method="POST" class="prime-form-stack">
        @csrf

        <div class="prime-panel prime-panel--compact">
            <div class="prime-panel-label mb-3">Dados do plano</div>
            <div class="prime-form-grid">
                <div>
                    <label for="name" class="prime-field-label">Nome do plano</label>
                    <input type="text" class="prime-field @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="price" class="prime-field-label">Preço (R$)</label>
                    <input type="number" step="0.01" class="prime-field @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                    @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="duration_type" class="prime-field-label">Tipo de duração</label>
                    <select class="prime-field @error('duration_type') is-invalid @enderror" id="duration_type" name="duration_type" required>
                        @foreach($durationTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('duration_type', 'monthly') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('duration_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="duration_value" class="prime-field-label">Quantidade</label>
                    <input type="number" class="prime-field @error('duration_value') is-invalid @enderror" id="duration_value" name="duration_value" value="{{ old('duration_value', 1) }}" required>
                    @error('duration_value')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="prime-form-grid__full">
                    <label for="description" class="prime-field-label">Descrição</label>
                    <textarea class="prime-field @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="prime-panel prime-panel--compact">
            <div class="prime-panel-label mb-3">Configurações</div>
            <div class="prime-form-switches">
                <label class="prime-switch">
                    <input type="checkbox" id="personal_training" name="personal_training" value="1" @checked(old('personal_training'))>
                    <span>Inclui treino personalizado</span>
                </label>
                <label class="prime-switch">
                    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                    <span>Plano ativo</span>
                </label>
            </div>
        </div>

        <div class="prime-form-actions">
            <a href="{{ route('membership-plans.index') }}" class="prime-btn-ghost">Cancelar</a>
            <button type="submit" class="prime-btn-primary"><i class="ri-save-line"></i> Criar plano</button>
        </div>
    </form>
</div>
@endsection
