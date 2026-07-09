<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sentry\State\Scope;
use Symfony\Component\HttpFoundation\Response;

use function Sentry\configureScope;

class SentryContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->bound('sentry') && config('sentry.dsn')) {
            configureScope(function (Scope $scope) use ($request): void {
                $user = $request->user();

                if ($user) {
                    $scope->setUser([
                        'id' => (string) $user->getAuthIdentifier(),
                        'email' => $user->email ?? null,
                        'username' => $user->name ?? null,
                    ]);
                }

                $scope->setTag('app.name', (string) config('app.name'));
                $scope->setTag('http.route', optional($request->route())->getName() ?? $request->path());
            });
        }

        return $next($request);
    }
}
