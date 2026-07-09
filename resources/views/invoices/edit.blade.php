@extends('layouts.master')

@section('title', 'Editar fatura')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">Editar fatura</h1>
        <p class="prime-page-sub">{{ $invoice->invoice_number }}</p>
    </div>
    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line me-1"></i> Voltar</a>
</div>

<div class="prime-panel">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('invoices.update', $invoice->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label for="member_id" class="form-label">Cliente</label>
                            <select class="form-select" id="member_id" name="member_id" required>
                                <option value="">Selecione o cliente</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ old('member_id', $invoice->member_id) == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} ({{ $member->member_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label for="invoice_date" class="form-label">Data da fatura</label>
                            <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-lg-4">
                            <label for="due_date" class="form-label">Vencimento</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Descrição</th>
                                            <th width="150">Qtd</th>
                                            <th width="150">Preço unit.</th>
                                            <th width="150">Total</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        @foreach($invoice->items as $index => $item)
                                        <tr>
                                            <td>
                                                <input type="text" name="items[{{ $index }}][description]" class="form-control" value="{{ $item->description }}" placeholder="Item description" required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" value="{{ $item->quantity }}" min="1" required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $index }}][unit_price]" class="form-control unit-price" value="{{ $item->unit_price }}" step="0.01" min="0" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control total-price" value="{{ number_format($item->total_price, 2, '.', '') }}" readonly>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-row">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5">
                                                <button type="button" class="btn btn-soft-secondary btn-sm" id="addItem">
                                                    <i class="ri-add-line align-middle me-1"></i> Adicionar item
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <label for="notes" class="form-label">Observações</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $invoice->notes) }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Subtotal:</th>
                                        <td class="text-end">R$ <span id="subtotal">{{ number_format($invoice->subtotal, 2, ',', '.') }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Impostos:</th>
                                        <td>
                                            <input type="number" name="tax_amount" id="tax_amount" class="form-control form-control-sm text-end" value="{{ old('tax_amount', $invoice->tax_amount) }}" step="0.01" min="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Desconto:</th>
                                        <td>
                                            <input type="number" name="discount_amount" id="discount_amount" class="form-control form-control-sm text-end" value="{{ old('discount_amount', $invoice->discount_amount) }}" step="0.01" min="0">
                                        </td>
                                    </tr>
                                    <tr class="border-top border-top-dashed">
                                        <th>Total:</th>
                                        <td class="text-end fw-bold fs-16">R$ <span id="totalAmount">{{ number_format($invoice->total_amount, 2, ',', '.') }}</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="hstack gap-2 justify-content-end">
                                <a href="{{ route('invoices.index') }}" class="btn btn-light">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Salvar fatura</button>
                            </div>
                        </div>
                    </div>
                </form>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = {{ $invoice->items->count() }};
    const itemsBody = document.getElementById('itemsBody');
    const addItemBtn = document.getElementById('addItem');
    
    // Add new row
    addItemBtn.addEventListener('click', function() {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="text" name="items[${itemIndex}][description]" class="form-control" placeholder="Item description" required>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" value="1" min="1" required>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price" value="0.00" step="0.01" min="0" required>
            </td>
            <td>
                <input type="text" class="form-control total-price" value="0.00" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        `;
        itemsBody.appendChild(row);
        itemIndex++;
        calculateTotals();
    });
    
    // Remove row
    itemsBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const row = e.target.closest('tr');
            if (itemsBody.children.length > 1) {
                row.remove();
                calculateTotals();
            }
        }
    });
    
    // Calculate row total and grand totals
    itemsBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('unit-price')) {
            const row = e.target.closest('tr');
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.unit-price').value) || 0;
            const total = qty * price;
            row.querySelector('.total-price').value = total.toFixed(2);
            calculateTotals();
        }
    });
    
    // Calculate grand totals on tax/discount change
    document.getElementById('tax_amount').addEventListener('input', calculateTotals);
    document.getElementById('discount_amount').addEventListener('input', calculateTotals);
    
    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.total-price').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });
        
        const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const total = subtotal + tax - discount;
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('totalAmount').textContent = total.toFixed(2);
    }
});
</script>
@endsection
