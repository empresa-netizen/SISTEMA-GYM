@extends('layouts.master')

@section('title', $trainer->name)

@section('content')
@php
    $initials = collect(explode(' ', $trainer->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $statusMap = [
        'active' => ['Ativo', 'bg-success-subtle text-success'],
        'inactive' => ['Inativo', 'bg-secondary-subtle text-secondary'],
    ];
    $status = $statusMap[$trainer->status] ?? [ucfirst($trainer->status), 'bg-warning-subtle text-warning'];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">{{ $trainer->name }}</h1>
        <p class="mg-page-sub">{{ $trainer->trainer_id }} · {{ $trainer->email }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('trainers.edit', $trainer->id) }}" class="btn btn-primary btn-sm"><i class="ri-pencil-line me-1"></i> Editar</a>
        <a href="{{ route('trainers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line"></i></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="mg-panel text-center">
            @if($trainer->photo)
                <img src="{{ asset('storage/'.$trainer->photo) }}" class="rounded-circle mb-3 img-fluid" alt="" style="width:6rem;height:6rem;object-fit:cover">
            @else
                <div class="mg-list-avatar mx-auto mb-3" style="width:4rem;height:4rem;font-size:1.1rem">{{ strtoupper($initials) }}</div>
            @endif
            <span class="badge {{ $status[1] }}">{{ $status[0] }}</span>
            @if($trainer->years_of_experience)
                <p class="small text-muted mt-3 mb-0">{{ $trainer->years_of_experience }} anos de experiência</p>
            @endif
        </div>
    </div>
    <div class="col-lg-8">
        <div class="mg-panel" style="height:auto">
            <div class="mg-panel-label mb-3">DETALHES</div>
            <dl class="mg-detail-grid mb-0">
                <dt>Código</dt><dd>{{ $trainer->trainer_id }}</dd>
                <dt>Nome</dt><dd>{{ $trainer->name }}</dd>
                <dt>E-mail</dt><dd>{{ $trainer->email }}</dd>
                <dt>Telefone</dt><dd>{{ $trainer->phone ?? '—' }}</dd>
                <dt>Especialização</dt><dd>{{ $trainer->specialization ?? '—' }}</dd>
                <dt>Experiência</dt><dd>{{ $trainer->experience_years ? $trainer->experience_years . ' anos' : '—' }}</dd>
                <dt>Data de cadastro</dt><dd>{{ $trainer->created_at->format('d/m/Y') }}</dd>
                <dt>Status</dt><dd>{{ $status[0] }}</dd>
                <dt>Bio</dt><dd>{{ $trainer->bio ?? '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
