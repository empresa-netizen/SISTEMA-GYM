@extends('layouts.master')

@section('title', 'Nova despesa')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Nova despesa</h1>
        <p class="mg-page-sub">Registre um gasto ou saída financeira.</p>
    </div>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mg-panel mb-3">
        <div class="mg-panel-label mb-3">DETALHES DA DESPESA</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="type_id" class="form-label">Tipo de despesa <span class="text-danger">*</span></label>
                <select class="form-select @error('type_id') is-invalid @enderror"
                        id="type_id" name="type_id" required>
                    <option value="">Selecione o tipo</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                @error('type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="expense_date" class="form-label">Data <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('expense_date') is-invalid @enderror"
                       id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('title') is-invalid @enderror"
                       id="title" name="title" value="{{ old('title') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="amount" class="form-label">Valor ($) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('amount') is-invalid @enderror"
                       id="amount" name="amount" value="{{ old('amount') }}"
                       step="0.01" min="0" required>
                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="payment_method" class="form-label">Forma de pagamento <span class="text-danger">*</span></label>
                <select class="form-select @error('payment_method') is-invalid @enderror"
                        id="payment_method" name="payment_method" required>
                    <option value="">Selecione</option>
                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Dinheiro</option>
                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Cartão</option>
                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Transferência bancária</option>
                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Outro</option>
                </select>
                @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Descrição</label>
                <textarea class="form-control" id="description" name="description" rows="2">{{ old('description') }}</textarea>
            </div>

            <div class="col-12">
                <label for="receipt" class="form-label">Comprovante (opcional)</label>
                <input type="file" class="form-control @error('receipt') is-invalid @enderror"
                       id="receipt" name="receipt" accept="image/*,.pdf">
                <small class="text-muted">Formatos aceitos: JPG, PNG, PDF (máx. 2MB)</small>
                @error('receipt')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label for="notes" class="form-label">Observações</label>
                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    <div class="text-end">
        <a href="{{ route('expenses.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar despesa</button>
    </div>
</form>
@endsection
