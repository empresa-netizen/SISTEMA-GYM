@extends('layouts.master')

@section('title', 'Afiliados')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Programa de Afiliados</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter prime-clients-counter--delivered">
                    <i class="ri-team-line"></i>
                    {{ $stats['active_affiliates'] }} ativos
                </span>
                <span class="prime-clients-counter">
                    <i class="ri-percent-line"></i>
                    {{ $stats['commission_rate'] }}% comissão
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('products.hub') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Hub produtos
            </a>
            <button type="button" class="prime-btn-primary" disabled title="Em breve">
                <i class="ri-user-add-line"></i> Convidar afiliado
            </button>
        </div>
    </div>

    <div class="prime-panel prime-panel--compact prime-affiliate-invite">
        <div class="prime-affiliate-invite__main">
            <div class="prime-panel-label mb-1">Seu link de indicação</div>
            <p class="small text-muted mb-2">Compartilhe com parceiros e acompanhe conversões.</p>
            <div class="prime-affiliate-invite__row">
                <code id="affiliate-invite-url" class="prime-code prime-code--block">{{ $inviteUrl }}</code>
                <button type="button" class="prime-btn-primary" id="copy-invite-btn">
                    <i class="ri-file-copy-line"></i> Copiar
                </button>
            </div>
        </div>
        <div class="prime-affiliate-invite__code">
            <div class="prime-panel-label mb-1">Código</div>
            <span class="prime-chip prime-chip--info">{{ $inviteCode }}</span>
        </div>
    </div>

    <div class="prime-stats-row">
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Comissões pagas</div>
            <div class="prime-stat-value">R$ {{ number_format($stats['total_commission'], 2, ',', '.') }}</div>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">A receber</div>
            <div class="prime-stat-value text-warning">R$ {{ number_format($stats['pending_commission'], 2, ',', '.') }}</div>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Conversões no mês</div>
            <div class="prime-stat-value">{{ $stats['conversions_month'] }}</div>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Afiliados ativos</div>
            <div class="prime-stat-value">{{ $stats['active_affiliates'] }}</div>
        </div>
    </div>

    <div class="prime-client-list">
        @forelse($affiliates as $affiliate)
            <div class="prime-client-card">
                <div class="prime-client-card__main">
                    @php
                        $initials = collect(explode(' ', $affiliate['name']))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                    @endphp
                    <div class="prime-client-card__avatar">{{ strtoupper($initials) }}</div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name">{{ $affiliate['name'] }}</div>
                        <div class="prime-client-card__meta">
                            <span>{{ $affiliate['email'] }}</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>{{ $affiliate['conversions'] }} conversões</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>R$ {{ number_format($affiliate['commission'], 2, ',', '.') }}</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>Desde {{ $affiliate['joined'] }}</span>
                        </div>
                        <div class="prime-client-chips">
                            @if($affiliate['status'] === 'ativo')
                                <span class="prime-chip prime-chip--success">Ativo</span>
                            @else
                                <span class="prime-chip prime-chip--warn">Pendente</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
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
