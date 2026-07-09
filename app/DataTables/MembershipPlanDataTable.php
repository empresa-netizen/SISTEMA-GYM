<?php

namespace App\DataTables;

use App\Models\MembershipPlan;
use App\Traits\DataTableConfigTrait;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MembershipPlanDataTable extends DataTable
{
    use DataTableConfigTrait;

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<MembershipPlan>  $query  Results from query() method.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('duration', function ($query) {
                $types = [
                    'daily' => 'dia(s)',
                    'weekly' => 'semana(s)',
                    'monthly' => 'mês(es)',
                    'quarterly' => 'trimestre(s)',
                    'half_yearly' => 'semestre(s)',
                    'yearly' => 'ano(s)',
                    'lifetime' => 'vitalício',
                ];
                $type = $types[$query->duration_type] ?? $query->duration_type;

                return $query->duration_type === 'lifetime'
                    ? 'Vitalício'
                    : $query->duration_value.' '.$type;
            })
            ->editColumn('price', function ($query) {
                return 'R$ '.number_format($query->price, 2, ',', '.');
            })
            ->addColumn('active', function ($query) {
                return $query->is_active
                    ? '<span class="badge bg-success">Ativo</span>'
                    : '<span class="badge bg-secondary">Inativo</span>';
            })
            ->addColumn('personal_training', function ($query) {
                return $query->personal_training
                    ? '<span class="badge bg-info">Sim</span>'
                    : '<span class="badge bg-secondary">Não</span>';
            })
            ->addColumn('action', function ($query) {
                return $query->action_buttons;
            })
            ->rawColumns(['duration', 'price', 'active', 'personal_training', 'action']);
    }

    public function query(MembershipPlan $model)
    {
        $parentId = parentId();

        return $model->newQuery()->where('parent_id', $parentId)->latest();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('membership-table')
            ->addTableClass('datatables-basic table table-striped')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->responsive(false)
            ->orderBy(1, 'asc')
            ->parameters($this->getDataTableParameters(6));
    }

    protected function getColumns()
    {
        return [
            Column::computed('DT_RowIndex', '#'),
            Column::make('name')->title('Nome'),
            Column::make('price')->title('Preço'),
            Column::make('duration')->title('Duração'),
            Column::make('active')->title('Status'),
            Column::make('personal_training')->title('Treino personalizado'),
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
        return 'MembershipPlan_'.date('YmdHis');
    }
}
