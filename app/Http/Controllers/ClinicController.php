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
     * Affiche le Dashboard selon le rôle de l'utilisateur.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');

        // 1. Récupération des cliniques (visible par tous ou filtrée)
        $clinics = Clinic::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->get();

        // 2. Initialisation de la collection des rendez-vous
        $appointments = collect();

        // 3. Logique de récupération des rendez-vous par rôle
        if ($user->role === 'secretaire') {
            // La secrétaire voit les RDV en attente de SA clinique
            $appointments = Appointment::with(['patient.user', 'doctor', 'service'])
                ->where('clinic_id', $user->clinic_id)
                ->where('status', 'pending')
                ->get();

        } elseif ($user->role === 'medecin') {
            // Le médecin voit ses RDV confirmés
            $appointments = Appointment::with(['patient.user', 'service'])
                ->where('doctor_id', $user->id)
                ->where('status', 'confirmed')
                ->get();

        } elseif ($user->role === 'patient') {
            // On récupère le profil Patient lié à l'User
            $patientProfile = Patient::where('user_id', $user->id)->first();

            if ($patientProfile) {
                // On utilise patient_id car c'est le nom dans ta migration appointments
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

    public function create()
    {
        return Inertia::render('Clinics/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // Création de la clinique liée à l'admin
        Auth::user()->clinics()->create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('dashboard')->with('message', 'Clinique créée avec succès !');
    }

    public function show(Clinic $clinic)
    {
        $user = Auth::user();

        // Vérification des droits d'accès
        if (!$user->isAdmin() && $user->clinic_id !== $clinic->id && $user->role !== 'patient') {
            abort(403, 'Accès non autorisé.');
        }

        return Inertia::render('Clinics/Show', [
            'clinic' => $clinic,
            'stats' => [
                'patients_count' => $clinic->patients()->count(),
                'doctors_count' => $clinic->doctors()->count(),
                'appointments_today' => $clinic->appointments()
                    ->whereDate('appointment_date', now()->toDateString())
                    ->count(),
            ]
        ]);
    }
}