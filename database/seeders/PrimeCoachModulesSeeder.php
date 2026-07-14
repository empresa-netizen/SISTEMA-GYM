<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\ClientFeedback;
use App\Models\CoachFeedItem;
use App\Models\CommunityGroup;
use App\Models\CommunityPost;
use App\Models\Conversation;
use App\Models\Coupon;
use App\Models\DietFood;
use App\Models\DietMenu;
use App\Models\FeedComment;
use App\Models\FeedLike;
use App\Models\Member;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrimeCoachModulesSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();
        if (! $owner) {
            return;
        }

        $members = Member::where('parent_id', $owner->id)->get();
        if ($members->isEmpty()) {
            return;
        }

        if (Conversation::where('parent_id', $owner->id)->exists()) {
            $this->seedRemaining($owner, $members);

            return;
        }

        foreach ($members as $member) {
            $conv = Conversation::create([
                'parent_id' => $owner->id,
                'member_id' => $member->id,
                'last_message' => 'Oi coach! Como está meu treino?',
                'last_message_at' => now()->subHours(2),
                'unread_by_coach' => $member->id === $members->first()->id,
            ]);

            ChatMessage::create([
                'conversation_id' => $conv->id,
                'sender_type' => 'member',
                'content' => 'Oi coach! Como está meu treino?',
            ]);
            ChatMessage::create([
                'conversation_id' => $conv->id,
                'sender_type' => 'coach',
                'content' => 'Tudo certo, '.$member->name.'! Foco na execução esta semana.',
            ]);
        }

        $this->seedRemaining($owner, $members);

        $this->command->info('✅ Módulos coach-pro espelhados (chat, feed, comunidade, dieta, cupons)');
    }

    private function seedRemaining($owner, $members): void
    {
        if (! CoachFeedItem::where('parent_id', $owner->id)->exists()) {
            CoachFeedItem::create([
                'parent_id' => $owner->id,
                'author_id' => $owner->id,
                'member_id' => $members->first()->id,
                'type' => 'FEEDBACK',
                'title' => 'Novo feedback com foto',
                'description' => 'Ana enviou fotos de evolução.',
                'meta' => 'Pendente',
            ]);
            CoachFeedItem::create([
                'parent_id' => $owner->id,
                'author_id' => $owner->id,
                'member_id' => $members->last()->id,
                'type' => 'DELIVERY_LATE',
                'title' => 'Entrega de treino atrasada',
                'description' => 'Carlos aguarda atualização do plano.',
                'meta' => '2 dias',
            ]);
            $post = CoachFeedItem::create([
                'parent_id' => $owner->id,
                'author_id' => $owner->id,
                'type' => 'POST',
                'title' => 'Dica da semana',
                'description' => 'Hidrate-se bem nos treinos de alta intensidade.',
                'meta' => 'Publicado',
                'likes_count' => 1,
                'comments_count' => 1,
            ]);

            FeedLike::create([
                'parent_id' => $owner->id,
                'coach_feed_item_id' => $post->id,
                'user_id' => $owner->id,
            ]);
            FeedComment::create([
                'parent_id' => $owner->id,
                'coach_feed_item_id' => $post->id,
                'user_id' => $owner->id,
                'body' => 'Vamos reforçar isso com o time esta semana!',
            ]);

            CoachFeedItem::create([
                'parent_id' => $owner->id,
                'author_id' => $owner->id,
                'type' => 'NEWS',
                'title' => 'Bem-vindos à comunidade MGTEAM',
                'description' => 'Use o feed para avisos, conquistas e alinhamento da equipe.',
                'meta' => 'Notícia',
            ]);
        }

        if (! Product::where('parent_id', $owner->id)->exists()) {
            $category = ProductCategory::firstOrCreate(
                ['parent_id' => $owner->id, 'name' => 'Geral'],
                ['description' => 'Categoria padrão', 'active' => true]
            );

            Product::create([
                'parent_id' => $owner->id,
                'category_id' => $category->id,
                'name' => 'Consultoria Online Mensal',
                'type' => 'plan',
                'description' => 'Acompanhamento semanal com treino e dieta.',
                'price' => 197,
                'cost_price' => 0,
                'stock_quantity' => 0,
                'min_stock_level' => 0,
                'unit' => 'unit',
                'active' => true,
                'track_inventory' => false,
            ]);
            Product::create([
                'parent_id' => $owner->id,
                'category_id' => $category->id,
                'name' => 'Avaliação Física',
                'type' => 'service',
                'description' => 'Sessão presencial ou remota de avaliação.',
                'price' => 97,
                'cost_price' => 0,
                'stock_quantity' => 0,
                'min_stock_level' => 0,
                'unit' => 'unit',
                'active' => true,
                'track_inventory' => false,
            ]);
        }

        if (! CommunityGroup::where('parent_id', $owner->id)->exists()) {
            $group = CommunityGroup::create([
                'parent_id' => $owner->id,
                'name' => 'Comunidade Consultoria',
                'description' => 'Grupo oficial dos alunos',
                'members_count' => $members->count(),
            ]);

            CommunityPost::create([
                'parent_id' => $owner->id,
                'community_group_id' => $group->id,
                'member_id' => $members->first()->id,
                'content' => 'Primeira semana concluída! 💪',
                'likes_count' => 3,
            ]);
        }

        if (! ClientFeedback::where('parent_id', $owner->id)->exists()) {
            ClientFeedback::create([
                'parent_id' => $owner->id,
                'member_id' => $members->first()->id,
                'status' => 'pending',
                'message' => 'Semana 4 — evolução visível no abdômen.',
                'rating' => 5,
            ]);
        }

        foreach ([
            ['name' => 'Peito de frango grelhado', 'food_group' => 'Proteínas', 'calories' => 165, 'protein' => 31, 'carbs' => 0, 'fat' => 3.6],
            ['name' => 'Arroz integral cozido', 'food_group' => 'Carboidratos', 'calories' => 124, 'protein' => 2.6, 'carbs' => 26, 'fat' => 1],
            ['name' => 'Ovos inteiros', 'food_group' => 'Proteínas', 'calories' => 143, 'protein' => 13, 'carbs' => 1.1, 'fat' => 9.5],
            ['name' => 'Aveia em flocos', 'food_group' => 'Carboidratos', 'calories' => 389, 'protein' => 16.9, 'carbs' => 66.3, 'fat' => 6.9],
            ['name' => 'Banana prata', 'food_group' => 'Frutas', 'calories' => 89, 'protein' => 1.1, 'carbs' => 22.8, 'fat' => 0.3],
            ['name' => 'Batata doce cozida', 'food_group' => 'Carboidratos', 'calories' => 86, 'protein' => 1.6, 'carbs' => 20.1, 'fat' => 0.1],
            ['name' => 'Abacate', 'food_group' => 'Gorduras', 'calories' => 160, 'protein' => 2, 'carbs' => 9, 'fat' => 15],
        ] as $food) {
            DietFood::updateOrCreate(
                ['parent_id' => $owner->id, 'name' => $food['name']],
                array_merge($food, ['parent_id' => $owner->id, 'unit' => '100g'])
            );
        }

        $menu = DietMenu::updateOrCreate(
            ['parent_id' => $owner->id, 'name' => 'Cutting — Semana 1'],
            [
                'parent_id' => $owner->id,
                'name' => 'Cutting — Semana 1',
                'status' => 'published',
                'meals_count' => 5,
                'total_calories' => 1800,
                'description' => 'Cardápio hipocalórico para fase de definição.',
            ]
        );

        if ($menu->meals()->count() === 0) {
            $foods = DietFood::where('parent_id', $owner->id)->get()->keyBy('name');

            $meals = [
                [
                    'name' => 'Café da manhã',
                    'time_label' => '07:00',
                    'notes' => 'Café sem açúcar liberado.',
                    'foods' => [
                        ['name' => 'Ovos inteiros', 'quantity_in_grams' => 120],
                        ['name' => 'Aveia em flocos', 'quantity_in_grams' => 40],
                        ['name' => 'Banana prata', 'quantity_in_grams' => 80],
                    ],
                ],
                [
                    'name' => 'Almoço',
                    'time_label' => '12:30',
                    'notes' => 'Vegetais verdes à vontade.',
                    'foods' => [
                        ['name' => 'Peito de frango grelhado', 'quantity_in_grams' => 160],
                        ['name' => 'Arroz integral cozido', 'quantity_in_grams' => 130],
                        ['name' => 'Abacate', 'quantity_in_grams' => 50],
                    ],
                ],
                [
                    'name' => 'Pré-treino',
                    'time_label' => '16:30',
                    'notes' => 'Consumir 60–90 min antes do treino.',
                    'foods' => [
                        ['name' => 'Banana prata', 'quantity_in_grams' => 100],
                        ['name' => 'Aveia em flocos', 'quantity_in_grams' => 25],
                    ],
                ],
                [
                    'name' => 'Jantar',
                    'time_label' => '20:00',
                    'notes' => 'Manter hidratação alta no período da noite.',
                    'foods' => [
                        ['name' => 'Peito de frango grelhado', 'quantity_in_grams' => 150],
                        ['name' => 'Batata doce cozida', 'quantity_in_grams' => 180],
                    ],
                ],
                [
                    'name' => 'Ceia',
                    'time_label' => '22:30',
                    'notes' => 'Opcional se houver fome.',
                    'foods' => [
                        ['name' => 'Ovos inteiros', 'quantity_in_grams' => 80],
                        ['name' => 'Abacate', 'quantity_in_grams' => 60],
                    ],
                ],
            ];

            foreach ($meals as $index => $mealData) {
                $meal = $menu->meals()->create([
                    'name' => $mealData['name'],
                    'time_label' => $mealData['time_label'],
                    'order' => $index + 1,
                    'notes' => $mealData['notes'],
                ]);

                foreach ($mealData['foods'] as $foodIndex => $mealFood) {
                    $food = $foods->get($mealFood['name']);
                    if (! $food) {
                        continue;
                    }

                    $meal->mealFoods()->create([
                        'diet_food_id' => $food->id,
                        'quantity_in_grams' => $mealFood['quantity_in_grams'],
                        'order' => $foodIndex + 1,
                    ]);
                }
            }

            $menu->syncAggregateCounters();
        }

        if (! Coupon::where('parent_id', $owner->id)->exists()) {
            Coupon::create([
                'parent_id' => $owner->id,
                'code' => 'PRIME10',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'expires_at' => now()->addMonths(3),
                'max_uses' => 50,
            ]);
        }
    }
}
