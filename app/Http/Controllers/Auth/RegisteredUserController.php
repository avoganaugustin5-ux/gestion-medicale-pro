<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'clinics' => Clinic::all(['id', 'name']),
            'services' => Service::all(['id', 'nom']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Validation stricte et adaptée
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,medecin,secretaire,patient',
            'telephone' => 'required|string|max:20',
            // Champs conditionnels
            'specialite' => 'required_if:role,medecin|nullable|string|max:255',
            'service_id' => 'required_if:role,medecin|nullable|exists:services,id',
            'clinic_id' => 'required_if:role,medecin,secretaire|nullable|exists:clinics,id',
        ]);

        // 1. Création de l'utilisateur (Table Users)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'numeroTelephone' => $request->telephone,
            'clinic_id' => $request->clinic_id,
        ]);

        // Découpage propre du nom pour les tables liées
        $nameParts = explode(' ', trim($request->name), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? ' ';

        // 2. Création du profil spécifique
        if ($request->role === 'medecin') {
            Doctor::create([
                'user_id'    => $user->id,
                'last_name'  => $lastName, // Utilisation des colonnes standards
                'first_name' => $firstName,
                'specialty'  => $request->specialite,
                'service_id' => $request->service_id,
                'clinic_id'  => $request->clinic_id,
            ]);
        } 
        elseif ($request->role === 'secretaire') {
            // Utilisation du modèle Secretary (vérifie bien l'existence du modèle)
            \App\Models\Secretary::create([
                'user_id'    => $user->id,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'clinic_id'  => $request->clinic_id,
            ]);
        }
        elseif ($request->role === 'patient') {
            Patient::create([
                'user_id'    => $user->id,
                'nom'        => $lastName,
                'prenom'     => $firstName,
                'telephone'  => $request->telephone,
                'clinic_id'  => $request->clinic_id ?? 1,
            ]);
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard'));
    }
}