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

        // 1. Récupération des cliniques
        $clinics = Clinic::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->get();

        $appointments = collect();

        // 2. Logique par rôle
        if ($role === 'admin') {
            // L'admin voit tout le monde
            $appointments = Appointment::with(['patient.user', 'doctor', 'clinic', 'service'])
                ->latest()
                ->take(10)
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

        Clinic::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id(), 
        ]);

        return redirect()->route('dashboard')->with('success', 'La clinique a été créée avec succès !');
    }

    public function show(Clinic $clinic)
    {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');

        if ($role !== 'admin' && $user->clinic_id !== $clinic->id && $role !== 'patient') {
            abort(403, 'Accès non autorisé.');
        }

        return Inertia::render('Clinics/Show', [
            'clinic' => $clinic,
            'stats' => [
                'patients_count' => $clinic->patients()->count(),
                'doctors_count' => $clinic->doctors()->count(),
                // Vérifie si c'est 'date_rdv' ou 'appointment_date' dans ta DB
                'appointments_today' => $clinic->appointments()
                    ->whereDate('appointment_date', now()->toDateString())
                    ->count(),
            ]
        ]);
    }
}