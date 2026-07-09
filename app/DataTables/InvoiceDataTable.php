<?php

namespace App\DataTables;

use App\Models\Invoice;
use App\Traits\DataTableConfigTrait;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InvoiceDataTable extends DataTable
{
    use DataTableConfigTrait;

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Invoice>  $query  Results from query() method.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('invoice', function ($query) {
                return '<a href="'.route('invoices.show', $query->id).'" class="fw-medium text-primary">'.$query->invoice_number.'</a>';
            })
            ->addColumn('member', function ($invoice) {
                if ($invoice->member) {
                    $html = '<div class="d-flex align-items-center">';

                    // Avatar
                    if ($invoice->member->avatar) {
                        $html .= '<div class="flex-shrink-0 me-2">';
                        $html .= '<img src="'.URL::asset('images/'.$invoice->member->avatar).'" alt="" class="avatar-xs rounded-circle">';
                        $html .= '</div>';
                    } else {
                        $html .= '<div class="avatar-xs me-2">';
                        $html .= '<span class="avatar-title rounded-circle bg-soft-primary text-primary">';
                        $html .= substr($invoice->member->name, 0, 1);
                        $html .= '</span>';
                        $html .= '</div>';
                    }

                    // Name
                    $html .= '<div class="flex-grow-1">';
                    $html .= e($invoice->member->name); // Use e() to escape HTML
                    $html .= '</div>';

                    $html .= '</div>';

                    return $html;
                } else {
                    return '<span class="text-muted">Unknown Member</span>';
                }
            })
            ->addColumn('invoice_date', function ($query) {
                return $query->invoice_date->format('d/m/Y');
            })->addColumn('due_date', function ($invoice) {
                $textClass = $invoice->isOverdue() ? 'text-danger fw-bold' : '';

                return '<span class="'.$textClass.'">'.$invoice->due_date->format('d/m/Y').'</span>';
            })->addColumn('total_amount', function ($query) {
                return 'R$ '.number_format($query->total_amount, 2, ',', '.');
            })->addColumn('paid_amount', function ($query) {
                return 'R$ '.number_format($query->paid_amount, 2, ',', '.');
            })->addColumn('status', function ($query) {
                if (method_exists($query, 'isOverdue') && $query->isOverdue() && in_array($query->status, ['unpaid', 'partially_paid'], true)) {
                    return '<span class="badge bg-danger">Atrasado</span>';
                }

                $labels = [
                    'paid' => ['Pago', 'bg-success'],
                    'partially_paid' => ['Pendente', 'bg-warning text-dark'],
                    'unpaid' => ['Em aberto', 'bg-danger'],
                    'cancelled' => ['Cancelado', 'bg-secondary'],
                ];
                [$label, $class] = $labels[$query->status] ?? [ucfirst($query->status), 'bg-secondary'];

                return '<span class="badge '.$class.'">'.$label.'</span>';
            })
            ->addColumn('action', function ($query) {
                return $query->action_buttons;
            })
            ->rawColumns(['invoice', 'member', 'invoice_date', 'due_date', 'total_amount', 'paid_amount', 'status', 'action']);
    }

    public function query(Invoice $model)
    {
        $parentId = parentId();

        $request = $this->request;

        $query = $model->newQuery()->where('parent_id', $parentId)
            ->with(['member', 'items']);

        // Search filter
        if ($request->has('search_value')) {
            $search = $request->search_value;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'overdue') {
                $query->whereIn('status', ['unpaid', 'partially_paid'])
                    ->whereDate('due_date', '<', now()->toDateString());
            } else {
                $query->where('status', $request->status);
            }
        }

        // Date range filter
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }
        $query = $query->latest('invoice_date');

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('invoice-table')
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
            Column::make('invoice')->title('Fatura'),
            Column::make('member')->title('Cliente'),
            Column::make('invoice_date')->title('Data'),
            Column::make('due_date')->title('Vencimento'),
            Column::make('status')->title('Status'),
            Column::make('total_amount')->title('Valor'),
            Column::make('paid_amount')->title('Pago'),
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
        return 'Invoice_'.date('YmdHis');
    }
}
