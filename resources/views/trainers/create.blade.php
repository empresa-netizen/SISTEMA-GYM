@extends('layouts.master')

@section('title', 'Novo treinador')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Novo treinador</h1>
        <p class="mg-page-sub">Cadastre um profissional na equipe.</p>
    </div>
    <a href="{{ route('trainers.index') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('trainers.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mg-panel mb-3">
        <div class="mg-panel-label mb-3">DADOS PESSOAIS</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Nome completo <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="phone" class="form-label">Telefone</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                       id="phone" name="phone" value="{{ old('phone') }}">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="date_of_birth" class="form-label">Data de nascimento</label>
                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="gender" class="form-label">Gênero</label>
                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                    <option value="">Selecione</option>
                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Masculino</option>
                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Feminino</option>
                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Outro</option>
                </select>
                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="photo" class="form-label">Foto</label>
                <input type="file" class="form-control @error('photo') is-invalid @enderror"
                       id="photo" name="photo" accept="image/*">
                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label for="address" class="form-label">Endereço</label>
                <textarea class="form-control @error('address') is-invalid @enderror"
                          id="address" name="address" rows="2">{{ old('address') }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="mg-panel mb-3">
        <div class="mg-panel-label mb-3">DADOS PROFISSIONAIS</div>
        <div class="row g-3">
            <div class="col-12">
                <label for="bio" class="form-label">Bio / descrição</label>
                <textarea class="form-control" id="bio" name="bio" rows="3">{{ old('bio') }}</textarea>
                <small class="text-muted">Breve apresentação e formação</small>
            </div>

            <div class="col-md-6">
                <label for="specializations" class="form-label">Especializações</label>
                <select class="form-select" id="specializations" name="specializations[]" multiple>
                    <option value="Yoga">Yoga</option>
                    <option value="Cardio">Cardio</option>
                    <option value="Strength Training">Strength Training</option>
                    <option value="CrossFit">CrossFit</option>
                    <option value="Pilates">Pilates</option>
                    <option value="HIIT">HIIT</option>
                    <option value="Boxing">Boxing</option>
                    <option value="Spinning">Spinning</option>
                    <option value="Nutrition">Nutrition</option>
                </select>
                <small class="text-muted">Segure Ctrl/Cmd para selecionar várias</small>
            </div>

            <div class="col-md-6">
                <label for="certifications" class="form-label">Certificações</label>
                <input type="text" class="form-control" id="certifications_input"
                       placeholder="Digite a certificação e pressione Enter">
                <div id="certifications_tags" class="mt-2"></div>
                <input type="hidden" name="certifications[]" id="certifications_hidden">
                <small class="text-muted">Digite e pressione Enter para adicionar</small>
            </div>

            <div class="col-md-6">
                <label for="years_of_experience" class="form-label">Anos de experiência <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('years_of_experience') is-invalid @enderror"
                       id="years_of_experience" name="years_of_experience"
                       value="{{ old('years_of_experience', 0) }}" min="0" max="50" required>
                @error('years_of_experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="hourly_rate" class="form-label">Valor hora ($)</label>
                <input type="number" class="form-control" id="hourly_rate" name="hourly_rate"
                       value="{{ old('hourly_rate') }}" min="0" step="0.01">
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>

            <div class="col-12">
                <label for="notes" class="form-label">Observações</label>
                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    <div class="text-end">
        <a href="{{ route('trainers.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar treinador</button>
    </div>
</form>
@endsection

@section('script')
<script>
let certifications = [];

document.getElementById('certifications_input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const value = this.value.trim();
        if (value && !certifications.includes(value)) {
            certifications.push(value);
            updateCertificationsTags();
            this.value = '';
        }
    }
});

function updateCertificationsTags() {
    const container = document.getElementById('certifications_tags');
    container.innerHTML = certifications.map((cert, index) => `
        <span class="badge badge-soft-primary me-1 mb-1">
            ${cert}
            <i class="ri-close-line ms-1" style="cursor: pointer;" onclick="removeCertification(${index})"></i>
        </span>
    `).join('');

    const form = document.querySelector('form');
    form.querySelectorAll('input[name="certifications[]"]').forEach(input => input.remove());
    certifications.forEach(cert => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'certifications[]';
        input.value = cert;
        form.appendChild(input);
    });
}

function removeCertification(index) {
    certifications.splice(index, 1);
    updateCertificationsTags();
}
</script>
@endsection
