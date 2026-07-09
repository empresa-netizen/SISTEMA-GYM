@extends('layouts.master')

@section('title', 'Ticket '.$supportTicket->ticket_number)

@section('content')
@php
    $statusMap = [
        'open' => ['Aberto', 'prime-chip--danger'],
        'in_progress' => ['Em andamento', 'prime-chip--warn'],
        'resolved' => ['Resolvido', 'prime-chip--success'],
        'closed' => ['Fechado', ''],
    ];
    [$statusLabel, $statusClass] = $statusMap[$supportTicket->status] ?? [ucfirst($supportTicket->status), ''];
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">{{ $supportTicket->subject }}</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-ticket-2-line"></i>
                    {{ $supportTicket->ticket_number }}
                </span>
                <span class="prime-chip {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('support-tickets.index') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <p class="prime-page-sub mb-0">{{ $supportTicket->member->name ?? 'Sem cliente' }}</p>

    <div class="row g-2">
        <div class="col-lg-8">
            <div class="prime-panel prime-panel--compact mb-2">
                <div class="prime-panel-label mb-2">DESCRIÇÃO</div>
                <p class="mb-0">{{ $supportTicket->description }}</p>
            </div>

            <div class="prime-panel prime-panel--compact">
                <div class="prime-panel-label mb-3">CONVERSA</div>

                @forelse($supportTicket->replies as $reply)
                    <div class="prime-feed-card mb-2">
                        <div class="d-flex justify-content-between gap-2">
                            <strong>{{ $reply->user->name }}</strong>
                            <small class="text-muted">{{ $reply->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        @if($reply->is_internal_note)
                            <span class="prime-chip mt-1">Nota interna</span>
                        @endif
                        <p class="mt-2 mb-0">{{ $reply->message }}</p>
                    </div>
                @empty
                    <div class="prime-empty-state prime-empty-state--compact">
                        <i class="ri-chat-1-line"></i>
                        <p>Nenhuma resposta ainda.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-4">
            <div class="prime-panel prime-panel--compact">
                <div class="prime-panel-label mb-3">RESPONDER</div>
                <form action="{{ route('support-tickets.reply', $supportTicket->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="message" class="prime-field-label">Mensagem</label>
                        <textarea name="message" id="message" class="prime-field" rows="6" required></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_internal_note" name="is_internal_note">
                        <label class="form-check-label" for="is_internal_note">
                            Nota interna (não visível ao cliente)
                        </label>
                    </div>
                    <button type="submit" class="prime-btn-primary w-100 mb-2 justify-content-center">
                        <i class="ri-send-plane-line"></i> Enviar resposta
                    </button>
                </form>
                <a href="{{ route('support-tickets.edit', $supportTicket->id) }}" class="prime-btn-ghost w-100 justify-content-center">
                    <i class="ri-pencil-line"></i> Editar ticket
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
