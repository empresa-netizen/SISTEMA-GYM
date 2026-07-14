@extends('layouts.master')

@section('title', 'Evolução')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Evolução</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-heart-pulse-line"></i>
                    Medidas e progresso
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('healths.create') }}" class="mg-btn-primary">
                <i class="ri-add-line"></i> Nova medição
            </a>
        </div>
    </div>

    <p class="mg-page-sub mb-0">Medidas corporais e progresso dos clientes.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mg-panel mg-panel--compact">
        <form action="{{ route('healths.index') }}" method="get" class="mg-clients-filters__form mb-3">
            <div class="mg-clients-filters__grid">
                <div>
                    <label class="mg-field-label">Cliente</label>
                    <select name="member" class="mg-field">
                        <option value="">Todos os clientes</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected(request('member') == $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mg-clients-filters__actions">
                    <button type="submit" class="mg-btn-primary">Filtrar</button>
                    <a href="{{ route('healths.index') }}" class="mg-btn-ghost">Limpar</a>
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
