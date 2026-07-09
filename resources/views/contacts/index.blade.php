@extends('layouts.master')

@section('title', 'Contatos')

@push('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 prime-page-header">
    <div>
        <h1 class="prime-page-title">Contatos</h1>
        <p class="prime-page-sub">Gerencie mensagens e relacionamento da MGTEAM FITNESS &amp; HEALTH.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal" onclick="openCreateModal()">
        <i class="ri-add-line align-bottom me-1"></i> Novo contato
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" id="success-alert">
        <i class="ri-check-line me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="prime-panel">
    <div class="prime-panel-body">
        <div class="d-flex justify-content-sm-end gap-2 mb-3">
            <form method="GET" class="d-flex gap-2">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Buscar contatos..." value="{{ request('search') }}">
                    <i class="ri-search-line search-icon"></i>
                </div>
                @if(request('search'))
                    <a href="{{ route('contacts.index') }}" class="btn btn-light">
                        <i class="ri-refresh-line me-1"></i> Limpar
                    </a>
                @endif
            </form>
        </div>

        <div class="row" id="contacts-grid">
            @forelse($contacts as $contact)
                <div class="col-xxl-3 col-sm-6 contact-card" data-contact-id="{{ $contact->id }}">
                    <div class="card card-height-100">
                        <div class="card-body">
                            <div class="d-flex flex-column h-100">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-4">
                                            <i class="ri-time-line align-bottom me-1"></i>
                                            <span class="contact-time">{{ $contact->created_at->diffForHumans() }}</span>
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted p-1 mt-n2 py-0 text-decoration-none fs-15"
                                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="ri-more-2-fill fs-17"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#" onclick="openEditModal({{ $contact->id }}); return false;">
                                                    <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="#" onclick="deleteContact({{ $contact->id }}); return false;">
                                                    <i class="ri-delete-bin-fill align-bottom me-2"></i> Remover
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20 contact-avatar">
                                                {{ strtoupper(substr($contact->name ?? 'N', 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fs-16">
                                            <a href="#" class="text-body contact-name" data-bs-toggle="modal" data-bs-target="#viewContactModal" onclick="openViewModal('{{ $contact->id }}')">
                                                {{ $contact->name ?? 'N/A' }}
                                            </a>
                                        </h5>
                                        <p class="text-muted mb-1 contact-email">
                                            @if($contact->email)
                                                <i class="ri-mail-line align-bottom me-1"></i>
                                                {{ Str::limit($contact->email, 25) }}
                                            @else
                                                Sem e-mail
                                            @endif
                                        </p>
                                        <p class="text-muted mb-0 contact-phone">
                                            @if($contact->contact_number)
                                                <i class="ri-phone-line align-bottom me-1"></i>
                                                {{ $contact->contact_number }}
                                            @else
                                                <span class="text-muted" style="visibility: hidden;">-</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <div class="contact-subject">
                                        @if($contact->subject)
                                            <div class="mb-2">
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ Str::limit($contact->subject, 30) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-muted text-truncate-two-lines mb-0 contact-message">
                                        @if($contact->message)
                                            {{ Str::limit($contact->message, 80) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-dashed py-2">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-muted contact-date">
                                        <i class="ri-calendar-event-fill me-1 align-bottom"></i>
                                        {{ $contact->created_at->format('d M, Y') }}
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-sm btn-soft-primary" data-bs-toggle="modal" data-bs-target="#viewContactModal" onclick="openViewModal('{{ $contact->id }}')">
                                        Ver detalhes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="mb-3">
                                <i class="ri-contacts-line display-4 text-muted"></i>
                            </div>
                            <h5>Nenhum contato encontrado</h5>
                            <p class="text-muted">Comece adicionando o primeiro contato.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal" onclick="openCreateModal()">
                                <i class="ri-add-line me-1"></i> Novo contato
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $contacts->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Novo contato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="contactForm" method="POST" action="javascript:void(0);">
                @csrf
                <input type="hidden" id="contactId" name="contact_id">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Informe o nome do contato">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Informe o e-mail">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="Informe o telefone">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">Assunto</label>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Informe o assunto">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Mensagem/Observacoes</label>
                        <textarea class="form-control" id="message" name="message" rows="4" placeholder="Digite a mensagem"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveContactBtn">
                        <i class="ri-save-line me-1"></i> Salvar contato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewContactModal" tabindex="-1" aria-labelledby="viewContactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewContactModalLabel">Detalhes do contato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-md">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-24" id="view_avatar">
                                N
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-1" id="view_name">Carregando...</h5>
                        <p class="text-muted mb-0" id="view_email_header"></p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 150px;">E-mail</th>
                                <td class="text-muted" id="view_email">-</td>
                            </tr>
                            <tr>
                                <th class="ps-0">Telefone</th>
                                <td class="text-muted" id="view_contact_number">-</td>
                            </tr>
                            <tr>
                                <th class="ps-0">Assunto</th>
                                <td class="text-muted" id="view_subject">-</td>
                            </tr>
                            <tr>
                                <th class="ps-0">Mensagem</th>
                                <td class="text-muted" id="view_message">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="view_edit_btn">
                    <i class="ri-pencil-line me-1"></i> Editar contato
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
function openCreateModal() {
    $('#contactModalLabel').text('Novo contato');
    $('#contactForm')[0].reset();
    $('#contactId').val('');
    $('#formMethod').val('POST');
    clearValidationErrors();
}

function openEditModal(contactId) {
    $('#contactModalLabel').text('Editar contato');
    $('#formMethod').val('PUT');
    $('#contactId').val(contactId);
    clearValidationErrors();

    $.ajax({
        url: `/contacts/${contactId}/edit`,
        type: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(data) {
            $('#name').val(data.contact.name || '');
            $('#email').val(data.contact.email || '');
            $('#contact_number').val(data.contact.contact_number || '');
            $('#subject').val(data.contact.subject || '');
            $('#message').val(data.contact.message || '');

            const modalEl = document.getElementById('contactModal');
            let modal = bootstrap.Modal.getInstance(modalEl);
            if (!modal) {
                modal = new bootstrap.Modal(modalEl);
            }
            modal.show();
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            showToast('Erro ao carregar dados do contato', 'danger');
        }
    });
}

function openViewModal(contactId) {
    $('#view_name').text('Carregando...');
    $('#view_email').text('-');
    $('#view_email_header').text('');
    $('#view_contact_number').text('-');
    $('#view_subject').text('-');
    $('#view_message').text('-');
    $('#view_avatar').text('...');

    $.ajax({
        url: `/contacts/${contactId}`,
        type: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(data) {
            if (data.success) {
                const contact = data.contact;
                const name = contact.name || 'N/A';
                const email = contact.email || 'N/A';

                $('#view_name').text(name);
                $('#view_avatar').text(name.charAt(0).toUpperCase());
                $('#view_email').text(email);
                $('#view_email_header').text(email !== 'N/A' ? email : '');
                $('#view_contact_number').text(contact.contact_number || 'N/A');
                $('#view_subject').text(contact.subject || 'N/A');
                $('#view_message').text(contact.message || 'N/A');

                const editBtn = document.getElementById('view_edit_btn');
                if (editBtn) {
                    editBtn.onclick = function() {
                        switchToEditModal(contact.id);
                    };
                }
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            showToast('Erro ao carregar detalhes do contato', 'danger');
        }
    });
}

function switchToEditModal(contactId) {
    const viewModalEl = document.getElementById('viewContactModal');
    const viewModal = bootstrap.Modal.getInstance(viewModalEl);
    if (viewModal) {
        viewModal.hide();
    }

    setTimeout(() => {
        openEditModal(contactId);
    }, 500);
}

function deleteContact(contactId) {
    Swal.fire({
        title: 'Confirmar exclusao?',
        text: 'Esta acao nao podera ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/contacts/${contactId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        $(`.contact-card[data-contact-id="${contactId}"]`).fadeOut(300, function() {
                            $(this).remove();
                            if ($('.contact-card').length === 0) {
                                location.reload();
                            }
                        });
                        Swal.fire({
                            title: 'Excluido!',
                            text: 'Contato removido com sucesso.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Falha ao excluir contato.',
                        icon: 'error'
                    });
                }
            });
        }
    });
}

function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

function displayValidationErrors(errors) {
    $.each(errors, function(field, messages) {
        const $input = $('#' + field);
        if ($input.length) {
            $input.addClass('is-invalid');
            $input.next('.invalid-feedback').text(messages[0]);
        }
    });
}

function showToast(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'ri-check-line' : 'ri-error-warning-line';

    const $alert = $(`
        <div class="alert ${alertClass} alert-dismissible fade show">
            <i class="${icon} me-2"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

    $('.prime-page-header').after($alert);
    setTimeout(() => $alert.alert('close'), 3000);
}

$(document).ready(function() {
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        clearValidationErrors();

        const formData = new FormData(this);
        const contactId = $('#contactId').val();
        const method = $('#formMethod').val();
        const url = contactId ? `/contacts/${contactId}` : '/contacts';

        if (method === 'PUT') {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    const modalEl = document.getElementById('contactModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }

                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    displayValidationErrors(xhr.responseJSON.errors);
                } else {
                    console.error('Error:', xhr);
                    showToast('Ocorreu um erro inesperado.', 'danger');
                }
            }
        });
    });
});
</script>
@endpush
