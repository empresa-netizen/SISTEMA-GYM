@extends('layouts.master')

@section('title', 'Frequência')

@section('css')
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Frequência</h1>
        <p class="mg-page-sub">Controle de check-in e check-out dos alunos.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('attendances.report') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri-bar-chart-line me-1"></i> Relatório
        </a>
        <a href="{{ route('attendances.create') }}" class="btn btn-primary btn-sm">
            <i class="ri-login-circle-line me-1"></i> Check-in
        </a>
    </div>
</div>

<div class="row g-2 mb-4">
    <div class="col-md-4">
        <div class="mg-stat-mini">
            <span>Visitas hoje</span>
            <strong>{{ $todayStats['total'] }}</strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mg-stat-mini">
            <span>Na academia agora</span>
            <strong>{{ $todayStats['checked_in'] }}</strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mg-stat-mini">
            <span>Check-out hoje</span>
            <strong>{{ $todayStats['checked_out'] }}</strong>
        </div>
    </div>
</div>

<div class="mg-panel" style="height:auto">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="get" action="{{ route('attendances.index') }}" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}">
        </div>
        <div class="col-md-5">
            <select name="mamber" class="form-select">
                <option value="">Todos os clientes</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="ri-filter-line me-1"></i> Filtrar
            </button>
        </div>
    </form>

    <div class="table-responsive">
        {!! $dataTable->table() !!}
    </div>
</div>

<div class="modal fade" id="checkOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="checkOutForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Check-out do aluno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="check_out_time" class="form-label">Horário de saída <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="check_out_time" name="check_out_time"
                               value="{{ date('H:i') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-logout-circle-line me-1"></i> Check-out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('script')
{!! $dataTable->scripts() !!}
<script>
function checkOut(attendanceId) {
    const modal = new bootstrap.Modal(document.getElementById('checkOutModal'));
    const form = document.getElementById('checkOutForm');
    form.action = '/attendances/' + attendanceId;
    modal.show();
}

function deleteAttendance(attendanceId) {
    Swal.fire({
        title: 'Excluir registro?',
        text: 'Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = '/attendances/' + attendanceId;
            form.submit();
        }
    });
}
</script>
@endsection
