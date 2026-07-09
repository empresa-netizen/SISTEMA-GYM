<?php

namespace App\Traits;

/**
 * Provides common DataTable configuration for consistent styling and behavior.
 *
 * This trait contains shared functionality used across all DataTable classes:
 * - Export buttons (Copy, CSV, Excel, PDF, Print)
 * - Language/i18n configuration
 * - DOM layout structure
 * - Common parameters (pagination, processing, state saving, etc.)
 */
trait DataTableConfigTrait
{
    /**
     * Get the DOM layout configuration.
     */
    protected function getDataTableDom(): string
    {
        return '<"top-container"<"left-length"l><"center-buttons"B><"right-search"f>>rt<"bottom-container"<"left-info"i><"right-pagination"p>>';
    }

    /**
     * Get the export buttons configuration.
     */
    protected function getDataTableButtons(): array
    {
        return [
            [
                'extend' => 'copy',
                'className' => 'btn btn-sm btn-outline-primary me-1',
                'text' => '<i class="ri-file-copy-line me-1"></i>Copy',
            ],
            [
                'extend' => 'csv',
                'className' => 'btn btn-sm btn-outline-primary me-1',
                'text' => '<i class="ri-file-line me-1"></i>CSV',
            ],
            [
                'extend' => 'excel',
                'className' => 'btn btn-sm btn-outline-primary me-1',
                'text' => '<i class="ri-file-excel-line me-1"></i>Excel',
            ],
            [
                'extend' => 'pdf',
                'className' => 'btn btn-sm btn-outline-primary me-1',
                'text' => '<i class="ri-file-pdf-line me-1"></i>PDF',
            ],
            [
                'extend' => 'print',
                'className' => 'btn btn-sm btn-outline-primary',
                'text' => '<i class="ri-printer-line me-1"></i>Print',
            ],
        ];
    }

    /**
     * Get the language/i18n configuration.
     */
    protected function getDataTableLanguage(): array
    {
        return [
            'paginate' => [
                'previous' => '<i class="ri-arrow-left-line"></i>',
                'next' => '<i class="ri-arrow-right-line"></i>',
            ],
            'searchPlaceholder' => 'Buscar...',
            'lengthMenu' => 'Mostrar _MENU_ registros',
            'info' => 'Página _PAGE_ de _PAGES_',
            'infoEmpty' => 'Nenhum registro',
            'infoFiltered' => '(filtrado de _MAX_ no total)',
            'emptyTable' => 'Nenhum dado disponível',
            'zeroRecords' => 'Nenhum resultado encontrado',
            'processing' => 'Carregando...',
        ];
    }

    /**
     * Get column definitions for sorting behavior.
     *
     * @param  int  $actionColumnIndex  The index of the action column (typically last column)
     */
    protected function getDataTableColumnDefs(int $actionColumnIndex): array
    {
        // Build an array of sortable column indices (excluding 0 and action column)
        $sortableColumns = range(1, $actionColumnIndex - 1);

        return [
            [
                'targets' => [0, $actionColumnIndex],
                'orderable' => false,
            ],
            [
                'targets' => $sortableColumns,
                'orderable' => true,
            ],
        ];
    }

    /**
     * Get the common DataTable parameters.
     *
     * @param  int  $actionColumnIndex  The index of the action column (typically last column)
     */
    protected function getDataTableParameters(int $actionColumnIndex): array
    {
        return [
            'dom' => $this->getDataTableDom(),
            'buttons' => $this->getDataTableButtons(),
            'language' => $this->getDataTableLanguage(),
            'columnDefs' => $this->getDataTableColumnDefs($actionColumnIndex),
            'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
            'pageLength' => 10,
            'processing' => true,
            'autoWidth' => false,
            'serverSide' => true,
            'responsive' => true,
            'stateSave' => true,
            'initComplete' => 'function(settings, json) {
                // Initialize tooltips
                $("[data-bs-toggle=\'tooltip\']").tooltip();
            }',
            'drawCallback' => 'function() {
                $("[data-bs-toggle=\'tooltip\']").tooltip();
            }',
        ];
    }
}
