<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
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
    /**
     * Affiche la vue d'inscription.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            // ON ENVOIE LA LISTE DES CLINIQUES AU FRONTEND
            'clinics' => Clinic::all(['id', 'name']),
        ]);
    }

    /**
     * Gère la demande d'enregistrement.
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'role' => 'required|in:admin,medecin,secretaire,patient',
        'telephone' => 'required|string|max:20',
        'specialite' => 'required_if:role,medecin|nullable|string|max:255',
        'service_id' => 'required_if:role,medecin|nullable|exists:services,id',
        'clinic_id' => 'required_if:role,medecin,secretaire|nullable|exists:clinics,id',
    ]);

    // 1. Création de l'utilisateur de base
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'numeroTelephone' => $request->telephone,
    ]);

    // Découpage du nom pour les profils
    $nameParts = explode(' ', $user->name, 2);
    $fName = $nameParts[0];
    $lName = $nameParts[1] ?? '.';

    // 2. Création du profil spécifique selon le rôle
    if ($request->role === 'medecin') {
        Doctor::create([
            'user_id'    => $user->id,
            'name'       => $user->name,
            'specialty'  => $request->specialite,
            'service_id' => $request->service_id,
            'clinic_id'  => $request->clinic_id,
        ]);
    } 
    elseif ($request->role === 'secretaire') {
        \App\Models\Secretary::create([
            'user_id'    => $user->id,
            'first_name' => $fName,
            'last_name'  => $lName,
            'clinic_id'  => $request->clinic_id,
        ]);
    }
    elseif ($request->role === 'patient') {
        Patient::create([
            'user_id'    => $user->id,
            'first_name' => $fName,
            'last_name'  => $lName,
            'phone'      => $user->numeroTelephone,
            'clinic_id'  => $request->clinic_id ?? 1, // Par défaut à la clinique 1 si vide
        ]);
    }
    // Pour 'admin', pas de table supplémentaire nécessaire d'après ta structure

    event(new Registered($user));
    Auth::login($user);

    return redirect(route('dashboard'));
}
}