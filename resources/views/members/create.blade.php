@extends('layouts.master')

@section('title', 'Novo cliente')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Novo cliente</h1>
        <p class="mg-page-sub">Cadastre um aluno e vincule ao plano de consultoria.</p>
    </div>
    <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('members.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mg-panel mb-3">
        <div class="mg-panel-label mb-3">DADOS PESSOAIS</div>
        <div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nome completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Telefone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="(11) 99999-0000">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth" class="form-label">Data de nascimento</label>
                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="gender" class="form-label">Gênero</label>
                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                        <option value="">Selecione</option>
                        <option value="male" @selected(old('gender') === 'male')>Masculino</option>
                        <option value="female" @selected(old('gender') === 'female')>Feminino</option>
                        <option value="other" @selected(old('gender') === 'other')>Outro</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="photo" class="form-label">Foto</label>
                    <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Endereço</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address') }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="mg-panel mb-3">
        <div class="mg-panel-label mb-3">CONTATO DE EMERGÊNCIA</div>
        <div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="emergency_contact_name" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}">
                </div>
                <div class="col-md-6">
                    <label for="emergency_contact_phone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}">
                </div>
                <div class="col-12">
                    <label for="medical_conditions" class="form-label">Condições médicas</label>
                    <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="2">{{ old('medical_conditions') }}</textarea>
                    <small class="text-muted">Alergias, lesões ou restrições importantes</small>
                </div>
            </div>
        </div>
    </div>

    <div class="mg-panel mb-3">
        <div class="mg-panel-label mb-3">PLANO E ASSINATURA</div>
        <div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="membership_plan_id" class="form-label">Plano <span class="text-danger">*</span></label>
                    <select class="form-select @error('membership_plan_id') is-invalid @enderror" id="membership_plan_id" name="membership_plan_id" required>
                        <option value="">Selecione o plano</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('membership_plan_id') == $plan->id)>
                                {{ $plan->name }} — R$ {{ number_format($plan->price, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('membership_plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="membership_start_date" class="form-label">Início <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('membership_start_date') is-invalid @enderror" id="membership_start_date" name="membership_start_date" value="{{ old('membership_start_date', date('Y-m-d')) }}" required>
                    @error('membership_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="active" @selected(old('status', 'active') === 'active')>Ativo</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inativo</option>
                        <option value="suspended" @selected(old('status') === 'suspended')>Suspenso</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">Observações</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end">
        <a href="{{ route('members.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar cliente</button>
    </div>
</form>
@endsection
