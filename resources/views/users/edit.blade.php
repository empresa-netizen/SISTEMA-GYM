@extends('layouts.master')

@section('title', 'Editar colaborador')

@section('content')
<div class="mb-4">
    <h1 class="prime-page-title">Editar colaborador</h1>
    <p class="prime-section-label mb-1">Atualize dados, perfil e senha de {{ $user->name }}.</p>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="prime-panel">
            <div class="prime-panel-body">
                <h4 class="mb-3">Informações do colaborador</h4>
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6" id="password">
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha <small class="text-muted">(deixe em branco para manter)</small></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar senha</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>



                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Perfil <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" 
                                        id="role" name="role" required>
                                    <option value="">Selecione o perfil</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" 
                                            {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                            {{ ['owner' => 'Proprietário', 'manager' => 'Gerente', 'trainer' => 'Treinador', 'receptionist' => 'Recepção'][$role->name] ?? ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('account.collaborators') }}" class="prime-btn prime-btn-outline">
                            <i class="ri-close-line align-middle me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="prime-btn">
                            <i class="ri-save-line align-middle me-1"></i> Salvar colaborador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
