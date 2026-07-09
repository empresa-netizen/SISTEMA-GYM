@php
    $statusLabels = [
        'pending' => ['Pendente', 'prime-chip--warn'],
        'resolved' => ['Resolvido', 'prime-chip--success'],
        'closed' => ['Fechado', ''],
    ];
@endphp

<div class="prime-tab-block">
    <div class="prime-tab-block__head">
        <div>
            <p class="prime-section-label mb-1">Relacionamento</p>
            <h2 class="prime-tab-block__title">Feedbacks</h2>
        </div>
        <a href="{{ route('feedbacks.index') }}" class="prime-btn-ghost">Fila geral</a>
    </div>

    @forelse($member->feedbacks as $feedback)
        @php [$statusLabel, $statusClass] = $statusLabels[$feedback->status] ?? [ucfirst($feedback->status), '']; @endphp
        <div class="prime-list-row align-items-start">
            <div class="prime-list-body">
                <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                    <span class="prime-chip {{ $statusClass }}">{{ $statusLabel }}</span>
                    @if($feedback->rating)<span class="prime-chip prime-chip--warn">{{ $feedback->rating }} ★</span>@endif
                </div>
                <div class="prime-list-title">{{ Str::limit($feedback->message ?? '—', 100) }}</div>
                <div class="prime-list-sub">{{ $feedback->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <a href="{{ route('feedbacks.index', ['tab' => $feedback->status]) }}" class="prime-btn-ghost prime-btn-ghost--sm">Abrir</a>
        </div>
    @empty
        <div class="prime-empty-state prime-empty-state--compact">
            <i class="ri-chat-smile-2-line"></i>
            <p>Nenhum feedback deste cliente.</p>
            <a href="{{ route('feedbacks.index') }}" class="prime-btn-ghost">Ver fila geral</a>
        </div>
    @endforelse
</div>
