@extends('layouts.master')

@section('title', 'Importar treino/dieta')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Importar treino/dieta</h1>
            <p class="mg-page-sub mb-0">Importe protocolos para a biblioteca</p>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('workouts.index') }}" class="mg-btn-ghost">Treinos</a>
            <a href="{{ route('library.diet.index') }}" class="mg-btn-ghost">Dieta</a>
        </div>
    </div>
    <div class="mg-empty-state">
        <i class="ri-file-upload-line"></i>
        <p>Use as bibliotecas de Treinos e Dieta para cadastrar protocolos. Importação em lote CSV/JSON em evolução.</p>
        <a href="{{ route('workouts.create') }}" class="mg-btn-primary">Novo treino</a>
    </div>
</div>
@endsection
