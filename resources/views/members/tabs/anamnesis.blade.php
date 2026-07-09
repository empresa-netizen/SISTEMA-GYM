@php
    $anamnesisStatus = $member->anamnesis?->status ?? 'pending';
@endphp

<div class="prime-tab-block">
    <div class="prime-tab-block__head">
        <div>
            <p class="prime-section-label mb-1">Ficha clínica</p>
            <h2 class="prime-tab-block__title">Anamnese</h2>
        </div>
        <div class="prime-tab-actions">
            <span class="prime-chip {{ $anamnesisStatus === 'completed' ? 'prime-chip--success' : 'prime-chip--warn' }}">
                {{ $anamnesisStatus === 'completed' ? 'Completa' : 'Pendente' }}
            </span>
            <button type="button" class="prime-btn-ghost" disabled title="Envio de formulário ainda não disponível">
                <i class="ri-send-plane-line"></i> Enviar anamnese
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('members.anamnesis.store', $member) }}" class="prime-dense-form">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="prime-field-label">Objetivos</label>
                <textarea name="goals" class="prime-field" rows="3">{{ old('goals', $member->anamnesis?->goals) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="prime-field-label">Lesões / restrições</label>
                <textarea name="injuries" class="prime-field" rows="3">{{ old('injuries', $member->anamnesis?->injuries) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="prime-field-label">Medicamentos</label>
                <textarea name="medications" class="prime-field" rows="2">{{ old('medications', $member->anamnesis?->medications) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="prime-field-label">Estilo de vida</label>
                <textarea name="lifestyle" class="prime-field" rows="2">{{ old('lifestyle', $member->anamnesis?->lifestyle) }}</textarea>
            </div>
            <div class="col-12">
                <label class="prime-field-label">Notas adicionais</label>
                <textarea name="notes" class="prime-field" rows="2">{{ old('notes', $member->anamnesis?->notes) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="prime-field-label">Status</label>
                <select name="status" class="prime-field">
                    <option value="pending" @selected($anamnesisStatus === 'pending')>Pendente</option>
                    <option value="completed" @selected($anamnesisStatus === 'completed')>Completa</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="prime-btn-primary"><i class="ri-save-line"></i> Salvar anamnese</button>
            </div>
        </div>
    </form>
</div>
