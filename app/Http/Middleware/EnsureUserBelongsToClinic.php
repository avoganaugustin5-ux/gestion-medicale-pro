<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToClinic
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $clinic = $request->route('clinic'); // On récupère la clinique dans l'URL

        // Si c'est une secrétaire et qu'elle essaie de voir une autre clinique que la sienne
        if ($user->role === 'secretary' && $user->clinic_id !== $clinic->id) {
            return redirect('/dashboard')->with('error', 'Vous n\'avez pas accès à cette clinique.');
        }

        return $next($request);
    }
}
