<?php

namespace App\DataTables;

use App\Models\User;
use App\Traits\DataTableConfigTrait;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    use DataTableConfigTrait;

    private array $staffRoles = ['owner', 'manager', 'trainer', 'receptionist'];

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<User> $query Results from query() method.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('name', function ($query) {
                $initial = mb_strtoupper(mb_substr($query->name, 0, 1));
                $html = '<div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-2">
                            <div class="prime-collaborator-avatar">' . e($initial) . '</div>
                    </div>
                    <div class="flex-grow-1">';

                $html .= '<div class="prime-collaborator-name">' . e($query->name) . '</div>';

                if ($query->id == auth()->id()) {
                    $html .= '<span class="prime-collaborator-badge ms-1">Você</span>';
                }

                $html .= '</div>
                    </div>';

                return $html;
            })
            ->editColumn('roles', function ($query) {
                if (!$query->roles || $query->roles->isEmpty()) {
                    return '<span class="prime-status-pill prime-status-pill--pending">Sem perfil</span>';
                }

                $badges = '';
                foreach ($query->roles as $role) {
                    $badges .= '<span class="prime-role-pill me-1">' . e($this->roleLabel($role->name)) . '</span>';
                }
                return $badges;
            })
            ->addColumn('status', function ($query) {
                return $this->statusBadge($query);
            })
            ->addColumn('created', function ($query) {
                return $query->created_at->format('d/m/Y');
            })
            ->addColumn('action', function ($query) {
                return $this->actionButtons($query);
            })
            ->rawColumns(['name', 'roles', 'status', 'created', 'action']);
    }

    public function query(User $model)
    {
        $request = $this->request;
        $parentId = parentId();
        $query = $model->newQuery()->with('roles')->where(function ($q) use ($parentId) {
            $q->where('parent_id', $parentId)
                ->orWhere('id', $parentId);
        })->whereHas('roles', function ($q) {
            $q->whereIn('name', $this->staffRoles);
        });

        $search = $request->get('search_value', $request->get('search'));
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role') && $request->role != '') {
            $query->role($request->role);
        }

        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'active') {
                $query->whereNotNull('email_verified_at');
            }

            if ($request->status === 'pending') {
                $query->whereNull('email_verified_at');
            }
        }

        return $query->latest('users.created_at');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('user-table')
            ->addTableClass('datatables-basic table prime-collaborators-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->responsive(false)
            ->orderBy(1, 'asc')
            ->parameters($this->getDataTableParameters(6));
    }

    protected function getColumns()
    {
        return [
            Column::computed('DT_RowIndex', 'No'),
            Column::make('name')->title('Colaborador'),
            Column::make('email')->title('Email'),
            Column::make('roles')->title('Perfil'),
            Column::computed('status')
                ->title('Status')
                ->searchable(false)
                ->orderable(false),
            Column::make('created')
                ->name('users.created_at')
                ->title('Criado em')
                ->orderable(true)
                ->searchable(false),
            Column::computed('action', 'Ações')
                ->exportable(false)
                ->printable(false)
                ->searchable(false)
                ->orderable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'User_' . date('YmdHis');
    }

    private function roleLabel(string $role): string
    {
        return [
            'owner' => 'Proprietário',
            'manager' => 'Gerente',
            'trainer' => 'Treinador',
            'receptionist' => 'Recepção',
        ][$role] ?? ucfirst($role);
    }

    private function statusBadge(User $user): string
    {
        if ($user->email_verified_at) {
            return '<span class="prime-status-pill prime-status-pill--active">Ativo</span>';
        }

        return '<span class="prime-status-pill prime-status-pill--pending">Pendente</span>';
    }

    private function actionButtons(User $user): string
    {
        $items = '';

        if (auth()->user()->can('edit users')) {
            $items .= '<li><a href="' . route('users.edit', $user) . '" class="dropdown-item"><i class="ri-pencil-line me-2"></i>Editar</a></li>';
            $items .= '<li><a href="' . route('users.edit', $user) . '#password" class="dropdown-item"><i class="ri-lock-password-line me-2"></i>Alterar senha</a></li>';
        }

        if (auth()->user()->can('delete users') && $user->id != auth()->id()) {
            $items .= '<li><button type="button" onclick="deleteRow(`' . route('users.destroy', $user) . '`,`' . csrf_token() . '`,`user-table`)" class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Excluir</button></li>';
        }

        if ($items === '') {
            $items = '<li><span class="dropdown-item text-muted">Sem ações</span></li>';
        }

        return '<div class="dropdown prime-action-menu">'
            . '<button class="prime-kebab" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Ações do colaborador"><i class="ri-more-2-fill"></i></button>'
            . '<ul class="dropdown-menu dropdown-menu-end">'
            . $items
            . '</ul></div>';
    }
}
