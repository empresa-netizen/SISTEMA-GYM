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

        if (! DietFood::where('parent_id', $owner->id)->exists()) {
            foreach ([
                ['name' => 'Peito de frango grelhado', 'food_group' => 'Proteínas', 'calories' => 165, 'protein' => 31, 'carbs' => 0, 'fat' => 3.6],
                ['name' => 'Arroz integral cozido', 'food_group' => 'Carboidratos', 'calories' => 124, 'protein' => 2.6, 'carbs' => 26, 'fat' => 1],
                ['name' => 'Abacate', 'food_group' => 'Gorduras', 'calories' => 160, 'protein' => 2, 'carbs' => 9, 'fat' => 15],
            ] as $food) {
                DietFood::create(array_merge($food, ['parent_id' => $owner->id]));
            }
        }

        if (! DietMenu::where('parent_id', $owner->id)->exists()) {
            DietMenu::create([
                'parent_id' => $owner->id,
                'name' => 'Cutting — Semana 1',
                'status' => 'published',
                'meals_count' => 5,
                'total_calories' => 1800,
                'description' => 'Cardápio hipocalórico para fase de definição.',
            ]);
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
