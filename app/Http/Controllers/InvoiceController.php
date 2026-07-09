<?php

namespace App\Http\Controllers;

use App\DataTables\InvoiceDataTable;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Member;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(InvoiceDataTable $dataTable, Request $request)
    {
        $parentId = parentId();

        // For regular paginated view (if you still need it)
        $query = Invoice::where('parent_id', $parentId)
            ->with(['member', 'items']);

        // Search filter
        if ($request->has('search_value') && $request->search_value != '') {
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
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }

        $invoiceIds = (clone $query)->pluck('id');
        $totalAmount = (clone $query)->sum('total_amount');
        $totalPaid = InvoicePayment::whereIn('invoice_id', $invoiceIds)->sum('amount');
        $totalDue = max(0, $totalAmount - $totalPaid);

        $invoices = $query->latest('invoice_date')->paginate(20);

        // Pass filters to view for form persistence
        $filters = [
            'search' => $request->search,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ];

        return $dataTable->render('invoices.index', compact('invoices', 'totalAmount', 'totalPaid', 'totalDue', 'filters'));
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create(): View
    {
        $parentId = parentId();
        $members = Member::where('parent_id', $parentId)->active()->get();
        $plans = MembershipPlan::where('parent_id', $parentId)->active()->get();

        return view('invoices.create', compact('members', 'plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $invoice = Invoice::create([
                'parent_id' => parentId(),
                'member_id' => $validated['member_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'status' => 'unpaid',
                'paid_amount' => 0,
            ]);

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            $invoice->calculateTotals();

            // Send email to member
            $member = $invoice->member;
            sendNotificationEmail('invoice_create', $member->email, [
                'gym_name' => settings('app_name', 'FitHub'),
                'member_name' => $member->name,
                'invoice_number' => $invoice->invoice_number,
                'amount' => number_format($invoice->total_amount, 2),
                'due_date' => $invoice->due_date->format('M d, Y'),
            ]);

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Venda criada com sucesso. Fatura: '.$invoice->invoice_number);

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao criar venda: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice): View
    {
        // Check multi-tenant isolation
        if ($invoice->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $invoice->load(['member', 'items', 'payments']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit(Invoice $invoice): View
    {
        // Check multi-tenant isolation
        if ($invoice->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        // Prevent editing paid invoices
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot edit a paid invoice');
        }

        $parentId = parentId();
        $members = Member::where('parent_id', $parentId)->active()->get();
        $invoice->load('items');

        return view('invoices.edit', compact('invoice', 'members'));
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        // Check multi-tenant isolation
        if ($invoice->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        // Prevent editing paid invoices
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot edit a paid invoice');
        }

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $invoice->update([
                'member_id' => $validated['member_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete existing items and recreate
            $invoice->items()->delete();

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            $invoice->calculateTotals();
            $invoice->updateStatus(); // Re-check status based on new totals

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error updating invoice: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice)
    {
        // Check multi-tenant isolation
        if ($invoice->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        // Prevent deleting paid invoices
        if ($invoice->paid_amount > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete an invoice that has payments',
            ]);
            //            return back()->with('error', 'Cannot delete an invoice that has payments');
        }

        $invoice->items()->delete();
        $invoice->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data deleted successfully',
        ]);
    }

    /**
     * Add payment to invoice
     */
    public function addPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        // Check multi-tenant isolation
        if ($invoice->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,bank_transfer,cheque,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Check if amount exceeds remaining balance
        if ($validated['amount'] > $invoice->remaining_balance) {
            return back()->with('error', 'Payment amount cannot exceed remaining balance');
        }

        DB::beginTransaction();

        try {
            $invoice->payments()->create([
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $invoice->increment('paid_amount', $validated['amount']);
            $invoice->refresh();
            $invoice->updateStatus();

            DB::commit();

            return back()->with('success', 'Pagamento registrado com sucesso');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao registrar pagamento: '.$e->getMessage());
        }
    }
}
