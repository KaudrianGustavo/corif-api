<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response{
        $tipoUser = $request->user()->tipo_user;

       if (! in_array($tipoUser, $roles)) {
        return response()->json([
            'message'   => 'Acesso negado.'
        ], 403);
       }

        return $next($request);
    }
}
