@extends('layouts.master')

@section('title', 'Importar treino/dieta')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Importar treino/dieta</h1>
            <p class="prime-page-sub mb-0">Importe protocolos para a biblioteca</p>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('workouts.index') }}" class="prime-btn-ghost">Treinos</a>
            <a href="{{ route('library.diet.index') }}" class="prime-btn-ghost">Dieta</a>
        </div>
    </div>
    <div class="prime-empty-state">
        <i class="ri-file-upload-line"></i>
        <p>Use as bibliotecas de Treinos e Dieta para cadastrar protocolos. Importação em lote CSV/JSON em evolução.</p>
        <a href="{{ route('workouts.create') }}" class="prime-btn-primary">Novo treino</a>
    </div>
</div>
@endsection
