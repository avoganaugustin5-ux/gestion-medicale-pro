<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Si l'utilisateur n'est pas connecté ou n'a pas le bon rôle
        if (!Auth::check() || !in_array($request->user()->role, $roles)) {
            // On le redirige vers le dashboard avec un message d'erreur
            return redirect('/dashboard')->with('error', "Vous n'avez pas l'autorisation d'accéder à cette page.");
        }

        return $next($request);
    }
}
