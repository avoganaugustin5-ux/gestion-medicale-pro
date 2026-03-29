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
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        $role = strtolower($user->role ?? 'guest');

        // 1. Récupération des cliniques (Recherche pour Admin et Patient)
        $clinics = Clinic::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->get();

        $appointments = collect();

        // 2. LOGIQUE DES DONNÉES PAR RÔLE
        
        // --- CAS ADMIN : TU VOIS TOUT ---
        if ($role === 'admin') {
            $appointments = Appointment::with(['patient.user', 'doctor', 'clinic', 'service'])
                ->latest()
                ->take(10) // Les 10 derniers RDV du site
                ->get();

        } elseif ($role === 'secretaire') {
            if ($user->clinic_id) {
                $appointments = Appointment::with(['patient.user', 'doctor', 'service'])
                    ->where('clinic_id', $user->clinic_id)
                    ->where('status', 'pending')
                    ->latest()
                    ->get();
            }

        } elseif ($role === 'medecin') {
            // Ici on suppose que doctor_id dans appointments correspond à l'ID du User
            $appointments = Appointment::with(['patient.user', 'service', 'clinic'])
                ->where('doctor_id', $user->id) 
                ->where('status', 'confirmed')
                ->latest()
                ->get();

        } elseif ($role === 'patient') {
            $patientProfile = Patient::where('user_id', $user->id)->first();
            if ($patientProfile) {
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

    // Garde tes méthodes create, store et show...
    public function create() { return Inertia::render('Clinics/Create'); }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);
        Clinic::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);
        return redirect()->route('dashboard')->with('success', 'Clinique créée avec succès !');
    }

    public function show(Clinic $clinic) {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');
        if ($role !== 'admin' && $user->clinic_id !== $clinic->id && $role !== 'patient') {
            abort(403);
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