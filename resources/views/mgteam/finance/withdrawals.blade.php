@extends('layouts.master')

@section('title', 'Saques')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Financeiro</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-bank-card-line"></i>
                    Saques e repasses
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <span class="mg-chip mg-chip--info">{{ config('brand.pay', 'MGTEAM Pay') }}</span>
        </div>
    </div>

    <p class="mg-page-sub mb-0">Saques e repasses com {{ config('brand.pay', 'MGTEAM Pay') }}.</p>

    @include('mgteam.finance._tabs', ['active' => 'withdrawals'])

    <div class="row g-2">
        <div class="col-lg-7">
            <div class="mg-panel mg-panel--compact">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mg-section-title h6 mb-0">Solicitar saque</h2>
                    <span class="mg-chip">Simulação</span>
                </div>
                <form class="row g-3" id="withdrawRequestForm" action="javascript:void(0)">
                    <div class="col-md-6">
                        <label class="mg-field-label" for="withdrawAmount">Valor do saque (R$)</label>
                        <input type="number" min="10" step="0.01" class="mg-field" id="withdrawAmount" placeholder="Ex: 850,00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="mg-field-label" for="withdrawAccount">Conta de destino</label>
                        <select class="mg-field" id="withdrawAccount" required>
                            <option value="">Selecione</option>
                            <option>Conta PJ principal</option>
                            <option>Conta PJ reserva</option>
                            <option>PIX CNPJ</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="mg-field-label">Previsão de processamento</label>
                        <input type="text" class="mg-field" value="Até 1 dia útil" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="mg-field-label" for="withdrawFeePreview">Taxa estimada</label>
                        <input type="text" class="mg-field" id="withdrawFeePreview" value="R$ 0,00" disabled>
                    </div>
                    <div class="col-12">
                        <label class="mg-field-label" for="withdrawNotes">Observações internas</label>
                        <textarea class="mg-field" rows="2" id="withdrawNotes" placeholder="Ex: fechamento semanal"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="mg-btn-primary"><i class="ri-send-plane-line"></i> Simular solicitação</button>
                    </div>
                </form>
                <div class="alert alert-info mt-3 mb-0 d-none" id="withdrawResult"></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="mg-panel mg-panel--compact h-100">
                <h2 class="mg-section-title h6 mb-3">Status de repasse</h2>
                <div class="mg-list-row">
                    <div class="mg-list-body">
                        <div class="mg-list-title">Saldo elegível</div>
                        <div class="mg-list-sub">Atualizado em tempo real</div>
                    </div>
                    <strong class="text-success">R$ 0,00</strong>
                </div>
                <div class="mg-list-row">
                    <div class="mg-list-body">
                        <div class="mg-list-title">Último repasse</div>
                        <div class="mg-list-sub">Nenhum repasse concluído</div>
                    </div>
                    <span class="mg-chip">Sem histórico</span>
                </div>
                <div class="mg-list-row">
                    <div class="mg-list-body">
                        <div class="mg-list-title">Janela de saque</div>
                        <div class="mg-list-sub">Segunda a sexta · 08h às 18h</div>
                    </div>
                    <span class="mg-chip mg-chip--success">Aberta</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const amountInput = document.getElementById('withdrawAmount');
    const feePreview = document.getElementById('withdrawFeePreview');
    const form = document.getElementById('withdrawRequestForm');
    const result = document.getElementById('withdrawResult');
    const account = document.getElementById('withdrawAccount');

    const toCurrency = function (value) {
        return 'R$ ' + Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const updateFee = function () {
        const amount = Number(amountInput.value || 0);
        const fee = amount > 0 ? Math.max(1.99, amount * 0.01) : 0;
        feePreview.value = toCurrency(fee);
    };

    amountInput.addEventListener('input', updateFee);
    updateFee();

    form.addEventListener('submit', function () {
        const amount = Number(amountInput.value || 0);
        const fee = amount > 0 ? Math.max(1.99, amount * 0.01) : 0;
        const liquid = Math.max(amount - fee, 0);
        if (!amount || !account.value) {
            result.className = 'alert alert-warning mt-3 mb-0';
            result.textContent = 'Preencha valor e conta para simular o saque.';
            return;
        }
        result.className = 'alert alert-info mt-3 mb-0';
        result.textContent = `Solicitação simulada no ${@json(config('brand.pay', 'MGTEAM Pay'))}: bruto ${toCurrency(amount)}, taxa ${toCurrency(fee)}, líquido ${toCurrency(liquid)}.`;
    });
});
</script>
@endsection
