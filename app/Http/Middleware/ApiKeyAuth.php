<?php

namespace App\Http\Middleware;

use App\Models\ThirdPartyApplication;
use Closure;
use Illuminate\Http\Request;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Please provide X-API-Key header or api_key parameter.'
            ], 401);
        }

        $application = ThirdPartyApplication::findByApiKey($apiKey);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive API key.'
            ], 401);
        }

        // Check IP whitelist if configured
        if (!empty($application->allowed_ips)) {
            $clientIp = $request->ip();
            if (!in_array($clientIp, $application->allowed_ips)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Your IP address is not whitelisted.'
                ], 403);
            }
        }

        // Record API request
        $application->recordApiRequest();

        // Attach application to request for use in controllers
        $request->merge(['third_party_app' => $application]);

        return $next($request);
    }
}

