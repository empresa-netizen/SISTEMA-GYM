@extends('layouts.master')

@section('title', 'Notas de Atualização')

@push('styles')
<style>
    .mg-release-page {
        max-width: 980px;
        margin: 0 auto;
    }

    .mg-release-hero {
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

    .mg-release-kicker,
    .mg-release-meta,
    .mg-release-detail {
        color: var(--mg-muted);
        font-size: 0.78rem;
    }

    .mg-release-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.35rem;
        color: #93c5fd;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .mg-release-count {
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

    .mg-release-list {
        display: grid;
        gap: 0.65rem;
        margin-top: 1rem;
    }

    .mg-release-item {
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.14);
        border-radius: 1rem;
        background: rgba(12, 16, 24, 0.94);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
    }

    .mg-release-button {
        width: 100%;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 0.85rem;
        align-items: center;
        padding: 0.9rem 1rem;
        border: 0;
        background: transparent;
        color: var(--mg-text);
        text-align: left;
    }

    .mg-release-button:hover {
        background: rgba(30, 41, 59, 0.48);
    }

    .mg-release-title-row,
    .mg-release-chip-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
    }

    .mg-release-title {
        font-size: 0.95rem;
        font-weight: 850;
        line-height: 1.25;
    }

    .mg-release-summary {
        margin: 0.35rem 0 0;
        color: #aebbd0;
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .mg-release-badge-new {
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

    .mg-release-area {
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

    .mg-release-version {
        color: #dbeafe;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .mg-release-date {
        color: #94a3b8;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .mg-release-chevron {
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

    .mg-release-button[aria-expanded="true"] .mg-release-chevron {
        transform: rotate(180deg);
    }

    .mg-release-body {
        padding: 0 1rem 0.95rem;
    }

    .mg-release-detail-list {
        display: grid;
        gap: 0.45rem;
        margin: 0;
        padding: 0.75rem;
        border-radius: 0.85rem;
        background: rgba(2, 6, 23, 0.58);
        list-style: none;
    }

    .mg-release-detail-list li {
        display: flex;
        gap: 0.5rem;
        color: #dbe6f8;
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .mg-release-detail-list i {
        margin-top: 0.12rem;
        color: #60a5fa;
    }

    @media (max-width: 767.98px) {
        .mg-release-hero,
        .mg-release-button {
            grid-template-columns: 1fr;
        }

        .mg-release-hero {
            flex-direction: column;
        }

        .mg-release-count {
            align-self: stretch;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="mg-release-page">
    <section class="mg-release-hero">
        <div>
            <span class="mg-release-kicker"><i class="ri-megaphone-line"></i> MGTEAM MGTEAM</span>
            <h1 class="mg-page-title mb-1">Notas de Atualização</h1>
            <p class="mg-page-sub mb-0">Releases locais do Web Profissional e App, organizadas em uma lista expansível no padrão MGTEAM.</p>
        </div>
        <span class="mg-release-count"><i class="ri-history-line"></i> {{ count($notes) }} atualizações</span>
    </section>

    <div class="mg-release-list" id="mgReleaseNotes">
        @foreach($notes as $note)
            @php($noteId = 'mgReleaseNote'.$loop->iteration)
            <article class="mg-release-item">
                <button
                    class="mg-release-button"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $noteId }}"
                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                    aria-controls="{{ $noteId }}"
                >
                    <span>
                        <span class="mg-release-chip-row mb-2">
                            <span class="mg-release-area">
                                <i class="{{ $note['area'] === 'App' ? 'ri-smartphone-line' : 'ri-window-line' }}"></i>
                                {{ $note['area'] }}
                            </span>
                            <span class="mg-release-version">v{{ $note['version'] }}</span>
                            <span class="mg-release-date">{{ $note['date'] }}</span>
                        </span>
                        <span class="mg-release-title-row">
                            <span class="mg-release-title">{{ $note['title'] }}</span>
                            @if($note['is_new'])
                                <span class="mg-release-badge-new">NEW</span>
                            @endif
                        </span>
                        <span class="mg-release-summary">{{ $note['summary'] }}</span>
                    </span>
                    <span class="mg-release-chevron" aria-hidden="true"><i class="ri-arrow-down-s-line"></i></span>
                </button>

                <div id="{{ $noteId }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#mgReleaseNotes">
                    <div class="mg-release-body">
                        <ul class="mg-release-detail-list">
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
