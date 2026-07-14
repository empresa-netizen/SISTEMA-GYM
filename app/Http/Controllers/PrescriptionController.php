<?php

namespace App\Http\Controllers;

use App\Models\DietPrescription;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'days' => $request->get('days', '30'),
            'type' => $request->get('type', ''),
            'date' => $request->get('date'),
            'sort' => $request->get('sort', 'expires_at'),
            'direction' => $request->get('direction', 'asc'),
        ];

        $days = in_array($filters['days'], ['7', '15', '30', '60', '90'], true)
            ? (int) $filters['days']
            : 30;

        $workoutsQuery = Workout::with('member')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $search = $filters['q'];

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($memberQuery) use ($search) {
                            $memberQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });

        $dietsQuery = DietPrescription::with(['member', 'dietMenu'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $search = $filters['q'];

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($memberQuery) use ($search) {
                            $memberQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });

        if ($filters['date']) {
            $workoutsQuery->whereDate('workout_date', $filters['date']);
            $dietsQuery->whereDate('scheduled_at', $filters['date']);
        } elseif ($filters['days'] !== 'all') {
            // Inclui atrasadas recentes + próximas N dias (senão dietas passadas somem da lista)
            $start = now()->subDays(7)->startOfDay();
            $end = now()->addDays($days)->endOfDay();

            $workoutsQuery->whereBetween('workout_date', [$start, $end]);
            $dietsQuery->whereBetween('scheduled_at', [$start, $end]);
        }

        $rows = collect();

        if ($filters['type'] !== 'diet') {
            $rows = $rows->concat($workoutsQuery->get()->map(fn ($workout) => [
                'expires_at' => $workout->workout_date,
                'name' => $workout->member?->name ?? 'Sem aluno',
                'email' => $workout->member?->email ?? '—',
                'title' => $workout->name,
                'type' => 'Treino',
                'status' => $workout->status,
                'chip' => 'prime-chip--info',
                'url' => route('workouts.show', $workout),
            ]));
        }

        if ($filters['type'] !== 'workout') {
            $rows = $rows->concat($dietsQuery->get()->map(fn ($diet) => [
                'expires_at' => $diet->scheduled_at,
                'name' => $diet->member?->name ?? 'Sem aluno',
                'email' => $diet->member?->email ?? '—',
                'title' => $diet->title,
                'type' => 'Dieta',
                'status' => $diet->delivery_status ?? $diet->status,
                'chip' => match ($diet->delivery_status) {
                    'DELIVERED' => 'prime-chip--success',
                    'LATE' => 'prime-chip--danger',
                    default => 'prime-chip--warn',
                },
                'url' => $diet->member ? route('members.show', [$diet->member, 'tab' => 'prescriptions']) : route('prescriptions.index'),
            ]));
        }

        $descending = $filters['direction'] === 'desc';
        $sortKey = match ($filters['sort']) {
            'name' => 'name',
            'type' => 'type',
            default => 'expires_at',
        };

        $rows = $rows
            ->sortBy(function ($row) use ($sortKey, $descending) {
                if ($row[$sortKey] instanceof \Carbon\CarbonInterface) {
                    return $row[$sortKey]->timestamp;
                }

                if ($sortKey === 'expires_at') {
                    return $descending ? PHP_INT_MIN : PHP_INT_MAX;
                }

                return $row[$sortKey] ?? '';
            }, SORT_REGULAR, $descending)
            ->values();

        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $prescriptions = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => route('prescriptions.index'), 'query' => $request->query()]
        );

        $stats = [
            'total' => $rows->count(),
            'workouts' => $rows->where('type', 'Treino')->count(),
            'diets' => $rows->where('type', 'Dieta')->count(),
            'late' => $rows->where('status', 'LATE')->count(),
        ];

        return view('prime.prescriptions.index', compact('prescriptions', 'filters', 'stats', 'days'));
    }
}
