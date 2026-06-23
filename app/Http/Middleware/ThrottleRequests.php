<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests
{
    /**
     * @var RateLimiter
     */
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $maxAttempts = (int) \App\Models\Config::getValue('rate_limit_requests', 2000);
        $decayMinutes = (int) \App\Models\Config::getValue('rate_limit_time', 60);
        
        // Rate limit por IP do usuário (se estiver logado, poderia ser pelo user_id, mas IP protege contra bruteforce global)
        $key = 'rate_limit:' . $request->ip();

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($key);
            
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Try again later.',
                'retry_after' => $retryAfter
            ], Response::HTTP_TOO_MANY_REQUESTS, [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Adiciona os cabeçalhos de controle ao response
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $this->limiter->attempts($key)));

        return $response;
    }
}
