@extends('layouts.master')

@section('title', 'Pagamento Cancelado')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 mg-page-header">
    <div>
        <h1 class="mg-page-title">Pagamento Cancelado</h1>
        <p class="mg-page-sub">A compra nao foi concluida na MGTEAM FITNESS &amp; HEALTH.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="mg-panel">
            <div class="mg-panel-body text-center">
                <div class="mb-4">
                    <i class="ri-close-circle-line display-1 text-warning"></i>
                </div>

                <h3 class="mb-3">Pagamento cancelado</h3>
                <p class="text-muted mb-4">
                    O processo foi interrompido antes da confirmacao e nenhuma cobranca foi realizada.
                </p>

                @if(session('error'))
                <div class="alert alert-warning alert-dismissible fade show text-start" role="alert">
                    <i class="ri-error-warning-line align-middle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <div class="alert alert-light border text-start mb-4">
                    <h5 class="mb-2">O que aconteceu?</h5>
                    <p class="text-muted mb-0">
                        O pagamento foi cancelado antes da finalizacao. Se desejar, voce pode escolher o plano novamente e tentar outra vez.
                    </p>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('subscriptions.index') }}" class="btn btn-primary">
                        <i class="ri-restart-line me-1"></i> Tentar novamente
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-soft-secondary">
                        <i class="ri-home-line me-1"></i> Ir para o painel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
