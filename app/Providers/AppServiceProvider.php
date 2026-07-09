<?php

namespace App\Providers;

use App\Models\ClientFeedback;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Member;
use App\Models\Workout;
use App\Observers\DashboardCacheObserver;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Schema::defaultStringLength(191);
        Carbon::setLocale('pt_BR');
        setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil');

        if (! file_exists(storage_path('framework/cache'))) {
            mkdir(storage_path('framework/cache'), 0755, true);
        }

        if (! file_exists(storage_path('framework/views'))) {
            mkdir(storage_path('framework/views'), 0755, true);
        }

        $this->configureRateLimiting();
        $this->registerDashboardCacheObservers();
    }

    private function configureRateLimiting(): void
    {
        // Login API: 5 tentativas/min por IP (anti brute-force)
        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(5)->by('api-login:'.$request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Muitas tentativas de login. Tente novamente em breve.',
                    'error' => 'too_many_requests',
                ], 429)->withHeaders($headers);
            });
        });

        // Rotas autenticadas da API: 100 req/min por usuario (fallback IP)
        RateLimiter::for('api-authenticated', function (Request $request) {
            return Limit::perMinute(100)
                ->by('api-auth:'.(optional($request->user())->id ?: $request->ip()))
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Limite de requisicoes excedido. Tente novamente em breve.',
                        'error' => 'too_many_requests',
                    ], 429)->withHeaders($headers);
                });
        });

        // Grupo api padrao (rotas legadas / health / fallback)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(100)->by(optional($request->user())->id ?: $request->ip());
        });
    }

    private function registerDashboardCacheObservers(): void
    {
        $observer = DashboardCacheObserver::class;

        Member::observe($observer);
        Invoice::observe($observer);
        InvoicePayment::observe($observer);
        Event::observe($observer);
        Workout::observe($observer);
        Conversation::observe($observer);
        ClientFeedback::observe($observer);
    }
}
