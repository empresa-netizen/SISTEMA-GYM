<?php

namespace App\Http\Controllers;

use App\Models\CardioPlan;
use App\Models\ClientFeedback;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\LibraryWorkout;
use App\Models\Member;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Workout;
use App\Support\DashboardCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $parentId = parentId();

        $cached = Cache::remember(
            DashboardCache::webStatsKey((int) $parentId),
            DashboardCache::TTL_SECONDS,
            fn () => $this->buildCachedStats((int) $parentId, $now)
        );

        $stats = $cached['stats'];
        $dailyTrend = collect($cached['daily_trend']);

        $recentClients = Member::latest()->take(5)->get();
        $recentPayments = InvoicePayment::whereHas('invoice', fn ($q) => $q->where('parent_id', $parentId))
            ->with('invoice.member')
            ->latest()
            ->take(5)
            ->get();

        $pendingInvoices = Invoice::where('parent_id', $parentId)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->with('member')
            ->orderBy('due_date')
            ->take(5)
            ->get();

        $birthdays = Member::where('status', 'active')
            ->whereNotNull('date_of_birth')
            ->get()
            ->filter(function (Member $member) use ($now) {
                $dob = $member->date_of_birth;
                if (! $dob) {
                    return false;
                }
                $next = $dob->copy()->year($now->year);
                if ($next->lt($now->copy()->startOfDay())) {
                    $next->addYear();
                }

                return $next->between($now->copy()->startOfDay(), $now->copy()->addDays(14));
            })
            ->sortBy(fn (Member $m) => $m->date_of_birth->format('m-d'))
            ->take(5)
            ->values();

        $expiringWorkouts = Workout::where('status', 'active')
            ->whereNotNull('workout_date')
            ->where('workout_date', '<=', now()->addDays(7))
            ->with('member')
            ->orderBy('workout_date')
            ->take(5)
            ->get();

        $upcomingEvents = Event::upcoming()
            ->with('member')
            ->take(50)
            ->get()
            ->groupBy(fn ($e) => $e->start_time->format('Y-m-d'));

        $upcomingDays = collect(range(0, 13))->map(function ($i) use ($now, $upcomingEvents) {
            $day = $now->copy()->addDays($i)->startOfDay();

            return [
                'date' => $day,
                'key' => $day->format('Y-m-d'),
                'count' => $upcomingEvents->get($day->format('Y-m-d'), collect())->count(),
            ];
        })->filter(fn ($d) => $d['count'] > 0 || $d['date']->isToday())->values();

        if ($upcomingDays->isEmpty()) {
            $upcomingDays = collect(range(0, 6))->map(function ($i) use ($now) {
                $day = $now->copy()->addDays($i)->startOfDay();

                return ['date' => $day, 'key' => $day->format('Y-m-d'), 'count' => 0];
            });
        }

        $pendingFeedbacksList = ClientFeedback::where('status', 'pending')
            ->with('member')
            ->latest()
            ->take(50)
            ->get();

        $recentConversations = Conversation::with('member')
            ->orderByDesc('last_message_at')
            ->take(4)
            ->get();

        $upcomingRenewals = Member::where('status', 'active')
            ->whereNotNull('membership_end_date')
            ->where('membership_end_date', '<=', now()->addDays(30))
            ->with('membershipPlan')
            ->orderBy('membership_end_date')
            ->take(5)
            ->get();

        return view('prime.dashboard', compact(
            'stats', 'recentClients', 'recentPayments', 'upcomingEvents', 'upcomingDays',
            'pendingFeedbacksList', 'recentConversations', 'upcomingRenewals', 'dailyTrend',
            'startOfMonth', 'endOfMonth', 'pendingInvoices', 'birthdays', 'expiringWorkouts'
        ));
    }

    /**
     * KPIs pesados do dashboard web — cacheados e invalidados via Observers.
     */
    private function buildCachedStats(int $parentId, Carbon $now): array
    {
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $startOfPrevWeek = $startOfWeek->copy()->subWeek();
        $endOfPrevWeek = $endOfWeek->copy()->subWeek();

        $payments = fn () => InvoicePayment::whereHas('invoice', fn ($q) => $q->where('parent_id', $parentId));

        $revenueToday = (float) $payments()->whereDate('payment_date', today())->sum('amount');
        $revenueYesterday = (float) $payments()->whereDate('payment_date', today()->subDay())->sum('amount');
        $revenueWeek = (float) $payments()->whereBetween('payment_date', [$startOfWeek, $endOfWeek])->sum('amount');
        $revenuePrevWeek = (float) $payments()->whereBetween('payment_date', [$startOfPrevWeek, $endOfPrevWeek])->sum('amount');
        $revenueMonth = (float) $payments()->where('payment_date', '>=', $startOfMonth)->sum('amount');
        $revenueYear = (float) $payments()->whereYear('payment_date', $now->year)->sum('amount');
        $transactionsMonth = $payments()->where('payment_date', '>=', $startOfMonth)->count();

        $prevMonthStart = $startOfMonth->copy()->subMonth();
        $prevMonthEnd = $startOfMonth->copy()->subDay();
        $revenuePrevMonth = (float) $payments()->whereBetween('payment_date', [$prevMonthStart, $prevMonthEnd])->sum('amount');

        $avg3Months = collect(range(1, 3))->map(function ($i) use ($payments, $startOfMonth) {
            $start = $startOfMonth->copy()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            return (float) $payments()->whereBetween('payment_date', [$start, $end])->sum('amount');
        })->avg() ?: 0;

        $activeClients = Member::where('status', 'active');
        $clientsMale = (clone $activeClients)->where('gender', 'male')->count();
        $clientsFemale = (clone $activeClients)->where('gender', 'female')->count();
        $clientsActive = $clientsMale + $clientsFemale + (clone $activeClients)->whereNotIn('gender', ['male', 'female'])->count();

        $monthPayments = $payments()->where('payment_date', '>=', $startOfMonth)->with('invoice.member')->get();
        $newSales = $monthPayments->filter(function ($payment) {
            $memberId = $payment->invoice?->member_id;
            if (! $memberId) {
                return true;
            }

            return InvoicePayment::whereHas('invoice', fn ($q) => $q->where('member_id', $memberId))
                ->where('payment_date', '<', $payment->payment_date)
                ->doesntExist();
        });
        $renewalSales = $monthPayments->reject(fn ($payment) => $newSales->contains('id', $payment->id));

        $uniqueMembersPaid = $monthPayments->pluck('invoice.member_id')->filter()->unique();
        $renewedCount = $renewalSales->count();
        $dueRenewalCount = max($uniqueMembersPaid->count(), 1);
        $renewalRate = $dueRenewalCount > 0 ? ($renewedCount / $dueRenewalCount) * 100 : 0;

        $totalMemberRevenue = (float) $payments()->sum('amount');
        $uniquePayingMembers = Invoice::where('parent_id', $parentId)->whereHas('payments')->distinct('member_id')->count('member_id');
        $ltvAvg = $uniquePayingMembers > 0 ? $totalMemberRevenue / $uniquePayingMembers : 0;

        $renewalExpectation = Member::where('status', 'active')
            ->whereNotNull('membership_end_date')
            ->whereBetween('membership_end_date', [now(), now()->addDays(30)])
            ->with('membershipPlan')
            ->get();
        $renewalExpectationAmount = $renewalExpectation->sum(fn ($m) => $m->membershipPlan?->price ?? 0);

        $dailyTrend = collect(range(0, 6))->map(function ($i) use ($payments, $now) {
            $day = $now->copy()->subDays(6 - $i)->startOfDay();
            $amount = (float) $payments()->whereDate('payment_date', $day)->sum('amount');

            return [
                'label' => $day->format('d/m'),
                'amount' => $amount,
            ];
        });
        $dailyTrendCumulative = 0;
        $dailyTrend = $dailyTrend->map(function ($row) use (&$dailyTrendCumulative) {
            $dailyTrendCumulative += $row['amount'];

            return array_merge($row, ['cumulative' => $dailyTrendCumulative]);
        })->values()->all();

        $overdueInvoices = Invoice::where('parent_id', $parentId)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        $openInvoices = Invoice::where('parent_id', $parentId)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->count();

        $activeCardioPlans = CardioPlan::where('parent_id', $parentId)->where('status', 'active')->count();
        $libraryTemplates = LibraryWorkout::where('parent_id', $parentId)->count();

        $stats = [
            'clients' => Member::count(),
            'clients_active' => $clientsActive,
            'clients_male' => $clientsMale,
            'clients_female' => $clientsFemale,
            'workouts' => Workout::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'revenue' => $totalMemberRevenue,
            'revenue_today' => $revenueToday,
            'revenue_yesterday' => $revenueYesterday,
            'revenue_week' => $revenueWeek,
            'revenue_prev_week' => $revenuePrevWeek,
            'revenue_month' => $revenueMonth,
            'revenue_prev_month' => $revenuePrevMonth,
            'revenue_year' => $revenueYear,
            'avg_3_months' => $avg3Months,
            'transactions_month' => $transactionsMonth,
            'open_tickets' => SupportTicket::where('parent_id', $parentId)->whereIn('status', ['open', 'in_progress'])->count(),
            'clients_without_workout' => Member::whereDoesntHave('workouts')->count(),
            'pending_feedbacks' => ClientFeedback::where('status', 'pending')->count(),
            'unread_messages' => Conversation::where('unread_by_coach', true)->count(),
            'pending_attendances' => Event::where('start_time', '>=', now())->where('status', 'scheduled')->count(),
            'active_attendances' => Event::where('start_time', '<=', now())->where('end_time', '>=', now())->whereIn('status', ['scheduled', 'ongoing'])->count(),
            'dropouts_30d' => Member::where('status', 'inactive')->where('updated_at', '>=', now()->subDays(30))->count(),
            'renewal_rate' => $renewalRate,
            'recurring_pct' => $revenueMonth > 0 ? ($renewalSales->sum('amount') / $revenueMonth) * 100 : 0,
            'chargeback_pct' => 0,
            'ltv_avg' => $ltvAvg,
            'new_sales_count' => $newSales->count(),
            'new_sales_total' => $newSales->sum('amount'),
            'renewal_sales_count' => $renewalSales->count(),
            'renewal_sales_total' => $renewalSales->sum('amount'),
            'renewal_expectation_amount' => $renewalExpectationAmount,
            'renewal_expectation_clients' => $renewalExpectation->count(),
            'overdue_invoices' => $overdueInvoices,
            'open_invoices' => $openInvoices,
            'active_cardio_plans' => $activeCardioPlans,
            'library_templates' => $libraryTemplates,
        ];

        $monthlyGoal = (float) (settings('monthly_goal', 10000) ?: 10000);
        $stats['monthly_goal'] = $monthlyGoal;
        $stats['goal_progress'] = $monthlyGoal > 0 ? min(100, ($stats['revenue_month'] / $monthlyGoal) * 100) : 0;
        $stats['goal_remaining'] = max(0, $monthlyGoal - $stats['revenue_month']);
        $stats['ticket_avg'] = $stats['transactions_month'] > 0
            ? $stats['revenue_month'] / $stats['transactions_month']
            : 0;

        $stats['delta_today'] = $revenueYesterday > 0
            ? (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100
            : ($revenueToday > 0 ? 100 : 0);
        $stats['delta_week'] = $revenuePrevWeek > 0
            ? (($revenueWeek - $revenuePrevWeek) / $revenuePrevWeek) * 100
            : ($revenueWeek > 0 ? 100 : 0);
        $stats['delta_month'] = $revenuePrevMonth > 0
            ? (($revenueMonth - $revenuePrevMonth) / $revenuePrevMonth) * 100
            : ($revenueMonth > 0 ? 100 : 0);

        return [
            'stats' => $stats,
            'daily_trend' => $dailyTrend,
        ];
    }
}
