@php
    $reviews = $member->healthRecords;
@endphp

<div class="mg-tab-block">
    <div class="mg-tab-block__head">
        <div>
            <p class="mg-section-label mb-1">Avaliações físicas</p>
            <h2 class="mg-tab-block__title">Histórico de avaliações</h2>
        </div>
        <a href="{{ route('healths.create', ['member_id' => $member->id]) }}" class="mg-btn-primary">
            <i class="ri-add-line"></i> Nova avaliação
        </a>
    </div>

    @forelse($reviews as $health)
        <div class="mg-list-row">
            <div class="mg-list-body">
                <div class="mg-list-title">{{ $health->measurement_date->format('d/m/Y') }}</div>
                <div class="mg-list-sub">
                    @if($health->getMeasurement('weight')) Peso {{ $health->getMeasurement('weight') }} kg @endif
                    @if($health->getMeasurement('body_fat')) · Gordura {{ $health->getMeasurement('body_fat') }}% @endif
                    @if($health->bmi) · IMC {{ $health->bmi }} @endif
                </div>
            </div>
            <a href="{{ route('healths.show', $health) }}" class="mg-btn-ghost mg-btn-ghost--sm">Abrir</a>
        </div>
    @empty
        <div class="mg-empty-state mg-empty-state--compact">
            <i class="ri-file-chart-line"></i>
            <p>Nenhuma avaliação registrada para este cliente.</p>
            <a href="{{ route('healths.create', ['member_id' => $member->id]) }}" class="mg-btn-primary">Nova avaliação</a>
        </div>
    @endforelse
</div>
