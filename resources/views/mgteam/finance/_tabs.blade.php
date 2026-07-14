@php $active = $active ?? 'dashboard'; @endphp
<div class="mg-segment-tabs mg-finance-segment" role="tablist">
    <a href="{{ route('finance.index') }}"
       class="mg-segment-tab @if($active === 'dashboard') is-active @endif">
        <i class="ri-pie-chart-2-line"></i> Dashboard
    </a>
    <a href="{{ route('finance.index', ['tab' => 'transactions']) }}"
       class="mg-segment-tab @if($active === 'transactions') is-active @endif">
        <i class="ri-wallet-3-line"></i> Transações
    </a>
    <a href="{{ route('finance.index', ['tab' => 'withdrawals']) }}"
       class="mg-segment-tab @if($active === 'withdrawals') is-active @endif">
        <i class="ri-bank-card-line"></i> Saques
    </a>
    <a href="{{ route('finance.index', ['tab' => 'reports']) }}"
       class="mg-segment-tab @if($active === 'reports') is-active @endif">
        <i class="ri-file-chart-line"></i> Relatórios
    </a>
    <span class="mg-finance-chip ms-auto">
        <i class="ri-secure-payment-line"></i>{{ config('brand.pay', 'MGTEAM Pay') }}
    </span>
</div>
