@extends('layouts.master')

@section('title', 'Pagamento Confirmado')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 mg-page-header">
    <div>
        <h1 class="mg-page-title">Pagamento Confirmado</h1>
        <p class="mg-page-sub">Assinatura ativada com sucesso na MGTEAM FITNESS &amp; HEALTH.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="mg-panel">
            <div class="mg-panel-body text-center">
                <div class="mb-4">
                    <i class="ri-checkbox-circle-line display-1 text-success"></i>
                </div>

                <h3 class="mb-3">Pagamento realizado com sucesso!</h3>
                <p class="text-muted mb-4">
                    Sua assinatura foi ativada e ja esta pronta para uso.
                </p>

                <div class="table-responsive mb-4">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted text-start">Plano:</td>
                                <td class="text-end"><strong>{{ $subscription->plan->name }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted text-start">Status:</td>
                                <td class="text-end">
                                    <span class="badge badge-soft-success">{{ ucfirst($subscription->status) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted text-start">Inicio:</td>
                                <td class="text-end">{{ $subscription->start_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted text-start">Fim:</td>
                                <td class="text-end">{{ $subscription->end_date->format('d/m/Y') }}</td>
                            </tr>
                            @if($subscription->trial_end_date)
                            <tr>
                                <td class="text-muted text-start">Fim do teste:</td>
                                <td class="text-end text-success">{{ $subscription->trial_end_date->format('d/m/Y') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="ri-home-line me-1"></i> Ir para o painel
                    </a>
                    <a href="{{ route('subscriptions.mine') }}" class="btn btn-soft-primary">
                        <i class="ri-file-list-line me-1"></i> Ver minha assinatura
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
