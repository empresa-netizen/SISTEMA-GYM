@extends('layouts.master')

@section('title', 'Afiliados')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Programa de Afiliados</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-team-line"></i>
                    {{ $stats['active_affiliates'] }} ativos
                </span>
                <span class="mg-clients-counter">
                    <i class="ri-percent-line"></i>
                    {{ $stats['commission_rate'] }}% comissão
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('products.hub') }}" class="mg-btn-ghost">
                <i class="ri-arrow-left-line"></i> Hub produtos
            </a>
            <button type="button" class="mg-btn-primary" disabled title="Em breve">
                <i class="ri-user-add-line"></i> Convidar afiliado
            </button>
        </div>
    </div>

    <div class="mg-panel mg-panel--compact mg-affiliate-invite">
        <div class="mg-affiliate-invite__main">
            <div class="mg-panel-label mb-1">Seu link de indicação</div>
            <p class="small text-muted mb-2">Compartilhe com parceiros e acompanhe conversões.</p>
            <div class="mg-affiliate-invite__row">
                <code id="affiliate-invite-url" class="mg-code mg-code--block">{{ $inviteUrl }}</code>
                <button type="button" class="mg-btn-primary" id="copy-invite-btn">
                    <i class="ri-file-copy-line"></i> Copiar
                </button>
            </div>
        </div>
        <div class="mg-affiliate-invite__code">
            <div class="mg-panel-label mb-1">Código</div>
            <span class="mg-chip mg-chip--info">{{ $inviteCode }}</span>
        </div>
    </div>

    <div class="mg-stats-row">
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Comissões pagas</div>
            <div class="mg-stat-value">R$ {{ number_format($stats['total_commission'], 2, ',', '.') }}</div>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">A receber</div>
            <div class="mg-stat-value text-warning">R$ {{ number_format($stats['pending_commission'], 2, ',', '.') }}</div>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Conversões no mês</div>
            <div class="mg-stat-value">{{ $stats['conversions_month'] }}</div>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Afiliados ativos</div>
            <div class="mg-stat-value">{{ $stats['active_affiliates'] }}</div>
        </div>
    </div>

    <div class="mg-client-list">
        @forelse($affiliates as $affiliate)
            <div class="mg-client-card">
                <div class="mg-client-card__main">
                    @php
                        $initials = collect(explode(' ', $affiliate['name']))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                    @endphp
                    <div class="mg-client-card__avatar">{{ strtoupper($initials) }}</div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $affiliate['name'] }}</div>
                        <div class="mg-client-card__meta">
                            <span>{{ $affiliate['email'] }}</span>
                            <span class="mg-client-card__sep">|</span>
                            <span>{{ $affiliate['conversions'] }} conversões</span>
                            <span class="mg-client-card__sep">|</span>
                            <span>R$ {{ number_format($affiliate['commission'], 2, ',', '.') }}</span>
                            <span class="mg-client-card__sep">|</span>
                            <span>Desde {{ $affiliate['joined'] }}</span>
                        </div>
                        <div class="mg-client-chips">
                            @if($affiliate['status'] === 'ativo')
                                <span class="mg-chip mg-chip--success">Ativo</span>
                            @else
                                <span class="mg-chip mg-chip--warn">Pendente</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-team-line"></i>
                <p>Nenhum afiliado cadastrado ainda.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('copy-invite-btn')?.addEventListener('click', function () {
    const url = document.getElementById('affiliate-invite-url')?.textContent?.trim();
    if (!url) return;
    navigator.clipboard.writeText(url).then(() => {
        const original = this.innerHTML;
        this.innerHTML = '<i class="ri-check-line"></i> Copiado!';
        setTimeout(() => { this.innerHTML = original; }, 2000);
    });
});
</script>
@endsection
