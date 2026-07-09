@extends('layouts.master')

@section('title', 'Editar medição')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">Editar medição</h1>
        <p class="prime-page-sub">Atualize peso, medidas e observações.</p>
    </div>
    <a href="{{ route('healths.show', $health) }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line me-1"></i> Voltar</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('healths.update', $health) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="prime-panel mb-3">
                <div class="prime-panel-label mb-3">DADOS GERAIS</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="member_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select class="form-select @error('member_id') is-invalid @enderror" id="member_id" name="member_id" required>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" @selected(old('member_id', $health->member_id) == $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="measurement_date" class="form-label">Data <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="measurement_date" name="measurement_date" value="{{ old('measurement_date', $health->measurement_date?->format('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>

            <div class="prime-panel mb-3">
                <div class="prime-panel-label mb-2">MEDIDAS CORPORAIS</div>
                <div class="row g-3">
                    @foreach($measurementTypes as $key => $label)
                    <div class="col-md-6">
                        <label for="measurement_{{ $key }}" class="form-label">{{ $label }}</label>
                        <input type="number" class="form-control" id="measurement_{{ $key }}" name="measurements[{{ $key }}]" value="{{ old('measurements.'.$key, $health->measurements[$key] ?? '') }}" step="0.1" min="0">
                    </div>
                    @endforeach
                    <div class="col-12">
                        <label for="notes" class="form-label">Observações</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $health->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('healths.show', $health) }}" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
