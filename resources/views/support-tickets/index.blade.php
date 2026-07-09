@extends('layouts.master')

@section('title', 'Suporte')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Central de ajuda</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-customer-service-2-line"></i>
                    FAQ e tickets
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('support-tickets.create') }}" class="prime-btn-primary">
                <i class="ri-add-line"></i> Novo ticket
            </a>
        </div>
    </div>

    <p class="prime-page-sub mb-0">Tickets de suporte e dúvidas.</p>

    <div class="row g-2">
        @foreach([
            ['q' => 'Quais são as taxas cobradas por cada venda?', 'a' => 'No clone local as taxas são simuladas. No Prime original, consulte a aba Taxas e prazos.'],
            ['q' => 'Como funciona a área financeira?', 'a' => 'Acesse Financeiro para ver saldo, transações, saques e relatórios.'],
            ['q' => 'Como prescrever treinos?', 'a' => 'Vá em Treinos → Nova prescrição, escolha o cliente e adicione exercícios da biblioteca.'],
            ['q' => 'Como cadastrar clientes?', 'a' => 'Clientes → Novo cliente, vincule ao plano de consultoria.'],
        ] as $faq)
        <div class="col-md-6">
            <div class="prime-panel prime-panel--compact h-100">
                <h6 class="mb-2">{{ $faq['q'] }}</h6>
                <p class="text-muted small mb-0">{{ $faq['a'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="prime-panel prime-panel--compact">
        <div class="prime-panel-label mb-3">MEUS TICKETS</div>
        <div class="table-responsive">{!! $dataTable->table() !!}</div>
    </div>
</div>
@endsection

@section('script')
{!! $dataTable->scripts() !!}
@endsection
