@extends('layouts.master')

@section('title', 'Novo ticket')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Novo ticket</h1>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('support-tickets.index') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <p class="prime-page-sub mb-0">Abra um chamado de suporte.</p>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('support-tickets.store') }}" method="POST">
                @csrf

                <div class="prime-panel prime-panel--compact mb-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="member_id" class="prime-field-label">Cliente (opcional)</label>
                            <select name="member_id" id="member_id" class="prime-field @error('member_id') is-invalid @enderror">
                                <option value="">— Sem cliente vinculado —</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>
                                        {{ $member->name }} ({{ $member->member_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="subject" class="prime-field-label">Assunto <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="subject" class="prime-field @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="description" class="prime-field-label">Descrição <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="prime-field @error('description') is-invalid @enderror" rows="6" required>{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="prime-field-label">Prioridade <span class="text-danger">*</span></label>
                            <select name="priority" id="priority" class="prime-field @error('priority') is-invalid @enderror" required>
                                <option value="low" @selected(old('priority') == 'low')>Baixa</option>
                                <option value="medium" @selected(old('priority', 'medium') == 'medium')>Média</option>
                                <option value="high" @selected(old('priority') == 'high')>Alta</option>
                                <option value="urgent" @selected(old('priority') == 'urgent')>Urgente</option>
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="assigned_to" class="prime-field-label">Responsável</label>
                            <select name="assigned_to" id="assigned_to" class="prime-field @error('assigned_to') is-invalid @enderror">
                                <option value="">— Não atribuído —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('assigned_to') == $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="prime-btn-primary"><i class="ri-save-line"></i> Criar ticket</button>
                    <a href="{{ route('support-tickets.index') }}" class="prime-btn-ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
