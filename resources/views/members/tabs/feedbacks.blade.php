@php
    $statusLabels = [
        'pending' => ['Pendente', 'mg-chip--warn'],
        'resolved' => ['Resolvido', 'mg-chip--success'],
        'closed' => ['Fechado', ''],
    ];
@endphp

<div class="mg-tab-block">
    <div class="mg-tab-block__head">
        <div>
            <p class="mg-section-label mb-1">Relacionamento</p>
            <h2 class="mg-tab-block__title">Feedbacks</h2>
        </div>
        <a href="{{ route('feedbacks.index') }}" class="mg-btn-ghost">Fila geral</a>
    </div>

    @forelse($member->feedbacks as $feedback)
        @php [$statusLabel, $statusClass] = $statusLabels[$feedback->status] ?? [ucfirst($feedback->status), '']; @endphp
        <div class="mg-list-row align-items-start">
            <div class="mg-list-body">
                <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                    <span class="mg-chip {{ $statusClass }}">{{ $statusLabel }}</span>
                    @if($feedback->rating)<span class="mg-chip mg-chip--warn">{{ $feedback->rating }} ★</span>@endif
                </div>
                <div class="mg-list-title">{{ Str::limit($feedback->message ?? '—', 100) }}</div>
                <div class="mg-list-sub">{{ $feedback->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <a href="{{ route('feedbacks.index', ['tab' => $feedback->status]) }}" class="mg-btn-ghost mg-btn-ghost--sm">Abrir</a>
        </div>
    @empty
        <div class="mg-empty-state mg-empty-state--compact">
            <i class="ri-chat-smile-2-line"></i>
            <p>Nenhum feedback deste cliente.</p>
            <a href="{{ route('feedbacks.index') }}" class="mg-btn-ghost">Ver fila geral</a>
        </div>
    @endforelse
</div>
