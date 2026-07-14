@extends('layouts.master')

@section('title', 'Importar clientes')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Importar clientes</h1>
            <p class="mg-page-sub mb-0">CSV com colunas: name, email, phone (opcional)</p>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('members.all') }}" class="mg-btn-ghost"><i class="ri-arrow-left-line"></i> Voltar</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mg-panel">
        <form method="post" action="{{ route('tools.import.customers.store') }}" enctype="multipart/form-data" class="mg-clients-filters__form">
            @csrf
            <label class="mg-field-label">Arquivo CSV</label>
            <input type="file" name="csv" class="mg-field" accept=".csv,text/csv" required>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="mg-btn-primary"><i class="ri-upload-2-line"></i> Importar</button>
            </div>
        </form>
    </div>
</div>
@endsection
