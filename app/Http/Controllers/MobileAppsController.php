<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MobileAppsController extends Controller
{
    public function index(): View
    {
        $statusMap = $this->integrationStatus();

        $integrations = [
            [
                'key' => 'webhooks',
                'label' => 'Webhooks',
                'description' => 'Receba eventos locais de pagamento e assinatura para automações internas.',
                'icon' => 'ri-webhook-line',
                'status' => $this->labelFor($statusMap['webhooks']),
                'href' => route('settings.index'),
            ],
            [
                'key' => 'api',
                'label' => 'API',
                'description' => 'Use os endpoints Laravel e Sanctum servidos por este ambiente.',
                'icon' => 'ri-code-s-slash-line',
                'status' => $this->labelFor($statusMap['api']),
                'href' => url('/api/health'),
            ],
            [
                'key' => 'facebook_pixel',
                'label' => 'Facebook Pixel',
                'description' => 'Configure identificadores de rastreamento sem enviar eventos externos no local.',
                'icon' => 'ri-facebook-circle-line',
                'status' => $this->labelFor($statusMap['facebook_pixel']),
                'href' => route('settings.index'),
            ],
            [
                'key' => 'enotas',
                'label' => 'eNotas',
                'description' => 'Prepare dados fiscais para emissão manual ou integração futura.',
                'icon' => 'ri-file-list-3-line',
                'status' => $this->labelFor($statusMap['enotas']),
                'href' => route('finance.index', ['tab' => 'reports']),
            ],
            [
                'key' => 'notazz',
                'label' => 'Notazz',
                'description' => 'Revise pedidos e notas a partir das vendas cadastradas localmente.',
                'icon' => 'ri-receipt-line',
                'status' => $this->labelFor($statusMap['notazz']),
                'href' => route('finance.index', ['tab' => 'transactions']),
            ],
            [
                'key' => 'whatsapp',
                'label' => 'WhatsApp',
                'description' => 'Acesse conversas e contatos do CRM sem disparos automáticos externos.',
                'icon' => 'ri-whatsapp-line',
                'status' => $this->labelFor($statusMap['whatsapp']),
                'href' => route('messages.index'),
            ],
            [
                'key' => 'stripe',
                'label' => 'Stripe',
                'description' => 'Gateway de pagamento — status mockado no ambiente local.',
                'icon' => 'ri-bank-card-line',
                'status' => $this->labelFor($statusMap['stripe']),
                'href' => route('finance.index'),
            ],
            [
                'key' => 'sales_origin',
                'label' => 'Origem das vendas',
                'description' => 'Acompanhe produtos, cupons e relatórios para entender canais de venda.',
                'icon' => 'ri-route-line',
                'status' => $this->labelFor($statusMap['sales_origin']),
                'href' => route('reports.index'),
            ],
        ];

        return view('prime.apps.index', compact('integrations', 'statusMap'));
    }

    public function status(): JsonResponse
    {
        return response()->json($this->integrationStatus());
    }

    private function integrationStatus(): array
    {
        return [
            'whatsapp' => 'connected',
            'stripe' => 'disconnected',
            'api' => 'connected',
            'webhooks' => 'connected',
            'facebook_pixel' => 'disconnected',
            'enotas' => 'disconnected',
            'notazz' => 'disconnected',
            'sales_origin' => 'connected',
        ];
    }

    private function labelFor(string $status): string
    {
        return match ($status) {
            'connected' => 'Ativa',
            'disconnected' => 'Desconectada',
            default => 'Stub',
        };
    }
}
