<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\ActivityLog;
use App\Models\User;
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
        $today = now()->toDateString();

        // --- 1. FILTRAGE DES CLINIQUES ---
        $clinicsQuery = Clinic::query();
        if ($role === 'admin') {
            $clinicsQuery->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            });
        } elseif (in_array($role, ['secretaire', 'medecin'])) {
            $clinicsQuery->where('id', $user->clinic_id);
        }

        $clinics = $clinicsQuery->withCount(['patients', 'appointments'])->get();

        // --- 2. STATISTIQUES DYNAMIQUES SELON RÔLE ---
        $stats = [
            'total_appointments' => 0,
            'pending_appointments' => 0,
            'today_appointments' => 0,
            'total_patients' => 0
        ];

        if ($role === 'admin') {
            $stats = [
                'total_clinics' => Clinic::count(),
                'total_users' => User::count(),
                'total_appointments' => Appointment::count(),
                'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
            ];
        } elseif ($role === 'secretaire') {
            $stats = [
                'pending_appointments' => Appointment::where('clinic_id', $user->clinic_id)->where('status', 'pending')->count(),
                'today_appointments' => Appointment::where('clinic_id', $user->clinic_id)->whereDate('appointment_date', $today)->count(),
                'total_patients' => Patient::whereHas('appointments', function($q) use ($user) { $q->where('clinic_id', $user->clinic_id); })->distinct()->count(),
            ];
        } elseif ($role === 'medecin') {
            $stats = [
                'confirmed_appointments' => Appointment::where('doctor_id', $user->id)->where('status', 'confirmed')->count(),
                'today_appointments' => Appointment::where('doctor_id', $user->id)->whereDate('appointment_date', $today)->where('status', 'confirmed')->count(),
                'total_consultations' => Appointment::where('doctor_id', $user->id)->where('status', 'confirmed')->count(), // Ajustable si tu as une table consultations
            ];
        }

        // --- 3. RÉCUPÉRATION DES RDV PAR RÔLE ---
        $appointments = collect();
        if ($role === 'admin') {
            $appointments = Appointment::with(['patient.user', 'doctor', 'clinic', 'service'])->latest()->take(10)->get();
        } elseif ($role === 'secretaire') {
            $appointments = Appointment::with(['patient.user', 'doctor', 'service'])
                ->where('clinic_id', $user->clinic_id)
                ->latest()->get();
        } elseif ($role === 'medecin') {
            $appointments = Appointment::with(['patient.user', 'service', 'clinic'])
                ->where('doctor_id', $user->id)
                ->where('status', 'confirmed')
                ->latest()->get();
        } elseif ($role === 'patient') {
            $patientProfile = Patient::where('user_id', $user->id)->first();
            if ($patientProfile) {
                $appointments = Appointment::with(['doctor', 'clinic', 'service'])
                    ->where('patient_id', $patientProfile->id)
                    ->latest()->get();
            }
        }

        // --- 4. LOGS D'ACTIVITÉ ---
        $activitiesQuery = ActivityLog::latest()->take(8);
        if ($role !== 'admin' && $user->clinic_id) {
            $clinicName = Clinic::find($user->clinic_id)?->name;
            $activitiesQuery->where('clinic_name', $clinicName);
        }
        $activities = $activitiesQuery->get()->map(fn($log) => [
            'id' => $log->id,
            'user_name' => $log->user_name,
            'action' => $log->action,
            'target' => $log->target,
            'time' => $log->created_at->diffForHumans(),
        ]);

        return Inertia::render('Dashboard', [
            'auth' => ['user' => $user],
            'clinics' => $clinics,
            'appointments' => $appointments,
            'activities' => $activities,
            'stats' => $stats,
            'filters' => $request->only(['search']),
            'userRole' => $role,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $clinic = Clinic::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id(), 
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'action' => 'Création',
            'target' => 'Clinique: ' . $clinic->name,
            'clinic_name' => 'Administration Centrale'
        ]);

        return redirect()->route('dashboard')->with('success', 'La clinique a été créée avec succès !');
    }

    public function show(Request $request, Clinic $clinic)
    {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');
        $search = $request->input('search');

        if ($role !== 'admin' && $user->clinic_id !== $clinic->id && $role !== 'patient') {
            abort(403, 'Accès non autorisé.');
        }

        return Inertia::render('Clinics/Show', [
            'clinic' => $clinic,
            'staff' => $clinic->users()
                ->whereIn('role', ['medecin', 'secretaire'])
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })->get(),
            'stats' => [
                'patients_count' => $clinic->patients()->count(),
                'doctors_count' => $clinic->users()->where('role', 'medecin')->count(),
                'appointments_today' => $clinic->appointments()
                    ->whereDate('appointment_date', now()->toDateString())
                    ->count(),
            ],
            'filters' => $request->only(['search']),
        ]);
    }
}