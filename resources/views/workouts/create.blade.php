@extends('layouts.master')

@section('title', 'Novo treino')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">Nova prescrição</h1>
        <p class="prime-page-sub">Monte um treino para o seu cliente.</p>
    </div>
    <a href="{{ route('workouts.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('workouts.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="prime-panel mb-3">
                <div class="prime-panel-label mb-3">INFORMAÇÕES DO TREINO</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nome do treino <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" placeholder="Ex: Treino A — Peito e tríceps" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="workout_date" class="form-label">Data</label>
                        <input type="date" class="form-control" id="workout_date" name="workout_date" value="{{ old('workout_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="member_id" class="form-label">Cliente</label>
                        <select class="form-select" id="member_id" name="member_id">
                            <option value="">Sem cliente específico</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" @selected(old('member_id', request('member')) == $member->id)>
                                    {{ $member->name }} ({{ $member->member_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="trainer_id" class="form-label">Responsável</label>
                        <select class="form-select" id="trainer_id" name="trainer_id">
                            <option value="">—</option>
                            @foreach($trainers as $trainer)
                                <option value="{{ $trainer->id }}" @selected(old('trainer_id') == $trainer->id)>{{ $trainer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" @selected(old('status', 'active') === 'active')>Ativo</option>
                            <option value="completed" @selected(old('status') === 'completed')>Concluído</option>
                            <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label for="notes" class="form-label">Observações internas</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="prime-panel mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="prime-panel-label mb-0">EXERCÍCIOS</div>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addActivity()">
                        <i class="ri-add-line"></i> Adicionar exercício
                    </button>
                </div>
                <div id="activities-container"></div>
                <p class="text-muted small mb-0" id="no-activities-msg">Clique em "Adicionar exercício" para montar o treino.</p>
            </div>

            <div class="d-flex gap-2 justify-content-end mb-3">
                <a href="{{ route('workouts.index') }}" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Salvar treino</button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('script')
<script>
let activityIndex = 0;

function addActivity() {
    const container = document.getElementById('activities-container');
    const noActivitiesMsg = document.getElementById('no-activities-msg');
    if (noActivitiesMsg) noActivitiesMsg.style.display = 'none';

    container.insertAdjacentHTML('beforeend', `
        <div class="activity-item border rounded p-3 mb-3" data-index="${activityIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Exercício ${activityIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeActivity(${activityIndex})">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="activities[${activityIndex}][exercise_name]" placeholder="Ex: Supino reto" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Instruções</label>
                    <textarea class="form-control" name="activities[${activityIndex}][description]" rows="1"></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Séries</label>
                    <input type="number" class="form-control" name="activities[${activityIndex}][sets]" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reps</label>
                    <input type="number" class="form-control" name="activities[${activityIndex}][reps]" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Duração (min)</label>
                    <input type="number" class="form-control" name="activities[${activityIndex}][duration_minutes]" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Carga (kg)</label>
                    <input type="number" class="form-control" name="activities[${activityIndex}][weight_kg]" step="0.5" min="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Descanso (seg)</label>
                    <input type="number" class="form-control" name="activities[${activityIndex}][rest_seconds]" value="60" min="0">
                </div>
            </div>
        </div>
    `);
    activityIndex++;
}

function removeActivity(index) {
    document.querySelector(`[data-index="${index}"]`)?.remove();
    const container = document.getElementById('activities-container');
    const noActivitiesMsg = document.getElementById('no-activities-msg');
    if (container.children.length === 0 && noActivitiesMsg) noActivitiesMsg.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', () => addActivity());
</script>
@endsection
