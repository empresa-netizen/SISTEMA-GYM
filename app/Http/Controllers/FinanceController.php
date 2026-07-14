<?php

namespace App\Http\Controllers;

use App\DataTables\InvoiceDataTable;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(InvoiceDataTable $dataTable, Request $request): View
    {
        $parentId = parentId();
        $tab = $request->get('tab', 'dashboard');

        $paymentsQuery = fn () => InvoicePayment::whereHas('invoice', fn ($q) => $q->where('parent_id', $parentId));

        $availableBalance = (float) $paymentsQuery()->sum('amount');
        $pendingBalance = (float) Invoice::where('parent_id', $parentId)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->sum(DB::raw('total_amount - paid_amount'));
        $monthRevenue = (float) $paymentsQuery()->where('payment_date', '>=', now()->startOfMonth())->sum('amount');
        $monthTransactions = $paymentsQuery()->where('payment_date', '>=', now()->startOfMonth())->count();

        $recentPayments = $paymentsQuery()
            ->with('invoice.member')
            ->latest()
            ->take(8)
            ->get();

        $recentInvoices = Invoice::where('parent_id', $parentId)
            ->with('member')
            ->latest()
            ->take(8)
            ->get();

        $stats = compact('availableBalance', 'pendingBalance', 'monthRevenue', 'monthTransactions', 'recentPayments', 'recentInvoices', 'tab');

        if ($tab === 'withdrawals') {
            return view('mgteam.finance.withdrawals', $stats);
        }

        if ($tab === 'reports') {
            $byMonth = $paymentsQuery()
                ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount) as total')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(6)
                ->get()
                ->reverse()
                ->values();

            return view('mgteam.finance.reports', array_merge($stats, compact('byMonth')));
        }

        if ($tab === 'transactions') {
            $query = Invoice::where('parent_id', $parentId)->with(['member', 'items']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('member', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            }
            if ($request->filled('status')) {
                if ($request->status === 'overdue') {
                    $query->whereIn('status', ['unpaid', 'partially_paid'])
                        ->whereDate('due_date', '<', now()->toDateString());
                } else {
                    $query->where('status', $request->status);
                }
            }
            if ($request->filled('start_date')) {
                $query->whereDate('invoice_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('invoice_date', '<=', $request->end_date);
            }

            $invoiceIds = (clone $query)->pluck('id');
            $totalAmount = (clone $query)->sum('total_amount');
            $totalPaid = InvoicePayment::whereIn('invoice_id', $invoiceIds)->sum('amount');
            $totalDue = max(0, $totalAmount - $totalPaid);

            return $dataTable->render('mgteam.finance.transactions', array_merge($stats, compact(
                'totalAmount', 'totalPaid', 'totalDue'
            )));
        }

        return view('mgteam.finance.index', $stats);
    }
}
