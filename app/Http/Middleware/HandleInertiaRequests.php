<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * Le template racine chargé lors de la première visite.
     */
    protected $rootView = 'app';

    /**
     * Détermine la version actuelle de l'asset.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Définit les données partagées par défaut.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            
            // On partage l'utilisateur avec toutes les propriétés nécessaires pour Vue
            'auth' => [
                'user' => $request->user() ? [
                    'id'      => $request->user()->id,
                    'name'    => $request->user()->name,
                    'email'   => $request->user()->email,
                    'role'    => $request->user()->role, // Indispensable pour tes v-if dans le Layout
                    'sexe'    => $request->user()->sexe,
                    'image'   => $request->user()->imageProfil, // Pour l'avatar dans le menu
                ] : null,
            ],

            // Partage des messages Flash pour les notifications (Succès/Erreur)
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],

            // Configuration Ziggy pour les routes dans Vue
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}