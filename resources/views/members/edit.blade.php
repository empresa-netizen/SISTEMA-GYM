@extends('layouts.master')

@section('title', 'Editar cliente')

@section('content')
@php
    $statusLabels = ['active' => 'Ativo', 'inactive' => 'Inativo', 'expired' => 'Expirado', 'suspended' => 'Suspenso'];
    $genderLabels = ['male' => 'Masculino', 'female' => 'Feminino', 'other' => 'Outro'];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">{{ $member->name }}</h1>
        <p class="mg-page-sub">{{ $member->member_id }} · Editar cadastro</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('members.show', $member) }}" class="btn btn-outline-primary btn-sm">Ver perfil</a>
        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri-arrow-left-line me-1"></i> Voltar
        </a>
    </div>
</div>

<form action="{{ route('members.update', $member->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mg-panel mb-3">
        <div class="mg-panel-label mb-3">DADOS PESSOAIS</div>
        <div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nome completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $member->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $member->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Telefone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $member->phone) }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="date_of_birth" class="form-label">Data de nascimento</label>
                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $member->date_of_birth?->format('Y-m-d')) }}">
                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="gender" class="form-label">Gênero</label>
                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                        <option value="">Selecione</option>
                        @foreach($genderLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('gender', $member->gender) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="photo" class="form-label">Foto</label>
                    <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                    @if($member->photo)
                        <small class="text-muted">Foto atual: {{ basename($member->photo) }}</small>
                    @endif
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Endereço</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address', $member->address) }}</textarea>
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
                    <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $member->emergency_contact_name) }}">
                </div>
                <div class="col-md-6">
                    <label for="emergency_contact_phone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $member->emergency_contact_phone) }}">
                </div>
                <div class="col-12">
                    <label for="medical_conditions" class="form-label">Condições médicas</label>
                    <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="2">{{ old('medical_conditions', $member->medical_conditions) }}</textarea>
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
                            <option value="{{ $plan->id }}" @selected(old('membership_plan_id', $member->membership_plan_id) == $plan->id)>
                                {{ $plan->name }} — R$ {{ number_format($plan->price, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('membership_plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="membership_start_date" class="form-label">Início <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('membership_start_date') is-invalid @enderror" id="membership_start_date" name="membership_start_date" value="{{ old('membership_start_date', $member->membership_start_date?->format('Y-m-d')) }}" required>
                    @error('membership_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Vencimento</label>
                    <input type="text" class="form-control" value="{{ $member->membership_end_date?->format('d/m/Y') ?? 'Vitalício' }}" readonly>
                    <small class="text-muted">Calculado automaticamente pelo plano</small>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $member->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">Adicionar nota (opcional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Será salva no histórico de notas">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end">
        <a href="{{ route('members.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar alterações</button>
    </div>
</form>
@endsection
