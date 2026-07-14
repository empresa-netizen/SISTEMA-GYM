<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductHubController extends Controller
{
    public function hub(): View
    {
        $products = Product::where('parent_id', parentId())
            ->latest()
            ->take(50)
            ->get();

        return view('mgteam.products.hub', compact('products'));
    }

    public function quickStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'type' => ['nullable', 'in:service,plan,digital,physical'],
            'description' => ['nullable', 'string'],
        ]);

        $category = ProductCategory::firstOrCreate(
            ['parent_id' => parentId(), 'name' => 'Geral'],
            ['description' => 'Categoria padrão', 'active' => true]
        );

        Product::create([
            'parent_id' => parentId(),
            'category_id' => $category->id,
            'name' => $validated['name'],
            'type' => $validated['type'] ?? 'service',
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'cost_price' => 0,
            'stock_quantity' => 0,
            'min_stock_level' => 0,
            'unit' => 'unit',
            'active' => true,
            'track_inventory' => false,
        ]);

        return back()->with('success', 'Produto criado na vitrine.');
    }

    public function affiliates(): View
    {
        $inviteCode = 'MGTEAM-'.strtoupper(substr(md5((string) (auth()->id() ?? 'demo')), 0, 6));
        $inviteUrl = url('/register?ref='.$inviteCode);

        $stats = [
            'total_commission' => 2847.50,
            'pending_commission' => 420.00,
            'conversions_month' => 12,
            'active_affiliates' => 8,
            'commission_rate' => 15,
        ];

        $affiliates = collect([
            ['name' => 'Ana Beatriz Silva', 'email' => 'anabeatriz@gmail.com', 'conversions' => 5, 'commission' => 875.00, 'status' => 'ativo', 'joined' => '12/03/2026'],
            ['name' => 'Carlos Mendes', 'email' => 'carlos.m@email.com', 'conversions' => 3, 'commission' => 540.00, 'status' => 'ativo', 'joined' => '28/02/2026'],
            ['name' => 'Juliana Costa', 'email' => 'ju.costa@email.com', 'conversions' => 2, 'commission' => 320.00, 'status' => 'ativo', 'joined' => '15/01/2026'],
            ['name' => 'Rafael Oliveira', 'email' => 'rafa.oliveira@email.com', 'conversions' => 1, 'commission' => 149.90, 'status' => 'pendente', 'joined' => '05/07/2026'],
            ['name' => 'Mariana Santos', 'email' => 'mari.santos@email.com', 'conversions' => 1, 'commission' => 149.90, 'status' => 'ativo', 'joined' => '20/06/2026'],
        ]);

        return view('mgteam.products.affiliates', compact('inviteCode', 'inviteUrl', 'stats', 'affiliates'));
    }

    public function cartRecovery(): View
    {
        $stats = [
            'abandoned_total' => 23,
            'abandoned_value' => 6840.00,
            'recovered_count' => 7,
            'recovered_value' => 2135.00,
            'recovery_rate' => 30.4,
            'emails_sent' => 18,
        ];

        $carts = collect([
            ['client' => 'Fernanda Lima', 'email' => 'fernanda.l@email.com', 'product' => 'Plano Trimestral Premium', 'value' => 597.00, 'abandoned_at' => '08/07/2026 14:32', 'status' => 'abandonado', 'attempts' => 0],
            ['client' => 'Pedro Henrique', 'email' => 'pedro.h@email.com', 'product' => 'Consultoria Online Mensal', 'value' => 249.00, 'abandoned_at' => '07/07/2026 19:15', 'status' => 'email_enviado', 'attempts' => 1],
            ['client' => 'Camila Rocha', 'email' => 'camila.r@email.com', 'product' => 'Plano Anual VIP', 'value' => 1890.00, 'abandoned_at' => '07/07/2026 11:08', 'status' => 'recuperado', 'attempts' => 2],
            ['client' => 'Lucas Ferreira', 'email' => 'lucas.f@email.com', 'product' => 'Avaliação Física + Treino', 'value' => 180.00, 'abandoned_at' => '06/07/2026 16:45', 'status' => 'email_enviado', 'attempts' => 1],
            ['client' => 'Beatriz Almeida', 'email' => 'bia.almeida@email.com', 'product' => 'Plano Semestral', 'value' => 990.00, 'abandoned_at' => '05/07/2026 09:22', 'status' => 'abandonado', 'attempts' => 0],
            ['client' => 'Thiago Martins', 'email' => 'thiago.m@email.com', 'product' => 'Nutrição + Treino', 'value' => 349.00, 'abandoned_at' => '04/07/2026 21:30', 'status' => 'recuperado', 'attempts' => 3],
        ]);

        return view('mgteam.products.cart-recovery', compact('stats', 'carts'));
    }
}
