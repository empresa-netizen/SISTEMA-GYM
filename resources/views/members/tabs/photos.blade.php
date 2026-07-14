@php
    $typeLabels = ['front' => 'Frente', 'back' => 'Costas', 'side' => 'Lateral', 'progress' => 'Evolução', 'document' => 'Documento'];
    $comparePhotos = $member->photos->whereIn('type', array_keys($typeLabels))->values();
    $photosByDate = $comparePhotos->groupBy(fn ($photo) => $photo->created_at->format('Y-m-d'));
@endphp

<div class="mg-tab-block">
    <div class="mg-tab-block__head">
        <div>
            <p class="mg-section-label mb-1">Evolução visual</p>
            <h2 class="mg-tab-block__title">Fotos <span class="mg-title-count">{{ $comparePhotos->count() }}</span></h2>
        </div>
        <div class="mg-tab-actions">
            <button type="button" class="mg-btn-ghost" data-bs-toggle="modal" data-bs-target="#comparePhotosModal" @disabled($comparePhotos->count() < 2)>
                <i class="ri-split-cells-horizontal"></i> Comparar fotos
            </button>
            <a href="#photo-upload" class="mg-btn-primary">
                <i class="ri-add-line"></i> Adicionar fotos
            </a>
        </div>
    </div>

    <form id="photo-upload" method="POST" action="{{ route('members.photos.store', $member) }}" enctype="multipart/form-data" class="mg-dense-form mg-photo-upload mb-4">
        @csrf
        <div class="mg-clients-filters__grid">
            <div>
                <label class="mg-field-label">Arquivo</label>
                <input type="file" name="photo" class="mg-field" accept="image/*" required>
            </div>
            <div>
                <label class="mg-field-label">Tipo</label>
                <select name="type" class="mg-field">
                    @foreach($typeLabels as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mg-field-label">Legenda</label>
                <input type="text" name="caption" class="mg-field" placeholder="Opcional">
            </div>
            <div class="mg-clients-filters__actions">
                <button class="mg-btn-primary">Adicionar fotos</button>
            </div>
        </div>
    </form>

    @forelse($photosByDate as $date => $photos)
        <section class="mg-photo-date-group">
            <div class="mg-photo-date-group__head">
                <h3>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>
                <span>{{ $photos->count() }} foto{{ $photos->count() === 1 ? '' : 's' }}</span>
            </div>
            <div class="mg-photo-grid">
                @foreach($photos as $photo)
                    <figure class="mg-photo-card">
                        <img src="{{ $photo->url }}" alt="{{ $photo->caption }}">
                        <figcaption>
                            <span class="mg-chip">{{ $typeLabels[$photo->type] ?? $photo->type }}</span>
                            @if($photo->caption)<span>{{ $photo->caption }}</span>@endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @empty
        <div class="mg-empty-state mg-empty-state--compact">
            <i class="ri-image-line"></i>
            <p>Nenhuma foto enviada.</p>
            <a href="#photo-upload" class="mg-btn-primary">Adicionar fotos</a>
        </div>
    @endforelse
</div>

<div class="modal fade" id="comparePhotosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('members.photos.compare', $member) }}" class="modal-content" id="comparePhotosForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Comparar fotos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($comparePhotos->count() < 2)
                    <p class="mb-0 text-muted">Envie ao menos duas fotos para comparar.</p>
                @else
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Foto A</label>
                            <select name="photo_a_id" class="form-select" required>
                                @foreach($comparePhotos as $photo)
                                    <option value="{{ $photo->id }}">
                                        {{ $photo->created_at?->format('d/m/Y') }} — {{ $typeLabels[$photo->type] ?? $photo->type }}{{ $photo->caption ? ' · '.$photo->caption : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Foto B</label>
                            <select name="photo_b_id" class="form-select" required>
                                @foreach($comparePhotos as $i => $photo)
                                    <option value="{{ $photo->id }}" @selected($i === 1)>
                                        {{ $photo->created_at?->format('d/m/Y') }} — {{ $typeLabels[$photo->type] ?? $photo->type }}{{ $photo->caption ? ' · '.$photo->caption : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="comparePhotosPreview" class="row g-3 d-none">
                        <div class="col-md-6">
                            <figure class="mg-photo-card mb-0">
                                <img id="comparePreviewA" src="" alt="Foto A">
                                <figcaption id="compareCaptionA"></figcaption>
                            </figure>
                        </div>
                        <div class="col-md-6">
                            <figure class="mg-photo-card mb-0">
                                <img id="comparePreviewB" src="" alt="Foto B">
                                <figcaption id="compareCaptionB"></figcaption>
                            </figure>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                @if($comparePhotos->count() >= 2)
                    <button type="submit" class="btn btn-primary">Comparar</button>
                @endif
            </div>
        </form>
    </div>
</div>

@if(session('compare_photos'))
    @php $cmp = session('compare_photos'); @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const preview = document.getElementById('comparePhotosPreview');
            if (!preview) return;
            preview.classList.remove('d-none');
            document.getElementById('comparePreviewA').src = @json($cmp['a']['url']);
            document.getElementById('comparePreviewB').src = @json($cmp['b']['url']);
            document.getElementById('compareCaptionA').textContent = @json(($cmp['a']['caption'] ?: 'Foto A'));
            document.getElementById('compareCaptionB').textContent = @json(($cmp['b']['caption'] ?: 'Foto B'));
            const modal = document.getElementById('comparePhotosModal');
            if (modal && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });
    </script>
@endif
