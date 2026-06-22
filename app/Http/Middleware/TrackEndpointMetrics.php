<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\EndpointCall;
use Illuminate\Http\Request;

class TrackEndpointMetrics
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
        $response = $next($request);

        if ($request->attributes->has('matched_endpoint_id')) {
            \App\Models\EndpointCall::create([
                'endpoint_id' => $request->attributes->get('matched_endpoint_id'),
                'method' => strtoupper($request->method()),
                'url' => $request->path(),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip()
            ]);
        }

        return $response;
    }
}
