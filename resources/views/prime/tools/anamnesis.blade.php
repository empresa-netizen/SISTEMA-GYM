@extends('layouts.master')

@section('title', 'Anamnese')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Anamnese</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-file-list-3-line"></i>
                    {{ $entries->total() }} formulários
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('members.index') }}" class="prime-btn-ghost">
                <i class="ri-group-line"></i> Clientes ativos
            </a>
        </div>
    </div>

    <p class="prime-page-sub mb-0">Formulários e respostas dos clientes.</p>

    <div class="prime-client-list">
        @forelse($entries as $entry)
            @php
                $name = $entry->member?->name ?? '—';
                $initials = collect(explode(' ', $name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
            @endphp
            <div class="prime-client-card">
                <div class="prime-client-card__main">
                    <div class="prime-client-card__avatar">{{ strtoupper($initials) }}</div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name">{{ $name }}</div>
                        <div class="prime-client-card__meta">
                            <span>Atualizado {{ $entry->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                <div class="prime-client-card__actions">
                    @if($entry->member)
                        <a href="{{ route('members.show', ['member' => $entry->member, 'tab' => 'anamnesis']) }}" class="prime-btn-ghost prime-btn-ghost--sm">
                            Abrir ficha
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-file-list-3-line"></i>
                <p>Nenhuma anamnese registrada.</p>
            </div>
        @endforelse
    </div>

    @if($entries->hasPages())
        <div class="prime-pagination">{{ $entries->links() }}</div>
    @endif
</div>
@endsection
