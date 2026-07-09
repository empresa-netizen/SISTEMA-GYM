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
                    <h4 class="card-title mb-0">Workout Activities ({{ $workout->activities->count() }})</h4>
                </div>
                <div class="card-body">
                    @if($workout->activities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Exercise</th>
                                        <th>Sets × Reps</th>
                                        <th>Duration</th>
                                        <th>Weight</th>
                                        <th>Rest</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workout->activities as $activity)
                                        <tr>
                                            <td>
                                                <strong>{{ $activity->exercise_name }}</strong>
                                                @if($activity->description)
                                                    <br><small class="text-muted">{{ $activity->description }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $activity->sets && $activity->reps ? "{$activity->sets} × {$activity->reps}" : '-' }}</td>
                                            <td>{{ $activity->duration_minutes ? "{$activity->duration_minutes} min" : '-' }}</td>
                                            <td>{{ $activity->weight_kg ? "{$activity->weight_kg} kg" : '-' }}</td>
                                            <td>{{ $activity->rest_seconds }}s</td>
                                            <td>
                                                @if($activity->is_completed)
                                                    <span class="badge badge-soft-success">Completed</span>
                                                @else
                                                    <span class="badge badge-soft-secondary">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mt-2">
                            Completion: {{ $workout->completion_percentage }}% 
                            ({{ $workout->activities->where('is_completed', true)->count() }}/{{ $workout->activities->count() }})
                        </p>
                    @else
                        <p class="text-muted">No activities in this workout.</p>
                    @endif
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
