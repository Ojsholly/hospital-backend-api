<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Roles
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, ...$role): Response
    {
        if (! ($request->user() && $request->user()->hasAnyRole($role))) {
            return response()->forbidden("You don't have permission to access this resource.");
        }

        return $next($request);
    }
}
