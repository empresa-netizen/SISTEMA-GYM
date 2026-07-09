@extends('layouts.master')

@section('title', $title)

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">{{ $title }}</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="{{ $icon }}"></i>
                    Em breve
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('library.diet.index') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <div class="prime-empty-state">
        <i class="{{ $icon }}"></i>
        <p>{{ $subtitle }}</p>
    </div>
</div>
@endsection
