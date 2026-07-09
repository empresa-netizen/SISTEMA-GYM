<?php

namespace App\Http\Controllers;

use App\Models\CommunityGroup;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Member;
use App\Models\MemberLogbook;
use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberHubController extends Controller
{
    public function renewals(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'period' => $request->get('period', '30'),
            'plan' => $request->get('plan'),
            'amount' => $request->get('amount'),
            'renewal_status' => $request->get('renewal_status', 'upcoming'),
            'sort' => $request->get('sort', 'membership_end_date'),
            'direction' => $request->get('direction', 'asc'),
            'recurring' => $request->boolean('recurring'),
            'history' => $request->boolean('history'),
        ];

        $periodDays = in_array($filters['period'], ['7', '15', '30', '60', '90'], true)
            ? (int) $filters['period']
            : 30;

        $renewedMemberIds = $this->renewedMemberIds($periodDays);
        $memberIdsWithPaymentHistory = Invoice::whereHas('payments')
            ->distinct()
            ->pluck('member_id');

        $membersQuery = Member::with('membershipPlan')
            ->where('status', 'active')
            ->withCount([
                'subscriptions as active_subscriptions_count' => fn ($query) => $query->where('status', 'active'),
                'subscriptions as subscriptions_count',
            ])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $search = $filters['q'];

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('member_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['plan'], fn ($query, $plan) => $query->where('membership_plan_id', $plan))
            ->when($filters['recurring'], fn ($query) => $query->whereHas('subscriptions', fn ($subscription) => $subscription->where('status', 'active')))
            ->when($filters['history'], fn ($query) => $query->whereIn('id', $memberIdsWithPaymentHistory));

        if ($filters['renewal_status'] === 'no_date') {
            $membersQuery->whereNull('membership_end_date');
        } else {
            $membersQuery->whereNotNull('membership_end_date');

            if ($filters['renewal_status'] === 'expired' || $filters['period'] === 'expired') {
                $membersQuery->whereDate('membership_end_date', '<', now()->toDateString());
            } elseif ($filters['renewal_status'] === 'urgent') {
                $membersQuery->whereBetween('membership_end_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()]);
            } elseif ($filters['renewal_status'] === 'renewed') {
                $membersQuery->whereIn('id', $renewedMemberIds);
            } elseif ($filters['period'] !== 'all') {
                $membersQuery->whereBetween('membership_end_date', [now()->startOfDay(), now()->addDays($periodDays)->endOfDay()]);
            }
        }

        match ($filters['amount']) {
            'with_value' => $membersQuery->whereHas('membershipPlan', fn ($query) => $query->where('price', '>', 0)),
            'no_value' => $membersQuery->where(function ($query) {
                $query->whereDoesntHave('membershipPlan')
                    ->orWhereHas('membershipPlan', fn ($plan) => $plan->where('price', '<=', 0));
            }),
            'under_100' => $membersQuery->whereHas('membershipPlan', fn ($query) => $query->where('price', '<', 100)),
            '100_300' => $membersQuery->whereHas('membershipPlan', fn ($query) => $query->whereBetween('price', [100, 300])),
            'over_300' => $membersQuery->whereHas('membershipPlan', fn ($query) => $query->where('price', '>', 300)),
            default => null,
        };

        $direction = $filters['direction'] === 'desc' ? 'desc' : 'asc';

        match ($filters['sort']) {
            'name' => $membersQuery->orderBy('name', $direction),
            'plan' => $membersQuery->orderBy(
                MembershipPlan::select('name')->whereColumn('membership_plans.id', 'members.membership_plan_id'),
                $direction
            )->orderBy('name'),
            'value' => $membersQuery->orderBy(
                MembershipPlan::select('price')->whereColumn('membership_plans.id', 'members.membership_plan_id'),
                $direction
            )->orderBy('name'),
            default => $membersQuery->orderByRaw('CASE WHEN membership_end_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('membership_end_date', $direction)
                ->orderBy('name'),
        };

        $kpiMembers = (clone $membersQuery)->get();
        $members = $membersQuery->paginate(25)->withQueryString();
        $plans = MembershipPlan::active()->orderBy('name')->get();

        $renewedCount = $kpiMembers->whereIn('id', $renewedMemberIds)->count();
        $stats = [
            'total_clients' => $kpiMembers->count(),
            'potential_revenue' => $kpiMembers->sum(fn ($member) => (float) ($member->membershipPlan?->price ?? 0)),
            'renewed_count' => $renewedCount,
            'renewal_rate' => $kpiMembers->count() > 0 ? ($renewedCount / $kpiMembers->count()) * 100 : 0,
            'urgent_count' => $kpiMembers->filter(fn ($member) => $member->membership_end_date && $member->membership_end_date->between(now()->startOfDay(), now()->addDays(7)->endOfDay()))->count(),
            'expired_count' => $kpiMembers->filter(fn ($member) => $member->membership_end_date && $member->membership_end_date->isPast())->count(),
        ];

        return view('prime.members.renewals', compact('members', 'plans', 'filters', 'stats', 'renewedMemberIds', 'periodDays'));
    }

    private function renewedMemberIds(int $periodDays): \Illuminate\Support\Collection
    {
        return InvoicePayment::query()
            ->whereBetween('payment_date', [now()->subDays($periodDays)->toDateString(), now()->toDateString()])
            ->whereHas('invoice.member')
            ->with('invoice:id,member_id')
            ->get()
            ->pluck('invoice.member_id')
            ->filter()
            ->unique()
            ->values();
    }

    public function pending(): View
    {
        $members = Member::where('status', 'active')
            ->whereDoesntHave('workouts')
            ->with('membershipPlan')
            ->latest()
            ->paginate(20);

        return view('prime.members.pending', compact('members'));
    }

    public function all(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'status' => $request->get('status'),
            'plan' => $request->get('plan'),
            'renewal' => $request->get('renewal'),
            'app_installed' => $request->get('app_installed'),
            'automatic_billing' => $request->get('automatic_billing'),
            'sort' => $request->get('sort', 'name_asc'),
            'birthday' => $request->get('birthday'),
        ];

        $membersQuery = Member::with('membershipPlan')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $search = $filters['q'];

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('member_id', 'like', "%{$search}%");
                });
            })
            ->when(in_array($filters['status'], ['active', 'inactive', 'expired', 'suspended'], true), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when($filters['plan'], function ($query, $plan) {
                $query->where('membership_plan_id', $plan);
            })
            ->when($filters['renewal'] === 'expired', function ($query) {
                $query->whereNotNull('membership_end_date')
                    ->where('membership_end_date', '<', now());
            })
            ->when(in_array($filters['renewal'], ['7', '15', '30'], true), function ($query) use ($filters) {
                $query->whereNotNull('membership_end_date')
                    ->whereBetween('membership_end_date', [now()->startOfDay(), now()->addDays((int) $filters['renewal'])->endOfDay()]);
            })
            ->when($filters['renewal'] === 'no_date', function ($query) {
                $query->whereNull('membership_end_date');
            })
            ->when($filters['birthday'] === 'month', function ($query) {
                $query->whereNotNull('date_of_birth')
                    ->whereMonth('date_of_birth', now()->month);
            });

        match ($filters['sort']) {
            'created_desc' => $membersQuery->latest(),
            'renewal_asc' => $membersQuery->orderByRaw('CASE WHEN membership_end_date IS NULL THEN 1 ELSE 0 END')->orderBy('membership_end_date')->orderBy('name'),
            'renewal_desc' => $membersQuery->orderByDesc('membership_end_date')->orderBy('name'),
            default => $membersQuery->orderBy('name'),
        };

        $members = $membersQuery->paginate(30)->withQueryString();
        $plans = MembershipPlan::orderBy('name')->get();

        return view('prime.members.all', compact('members', 'plans', 'filters'));
    }

    public function groups(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'members_count' => $request->get('members_count'),
            'plan' => $request->get('plan'),
            'gender' => $request->get('gender'),
            'sync' => $request->get('sync'),
        ];

        $groupsQuery = CommunityGroup::withCount('posts')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $search = $filters['q'];

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        match ($filters['members_count']) {
            'empty' => $groupsQuery->where('members_count', 0),
            '1_10' => $groupsQuery->whereBetween('members_count', [1, 10]),
            '11_50' => $groupsQuery->whereBetween('members_count', [11, 50]),
            '51_plus' => $groupsQuery->where('members_count', '>=', 51),
            default => null,
        };

        $groups = $groupsQuery
            ->orderByDesc('members_count')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $plans = MembershipPlan::orderBy('name')->get();
        $stats = [
            'total_groups' => CommunityGroup::count(),
            'total_members' => CommunityGroup::sum('members_count'),
        ];

        return view('prime.members.groups', compact('groups', 'plans', 'filters', 'stats'));
    }

    public function engagement(Request $request): View|StreamedResponse
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'scope' => in_array($request->get('scope'), ['season', 'history'], true) ? $request->get('scope') : 'season',
        ];
        $seasonStart = now()->startOfMonth();

        $membersQuery = Member::where('status', 'active')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $search = $filters['q'];

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('member_id', 'like', "%{$search}%");
                });
            })
            ->withCount([
                'workouts as completed_workouts_count' => function ($query) use ($filters, $seasonStart) {
                    $query->where('status', 'completed');

                    if ($filters['scope'] === 'season') {
                        $query->where(function ($query) use ($seasonStart) {
                            $query->whereDate('workout_date', '>=', $seasonStart)
                                ->orWhere(function ($query) use ($seasonStart) {
                                    $query->whereNull('workout_date')
                                        ->where('created_at', '>=', $seasonStart);
                                });
                        });
                    }
                },
                'workouts as completed_activities_count' => function ($query) use ($filters, $seasonStart) {
                    $query->join('workout_activities', 'workouts.id', '=', 'workout_activities.workout_id')
                        ->where('workout_activities.is_completed', true);

                    if ($filters['scope'] === 'season') {
                        $query->where(function ($query) use ($seasonStart) {
                            $query->whereDate('workouts.workout_date', '>=', $seasonStart)
                                ->orWhere(function ($query) use ($seasonStart) {
                                    $query->whereNull('workouts.workout_date')
                                        ->where('workouts.created_at', '>=', $seasonStart);
                                });
                        });
                    }
                },
                'feedbacks as feedbacks_count' => function ($query) use ($filters, $seasonStart) {
                    if ($filters['scope'] === 'season') {
                        $query->where('created_at', '>=', $seasonStart);
                    }
                },
                'logbooks as logbooks_count' => function ($query) use ($filters, $seasonStart) {
                    if ($filters['scope'] === 'season') {
                        $query->whereDate('logged_at', '>=', $seasonStart);
                    }
                },
                'photos as photos_count' => function ($query) use ($filters, $seasonStart) {
                    if ($filters['scope'] === 'season') {
                        $query->where('created_at', '>=', $seasonStart);
                    }
                },
            ]);

        $rankedMembers = $membersQuery->get()
            ->map(function (Member $member) {
                // XP formula: completed workouts x50 + completed exercise check-ins x10 + logbooks x20 + feedbacks x25 + progress photos x15.
                $member->engagement_xp = ($member->completed_workouts_count * 50)
                    + ($member->completed_activities_count * 10)
                    + ($member->logbooks_count * 20)
                    + ($member->feedbacks_count * 25)
                    + ($member->photos_count * 15);

                return $member;
            })
            ->sort(function (Member $a, Member $b) {
                return [
                    $b->engagement_xp,
                    $b->completed_workouts_count,
                    $b->completed_activities_count,
                    $b->logbooks_count,
                    mb_strtolower($a->name),
                ] <=> [
                    $a->engagement_xp,
                    $a->completed_workouts_count,
                    $a->completed_activities_count,
                    $a->logbooks_count,
                    mb_strtolower($b->name),
                ];
            })
            ->values()
            ->each(function (Member $member, int $index) {
                $member->engagement_rank = $index + 1;
            });

        if ($request->boolean('export')) {
            return response()->streamDownload(function () use ($rankedMembers, $filters) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['rank', 'nome', 'email', 'telefone', 'xp', 'treinos_concluidos', 'exercicios_concluidos', 'diarios', 'feedbacks', 'fotos', 'escopo']);

                foreach ($rankedMembers as $member) {
                    fputcsv($handle, [
                        $member->engagement_rank,
                        $member->name,
                        $member->email,
                        $member->phone,
                        $member->engagement_xp,
                        $member->completed_workouts_count,
                        $member->completed_activities_count,
                        $member->logbooks_count,
                        $member->feedbacks_count,
                        $member->photos_count,
                        $filters['scope'],
                    ]);
                }

                fclose($handle);
            }, 'ranking-engajamento-'.$filters['scope'].'-'.now()->format('Y-m-d').'.csv');
        }

        $perPage = 20;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $members = new LengthAwarePaginator(
            $rankedMembers->forPage($page, $perPage)->values(),
            $rankedMembers->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $seasonLabel = $seasonStart->format('d/m/Y').' a '.now()->format('d/m/Y');

        return view('prime.members.engagement', compact('members', 'filters', 'seasonLabel'));
    }

    public function dropouts(Request $request): View|StreamedResponse
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'plan' => $request->get('plan'),
            'period' => in_array($request->get('period', '30'), ['7', '30', '60', '90', 'all'], true) ? $request->get('period', '30') : '30',
            'show_refunded' => $request->boolean('show_refunded'),
        ];

        $membersQuery = $this->dropoutMembersQuery($filters);

        if ($request->boolean('export')) {
            return response()->streamDownload(function () use ($membersQuery) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['nome', 'email', 'whatsapp', 'plano', 'status', 'vencimento', 'valor_plano', 'reembolsado']);

                (clone $membersQuery)->get()->each(function (Member $member) use ($handle) {
                    fputcsv($handle, [
                        $member->name,
                        $member->email,
                        $member->phone,
                        $member->membershipPlan?->name ?? 'Sem plano',
                        $member->status,
                        $member->membership_end_date?->format('Y-m-d'),
                        (float) ($member->membershipPlan?->price ?? 0),
                        ($member->refunded_payment_transactions_count ?? 0) > 0 ? 'sim' : 'nao',
                    ]);
                });

                fclose($handle);
            }, 'desistencias-'.now()->format('Y-m-d').'.csv');
        }

        $statsMembers = (clone $membersQuery)->get();
        $stats = [
            'total_clients' => $statsMembers->count(),
            'expired_count' => $statsMembers->filter(fn (Member $member) => $member->membership_end_date?->isPast())->count(),
            'suspended_count' => $statsMembers->where('status', 'suspended')->count(),
            'refunded_count' => $statsMembers->filter(fn (Member $member) => ($member->refunded_payment_transactions_count ?? 0) > 0)->count(),
            'potential_revenue' => $statsMembers->sum(fn (Member $member) => (float) ($member->membershipPlan?->price ?? 0)),
        ];

        $members = $membersQuery->paginate(25)->withQueryString();
        $plans = MembershipPlan::orderBy('name')->get();

        return view('prime.members.dropouts', compact('members', 'plans', 'filters', 'stats'));
    }

    private function dropoutMembersQuery(array $filters): Builder
    {
        $membersQuery = Member::with('membershipPlan')
            ->withCount([
                'paymentTransactions as refunded_payment_transactions_count' => fn ($query) => $query->where('status', 'refunded'),
            ])
            ->where(function ($query) {
                $query->whereIn('status', ['inactive', 'expired', 'suspended'])
                    ->orWhere(function ($query) {
                        $query->whereNotNull('membership_end_date')
                            ->whereDate('membership_end_date', '<', now()->toDateString());
                    });
            })
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $search = $filters['q'];

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('member_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['plan'], fn ($query, $plan) => $query->where('membership_plan_id', $plan))
            ->when(! $filters['show_refunded'], function ($query) {
                $query->whereDoesntHave('paymentTransactions', fn ($payment) => $payment->where('status', 'refunded'));
            });

        if ($filters['period'] !== 'all') {
            $cutoff = now()->subDays((int) $filters['period'])->startOfDay();

            $membersQuery->where(function ($query) use ($cutoff) {
                $query->where(function ($query) use ($cutoff) {
                    $query->whereNotNull('membership_end_date')
                        ->whereBetween('membership_end_date', [$cutoff->toDateString(), now()->toDateString()]);
                })->orWhere(function ($query) use ($cutoff) {
                    $query->whereIn('status', ['inactive', 'expired', 'suspended'])
                        ->where('updated_at', '>=', $cutoff);
                });
            });
        }

        return $membersQuery
            ->orderByRaw('CASE WHEN membership_end_date IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('membership_end_date')
            ->orderByDesc('updated_at')
            ->orderBy('name');
    }

    public function attendances(): View
    {
        $pending = Event::where('start_time', '>=', now())
            ->where('status', 'scheduled')
            ->with('member')
            ->orderBy('start_time')
            ->get();

        $active = Event::where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->with('member')
            ->orderBy('start_time')
            ->get();

        return view('prime.members.attendances', compact('pending', 'active'));
    }

    public function logbook(Request $request): View
    {
        $type = $request->get('type', 'TRAINING');
        $allowedTypes = ['TRAINING', 'DIET', 'CARDIO'];
        $activeType = in_array($type, $allowedTypes, true) ? $type : 'TRAINING';

        $entriesQuery = MemberLogbook::with(['member.feedbacks'])
            ->where('type', $activeType)
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim();

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($memberQuery) use ($search) {
                            $memberQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('date'), fn ($query) => $query->whereDate('logged_at', $request->date('date')))
            ->when($request->get('has_feedback') === 'yes', fn ($query) => $query->whereHas('member.feedbacks'))
            ->when($request->get('has_feedback') === 'no', fn ($query) => $query->whereDoesntHave('member.feedbacks'))
            ->orderByDesc('logged_at')
            ->orderByDesc('created_at');

        $entries = $entriesQuery->paginate(25)->withQueryString();

        $counts = [
            'TRAINING' => MemberLogbook::where('type', 'TRAINING')->count(),
            'DIET' => MemberLogbook::where('type', 'DIET')->count(),
            'CARDIO' => MemberLogbook::where('type', 'CARDIO')->count(),
        ];

        return view('prime.members.logbook', compact('entries', 'counts', 'activeType'));
    }

    public function destroyLogbook(MemberLogbook $entry): RedirectResponse
    {
        abort_unless($entry->parent_id === parentId(), 403);

        $entry->delete();

        return back()->with('success', 'Registro removido do diário.');
    }
}
