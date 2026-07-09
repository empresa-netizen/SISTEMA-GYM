@php
    $examPhotos = $member->photos->whereIn('type', ['exam_document', 'document']);
    $examNotes = collect(preg_split('/\n+/', (string) ($member->medical_conditions ?? '')))
        ->map(fn ($l) => trim($l))
        ->filter();
    $typeLabels = [
        'exam_document' => 'Exame',
        'document' => 'Documento',
    ];
@endphp

<div class="prime-tab-block">
    <div class="prime-tab-block__head">
        <div>
            <p class="prime-section-label mb-1">Saúde</p>
            <h2 class="prime-tab-block__title">Exames</h2>
        </div>
        <div class="prime-tab-actions">
            <a href="#exam-upload" class="prime-btn-primary">
                <i class="ri-upload-2-line"></i> Upload de exame
            </a>
            <button type="button" class="prime-btn-ghost" data-bs-toggle="modal" data-bs-target="#notifyClientModal">
                <i class="ri-file-add-line"></i> Solicitar exame
            </button>
        </div>
    </div>

    <form id="exam-upload" method="POST" action="{{ route('members.photos.store', $member) }}" enctype="multipart/form-data" class="prime-dense-form prime-photo-upload mb-4">
        @csrf
        <input type="hidden" name="type" value="exam_document">
        <div class="prime-clients-filters__grid">
            <div>
                <label class="prime-field-label">Arquivo</label>
                <input type="file" name="photo" class="prime-field" accept="image/*,.pdf,.doc,.docx" required>
            </div>
            <div>
                <label class="prime-field-label">Descrição</label>
                <input type="text" name="caption" class="prime-field" placeholder="Ex: Hemograma 07/2026">
            </div>
            <div class="prime-clients-filters__actions">
                <button class="prime-btn-primary">Enviar exame</button>
            </div>
        </div>
    </form>

    @if($examNotes->isNotEmpty())
        <div class="prime-note-box mb-3">
            <div class="prime-panel-label mb-2">CONDIÇÕES / OBSERVAÇÕES MÉDICAS</div>
            @foreach($examNotes as $note)
                <p class="mb-1 small">{{ $note }}</p>
            @endforeach
        </div>
    @endif

    <div class="prime-prescription-list">
    @forelse($examPhotos as $photo)
        <article class="prime-prescription-card">
            <div class="prime-prescription-card__main">
                <div class="prime-prescription-card__eyebrow">{{ $photo->created_at->format('d/m/Y') }}</div>
                <h3 class="prime-prescription-card__title">{{ $photo->caption ?: 'Arquivo de exame' }}</h3>
                <div class="prime-prescription-card__meta">
                    <span>{{ $typeLabels[$photo->type] ?? 'Documento' }} enviado</span>
                </div>
            </div>
            <div class="prime-card-actions">
                <span class="prime-chip">{{ $typeLabels[$photo->type] ?? 'Documento' }}</span>
                <a href="{{ $photo->url }}" target="_blank" rel="noopener" class="prime-icon-btn" title="Abrir"><i class="ri-eye-line"></i></a>
            </div>
        </article>
    @empty
        @if($examNotes->isEmpty())
            <div class="prime-empty-state prime-empty-state--compact">
                <i class="ri-file-list-3-line"></i>
                <p>Este aluno ainda não enviou nenhum arquivo de exame</p>
                <a href="#exam-upload" class="prime-btn-primary">Upload de exame</a>
            </div>
        @endif
    @endforelse
    </div>
</div>
