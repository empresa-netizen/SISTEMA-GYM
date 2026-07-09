@extends('layouts.master')

@section('title', 'Importar clientes')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Importar clientes</h1>
            <p class="prime-page-sub mb-0">CSV com colunas: name, email, phone (opcional)</p>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('members.all') }}" class="prime-btn-ghost"><i class="ri-arrow-left-line"></i> Voltar</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="prime-panel">
        <form method="post" action="{{ route('tools.import.customers.store') }}" enctype="multipart/form-data" class="prime-clients-filters__form">
            @csrf
            <label class="prime-field-label">Arquivo CSV</label>
            <input type="file" name="csv" class="prime-field" accept=".csv,text/csv" required>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="prime-btn-primary"><i class="ri-upload-2-line"></i> Importar</button>
            </div>
        </form>
    </div>
</div>
@endsection
