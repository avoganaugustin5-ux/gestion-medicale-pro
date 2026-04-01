<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'   => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'clinic_id' => $user->clinic_id,
                    // On vérifie l'existence du profil patient avant d'extraire les IDs
                    'patient' => $user->patient ? [
                        'id' => $user->patient->id,
                        'clinic_id' => $user->patient->clinic_id,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}