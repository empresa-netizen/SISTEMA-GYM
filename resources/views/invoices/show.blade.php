@extends('layouts.master')

@section('title', 'Fatura '.$invoice->invoice_number)

@section('content')
@php
    $statusLabels = [
        'paid' => ['Pago', 'bg-success'],
        'partially_paid' => ['Parcial', 'bg-warning text-dark'],
        'unpaid' => ['Em aberto', 'bg-danger'],
        'cancelled' => ['Cancelado', 'bg-secondary'],
    ];
    [$statusLabel, $statusClass] = $statusLabels[$invoice->status] ?? [$invoice->status, 'bg-secondary'];
    $methodLabels = [
        'cash' => 'Dinheiro',
        'card' => 'Cartão',
        'bank_transfer' => 'PIX / Transferência',
        'cheque' => 'Cheque',
        'other' => 'Outro',
    ];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Fatura {{ $invoice->invoice_number }}</h1>
        <p class="mg-page-sub">{{ $invoice->member->name }} · {{ $invoice->invoice_date->format('d/m/Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Voltar
        </a>
        @if($invoice->remaining_balance > 0)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                <i class="ri-money-dollar-circle-line me-1"></i> Registrar pagamento
            </button>
        @endif
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xxl-9">
        <div class="mg-panel">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <h5 class="mb-1">{{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}</h5>
                    <p class="text-muted mb-0 small">Consultoria online</p>
                </div>
                <div class="text-end">
                    <div class="mb-1"><span class="text-muted">Nº:</span> <strong>{{ $invoice->invoice_number }}</strong></div>
                    <div class="mb-1"><span class="text-muted">Emissão:</span> {{ $invoice->invoice_date->format('d/m/Y') }}</div>
                    <div class="mb-1"><span class="text-muted">Vencimento:</span> {{ $invoice->due_date->format('d/m/Y') }}</div>
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
            </div>

            <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <p class="text-muted text-uppercase small fw-semibold mb-1">Cliente</p>
                        <h6 class="mb-0">{{ $invoice->member->name }}</h6>
                        <p class="text-muted mb-0 small">{{ $invoice->member->email }}</p>
                        @if($invoice->member->phone)
                            <p class="text-muted mb-0 small">{{ $invoice->member->phone }}</p>
                        @endif
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Descrição</th>
                                <th class="text-end">Valor unit.</th>
                                <th class="text-end">Qtd</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-end">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        @if($invoice->notes)
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Observações</p>
                            <p class="mb-0">{{ $invoice->notes }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless ms-auto" style="max-width: 280px">
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-end">R$ {{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
                            </tr>
                            @if($invoice->tax_amount > 0)
                            <tr>
                                <td>Impostos</td>
                                <td class="text-end">R$ {{ number_format($invoice->tax_amount, 2, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($invoice->discount_amount > 0)
                            <tr>
                                <td>Desconto</td>
                                <td class="text-end text-danger">- R$ {{ number_format($invoice->discount_amount, 2, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="border-top">
                                <th>Total</th>
                                <th class="text-end">R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <td>Pago</td>
                                <td class="text-end text-success">R$ {{ number_format($invoice->paid_amount, 2, ',', '.') }}</td>
                            </tr>
                            <tr class="border-top">
                                <th>Saldo</th>
                                <th class="text-end {{ $invoice->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                                    R$ {{ number_format($invoice->remaining_balance, 2, ',', '.') }}
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($invoice->payments->count() > 0)
                <hr>
                <h6 class="mb-3">Histórico de pagamentos</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Método</th>
                                <th>Referência</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td>{{ $methodLabels[$payment->payment_method] ?? $payment->payment_method }}</td>
                                <td>{{ $payment->reference_number ?? '—' }}</td>
                                <td class="text-end text-success">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
        </div>
    </div>
</div>

<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('invoices.addPayment', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Valor (R$)</label>
                        <input type="number" name="amount" class="form-control" value="{{ $invoice->remaining_balance }}" max="{{ $invoice->remaining_balance }}" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Data do pagamento</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Forma de pagamento</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="bank_transfer">PIX / Transferência</option>
                            <option value="card">Cartão</option>
                            <option value="cash">Dinheiro</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Outro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Referência (ex: chave PIX)</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Opcional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar pagamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
