@extends('layouts.master')

@section('title', 'Anamnese')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Anamnese</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-file-list-3-line"></i>
                    {{ $entries->total() }} formulários
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('members.index') }}" class="mg-btn-ghost">
                <i class="ri-group-line"></i> Clientes ativos
            </a>
        </div>
    </div>

    <p class="mg-page-sub mb-0">Formulários e respostas dos clientes.</p>

    <div class="mg-client-list">
        @forelse($entries as $entry)
            @php
                $name = $entry->member?->name ?? '—';
                $initials = collect(explode(' ', $name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
            @endphp
            <div class="mg-client-card">
                <div class="mg-client-card__main">
                    <div class="mg-client-card__avatar">{{ strtoupper($initials) }}</div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $name }}</div>
                        <div class="mg-client-card__meta">
                            <span>Atualizado {{ $entry->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                <div class="mg-client-card__actions">
                    @if($entry->member)
                        <a href="{{ route('members.show', ['member' => $entry->member, 'tab' => 'anamnesis']) }}" class="mg-btn-ghost mg-btn-ghost--sm">
                            Abrir ficha
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-file-list-3-line"></i>
                <p>Nenhuma anamnese registrada.</p>
            </div>
        @endforelse
    </div>

    @if($entries->hasPages())
        <div class="mg-pagination">{{ $entries->links() }}</div>
    @endif
</div>
@endsection
