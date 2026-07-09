@php
    $reviews = $member->healthRecords;
@endphp

<div class="prime-tab-block">
    <div class="prime-tab-block__head">
        <div>
            <p class="prime-section-label mb-1">Avaliações físicas</p>
            <h2 class="prime-tab-block__title">Histórico de avaliações</h2>
        </div>
        <a href="{{ route('healths.create', ['member_id' => $member->id]) }}" class="prime-btn-primary">
            <i class="ri-add-line"></i> Nova avaliação
        </a>
    </div>

    @forelse($reviews as $health)
        <div class="prime-list-row">
            <div class="prime-list-body">
                <div class="prime-list-title">{{ $health->measurement_date->format('d/m/Y') }}</div>
                <div class="prime-list-sub">
                    @if($health->getMeasurement('weight')) Peso {{ $health->getMeasurement('weight') }} kg @endif
                    @if($health->getMeasurement('body_fat')) · Gordura {{ $health->getMeasurement('body_fat') }}% @endif
                    @if($health->bmi) · IMC {{ $health->bmi }} @endif
                </div>
            </div>
            <a href="{{ route('healths.show', $health) }}" class="prime-btn-ghost prime-btn-ghost--sm">Abrir</a>
        </div>
    @empty
        <div class="prime-empty-state prime-empty-state--compact">
            <i class="ri-file-chart-line"></i>
            <p>Nenhuma avaliação registrada para este cliente.</p>
            <a href="{{ route('healths.create', ['member_id' => $member->id]) }}" class="prime-btn-primary">Nova avaliação</a>
        </div>
    @endforelse
</div>
