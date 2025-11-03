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
            return route('login');
        }
    }

    protected function getGuard(Request $request): string
    {
        $route = $request->route();

        if ($route && $route->getAction('middleware')) {
            $middleware = $route->getAction('middleware');

            if (is_array($middleware)) {
                foreach ($middleware as $item) {
                    if (str_contains($item, 'auth:delivery')) {
                        return 'delivery';
                    }
                }
            } elseif (str_contains($middleware, 'auth:delivery')) {
                return 'delivery';
            }
        }

        return 'web';
    }
}
