@extends('layouts.master')

@section('title', 'Modelos de Notificacao')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 mg-page-header">
    <div>
        <h1 class="mg-page-title">Modelos de Notificacao</h1>
        <p class="mg-page-sub">Gerencie os templates de e-mail da MGTEAM FITNESS &amp; HEALTH.</p>
    </div>
    <span class="badge bg-success">{{ $notifications->count() }} modelos</span>
</div>

<div class="mg-panel">
    <div class="mg-panel-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line align-middle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%">Modulo</th>
                        <th style="width: 30%">Assunto</th>
                        <th style="width: 15%">Status e-mail</th>
                        <th style="width: 15%">Status web</th>
                        <th style="width: 15%">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifications as $notification)
                        <tr>
                            <td>
                                <strong>{{ ucfirst(str_replace('_', ' ', $notification->module)) }}</strong>
                                <br><small class="text-muted">{{ $notification->module }}</small>
                            </td>
                            <td>{{ $notification->subject }}</td>
                            <td>
                                @if($notification->enabled_email)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-secondary">Inativo</span>
                                @endif
                            </td>
                            <td>
                                @if($notification->enabled_web)
                                    <span class="badge bg-info">Ativo</span>
                                @else
                                    <span class="badge bg-secondary">Inativo</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('notifications.edit', $notification->id) }}" class="btn btn-sm btn-primary">
                                    <i class="ri-pencil-line align-middle"></i> Editar
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="alert alert-info mt-4">
            <h5 class="alert-heading"><i class="ri-information-line"></i> Shortcodes disponiveis</h5>
            <p class="mb-0">Use estes shortcodes nos templates. Eles serao substituidos automaticamente:</p>
            <ul class="mt-2 mb-0">
                <li><code>{gym_name}</code> - Nome da academia/aplicacao</li>
                <li><code>{user_name}</code>, <code>{member_name}</code>, <code>{trainer_name}</code> - Nomes de usuarios</li>
                <li><code>{email}</code>, <code>{password}</code> - Credenciais de acesso</li>
                <li><code>{member_id}</code>, <code>{trainer_id}</code>, <code>{invoice_number}</code> - Identificadores</li>
                <li><code>{membership_plan}</code>, <code>{expiry_date}</code> - Detalhes do plano</li>
                <li><code>{class_name}</code>, <code>{schedule_time}</code>, <code>{capacity}</code> - Dados das aulas</li>
                <li><code>{weight}</code>, <code>{bmi}</code>, <code>{date}</code> - Acompanhamento fisico</li>
                <li><code>{locker_number}</code>, <code>{start_date}</code> - Dados de armario</li>
            </ul>
        </div>
    </div>
</div>
@endsection
