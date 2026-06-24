<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DynamicCorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // A rota dinâmica de mocks agora está explicitamente isolada sob api/mock/
        if ($request->is('api/mock/*')) {
            $origin = $request->header('Origin') ?: '*';

            // Intercepta requisições OPTIONS (preflight)
            if ($request->isMethod('OPTIONS')) {
                return response('', 200)
                    ->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Project-Token, Accept')
                    ->header('Access-Control-Allow-Credentials', 'true');
            }

            // Para outras requisições, prossegue normalmente e adiciona os cabeçalhos na resposta
            $response = $next($request);

            if (method_exists($response, 'header')) {
                $response->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Project-Token, Accept')
                    ->header('Access-Control-Allow-Credentials', 'true');
            }

            return $response;
        }

        return $next($request);
    }
}
