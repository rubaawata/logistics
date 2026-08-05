<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            $guard = $this->getGuard($request);
            if ($guard === 'delivery') {
                return route('deliveries.login');
            }
            if ($guard === 'seller') {
                return route('sellers.login');
            }
            return route('login');
        }
    }

    protected function getGuard(Request $request): string
    {
        $route = $request->route();

        if ($route && $route->getAction('middleware')) {
            $middleware = $route->getAction('middleware');
            $middleware = is_array($middleware) ? $middleware : [$middleware];

            foreach ($middleware as $item) {
                if (str_contains($item, 'auth:delivery')) {
                    return 'delivery';
                }
                if (str_contains($item, 'auth:seller')) {
                    return 'seller';
                }
            }
        }

        return 'web';
    }
}
