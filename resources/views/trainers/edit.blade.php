@extends('layouts.master')

@section('title', 'Editar treinador')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">Editar treinador</h1>
        <p class="prime-page-sub">{{ $trainer->name }} · {{ $trainer->trainer_id }}</p>
    </div>
    <a href="{{ route('trainers.index') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('trainers.update', $trainer->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="prime-panel mb-3">
        <div class="prime-panel-label mb-3">DADOS PESSOAIS</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Nome completo <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name" value="{{ old('name', $trainer->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email', $trainer->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="phone" class="form-label">Telefone</label>
                <input type="text" class="form-control"
                       id="phone" name="phone" value="{{ old('phone', $trainer->phone) }}">
            </div>

            <div class="col-md-6">
                <label for="date_of_birth" class="form-label">Data de nascimento</label>
                <input type="date" class="form-control"
                       id="date_of_birth" name="date_of_birth"
                       value="{{ old('date_of_birth', $trainer->date_of_birth?->format('Y-m-d')) }}">
            </div>

            <div class="col-md-6">
                <label for="gender" class="form-label">Gênero</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">Selecione</option>
                    <option value="male" {{ old('gender', $trainer->gender) == 'male' ? 'selected' : '' }}>Masculino</option>
                    <option value="female" {{ old('gender', $trainer->gender) == 'female' ? 'selected' : '' }}>Feminino</option>
                    <option value="other" {{ old('gender', $trainer->gender) == 'other' ? 'selected' : '' }}>Outro</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="photo" class="form-label">Foto</label>
                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                @if($trainer->photo)
                    <small class="text-muted">Foto atual: {{ basename($trainer->photo) }}</small>
                @endif
            </div>

            <div class="col-12">
                <label for="address" class="form-label">Endereço</label>
                <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $trainer->address) }}</textarea>
            </div>
        </div>
    </div>

    <div class="prime-panel mb-3">
        <div class="prime-panel-label mb-3">DADOS PROFISSIONAIS</div>
        <div class="row g-3">
            <div class="col-12">
                <label for="bio" class="form-label">Bio / descrição</label>
                <textarea class="form-control" id="bio" name="bio" rows="3">{{ old('bio', $trainer->bio) }}</textarea>
            </div>

            <div class="col-md-6">
                <label for="specializations" class="form-label">Especializações</label>
                <select class="form-select" id="specializations" name="specializations[]" multiple>
                    @php
                        $selected = old('specializations', $trainer->specializations ?? []);
                        $options = ['Yoga', 'Cardio', 'Strength Training', 'CrossFit', 'Pilates', 'HIIT', 'Boxing', 'Spinning', 'Nutrition'];
                    @endphp
                    @foreach($options as $option)
                        <option value="{{ $option }}" {{ in_array($option, $selected) ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Segure Ctrl/Cmd para selecionar várias</small>
            </div>

            <div class="col-md-6">
                <label for="certifications" class="form-label">Certificações</label>
                <input type="text" class="form-control" id="certifications_input"
                       placeholder="Digite a certificação e pressione Enter">
                <div id="certifications_tags" class="mt-2"></div>
                <small class="text-muted">Digite e pressione Enter para adicionar</small>
            </div>

            <div class="col-md-6">
                <label for="years_of_experience" class="form-label">Anos de experiência <span class="text-danger">*</span></label>
                <input type="number" class="form-control"
                       id="years_of_experience" name="years_of_experience"
                       value="{{ old('years_of_experience', $trainer->years_of_experience) }}" min="0" max="50" required>
            </div>

            <div class="col-md-6">
                <label for="hourly_rate" class="form-label">Valor hora ($)</label>
                <input type="number" class="form-control" id="hourly_rate" name="hourly_rate"
                       value="{{ old('hourly_rate', $trainer->hourly_rate) }}" min="0" step="0.01">
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active" {{ old('status', $trainer->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="inactive" {{ old('status', $trainer->status) == 'inactive' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>

            <div class="col-12">
                <label for="notes" class="form-label">Observações</label>
                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $trainer->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="text-end">
        <a href="{{ route('trainers.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary">Atualizar treinador</button>
    </div>
</form>
@endsection

@section('script')
<script>
let certifications = @json(old('certifications', $trainer->certifications ?? []));
updateCertificationsTags();

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
