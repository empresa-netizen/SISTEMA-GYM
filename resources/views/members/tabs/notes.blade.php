@php
    $notes = $member->memberNotes ?? collect();
@endphp

<div class="mg-tab-block">
    <div class="mg-tab-block__head">
        <div>
            <p class="mg-section-label mb-1">Anotações internas</p>
            <h2 class="mg-tab-block__title">Notas do coach <span class="mg-title-count">{{ $notes->count() }}</span></h2>
        </div>
    </div>

    <form method="POST" action="{{ route('members.notes.store', $member) }}" class="mg-note-composer mb-3">
        @csrf
        <label class="mg-field-label">Nova nota</label>
        <textarea name="body" class="mg-field" rows="4" placeholder="Escreva uma observação interna sobre este aluno..." required>{{ old('body') }}</textarea>
        <div class="mg-note-composer__actions">
            <button type="submit" class="mg-btn-primary"><i class="ri-add-line"></i> Adicionar nota</button>
        </div>
    </form>

    @forelse($notes as $note)
        <article class="mg-note-card mb-2">
            <div class="mg-note-card__head">
                <span>{{ $note->author?->name ?? 'Coach' }}</span>
                <small>{{ $note->noted_at?->format('d/m/Y H:i') ?? $note->created_at?->format('d/m/Y H:i') }}</small>
            </div>
            <div class="mg-note-card__body">{!! nl2br(e($note->body)) !!}</div>
        </article>
    @empty
        <div class="mg-empty-state mg-empty-state--compact">
            <i class="ri-sticky-note-line"></i>
            <p>Nenhuma nota registrada para este cliente.</p>
        </div>
    @endforelse

    @if($member->emergency_contact_name || $member->emergency_contact_phone)
        <div class="mg-note-box mt-3">
            <div class="mg-panel-label mb-2">CONTATO DE EMERGÊNCIA</div>
            <p class="mb-0 small">{{ $member->emergency_contact_name ?? '—' }} · {{ $member->emergency_contact_phone ?? '—' }}</p>
        </div>
    @endif
</div>
