@php
    $workoutsCount = $member->workouts->count();
    $feedbacksCount = $member->feedbacks->count();
    $hydrationEntries = $member->logbooks->where('type', 'DIET')->count();
    $weightRecords = $member->healthRecords->filter(fn ($h) => $h->getMeasurement('weight'))->values();
    $latestWeight = $weightRecords->first()?->getMeasurement('weight');
    $firstWeight = $weightRecords->last()?->getMeasurement('weight');
    $weightDelta = ($latestWeight && $firstWeight) ? round($latestWeight - $firstWeight, 1) : null;
@endphp

<div class="prime-progress">
    <div class="prime-progress-metrics">
        <div class="prime-progress-metric">
            <div class="prime-progress-metric__icon"><i class="ri-run-line"></i></div>
            <div>
                <span class="prime-progress-metric__label">Treinos</span>
                <strong class="prime-progress-metric__value">{{ $workoutsCount }}</strong>
            </div>
        </div>
        <div class="prime-progress-metric">
            <div class="prime-progress-metric__icon"><i class="ri-chat-smile-2-line"></i></div>
            <div>
                <span class="prime-progress-metric__label">Feedbacks</span>
                <strong class="prime-progress-metric__value">{{ $feedbacksCount }}</strong>
            </div>
        </div>
        <div class="prime-progress-metric">
            <div class="prime-progress-metric__icon"><i class="ri-drop-line"></i></div>
            <div>
                <span class="prime-progress-metric__label">Hidratação / dieta</span>
                <strong class="prime-progress-metric__value">{{ $hydrationEntries }}</strong>
            </div>
        </div>
        <div class="prime-progress-metric">
            <div class="prime-progress-metric__icon"><i class="ri-scales-3-line"></i></div>
            <div>
                <span class="prime-progress-metric__label">Peso atual</span>
                <strong class="prime-progress-metric__value">
                    {{ $latestWeight ? rtrim(rtrim(number_format($latestWeight, 1, ',', ''), '0'), ',').' kg' : '—' }}
                </strong>
                @if($weightDelta !== null)
                    <span class="prime-progress-metric__delta {{ $weightDelta <= 0 ? 'is-down' : 'is-up' }}">
                        {{ $weightDelta > 0 ? '+' : '' }}{{ number_format($weightDelta, 1, ',', '') }} kg
                    </span>
                @endif
            </div>
        </div>
    </div>

    <section class="prime-progress-section">
        <div class="prime-progress-section__head">
            <div>
                <p class="prime-section-label mb-1">Evolução</p>
                <h2 class="prime-progress-section__title">Evolução do Peso</h2>
            </div>
            <a href="{{ route('healths.create', ['member_id' => $member->id]) }}" class="prime-btn-ghost prime-btn-ghost--sm">
                <i class="ri-add-line"></i> Nova medição
            </a>
        </div>

        @if($weightRecords->isNotEmpty())
            <div class="prime-weight-chart" aria-hidden="true">
                @php
                    $weights = $weightRecords->take(12)->reverse()->values();
                    $min = $weights->min(fn ($h) => $h->getMeasurement('weight'));
                    $max = $weights->max(fn ($h) => $h->getMeasurement('weight'));
                    $range = max(0.1, $max - $min);
                @endphp
                <div class="prime-weight-chart__bars">
                    @foreach($weights as $record)
                        @php
                            $w = $record->getMeasurement('weight');
                            $pct = 18 + (($w - $min) / $range) * 82;
                        @endphp
                        <div class="prime-weight-chart__col" title="{{ $record->measurement_date->format('d/m/Y') }} · {{ $w }} kg">
                            <div class="prime-weight-chart__bar" style="height: {{ $pct }}%"></div>
                            <span>{{ $record->measurement_date->format('d/m') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="prime-weight-table">
                @foreach($weightRecords->take(8) as $record)
                    <div class="prime-list-row">
                        <div class="prime-list-body">
                            <div class="prime-list-title">{{ $record->getMeasurement('weight') }} kg</div>
                            <div class="prime-list-sub">
                                {{ $record->measurement_date->format('d/m/Y') }}
                                @if($record->bmi) · IMC {{ $record->bmi }} @endif
                            </div>
                        </div>
                        <a href="{{ route('healths.show', $record) }}" class="prime-btn-ghost prime-btn-ghost--sm">Ver</a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="prime-empty-state prime-empty-state--compact">
                <i class="ri-scales-3-line"></i>
                <p>Nenhuma medição de peso registrada.</p>
                <a href="{{ route('healths.create', ['member_id' => $member->id]) }}" class="prime-btn-primary">Registrar peso</a>
            </div>
        @endif
    </section>
</div>
