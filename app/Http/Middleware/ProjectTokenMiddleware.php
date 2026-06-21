<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ProjectToken;

class ProjectTokenMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->header('X-Project-Token') ?? $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Project token not provided.'], 401);
        }

        // We lookup the project token in DB
        $projectToken = ProjectToken::where('token', $token)->first();

        if (!$projectToken) {
            return response()->json(['error' => 'Invalid project token.'], 401);
        }

        // Attach project to request to be used later
        $request->attributes->set('auth_project', $projectToken->project);

        return $next($request);
    }
}
