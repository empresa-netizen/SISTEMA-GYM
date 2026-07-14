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

<div class="mg-tab-block">
    <div class="mg-tab-block__head">
        <div>
            <p class="mg-section-label mb-1">Saúde</p>
            <h2 class="mg-tab-block__title">Exames</h2>
        </div>
        <div class="mg-tab-actions">
            <a href="#exam-upload" class="mg-btn-primary">
                <i class="ri-upload-2-line"></i> Upload de exame
            </a>
            <button type="button" class="mg-btn-ghost" data-bs-toggle="modal" data-bs-target="#notifyClientModal">
                <i class="ri-file-add-line"></i> Solicitar exame
            </button>
        </div>
    </div>

    <form id="exam-upload" method="POST" action="{{ route('members.photos.store', $member) }}" enctype="multipart/form-data" class="mg-dense-form mg-photo-upload mb-4">
        @csrf
        <input type="hidden" name="type" value="exam_document">
        <div class="mg-clients-filters__grid">
            <div>
                <label class="mg-field-label">Arquivo</label>
                <input type="file" name="photo" class="mg-field" accept="image/*,.pdf,.doc,.docx" required>
            </div>
            <div>
                <label class="mg-field-label">Descrição</label>
                <input type="text" name="caption" class="mg-field" placeholder="Ex: Hemograma 07/2026">
            </div>
            <div class="mg-clients-filters__actions">
                <button class="mg-btn-primary">Enviar exame</button>
            </div>
        </div>
    </form>

    @if($examNotes->isNotEmpty())
        <div class="mg-note-box mb-3">
            <div class="mg-panel-label mb-2">CONDIÇÕES / OBSERVAÇÕES MÉDICAS</div>
            @foreach($examNotes as $note)
                <p class="mb-1 small">{{ $note }}</p>
            @endforeach
        </div>
    @endif

    <div class="mg-prescription-list">
    @forelse($examPhotos as $photo)
        <article class="mg-prescription-card">
            <div class="mg-prescription-card__main">
                <div class="mg-prescription-card__eyebrow">{{ $photo->created_at->format('d/m/Y') }}</div>
                <h3 class="mg-prescription-card__title">{{ $photo->caption ?: 'Arquivo de exame' }}</h3>
                <div class="mg-prescription-card__meta">
                    <span>{{ $typeLabels[$photo->type] ?? 'Documento' }} enviado</span>
                </div>
            </div>
            <div class="mg-card-actions">
                <span class="mg-chip">{{ $typeLabels[$photo->type] ?? 'Documento' }}</span>
                <a href="{{ $photo->url }}" target="_blank" rel="noopener" class="mg-icon-btn" title="Abrir"><i class="ri-eye-line"></i></a>
            </div>
        </article>
    @empty
        @if($examNotes->isEmpty())
            <div class="mg-empty-state mg-empty-state--compact">
                <i class="ri-file-list-3-line"></i>
                <p>Este aluno ainda não enviou nenhum arquivo de exame</p>
                <a href="#exam-upload" class="mg-btn-primary">Upload de exame</a>
            </div>
        @endif
    @endforelse
    </div>
</div>
