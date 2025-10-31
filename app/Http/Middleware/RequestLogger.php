<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Traiter la requête
        $response = $next($request);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // en millisecondes

        // Logger les informations de la requête
        Log::channel('elasticsearch')->info('API Request', [
            'type' => 'api_request',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            'request_size' => strlen($request->getContent()),
            'response_size' => strlen($response->getContent()),
            'timestamp' => now()->toIso8601String(),
        ]);

        return $response;
    }
}
