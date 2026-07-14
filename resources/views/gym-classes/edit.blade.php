@extends('layouts.master')

@section('title', 'Editar aula')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Editar aula</h1>
        <p class="mg-page-sub">{{ $gymClass->name }} · {{ $gymClass->class_id }}</p>
    </div>
    <a href="{{ route('gym-classes.index') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<form action="{{ route('gym-classes.update', $gymClass->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mg-panel mb-3">
        <div class="mg-panel-label mb-3">INFORMAÇÕES DA AULA</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Nome da aula <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name" value="{{ old('name', $gymClass->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="category_id" class="form-label">Categoria <span class="text-danger">*</span></label>
                <select class="form-select @error('category_id') is-invalid @enderror"
                        id="category_id" name="category_id" required>
                    <option value="">Selecione a categoria</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id', $gymClass->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Descrição</label>
                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $gymClass->description) }}</textarea>
            </div>

            <div class="col-md-4">
                <label for="max_capacity" class="form-label">Capacidade máxima <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="max_capacity" name="max_capacity"
                       value="{{ old('max_capacity', $gymClass->max_capacity) }}" min="1" max="100" required>
                <small class="text-muted">Matriculados: {{ $gymClass->enrolled_count }}</small>
            </div>

            <div class="col-md-4">
                <label for="duration_minutes" class="form-label">Duração (minutos) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="duration_minutes" name="duration_minutes"
                       value="{{ old('duration_minutes', $gymClass->duration_minutes) }}" min="15" max="240" required>
            </div>

            <div class="col-md-4">
                <label for="difficulty_level" class="form-label">Nível de dificuldade <span class="text-danger">*</span></label>
                <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                    <option value="beginner" {{ old('difficulty_level', $gymClass->difficulty_level) == 'beginner' ? 'selected' : '' }}>Iniciante</option>
                    <option value="intermediate" {{ old('difficulty_level', $gymClass->difficulty_level) == 'intermediate' ? 'selected' : '' }}>Intermediário</option>
                    <option value="advanced" {{ old('difficulty_level', $gymClass->difficulty_level) == 'advanced' ? 'selected' : '' }}>Avançado</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label">Imagem da aula</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                @if($gymClass->image)
                    <small class="text-muted">Atual: {{ basename($gymClass->image) }}</small>
                @endif
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active" {{ old('status', $gymClass->status) == 'active' ? 'selected' : '' }}>Ativa</option>
                    <option value="inactive" {{ old('status', $gymClass->status) == 'inactive' ? 'selected' : '' }}>Inativa</option>
                    <option value="cancelled" {{ old('status', $gymClass->status) == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mg-panel mb-3">
        <div class="mg-panel-label mb-3">HORÁRIOS ATUAIS</div>
        @if($gymClass->schedules->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Dia</th>
                            <th>Horário</th>
                            <th>Treinador</th>
                            <th>Sala</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gymClass->schedules as $schedule)
                            <tr>
                                <td>{{ ucfirst($schedule->day_of_week) }}</td>
                                <td>{{ $schedule->time_range }}</td>
                                <td>{{ $schedule->trainer ? $schedule->trainer->name : 'Sem treinador' }}</td>
                                <td>{{ $schedule->room_location ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-2 mb-0">Para alterar horários, exclua esta aula e crie uma nova, ou entre em contato com o suporte.</p>
        @else
            <p class="text-muted mb-0">Nenhum horário configurado para esta aula.</p>
        @endif
    </div>

    <div class="text-end">
        <a href="{{ route('gym-classes.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary">Atualizar aula</button>
    </div>
</form>
@endsection
