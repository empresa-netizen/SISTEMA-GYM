<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberAnamnesis;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ToolsController extends Controller
{
    public function anamnesis(): View
    {
        $entries = MemberAnamnesis::with('member')->latest()->paginate(25);

        return view('prime.tools.anamnesis', compact('entries'));
    }

    public function importCustomers(): View
    {
        return view('prime.tools.import-customers');
    }

    public function importCustomersStore(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $handle = fopen($request->file('csv')->getRealPath(), 'r');
        if (! $handle) {
            return back()->with('error', 'Não foi possível ler o arquivo.');
        }

        $header = fgetcsv($handle);
        $created = 0;
        $parentId = parentId();
        $defaultPlan = MembershipPlan::where('parent_id', $parentId)->active()->first();

        while (($row = fgetcsv($handle)) !== false) {
            $data = @array_combine($header ?: [], $row);
            if (! is_array($data)) {
                continue;
            }
            $email = trim((string) ($data['email'] ?? $data['Email'] ?? ''));
            $name = trim((string) ($data['name'] ?? $data['nome'] ?? $data['Nome'] ?? ''));
            if ($email === '' || $name === '') {
                continue;
            }
            if (Member::where('email', $email)->where('parent_id', $parentId)->exists()) {
                continue;
            }
            $start = now()->toDateString();
            Member::create([
                'parent_id' => $parentId,
                'name' => $name,
                'email' => $email,
                'phone' => $data['phone'] ?? $data['telefone'] ?? $data['whatsapp'] ?? null,
                'status' => 'active',
                'membership_plan_id' => $defaultPlan?->id,
                'membership_start_date' => $start,
                'membership_end_date' => $defaultPlan ? $defaultPlan->calculateExpiryDate($start) : now()->addDays(30)->toDateString(),
            ]);
            $created++;
        }
        fclose($handle);

        return back()->with('success', "{$created} cliente(s) importado(s).");
    }

    public function importProtocols(): View
    {
        return view('prime.tools.import-protocols');
    }

    public function patchNotes(): View
    {
        $notes = [
            [
                'version' => '1.5.0',
                'date' => '09/07/2026',
                'area' => 'Web Profissional',
                'title' => 'Notas de Atualização no padrão Prime',
                'summary' => 'Página local com acordeão escuro, badges NEW e releases MGTEAM organizadas por produto.',
                'is_new' => true,
                'items' => [
                    'Lista densa e expansível para acompanhar entregas sem sair do painel.',
                    'Separação por área de produto para Web Profissional e App.',
                    'Conteúdo estático local, sem consumo de APIs externas.',
                ],
            ],
            [
                'version' => '1.4.0',
                'date' => '08/07/2026',
                'area' => 'Web Profissional',
                'title' => 'Clientes e Perfil 360 com visual Prime',
                'summary' => 'Cards, filtros e abas de evolução ganharam densidade visual para operação diária do coach.',
                'is_new' => true,
                'items' => [
                    'Diretório de clientes com status, plano, tags e ações rápidas.',
                    'Perfil 360 reunindo progresso, treinos, dieta, fotos e registros.',
                    'Estados vazios e indicadores alinhados à identidade MGTEAM.',
                ],
            ],
            [
                'version' => '1.3.0',
                'date' => '07/07/2026',
                'area' => 'App',
                'title' => 'Base dos apps conectada ao ecossistema MGTEAM',
                'summary' => 'Rotas e módulos locais preparados para refletir a experiência do aluno no aplicativo.',
                'is_new' => false,
                'items' => [
                    'Feed, comunidade e mensagens organizados para comunicação com alunos.',
                    'Áreas de treino e dieta espelhadas para consulta rápida.',
                    'Conteúdo demonstrativo local para validação sem integrações externas.',
                ],
            ],
            [
                'version' => '1.2.0',
                'date' => '06/07/2026',
                'area' => 'Web Profissional',
                'title' => 'Dashboard operacional MGTEAM',
                'summary' => 'Visão inicial para receita, agenda e indicadores-chave do negócio no layout Prime.',
                'is_new' => false,
                'items' => [
                    'Cards de resumo para acompanhar operação e evolução financeira.',
                    'Atalhos para clientes, treinos, planos e suporte.',
                    'Tema escuro como padrão para manter consistência visual.',
                ],
            ],
        ];

        return view('prime.tools.patch-notes', compact('notes'));
    }
}
