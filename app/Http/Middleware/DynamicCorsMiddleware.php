<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DynamicCorsMiddleware
{
    private $allowedHeaders = [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'X-Project-Token',
        'Accept',
        'X-Origin'
    ];

    private array $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS'
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->is('api/mock/*')) {
            $origin = $request->header('Origin')
                ?: $request->header('X-Origin')
                    ?: '*';

            if ($request->isMethod('OPTIONS')) {
                return response('', 200)
                    ->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', implode(', ', $this->allowedHeaders))
                    ->header('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders))
                    ->header('Access-Control-Allow-Credentials', 'true');
            }

            $response = $next($request);

            if (method_exists($response, 'header')) {
                $response->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', implode(', ', $this->allowedHeaders))
                    ->header('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders))
                    ->header('Access-Control-Allow-Credentials', 'true');
            }

            return $response;
        }

        return $next($request);
    }
}
