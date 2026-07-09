@extends('layouts.master')

@section('title', 'Evolução')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Evolução</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-heart-pulse-line"></i>
                    Medidas e progresso
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('healths.create') }}" class="prime-btn-primary">
                <i class="ri-add-line"></i> Nova medição
            </a>
        </div>
    </div>

    <p class="prime-page-sub mb-0">Medidas corporais e progresso dos clientes.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="prime-panel prime-panel--compact">
        <form action="{{ route('healths.index') }}" method="get" class="prime-clients-filters__form mb-3">
            <div class="prime-clients-filters__grid">
                <div>
                    <label class="prime-field-label">Cliente</label>
                    <select name="member" class="prime-field">
                        <option value="">Todos os clientes</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected(request('member') == $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="prime-clients-filters__actions">
                    <button type="submit" class="prime-btn-primary">Filtrar</button>
                    <a href="{{ route('healths.index') }}" class="prime-btn-ghost">Limpar</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">{!! $dataTable->table() !!}</div>
    </div>
</div>
@endsection

@section('script')
{!! $dataTable->scripts() !!}
@endsection
