@extends('layouts.master')

@section('title', 'Transações')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Financeiro</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter prime-clients-counter--delivered">
                    <i class="ri-checkbox-circle-fill"></i>
                    R$ {{ number_format($totalPaid, 2, ',', '.') }} recebido
                </span>
                <span class="prime-clients-counter prime-clients-counter--pending">
                    <i class="ri-error-warning-fill"></i>
                    R$ {{ number_format($totalDue, 2, ',', '.') }} em aberto
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            @can('create payments')
                <a href="{{ route('invoices.create') }}" class="prime-btn-primary">
                    <i class="ri-add-line"></i> Nova venda
                </a>
            @endcan
        </div>
    </div>

    <p class="prime-page-sub mb-0">Transações, faturas e recebimentos no {{ config('brand.pay', 'MGTEAM Pay') }}.</p>

    @include('prime.finance._tabs', ['active' => 'transactions'])

    <div class="row g-2">
        <div class="col-md-4">
            <div class="prime-stat-card prime-stat-card--compact">
                <div class="p-2">
                    <div class="prime-stat-label">Total faturado</div>
                    <div class="prime-stat-value">R$ {{ number_format($totalAmount, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="prime-stat-card prime-stat-card--compact">
                <div class="p-2">
                    <div class="prime-stat-label">Total recebido</div>
                    <div class="prime-stat-value text-success">R$ {{ number_format($totalPaid, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="prime-stat-card prime-stat-card--compact">
                <div class="p-2">
                    <div class="prime-stat-label">Em aberto</div>
                    <div class="prime-stat-value text-danger">R$ {{ number_format($totalDue, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="prime-panel prime-panel--compact">
        <form method="GET" action="{{ route('finance.index') }}" class="prime-clients-filters__form mb-3">
            <input type="hidden" name="tab" value="transactions">
            <div class="prime-clients-filters__grid">
                <div>
                    <label class="prime-field-label">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="prime-field" placeholder="Fatura ou cliente...">
                </div>
                <div>
                    <label class="prime-field-label">Status</label>
                    <select name="status" class="prime-field">
                        <option value="">Todos os status</option>
                        <option value="paid" @selected(request('status') === 'paid')>Pago</option>
                        <option value="partially_paid" @selected(request('status') === 'partially_paid')>Parcial / Pendente</option>
                        <option value="unpaid" @selected(request('status') === 'unpaid')>Em aberto</option>
                        <option value="overdue" @selected(request('status') === 'overdue')>Atrasado</option>
                    </select>
                </div>
                <div>
                    <label class="prime-field-label">De</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="prime-field">
                </div>
                <div>
                    <label class="prime-field-label">Até</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="prime-field">
                </div>
                <div class="prime-clients-filters__actions">
                    <button type="submit" class="prime-btn-primary">Filtrar</button>
                    <a href="{{ route('finance.index', ['tab' => 'transactions']) }}" class="prime-btn-ghost">Limpar</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">{!! $dataTable->table() !!}</div>
    </div>
</div>
@endsection

@section('script')
{!! $dataTable->scripts() !!}
@endsection
