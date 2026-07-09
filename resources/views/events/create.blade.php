@extends('layouts.master')

@section('title', 'Novo evento')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">Novo evento</h1>
        <p class="prime-page-sub">Agende consultas, avaliações ou atendimentos.</p>
    </div>
    <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="prime-panel">
            <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}" placeholder="Ex: Avaliação física — Ana Silva" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="start_time" class="form-label">Início <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror"
                                   id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                            @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="end_time" class="form-label">Término <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror"
                                   id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                            @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="member_id" class="form-label">Cliente (opcional)</label>
                            <select class="form-select" id="member_id" name="member_id">
                                <option value="">— Sem vínculo —</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" @selected(old('member_id', $selectedMemberId ?? null) == $m->id)>{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="location" class="form-label">Local</label>
                            <input type="text" class="form-control" id="location" name="location"
                                   value="{{ old('location') }}" placeholder="Online, academia, etc.">
                        </div>

                        <div class="col-md-6">
                            <label for="max_participants" class="form-label">Vagas</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants"
                                   value="{{ old('max_participants') }}" min="1" placeholder="Ilimitado">
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="scheduled" @selected(old('status', 'scheduled') === 'scheduled')>Agendado</option>
                                <option value="ongoing" @selected(old('status') === 'ongoing')>Em andamento</option>
                                <option value="completed" @selected(old('status') === 'completed')>Concluído</option>
                                <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelado</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="image" class="form-label">Imagem (opcional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('events.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Salvar evento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
