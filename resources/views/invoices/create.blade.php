@extends('layouts.master')

@section('title', 'Nova venda')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Nova venda</h1>
        <p class="mg-page-sub">Registre uma fatura para um cliente.</p>
    </div>
    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<div class="mg-panel">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
            @csrf
            <div class="row g-3">
                <div class="col-lg-4">
                    <label for="member_id" class="form-label">Cliente</label>
                    <select class="form-select" id="member_id" name="member_id" required>
                        <option value="">Selecione o cliente</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4">
                    <label for="plan_id" class="form-label">Plano (opcional)</label>
                    <select class="form-select" id="plan_id">
                        <option value="">Preencher manualmente</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" data-name="{{ $plan->name }}" data-price="{{ $plan->price }}">
                                {{ $plan->name }} — R$ {{ number_format($plan->price, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label for="invoice_date" class="form-label">Data da venda</label>
                    <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-lg-2">
                    <label for="due_date" class="form-label">Vencimento</label>
                    <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                </div>

                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th width="120">Qtd</th>
                                    <th width="150">Valor unit.</th>
                                    <th width="150">Total</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr>
                                    <td>
                                        <input type="text" name="items[0][description]" class="form-control item-description" placeholder="Ex: Consultoria Online" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control quantity" value="1" min="1" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][unit_price]" class="form-control unit-price" value="0.00" step="0.01" min="0" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control total-price" value="0,00" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="addItem">
                                            <i class="ri-add-line me-1"></i> Adicionar item
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6">
                    <label for="notes" class="form-label">Observações</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="PIX, condições, etc.">{{ old('notes') }}</textarea>
                </div>

                <div class="col-lg-6">
                    <table class="table table-borderless">
                        <tr>
                            <th>Subtotal:</th>
                            <td class="text-end">R$ <span id="subtotal">0,00</span></td>
                        </tr>
                        <tr>
                            <th>Impostos:</th>
                            <td>
                                <input type="number" name="tax_amount" id="tax_amount" class="form-control form-control-sm text-end" value="{{ old('tax_amount', 0) }}" step="0.01" min="0">
                            </td>
                        </tr>
                        <tr>
                            <th>Desconto:</th>
                            <td>
                                <input type="number" name="discount_amount" id="discount_amount" class="form-control form-control-sm text-end" value="{{ old('discount_amount', 0) }}" step="0.01" min="0">
                            </td>
                        </tr>
                        <tr class="border-top">
                            <th class="fs-5">Total:</th>
                            <td class="text-end fw-bold fs-5">R$ <span id="totalAmount">0,00</span></td>
                        </tr>
                    </table>
                </div>

                <div class="col-lg-12">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('invoices.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-check-line me-1"></i> Criar venda
                        </button>
                    </div>
                </div>
            </div>
        </form>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 1;
    const itemsBody = document.getElementById('itemsBody');
    const planSelect = document.getElementById('plan_id');

    function formatBRL(value) {
        return value.toFixed(2).replace('.', ',');
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.total-price').forEach(input => {
            subtotal += parseFloat(input.dataset.value || 0);
        });
        const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const total = subtotal + tax - discount;
        document.getElementById('subtotal').textContent = formatBRL(subtotal);
        document.getElementById('totalAmount').textContent = formatBRL(total);
    }

    function updateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity').value) || 0;
        const price = parseFloat(row.querySelector('.unit-price').value) || 0;
        const total = qty * price;
        const totalInput = row.querySelector('.total-price');
        totalInput.dataset.value = total;
        totalInput.value = formatBRL(total);
        calculateTotals();
    }

    planSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (!option.value) return;
        const row = itemsBody.querySelector('tr');
        row.querySelector('.item-description').value = option.dataset.name;
        row.querySelector('.unit-price').value = parseFloat(option.dataset.price).toFixed(2);
        row.querySelector('.quantity').value = 1;
        updateRowTotal(row);
    });

    document.getElementById('addItem').addEventListener('click', function() {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="items[${itemIndex}][description]" class="form-control" required></td>
            <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" value="1" min="1" required></td>
            <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price" value="0.00" step="0.01" min="0" required></td>
            <td><input type="text" class="form-control total-price" value="0,00" readonly data-value="0"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="ri-delete-bin-line"></i></button></td>
        `;
        itemsBody.appendChild(row);
        itemIndex++;
    });

    itemsBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row') && itemsBody.children.length > 1) {
            e.target.closest('tr').remove();
            calculateTotals();
        }
    });

    itemsBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('unit-price')) {
            updateRowTotal(e.target.closest('tr'));
        }
    });

    document.getElementById('tax_amount').addEventListener('input', calculateTotals);
    document.getElementById('discount_amount').addEventListener('input', calculateTotals);

    updateRowTotal(itemsBody.querySelector('tr'));
});
</script>
@endsection
