@extends('layouts.master')

@section('title', 'Planos de Assinatura')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 prime-page-header">
    <div>
        <h1 class="prime-page-title">Planos de Assinatura</h1>
        <p class="prime-page-sub">Escolha o plano ideal para sua jornada na MGTEAM FITNESS &amp; HEALTH.</p>
    </div>
</div>

<div class="prime-panel">
    <div class="prime-panel-body">
        <div class="text-center mb-5">
            <h3 class="mb-3">Encontre o plano ideal para voce</h3>
            <p class="text-muted">Selecione uma assinatura e comece agora com a MGTEAM FITNESS &amp; HEALTH.</p>
        </div>

        <div class="row g-4">
            @foreach($plans as $plan)
            <div class="col-lg-4">
                <div class="card h-100 pricing-box {{ $plan->is_featured ? 'border-primary' : '' }}">
                    @if($plan->is_featured)
                        <div class="ribbon-two ribbon-two-primary"><span>Mais escolhido</span></div>
                    @endif

                    <div class="card-body p-4 d-flex flex-column">
                        <div class="text-center">
                            <h5 class="mb-1">{{ $plan->name }}</h5>
                            <p class="text-muted">{{ $plan->description }}</p>
                        </div>

                        <div class="py-4 text-center">
                            <h1 class="month">
                                <span class="ff-secondary fw-bold">R$ {{ number_format($plan->price, 2, ',', '.') }}</span>
                                <span class="fs-13 text-muted">/ {{ $plan->duration_days }} dias</span>
                            </h1>
                        </div>

                        <div class="mb-4 flex-grow-1">
                            <h6 class="fs-15 fw-semibold text-uppercase mb-3">Recursos inclusos:</h6>
                            <ul class="list-unstyled vstack gap-3">
                                @if($plan->features)
                                    @foreach($plan->features as $feature)
                                    <li>
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 text-success me-2">
                                                <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                {{ $feature }}
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                @endif

                                @if($plan->max_members)
                                <li>
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 text-success me-2">
                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            Ate {{ $plan->max_members }} membros
                                        </div>
                                    </div>
                                </li>
                                @endif

                                @if($plan->trial_days > 0)
                                <li>
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 text-primary me-2">
                                            <i class="ri-gift-line fs-15 align-middle"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong>{{ $plan->trial_days }} dias gratis</strong>
                                        </div>
                                    </div>
                                </li>
                                @endif
                            </ul>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('subscriptions.checkout', $plan->id) }}" class="btn {{ $plan->is_featured ? 'btn-primary' : 'btn-soft-primary' }} w-100">
                                Assinar agora
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 text-center">
            <p class="text-muted mb-0">
                <i class="ri-secure-payment-line align-middle me-1"></i>
                Pagamentos seguros processados via Stripe e PayPal.
            </p>
        </div>
    </div>
</div>
@endsection
