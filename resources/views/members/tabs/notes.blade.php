@php
    $notes = $member->memberNotes ?? collect();
@endphp

<div class="prime-tab-block">
    <div class="prime-tab-block__head">
        <div>
            <p class="prime-section-label mb-1">Anotações internas</p>
            <h2 class="prime-tab-block__title">Notas do coach <span class="prime-title-count">{{ $notes->count() }}</span></h2>
        </div>
    </div>

    <form method="POST" action="{{ route('members.notes.store', $member) }}" class="prime-note-composer mb-3">
        @csrf
        <label class="prime-field-label">Nova nota</label>
        <textarea name="body" class="prime-field" rows="4" placeholder="Escreva uma observação interna sobre este aluno..." required>{{ old('body') }}</textarea>
        <div class="prime-note-composer__actions">
            <button type="submit" class="prime-btn-primary"><i class="ri-add-line"></i> Adicionar nota</button>
        </div>
    </form>

    @forelse($notes as $note)
        <article class="prime-note-card mb-2">
            <div class="prime-note-card__head">
                <span>{{ $note->author?->name ?? 'Coach' }}</span>
                <small>{{ $note->noted_at?->format('d/m/Y H:i') ?? $note->created_at?->format('d/m/Y H:i') }}</small>
            </div>
            <div class="prime-note-card__body">{!! nl2br(e($note->body)) !!}</div>
        </article>
    @empty
        <div class="prime-empty-state prime-empty-state--compact">
            <i class="ri-sticky-note-line"></i>
            <p>Nenhuma nota registrada para este cliente.</p>
        </div>
    @endforelse

    @if($member->emergency_contact_name || $member->emergency_contact_phone)
        <div class="prime-note-box mt-3">
            <div class="prime-panel-label mb-2">CONTATO DE EMERGÊNCIA</div>
            <p class="mb-0 small">{{ $member->emergency_contact_name ?? '—' }} · {{ $member->emergency_contact_phone ?? '—' }}</p>
        </div>
    @endif
</div>
