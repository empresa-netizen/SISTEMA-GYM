@extends('layouts.master')

@section('title', 'Notas de Atualização')

@push('styles')
<style>
    .prime-release-page {
        max-width: 980px;
        margin: 0 auto;
    }

    .prime-release-hero {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 1.25rem;
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.22), transparent 32rem),
            linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(2, 6, 23, 0.94));
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.28);
    }

    .prime-release-kicker,
    .prime-release-meta,
    .prime-release-detail {
        color: var(--prime-muted);
        font-size: 0.78rem;
    }

    .prime-release-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.35rem;
        color: #93c5fd;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .prime-release-count {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.7rem;
        border: 1px solid rgba(96, 165, 250, 0.26);
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.82);
        color: #bfdbfe;
        font-size: 0.78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .prime-release-list {
        display: grid;
        gap: 0.65rem;
        margin-top: 1rem;
    }

    .prime-release-item {
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.14);
        border-radius: 1rem;
        background: rgba(12, 16, 24, 0.94);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
    }

    .prime-release-button {
        width: 100%;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 0.85rem;
        align-items: center;
        padding: 0.9rem 1rem;
        border: 0;
        background: transparent;
        color: var(--prime-text);
        text-align: left;
    }

    .prime-release-button:hover {
        background: rgba(30, 41, 59, 0.48);
    }

    .prime-release-title-row,
    .prime-release-chip-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
    }

    .prime-release-title {
        font-size: 0.95rem;
        font-weight: 850;
        line-height: 1.25;
    }

    .prime-release-summary {
        margin: 0.35rem 0 0;
        color: #aebbd0;
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .prime-release-badge-new {
        display: inline-flex;
        align-items: center;
        padding: 0.18rem 0.42rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: #04130a;
        font-size: 0.62rem;
        font-weight: 900;
        letter-spacing: 0.08em;
    }

    .prime-release-area {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.22rem 0.55rem;
        border: 1px solid rgba(96, 165, 250, 0.22);
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.12);
        color: #bfdbfe;
        font-size: 0.72rem;
        font-weight: 800;
    }

    .prime-release-version {
        color: #dbeafe;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .prime-release-date {
        color: #94a3b8;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .prime-release-chevron {
        display: inline-flex;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.9);
        color: #cbd5e1;
        transition: transform 0.18s ease;
    }

    .prime-release-button[aria-expanded="true"] .prime-release-chevron {
        transform: rotate(180deg);
    }

    .prime-release-body {
        padding: 0 1rem 0.95rem;
    }

    .prime-release-detail-list {
        display: grid;
        gap: 0.45rem;
        margin: 0;
        padding: 0.75rem;
        border-radius: 0.85rem;
        background: rgba(2, 6, 23, 0.58);
        list-style: none;
    }

    .prime-release-detail-list li {
        display: flex;
        gap: 0.5rem;
        color: #dbe6f8;
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .prime-release-detail-list i {
        margin-top: 0.12rem;
        color: #60a5fa;
    }

    @media (max-width: 767.98px) {
        .prime-release-hero,
        .prime-release-button {
            grid-template-columns: 1fr;
        }

        .prime-release-hero {
            flex-direction: column;
        }

        .prime-release-count {
            align-self: stretch;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="prime-release-page">
    <section class="prime-release-hero">
        <div>
            <span class="prime-release-kicker"><i class="ri-megaphone-line"></i> MGTEAM Prime</span>
            <h1 class="prime-page-title mb-1">Notas de Atualização</h1>
            <p class="prime-page-sub mb-0">Releases locais do Web Profissional e App, organizadas em uma lista expansível no padrão Prime.</p>
        </div>
        <span class="prime-release-count"><i class="ri-history-line"></i> {{ count($notes) }} atualizações</span>
    </section>

    <div class="prime-release-list" id="primeReleaseNotes">
        @foreach($notes as $note)
            @php($noteId = 'primeReleaseNote'.$loop->iteration)
            <article class="prime-release-item">
                <button
                    class="prime-release-button"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $noteId }}"
                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                    aria-controls="{{ $noteId }}"
                >
                    <span>
                        <span class="prime-release-chip-row mb-2">
                            <span class="prime-release-area">
                                <i class="{{ $note['area'] === 'App' ? 'ri-smartphone-line' : 'ri-window-line' }}"></i>
                                {{ $note['area'] }}
                            </span>
                            <span class="prime-release-version">v{{ $note['version'] }}</span>
                            <span class="prime-release-date">{{ $note['date'] }}</span>
                        </span>
                        <span class="prime-release-title-row">
                            <span class="prime-release-title">{{ $note['title'] }}</span>
                            @if($note['is_new'])
                                <span class="prime-release-badge-new">NEW</span>
                            @endif
                        </span>
                        <span class="prime-release-summary">{{ $note['summary'] }}</span>
                    </span>
                    <span class="prime-release-chevron" aria-hidden="true"><i class="ri-arrow-down-s-line"></i></span>
                </button>

                <div id="{{ $noteId }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#primeReleaseNotes">
                    <div class="prime-release-body">
                        <ul class="prime-release-detail-list">
                            @foreach($note['items'] as $item)
                                <li><i class="ri-checkbox-circle-line"></i><span>{{ $item }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
