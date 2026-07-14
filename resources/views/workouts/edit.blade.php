@extends('layouts.master')

@section('title', 'Editar treino — '.$workout->name)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">Editar treino</h1>
        <p class="prime-page-sub">{{ $workout->name }} · {{ $workout->workout_id }}</p>
    </div>
    <a href="{{ route('workouts.show', $workout) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('workouts.update', $workout->id) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="sync_activities" value="1">
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Workout Information</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <strong>Workout ID:</strong> {{ $workout->workout_id }}
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Workout Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $workout->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="workout_date" class="form-label">Workout Date</label>
                                <input type="date" class="form-control" id="workout_date" name="workout_date" 
                                       value="{{ old('workout_date', $workout->workout_date?->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="member_id" class="form-label">Assign to Member</label>
                                <select class="form-select" id="member_id" name="member_id">
                                    <option value="">No specific member</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" 
                                            {{ old('member_id', $workout->member_id) == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }} ({{ $member->member_id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="trainer_id" class="form-label">Created by Trainer</label>
                                <select class="form-select" id="trainer_id" name="trainer_id">
                                    <option value="">No trainer</option>
                                    @foreach($trainers as $trainer)
                                        <option value="{{ $trainer->id }}" 
                                            {{ old('trainer_id', $workout->trainer_id) == $trainer->id ? 'selected' : '' }}>
                                            {{ $trainer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" {{ old('status', $workout->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ old('status', $workout->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $workout->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $workout->description) }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $workout->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h4 class="card-title mb-0">Exercícios do treino</h4>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addActivity()">
                            <i class="ri-add-line"></i> Adicionar exercício
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $editableActivities = collect(old('activities', $workout->activities->map(fn ($activity) => [
                            'exercise_name' => $activity->exercise_name,
                            'description' => $activity->description,
                            'sets' => $activity->sets,
                            'reps' => $activity->reps,
                            'duration_minutes' => $activity->duration_minutes,
                            'rest_seconds' => $activity->rest_seconds,
                            'weight_kg' => $activity->weight_kg,
                        ])->all()))->values();
                    @endphp
                    <div id="activities-container">
                        @foreach($editableActivities as $index => $activity)
                            <div class="activity-item border rounded p-3 mb-3" data-activity-item>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Exercício {{ $index + 1 }}</h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeActivity(this)">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label">Nome <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="activities[{{ $index }}][exercise_name]" value="{{ $activity['exercise_name'] ?? '' }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Instruções</label>
                                        <textarea class="form-control" name="activities[{{ $index }}][description]" rows="1">{{ $activity['description'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Séries</label>
                                        <input type="number" class="form-control" name="activities[{{ $index }}][sets]" value="{{ $activity['sets'] ?? '' }}" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Reps</label>
                                        <input type="number" class="form-control" name="activities[{{ $index }}][reps]" value="{{ $activity['reps'] ?? '' }}" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Duração (min)</label>
                                        <input type="number" class="form-control" name="activities[{{ $index }}][duration_minutes]" value="{{ $activity['duration_minutes'] ?? '' }}" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Carga (kg)</label>
                                        <input type="number" class="form-control" name="activities[{{ $index }}][weight_kg]" value="{{ $activity['weight_kg'] ?? '' }}" step="0.5" min="0">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Descanso (seg)</label>
                                        <input type="number" class="form-control" name="activities[{{ $index }}][rest_seconds]" value="{{ $activity['rest_seconds'] ?? 60 }}" min="0">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-muted small mb-0 {{ $editableActivities->isNotEmpty() ? 'd-none' : '' }}" id="no-activities-msg">
                        Nenhum exercício cadastrado. Use "Adicionar exercício" para montar a ficha.
                    </p>
                </div>
            </div>

            <div class="text-end mb-3">
                <a href="{{ route('workouts.index') }}" class="btn btn-secondary">
                    <i class="ri-close-line me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="ri-save-line me-1"></i> Update Workout
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('script')
<script>
let activityIndex = document.querySelectorAll('[data-activity-item]').length;

function activityTemplate(index) {
    return `
        <div class="activity-item border rounded p-3 mb-3" data-activity-item>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Exercício ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeActivity(this)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="activities[${index}][exercise_name]" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Instruções</label>
                    <textarea class="form-control" name="activities[${index}][description]" rows="1"></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Séries</label>
                    <input type="number" class="form-control" name="activities[${index}][sets]" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reps</label>
                    <input type="number" class="form-control" name="activities[${index}][reps]" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Duração (min)</label>
                    <input type="number" class="form-control" name="activities[${index}][duration_minutes]" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Carga (kg)</label>
                    <input type="number" class="form-control" name="activities[${index}][weight_kg]" step="0.5" min="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Descanso (seg)</label>
                    <input type="number" class="form-control" name="activities[${index}][rest_seconds]" value="60" min="0">
                </div>
            </div>
        </div>
    `;
}

function updateActivitiesEmptyState() {
    const emptyState = document.getElementById('no-activities-msg');
    if (!emptyState) return;

    emptyState.classList.toggle('d-none', document.querySelectorAll('[data-activity-item]').length > 0);
}

function addActivity() {
    document.getElementById('activities-container').insertAdjacentHTML('beforeend', activityTemplate(activityIndex));
    activityIndex++;
    updateActivitiesEmptyState();
}

function removeActivity(button) {
    button.closest('[data-activity-item]')?.remove();
    updateActivitiesEmptyState();
}
</script>
@endsection
