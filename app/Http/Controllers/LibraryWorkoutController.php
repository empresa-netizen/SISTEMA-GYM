<?php

namespace App\Http\Controllers;

use App\Models\LibraryWorkout;
use App\Models\Member;
use App\Models\Workout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LibraryWorkoutController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
        ]);

        $templates = LibraryWorkout::query()
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(function ($inner) use ($term) {
                $inner->where('title', 'like', "%{$term}%")
                    ->orWhere('focus', 'like', "%{$term}%");
            }))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['level'] ?? null, fn ($q, $level) => $q->where('level', $level))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $members = Member::where('parent_id', parentId())
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('prime.library.workout-templates', [
            'templates' => $templates,
            'members' => $members,
            'filters' => array_merge([
                'q' => '',
                'status' => '',
                'level' => '',
            ], $filters),
            'levelLabels' => [
                'beginner' => 'Iniciante',
                'intermediate' => 'Intermediário',
                'advanced' => 'Avançado',
            ],
            'statusLabels' => [
                'draft' => 'Rascunho',
                'published' => 'Publicado',
                'archived' => 'Arquivado',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'focus' => ['nullable', 'string', 'max:120'],
            'duration_weeks' => ['nullable', 'integer', 'min:1', 'max:52'],
            'sessions_per_week' => ['nullable', 'integer', 'min:1', 'max:14'],
            'level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        LibraryWorkout::create([
            'parent_id' => parentId(),
            'title' => $validated['title'],
            'focus' => $validated['focus'] ?? null,
            'duration_weeks' => $validated['duration_weeks'] ?? null,
            'sessions_per_week' => $validated['sessions_per_week'] ?? null,
            'level' => $validated['level'] ?? 'intermediate',
            'status' => $validated['status'] ?? 'draft',
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Template de treino criado na biblioteca.');
    }

    public function assign(Request $request, LibraryWorkout $template): RedirectResponse
    {
        abort_unless($template->parent_id === parentId(), 403);

        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
        ]);

        $member = Member::where('parent_id', parentId())->findOrFail($validated['member_id']);

        Workout::create([
            'parent_id' => parentId(),
            'member_id' => $member->id,
            'name' => $template->title,
            'description' => $template->description,
            'notes' => trim(($template->notes ? $template->notes."\n" : '').'Importado da biblioteca #'.$template->id),
            'workout_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        return back()->with('success', "Template atribuído a {$member->name}.");
    }
}
