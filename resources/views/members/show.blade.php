@extends('layouts.master')

@section('title', $member->name)

@section('content')
@php
    $initials = collect(explode(' ', $member->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $latestHealth = $member->healthRecords->first();
    $weight = $latestHealth?->getMeasurement('weight');
    $height = $latestHealth?->getMeasurement('height');
    $age = $member->date_of_birth ? $member->date_of_birth->age : null;
    $daysLeft = $member->membership_end_date ? (int) now()->startOfDay()->diffInDays($member->membership_end_date->startOfDay(), false) : null;
    $waPhone = $member->phone ? preg_replace('/\D+/', '', $member->phone) : null;
    if ($waPhone && strlen($waPhone) <= 11) {
        $waPhone = '55'.$waPhone;
    }
    $appInstalled = (bool) $member->user_id;
    $tabs = [
        'progress' => 'Progresso',
        'appointments' => 'Agendamentos',
        'anamnesis' => 'Anamnese',
        'reviews' => 'Avaliações',
        'diet' => 'Dietas',
        'workout' => 'Treinos',
        'cardio' => 'Cardio',
        'exams' => 'Exames',
        'feedbacks' => 'Feedbacks',
        'photos' => 'Fotos',
        'notes' => 'Notas',
    ];
@endphp

<div class="mg-client-profile">
    <header class="mg-client-hero">
        <div class="mg-client-hero__top">
            <a href="{{ route('members.index') }}" class="mg-icon-btn" title="Voltar"><i class="ri-arrow-left-line"></i></a>

            <div class="mg-client-hero__identity">
                @if($member->photo)
                    <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="mg-client-hero__avatar-img">
                @else
                    <div class="mg-client-hero__avatar">{{ strtoupper($initials) }}</div>
                @endif
                <div>
                    <h1 class="mg-client-hero__name">{{ $member->name }}</h1>
                    <div class="mg-client-hero__contact">
                        <span>{{ $member->email }}</span>
                        @if($appInstalled)
                            <span class="mg-chip mg-chip--success"><i class="ri-smartphone-line"></i> App instalado</span>
                        @else
                            <span class="mg-chip"><i class="ri-smartphone-line"></i> App não instalado</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mg-client-hero__cta">
                <a href="{{ route('members.edit', $member) }}" class="mg-btn-ghost"><i class="ri-pencil-line"></i> Editar cliente</a>
                <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#assumeClientModal">
                    <i class="ri-user-shared-line"></i>
                    {{ $member->coach_user_id === auth()->id() ? 'Cliente sob sua responsabilidade' : 'Assumir cliente' }}
                </button>
                <button type="button" class="mg-btn-ghost" data-bs-toggle="modal" data-bs-target="#notifyClientModal">
                    <i class="ri-notification-3-line"></i> Notificar
                </button>
            </div>
        </div>

        <div class="mg-client-hero__utils">
            <a href="{{ route('members.show', [$member, 'tab' => 'progress']) }}" class="mg-icon-btn" title="Progresso"><i class="ri-checkbox-circle-line"></i></a>
            <a href="{{ route('members.show', [$member, 'tab' => 'notes']) }}" class="mg-icon-btn" title="Histórico"><i class="ri-history-line"></i></a>
            <button type="button" class="mg-icon-btn" title="Copiar link" onclick="navigator.clipboard?.writeText(window.location.href)"><i class="ri-link"></i></button>
            <a href="mailto:{{ $member->email }}" class="mg-icon-btn" title="E-mail"><i class="ri-mail-line"></i></a>
            <a href="{{ route('members.show', [$member, 'tab' => 'appointments']) }}" class="mg-icon-btn" title="Agenda"><i class="ri-calendar-line"></i></a>
            <form method="POST" action="{{ route('messages.start', $member) }}" class="d-inline">
                @csrf
                <button type="submit" class="mg-icon-btn" title="Chat"><i class="ri-message-3-line"></i></button>
            </form>
            @if($waPhone)
                <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="mg-icon-btn mg-icon-btn--whatsapp" title="WhatsApp"><i class="ri-whatsapp-line"></i></a>
            @endif
        </div>

        <div class="mg-client-chips mg-client-chips--hero">
            @if($age !== null)
                <span class="mg-chip">{{ $age }} anos</span>
            @endif
            @if($height)
                <span class="mg-chip">{{ rtrim(rtrim(number_format($height, 1, ',', ''), '0'), ',') }} cm</span>
            @endif
            @if($weight)
                <span class="mg-chip">{{ rtrim(rtrim(number_format($weight, 1, ',', ''), '0'), ',') }} kg</span>
            @endif
            @if($member->membershipPlan)
                <span class="mg-chip">{{ $member->membershipPlan->name }}</span>
            @endif
            @if($daysLeft !== null)
                @if($daysLeft < 0)
                    <span class="mg-chip mg-chip--danger">Expirado</span>
                @else
                    <span class="mg-chip mg-chip--success">{{ $daysLeft }} dias restantes</span>
                @endif
            @endif
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <strong>Revise os dados enviados.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <nav class="mg-client-tabs" aria-label="Abas do cliente">
        @foreach($tabs as $key => $label)
            <a href="{{ route('members.show', [$member, 'tab' => $key]) }}" class="mg-client-tabs__link @if($tab === $key) is-active @endif">{{ $label }}</a>
        @endforeach
    </nav>

    <div class="mg-client-tab-body">
        @include('members.tabs.'.$tab)
    </div>
</div>

<div class="modal fade" id="assumeClientModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('members.assume', $member) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Assumir cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Você passará a ser o coach responsável por <strong>{{ $member->name }}</strong>.</p>
                @if($member->coach)
                    <p class="small text-muted mb-0">Responsável atual: {{ $member->coach->name }}</p>
                @else
                    <p class="small text-muted mb-0">Nenhum coach responsável definido ainda.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="notifyClientModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('members.notify', $member) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Notificar cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Canal</label>
                    <select name="channel" class="form-select">
                        <option value="app">App do aluno</option>
                        <option value="email">E-mail</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assunto</label>
                    <input type="text" name="subject" class="form-control" placeholder="Ex: Novo plano disponível" value="Atualização do seu acompanhamento">
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensagem</label>
                    <textarea name="message" class="form-control" rows="3" placeholder="Escreva a mensagem para o aluno...">Olá {{ $member->name }}, há uma atualização no seu acompanhamento.</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Enviar notificação</button>
            </div>
        </form>
    </div>
</div>
@endsection
