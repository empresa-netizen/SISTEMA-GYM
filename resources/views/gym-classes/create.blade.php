@extends('layouts.master')

@section('title', 'Nova aula')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Nova aula</h1>
        <p class="mg-page-sub">Cadastre uma turma e configure os horários.</p>
    </div>
    <a href="{{ route('gym-classes.index') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('gym-classes.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mg-panel mb-3">
        <div class="mg-panel-label mb-3">INFORMAÇÕES DA AULA</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Nome da aula <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="category_id" class="form-label">Categoria <span class="text-danger">*</span></label>
                <select class="form-select @error('category_id') is-invalid @enderror"
                        id="category_id" name="category_id" required>
                    <option value="">Selecione a categoria</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Descrição</label>
                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="col-md-4">
                <label for="max_capacity" class="form-label">Capacidade máxima <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="max_capacity" name="max_capacity"
                       value="{{ old('max_capacity', 20) }}" min="1" max="100" required>
            </div>

            <div class="col-md-4">
                <label for="duration_minutes" class="form-label">Duração (minutos) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="duration_minutes" name="duration_minutes"
                       value="{{ old('duration_minutes', 60) }}" min="15" max="240" required>
            </div>

            <div class="col-md-4">
                <label for="difficulty_level" class="form-label">Nível de dificuldade <span class="text-danger">*</span></label>
                <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                    <option value="beginner" {{ old('difficulty_level') == 'beginner' ? 'selected' : '' }}>Iniciante</option>
                    <option value="intermediate" {{ old('difficulty_level') == 'intermediate' ? 'selected' : '' }}>Intermediário</option>
                    <option value="advanced" {{ old('difficulty_level') == 'advanced' ? 'selected' : '' }}>Avançado</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label">Imagem da aula</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Ativa</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inativa</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mg-panel mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="mg-panel-label mb-0">HORÁRIOS</div>
            <button type="button" class="btn btn-sm btn-primary" onclick="addSchedule()">
                <i class="ri-add-line me-1"></i> Adicionar horário
            </button>
        </div>
        <div id="schedules-container"></div>
        <p class="text-muted mb-0" id="no-schedules-msg">Clique em "Adicionar horário" para criar os horários da aula</p>
    </div>

    <div class="text-end">
        <a href="{{ route('gym-classes.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary">Criar aula</button>
    </div>
</form>
@endsection

@section('script')
<script>
let scheduleIndex = 0;

function addSchedule() {
    const container = document.getElementById('schedules-container');
    const noSchedulesMsg = document.getElementById('no-schedules-msg');

    if (noSchedulesMsg) {
        noSchedulesMsg.style.display = 'none';
    }

    const scheduleHtml = `
        <div class="schedule-item border rounded p-3 mb-3" data-index="${scheduleIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Horário ${scheduleIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSchedule(${scheduleIndex})">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Dia da semana <span class="text-danger">*</span></label>
                    <select class="form-select" name="schedules[${scheduleIndex}][day_of_week]" required>
                        <option value="">Selecione</option>
                        <option value="monday">Segunda-feira</option>
                        <option value="tuesday">Terça-feira</option>
                        <option value="wednesday">Quarta-feira</option>
                        <option value="thursday">Quinta-feira</option>
                        <option value="friday">Sexta-feira</option>
                        <option value="saturday">Sábado</option>
                        <option value="sunday">Domingo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Início <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" name="schedules[${scheduleIndex}][start_time]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Término <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" name="schedules[${scheduleIndex}][end_time]" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Treinador</label>
                    <select class="form-select" name="schedules[${scheduleIndex}][trainer_id]">
                        <option value="">Sem treinador</option>
                        @foreach($trainers as $trainer)
                            <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sala / local</label>
                    <input type="text" class="form-control" name="schedules[${scheduleIndex}][room_location]" placeholder="Ex.: Estúdio A">
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', scheduleHtml);
    scheduleIndex++;
}

function removeSchedule(index) {
    const scheduleItem = document.querySelector(`[data-index="${index}"]`);
    if (scheduleItem) {
        scheduleItem.remove();
    }

    const container = document.getElementById('schedules-container');
    const noSchedulesMsg = document.getElementById('no-schedules-msg');
    if (container.children.length === 0 && noSchedulesMsg) {
        noSchedulesMsg.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    addSchedule();
});
</script>
@endsection
