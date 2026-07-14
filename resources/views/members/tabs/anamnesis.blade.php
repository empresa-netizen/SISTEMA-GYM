@php
    $anamnesisStatus = $member->anamnesis?->status ?? 'pending';
@endphp

<div class="mg-tab-block">
    <div class="mg-tab-block__head">
        <div>
            <p class="mg-section-label mb-1">Ficha clínica</p>
            <h2 class="mg-tab-block__title">Anamnese</h2>
        </div>
        <div class="mg-tab-actions">
            <span class="mg-chip {{ $anamnesisStatus === 'completed' ? 'mg-chip--success' : 'mg-chip--warn' }}">
                {{ $anamnesisStatus === 'completed' ? 'Completa' : 'Pendente' }}
            </span>
            <button type="button" class="mg-btn-ghost" data-bs-toggle="modal" data-bs-target="#notifyClientModal">
                <i class="ri-send-plane-line"></i> Notificar cliente
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('members.anamnesis.store', $member) }}" class="mg-dense-form">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="mg-field-label">Objetivos</label>
                <textarea name="goals" class="mg-field" rows="3">{{ old('goals', $member->anamnesis?->goals) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="mg-field-label">Lesões / restrições</label>
                <textarea name="injuries" class="mg-field" rows="3">{{ old('injuries', $member->anamnesis?->injuries) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="mg-field-label">Medicamentos</label>
                <textarea name="medications" class="mg-field" rows="2">{{ old('medications', $member->anamnesis?->medications) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="mg-field-label">Estilo de vida</label>
                <textarea name="lifestyle" class="mg-field" rows="2">{{ old('lifestyle', $member->anamnesis?->lifestyle) }}</textarea>
            </div>
            <div class="col-12">
                <label class="mg-field-label">Notas adicionais</label>
                <textarea name="notes" class="mg-field" rows="2">{{ old('notes', $member->anamnesis?->notes) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="mg-field-label">Status</label>
                <select name="status" class="mg-field">
                    <option value="pending" @selected($anamnesisStatus === 'pending')>Pendente</option>
                    <option value="completed" @selected($anamnesisStatus === 'completed')>Completa</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="mg-btn-primary"><i class="ri-save-line"></i> Salvar anamnese</button>
            </div>
        </div>
    </form>
</div>
