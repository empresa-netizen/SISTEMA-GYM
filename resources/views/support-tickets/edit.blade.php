@extends('layouts.master')

@section('title', 'Editar chamado')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Editar chamado</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-ticket-2-line"></i>
                    {{ $supportTicket->ticket_number }}
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('support-tickets.show', $supportTicket) }}" class="mg-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('support-tickets.update', $supportTicket) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mg-panel mg-panel--compact mb-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="member_id" class="mg-field-label">Cliente (opcional)</label>
                            <select name="member_id" id="member_id" class="mg-field @error('member_id') is-invalid @enderror">
                                <option value="">— Sem cliente vinculado —</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" @selected(old('member_id', $supportTicket->member_id) == $member->id)>{{ $member->name }}</option>
                                @endforeach
                            </select>
                            @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="subject" class="mg-field-label">Assunto <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="subject" class="mg-field @error('subject') is-invalid @enderror" value="{{ old('subject', $supportTicket->subject) }}" required>
                            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="description" class="mg-field-label">Descrição <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="mg-field @error('description') is-invalid @enderror" rows="6" required>{{ old('description', $supportTicket->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="mg-field-label">Prioridade</label>
                            <select name="priority" id="priority" class="mg-field @error('priority') is-invalid @enderror" required>
                                <option value="low" @selected(old('priority', $supportTicket->priority) == 'low')>Baixa</option>
                                <option value="medium" @selected(old('priority', $supportTicket->priority) == 'medium')>Média</option>
                                <option value="high" @selected(old('priority', $supportTicket->priority) == 'high')>Alta</option>
                                <option value="urgent" @selected(old('priority', $supportTicket->priority) == 'urgent')>Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="mg-field-label">Status</label>
                            <select name="status" id="status" class="mg-field @error('status') is-invalid @enderror" required>
                                <option value="open" @selected(old('status', $supportTicket->status) == 'open')>Aberto</option>
                                <option value="in_progress" @selected(old('status', $supportTicket->status) == 'in_progress')>Em andamento</option>
                                <option value="resolved" @selected(old('status', $supportTicket->status) == 'resolved')>Resolvido</option>
                                <option value="closed" @selected(old('status', $supportTicket->status) == 'closed')>Fechado</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="assigned_to" class="mg-field-label">Responsável</label>
                            <select name="assigned_to" id="assigned_to" class="mg-field @error('assigned_to') is-invalid @enderror">
                                <option value="">— Não atribuído —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('assigned_to', $supportTicket->assigned_to) == $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                @if($supportTicket->resolved_at)
                    <div class="alert alert-success mb-3"><i class="ri-checkbox-circle-line me-2"></i>Resolvido em {{ $supportTicket->resolved_at->format('d/m/Y H:i') }}</div>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="mg-btn-primary"><i class="ri-save-line"></i> Salvar</button>
                    <a href="{{ route('support-tickets.show', $supportTicket) }}" class="mg-btn-ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
