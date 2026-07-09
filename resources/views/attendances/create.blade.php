@extends('layouts.master')

@section('title', 'Check-in')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">Check-in</h1>
        <p class="prime-page-sub">Registre a entrada de um aluno na academia.</p>
    </div>
    <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('attendances.store') }}" method="POST">
    @csrf

    <div class="prime-panel mb-3">
        <div class="prime-panel-label mb-3">DADOS DO CHECK-IN</div>
        <div class="row g-3">
            <div class="col-12">
                <label for="member_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                <select class="form-select @error('member_id') is-invalid @enderror"
                        id="member_id" name="member_id" required autofocus>
                    <option value="">Selecione o cliente...</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                            {{ $member->name }} ({{ $member->member_id }})
                        </option>
                    @endforeach
                </select>
                @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="date" class="form-label">Data <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="date" name="date"
                       value="{{ old('date', date('Y-m-d')) }}" required>
            </div>

            <div class="col-md-6">
                <label for="check_in_time" class="form-label">Horário de entrada <span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="check_in_time" name="check_in_time"
                       value="{{ old('check_in_time', date('H:i')) }}" required>
            </div>

            <div class="col-12">
                <label for="notes" class="form-label">Observações</label>
                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                <small class="text-muted">Observações opcionais sobre este check-in</small>
            </div>
        </div>
    </div>

    <div class="text-end">
        <a href="{{ route('attendances.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary">
            <i class="ri-login-circle-line me-1"></i> Registrar check-in
        </button>
    </div>
</form>
@endsection
