@extends('layouts.master')

@section('title', 'Busca')

@section('content')
<div class="mb-4">
    <h1 class="prime-page-title">Buscar</h1>
    <p class="prime-page-sub">Clientes, treinos e faturas.</p>
</div>

<form method="GET" action="{{ route('search') }}" class="mb-4">
    <div class="input-group input-group-lg">
        <span class="input-group-text"><i class="ri-search-line"></i></span>
        <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Digite para buscar..." autofocus>
        <button class="btn btn-primary" type="submit">Buscar</button>
    </div>
</form>

@if(strlen($q) < 2)
    <div class="text-muted">Digite pelo menos 2 caracteres.</div>
@else
    <div class="row g-4">
        <div class="col-lg-4">
            <h5 class="mb-3">Clientes</h5>
            @forelse($members as $member)
                <a href="{{ route('members.show', $member) }}" class="prime-search-item">
                    <strong>{{ $member->name }}</strong>
                    <span>{{ $member->email }}</span>
                </a>
            @empty
                <p class="text-muted small">Nenhum cliente encontrado.</p>
            @endforelse
        </div>
        <div class="col-lg-4">
            <h5 class="mb-3">Treinos</h5>
            @forelse($workouts as $workout)
                <a href="{{ route('workouts.show', $workout) }}" class="prime-search-item">
                    <strong>{{ $workout->name }}</strong>
                    <span>{{ $workout->member?->name ?? 'Sem cliente' }}</span>
                </a>
            @empty
                <p class="text-muted small">Nenhum treino encontrado.</p>
            @endforelse
        </div>
        <div class="col-lg-4">
            <h5 class="mb-3">Faturas</h5>
            @forelse($invoices as $invoice)
                <a href="{{ route('invoices.show', $invoice) }}" class="prime-search-item">
                    <strong>{{ $invoice->invoice_number }}</strong>
                    <span>{{ $invoice->member?->name }} · R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</span>
                </a>
            @empty
                <p class="text-muted small">Nenhuma fatura encontrada.</p>
            @endforelse
        </div>
    </div>
@endif
@endsection
