<?php

use App\Models\DietFood;
use App\Models\DietMenu;
use Database\Seeders\MgteamModulesSeeder;

describe('MGTEAM seeded diet', function () {
    it('creates a mobile-ready diet menu with meals, foods and computed macros', function () {
        $owner = createOwner(['email' => 'seeded.diet.owner@test.app']);
        createMemberFor($owner, [
            'name' => 'Ana Seed',
            'email' => 'ana.seeded.diet@test.app',
        ]);

        $this->seed(MgteamModulesSeeder::class);

        $menu = DietMenu::query()
            ->where('parent_id', $owner->id)
            ->where('name', 'Cutting — Semana 1')
            ->with('meals.mealFoods.dietFood')
            ->first();

        expect($menu)->not->toBeNull()
            ->and($menu->meals)->toHaveCount(5)
            ->and($menu->meals->first()->name)->toBe('Café da manhã')
            ->and($menu->meals->first()->mealFoods)->toHaveCount(3)
            ->and($menu->meals->first()->mealFoods->first()->dietFood->name)->toBe('Ovos inteiros')
            ->and($menu->computedMacros()['calories'])->toBeGreaterThan(1600)
            ->and($menu->computedMacros()['protein'])->toBeGreaterThan(100);
    });

    it('creates diet menus with real meals, foods and tenant-scoped macro calculation', function () {
        $ownerA = createOwner(['email' => 'diet.menu.a@test.app']);
        $ownerB = createOwner(['email' => 'diet.menu.b@test.app']);

        $rice = DietFood::query()->create([
            'parent_id' => $ownerA->id,
            'name' => 'Arroz integral',
            'food_group' => 'Carboidrato',
            'calories' => 120,
            'protein' => 2.6,
            'carbs' => 25,
            'fat' => 1,
        ]);
        $otherTenantFood = DietFood::query()->create([
            'parent_id' => $ownerB->id,
            'name' => 'Alimento privado B',
            'calories' => 999,
        ]);

        $this->actingAs($ownerA)
            ->post(route('library.diet.menus.store'), [
                'name' => 'Bulking controlado',
                'status' => 'published',
                'meals' => [
                    [
                        'name' => 'Almoço',
                        'time_label' => '12:30',
                        'foods' => [
                            [
                                'diet_food_id' => $rice->id,
                                'quantity_in_grams' => 150,
                            ],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $menu = DietMenu::query()
            ->where('parent_id', $ownerA->id)
            ->where('name', 'Bulking controlado')
            ->with('meals.mealFoods.dietFood')
            ->firstOrFail();

        expect((int) $menu->meals_count)->toBe(1)
            ->and((float) $menu->total_calories)->toBe(180.0)
            ->and($menu->meals->first()->name)->toBe('Almoço')
            ->and($menu->meals->first()->mealFoods)->toHaveCount(1)
            ->and((float) $menu->meals->first()->mealFoods->first()->quantity_in_grams)->toBe(150.0);

        $this->actingAs($ownerA)
            ->get(route('library.diet.menus'))
            ->assertOk()
            ->assertSee('Bulking controlado')
            ->assertSee('Almoço')
            ->assertSee('Arroz integral');

        $this->actingAs($ownerA)
            ->post(route('library.diet.menus.store'), [
                'name' => 'Cardápio inválido',
                'status' => 'published',
                'meals' => [
                    [
                        'name' => 'Jantar',
                        'foods' => [
                            [
                                'diet_food_id' => $otherTenantFood->id,
                                'quantity_in_grams' => 100,
                            ],
                        ],
                    ],
                ],
            ])
            ->assertSessionHasErrors('meals.0.foods.0.diet_food_id');

        expect(DietMenu::query()->where('name', 'Cardápio inválido')->exists())->toBeFalse();
    });
});
