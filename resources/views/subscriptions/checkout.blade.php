@extends('layouts.master')

@section('title', 'Checkout da Assinatura')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 mg-page-header">
    <div>
        <h1 class="mg-page-title">Checkout da Assinatura</h1>
        <p class="mg-page-sub">Finalize seu plano na MGTEAM FITNESS &amp; HEALTH com seguranca.</p>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ri-error-warning-line align-middle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-5">
        <div class="mg-panel h-100">
            <div class="mg-panel-body">
                <h5 class="mb-3">Resumo do Pedido</h5>
                <div class="mb-3">
                    <h6 class="fw-semibold">{{ $plan->name }}</h6>
                    <p class="text-muted mb-0">{{ $plan->description }}</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td>Valor do plano:</td>
                                <td class="text-end">R$ {{ number_format($plan->price, 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Duracao:</td>
                                <td class="text-end">{{ $plan->duration_days }} dias</td>
                            </tr>
                            @if($plan->trial_days > 0)
                            <tr>
                                <td>Periodo de teste:</td>
                                <td class="text-end text-success">{{ $plan->trial_days }} dias gratis</td>
                            </tr>
                            @endif
                            <tr class="border-top">
                                <th>Total:</th>
                                <th class="text-end">R$ {{ number_format($plan->price, 2, ',', '.') }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="mg-panel h-100">
            <div class="mg-panel-body">
                <h5 class="mb-3">Forma de Pagamento</h5>

                <form action="{{ route('subscriptions.purchase', $plan->id) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Dados do Aluno</label>
                        <div class="border p-3 rounded">
                            <p class="mb-1"><strong>{{ $member->first_name }} {{ $member->last_name }}</strong></p>
                            <p class="mb-1 text-muted">{{ $member->email }}</p>
                            <p class="mb-0 text-muted">{{ $member->phone }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Escolha o gateway de pagamento</label>
                        @foreach($gateways as $key => $gateway)
                        <div class="form-check card-radio mb-2">
                            <input class="form-check-input" type="radio" name="payment_gateway"
                                id="gateway_{{ $key }}" value="{{ $key }}"
                                {{ $loop->first ? 'checked' : '' }} required>
                            <label class="form-check-label" for="gateway_{{ $key }}">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="{{ $gateway['icon'] }} fs-20"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $gateway['name'] }}</h6>
                                        <p class="text-muted mb-0">{{ $gateway['description'] }}</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <div class="alert alert-info">
                        <i class="ri-information-line align-middle me-2"></i>
                        Voce sera redirecionado para uma pagina segura para concluir o pagamento.
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="ri-secure-payment-line me-1"></i> Ir para o pagamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
