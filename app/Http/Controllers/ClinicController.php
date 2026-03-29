<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClinicController extends Controller
{
    /**
     * Affiche le Dashboard dynamique selon le rôle de l'utilisateur.
     */
    public function index(Request $request)
    {
        return "Le contrôleur ClinicController est bien appelé !";
        $user = Auth::user();
        $search = $request->input('search');

        // --- STRATÉGIE PRO : Normalisation du rôle pour éviter les conflits de casse ---
        $role = strtolower($user->role ?? 'guest');

        // 1. Récupération des cliniques (Recherche fluide pour Admin et Patient)
        $clinics = Clinic::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->get();

        // 2. Initialisation sécurisée de la collection des rendez-vous
        $appointments = collect();

        // 3. Logique de récupération des données par rôle
        if ($role === 'secretaire') {
            // La secrétaire voit les RDV en attente de SA clinique
            if ($user->clinic_id) {
                $appointments = Appointment::with(['patient.user', 'doctor', 'service'])
                    ->where('clinic_id', $user->clinic_id)
                    ->where('status', 'pending')
                    ->latest()
                    ->get();
            }

        } elseif ($role === 'medecin') {
            // Le médecin voit ses propres RDV confirmés
            $appointments = Appointment::with(['patient.user', 'service', 'clinic'])
                ->where('doctor_id', $user->id)
                ->where('status', 'confirmed')
                ->latest()
                ->get();

        } elseif ($role === 'patient') {
            // On récupère le profil Patient lié à l'ID de l'utilisateur
            $patientProfile = Patient::where('user_id', $user->id)->first();

            if ($patientProfile) {
                // Récupération des rendez-vous du patient via son ID de profil
                $appointments = Appointment::with(['doctor', 'clinic', 'service'])
                    ->where('patient_id', $patientProfile->id)
                    ->latest()
                    ->get();
            }
        }

        return Inertia::render('Dashboard', [
            'clinics' => $clinics,
            'appointments' => $appointments,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Formulaire de création de clinique (Admin).
     */
    public function create()
    {
        return Inertia::render('Clinics/Create');
    }

    /**
     * Enregistre une nouvelle clinique.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // Création via la relation si définie, sinon via Model direct
        Clinic::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id(), // Si tu suis qui a créé la clinique
        ]);

        return redirect()->route('dashboard')->with('success', 'La clinique a été créée avec succès !');
    }

    /**
     * Détails d'une clinique spécifique.
     */
    public function show(Clinic $clinic)
    {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');

        // Vérification des droits (Admin voit tout, les autres voient leur clinique)
        if ($role !== 'admin' && $user->clinic_id !== $clinic->id && $role !== 'patient') {
            abort(403, 'Accès non autorisé à cette clinique.');
        }

        return Inertia::render('Clinics/Show', [
            'clinic' => $clinic,
            'stats' => [
                'patients_count' => $clinic->patients()->count(),
                'doctors_count' => $clinic->doctors()->count(),
                'appointments_today' => $clinic->appointments()
                    ->whereDate('date_rdv', now()->toDateString())
                    ->count(),
            ]
        ]);
    }
}