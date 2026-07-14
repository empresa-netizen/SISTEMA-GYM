<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('finance:check-overdue')->dailyAt('06:00');
        $schedule->command('payments:send-reminders')->dailyAt('08:00');
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SentryContext::class,
        ]);

        $middleware->appendToGroup('api', [
            \App\Http\Middleware\SentryContext::class,
        ]);

        $middleware->alias([
            'verify2fa' => \App\Http\Middleware\Verify2FA::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Captura global → Sentry (erros 500, Jobs falhos, exceptions não tratadas)
        Integration::handles($exceptions);

        $wantsJson = static function (Request $request): bool {
            return $request->expectsJson()
                || $request->is('api/*')
                || $request->bearerToken() !== null;
        };

        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) use ($wantsJson) {
            return $wantsJson($request);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            return response()->json([
                'message' => 'Nao autenticado.',
                'error' => 'unauthenticated',
            ], 401);
        });

        $exceptions->render(function (ValidationException $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            return response()->json([
                'message' => 'Os dados enviados sao invalidos.',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            return response()->json([
                'message' => 'Recurso nao encontrado.',
                'error' => 'not_found',
            ], 404);
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            return response()->json([
                'message' => 'Muitas requisicoes. Tente novamente em breve.',
                'error' => 'too_many_requests',
            ], 429)->withHeaders($e->getHeaders());
        });

        $exceptions->render(function (\Throwable $e, Request $request) use ($wantsJson) {
            // Respostas customizadas do RateLimiter (Limit::response)
            if ($e instanceof HttpResponseException) {
                return $e->getResponse();
            }

            if (! $wantsJson($request)) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $e->getMessage() !== '' ? $e->getMessage() : match ($status) {
                    403 => 'Acesso nao autorizado.',
                    404 => 'Recurso nao encontrado.',
                    429 => 'Muitas requisicoes. Tente novamente em breve.',
                    default => 'Erro na requisicao.',
                };

                return response()->json([
                    'message' => $message,
                    'error' => 'http_error',
                ], $status);
            }

            // Nunca vazar stack traces em JSON de producao
            $payload = [
                'message' => app()->hasDebugModeEnabled()
                    ? $e->getMessage()
                    : 'Erro interno do servidor.',
                'error' => 'server_error',
            ];

            if (app()->hasDebugModeEnabled()) {
                $payload['exception'] = class_basename($e);
            }

            return response()->json($payload, 500);
        });
    })->create();
