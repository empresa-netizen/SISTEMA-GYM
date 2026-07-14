<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDietMenuRequest;
use App\Models\DietFood;
use App\Models\DietMenu;
use App\Models\LibraryCourse;
use App\Models\Member;
use App\Services\DietMenuBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LibraryDietController extends Controller
{
    public function index(): View
    {
        return view('prime.library.diet.index');
    }

    public function courses(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'product' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in(['recent', 'title', 'status'])],
        ]);

        $filters = array_merge([
            'q' => '',
            'status' => '',
            'product' => '',
            'sort' => 'recent',
        ], $filters);

        $query = LibraryCourse::query()
            ->when($filters['q'], fn ($q) => $q->where(function ($inner) use ($filters) {
                $inner->where('title', 'like', '%'.$filters['q'].'%')
                    ->orWhere('product', 'like', '%'.$filters['q'].'%');
            }))
            ->when($filters['status'], fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['product'], fn ($q) => $q->where('product', $filters['product']));

        $query = match ($filters['sort']) {
            'title' => $query->orderBy('title'),
            'status' => $query->orderBy('status')->orderByDesc('created_at'),
            default => $query->latest(),
        };

        $courses = $query->paginate(20)->withQueryString();
        $products = LibraryCourse::query()
            ->whereNotNull('product')
            ->where('product', '!=', '')
            ->distinct()
            ->orderBy('product')
            ->pluck('product');

        return view('prime.library.courses', [
            'courses' => $courses,
            'filters' => $filters,
            'products' => $products,
            'members' => Member::where('parent_id', parentId())->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'statusOptions' => [
                'draft' => 'Rascunho',
                'published' => 'Publicado',
                'archived' => 'Arquivado',
            ],
            'sortOptions' => [
                'recent' => 'Mais recentes',
                'title' => 'Título',
                'status' => 'Status',
            ],
        ]);
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'product' => ['nullable', 'string', 'max:255'],
            'modules_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'lessons_count' => ['nullable', 'integer', 'min:0', 'max:500'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'description' => ['nullable', 'string'],
        ]);

        LibraryCourse::create([
            'parent_id' => parentId(),
            'title' => $validated['title'],
            'product' => $validated['product'] ?? null,
            'modules_count' => $validated['modules_count'] ?? 0,
            'lessons_count' => $validated['lessons_count'] ?? 0,
            'status' => $validated['status'] ?? 'draft',
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Curso criado na biblioteca.');
    }

    public function foods(Request $request): View
    {
        $foods = DietFood::when($request->q, fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->group, fn ($q) => $q->where('food_group', $request->group))
            ->orderBy('name')
            ->paginate(25);

        $groups = DietFood::distinct()->pluck('food_group')->filter();

        return view('prime.library.diet.foods', compact('foods', 'groups'));
    }

    public function menus(Request $request): View
    {
        $menus = DietMenu::with('meals.mealFoods.dietFood')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);
        $foods = DietFood::query()
            ->orderBy('name')
            ->get(['id', 'name', 'food_group', 'calories', 'protein', 'carbs', 'fat']);

        return view('prime.library.diet.menus', compact('foods', 'menus'));
    }

    public function formulas(): View
    {
        return view('prime.library.diet.stub', [
            'title' => 'Minhas fórmulas',
            'subtitle' => 'Área reservada para fórmulas nutricionais locais.',
            'icon' => 'ri-flask-line',
        ]);
    }

    public function predefinedMeals(): View
    {
        return view('prime.library.diet.stub', [
            'title' => 'Refeições predefinidas',
            'subtitle' => 'Modelos prontos de refeições para acelerar prescrições.',
            'icon' => 'ri-bowl-line',
        ]);
    }

    public function storeFood(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'food_group' => 'nullable|string|max:100',
            'calories' => 'nullable|numeric|min:0',
            'protein' => 'nullable|numeric|min:0',
            'carbs' => 'nullable|numeric|min:0',
            'fat' => 'nullable|numeric|min:0',
        ]);

        DietFood::create(array_merge($validated, ['parent_id' => parentId()]));

        return back()->with('success', 'Alimento cadastrado.');
    }

    public function storeMenu(StoreDietMenuRequest $request, DietMenuBuilder $builder): RedirectResponse
    {
        $builder->createForTenant(parentId(), $request->validated());

        return back()->with('success', 'Cardápio criado.');
    }
}
