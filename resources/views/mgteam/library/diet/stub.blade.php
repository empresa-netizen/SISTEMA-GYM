@extends('layouts.master')

@section('title', $title)

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">{{ $title }}</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="{{ $icon }}"></i>
                    Em breve
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('library.diet.index') }}" class="mg-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <div class="mg-empty-state">
        <i class="{{ $icon }}"></i>
        <p>{{ $subtitle }}</p>
    </div>
</div>
@endsection
