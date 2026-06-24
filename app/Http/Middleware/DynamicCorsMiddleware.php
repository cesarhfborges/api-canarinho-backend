<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DynamicCorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Lista de rotas administrativas reais
        $adminPrefixes = [
            'api/admin/login',
            'api/admin/register',
            'api/admin/logout',
            'api/admin/me',
            'api/admin/dashboard',
            'api/admin/config',
            'api/admin/cache',
            'api/admin/users',
            'api/admin/projects',
            'api/admin/endpoints',
            'api/admin/tokens',
            'api/admin/rules'
        ];

        $isRealAdmin = false;
        foreach ($adminPrefixes as $prefix) {
            if ($request->is($prefix) || $request->is($prefix . '/*')) {
                $isRealAdmin = true;
                break;
            }
        }

        // Se for API, mas não for uma rota administrativa real, nem health/system, é uma rota dinâmica (mesmo que o usuário se chame 'admin')
        if ($request->is('api/*') && !$isRealAdmin && !$request->is('api/health/*') && !$request->is('api/system/*')) {
            
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
