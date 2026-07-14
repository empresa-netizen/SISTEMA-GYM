@extends('layouts.master')

@section('title', 'Armarios')

@section('css')
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 mg-page-header">
    <div>
        <h1 class="mg-page-title">Gestao de Armarios</h1>
        <p class="mg-page-sub">Controle ocupacao e disponibilidade na MGTEAM FITNESS &amp; HEALTH.</p>
    </div>
    <a href="{{ route('lockers.create') }}" class="btn btn-primary">
        <i class="ri-add-line align-middle me-1"></i> Novo armario
    </a>
</div>

<div class="row mb-3 g-3">
    <div class="col-md-4">
        <div class="mg-panel h-100">
            <div class="mg-panel-body">
                <p class="text-uppercase fw-medium text-muted mb-1">Total de armarios</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ $stats['total'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mg-panel h-100">
            <div class="mg-panel-body">
                <p class="text-uppercase fw-medium text-muted mb-1">Disponiveis</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-success">{{ $stats['available'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mg-panel h-100">
            <div class="mg-panel-body">
                <p class="text-uppercase fw-medium text-muted mb-1">Ocupados</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-danger">{{ $stats['occupied'] }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="mg-panel">
    <div class="mg-panel-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line align-middle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line align-middle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-10">
                <select id="statusFilter" class="form-select">
                    <option value="">Todos os status</option>
                    <option value="available">Disponivel</option>
                    <option value="occupied">Ocupado</option>
                    <option value="maintenance">Manutencao</option>
                </select>
            </div>
            <div class="col-md-2">
                <button id="resetFilters" class="btn btn-light w-100">
                    <i class="ri-refresh-line"></i> Limpar
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="lockersTable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>Armario</th>
                        <th>Localizacao</th>
                        <th>Status</th>
                        <th>Valor mensal</th>
                        <th>Alocacao atual</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lockers as $locker)
                        <tr>
                            <td><strong>{{ $locker->locker_number }}</strong></td>
                            <td>{{ $locker->location ?? '-' }}</td>
                            <td>
                                <span class="badge
                                    @if($locker->status == 'available') bg-success
                                    @elseif($locker->status == 'occupied') bg-danger
                                    @else bg-warning text-dark
                                    @endif">
                                    {{ ucfirst($locker->status) }}
                                </span>
                            </td>
                            <td>R$ {{ number_format($locker->monthly_fee, 2, ',', '.') }}</td>
                            <td>
                                @if($locker->currentAssignment)
                                    <span class="badge bg-info">{{ $locker->currentAssignment->member->member_id }}</span>
                                @else
                                    <span class="text-muted">Nenhuma</span>
                                @endif
                            </td>
                            <td>
                                <div class="hstack gap-3 flex-wrap">
                                    <a href="{{ route('lockers.show', $locker->id) }}" class="link-success" title="Visualizar">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('lockers.edit', $locker->id) }}" class="link-info" title="Editar">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="link-danger" onclick="deleteLocker({{ $locker->id }})" title="Excluir">
                                        <i class="ri-delete-bin-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $lockers->links() }}
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
$(document).ready(function() {
    var table = $('#lockersTable').DataTable({
        responsive: true,
        pageLength: 20,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [5] }
        ]
    });

    $('#statusFilter').on('change', function() {
        table.column(2).search(this.value).draw();
    });

    $('#resetFilters').on('click', function() {
        $('#statusFilter').val('');
        table.columns().search('').draw();
    });
});

function deleteLocker(lockerId) {
    Swal.fire({
        title: 'Confirmar exclusao?',
        text: 'Este armario sera removido permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = '/lockers/' + lockerId;
            form.submit();
        }
    });
}
</script>
@endsection
