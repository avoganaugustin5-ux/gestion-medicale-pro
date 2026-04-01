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

    // Gestion des cliniques selon le rôle
    $clinicsQuery = Clinic::query();
    if ($role === 'admin') {
        $clinicsQuery->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        });
    } elseif (in_array($role, ['secretaire', 'medecin'])) {
        $clinicsQuery->where('id', $user->clinic_id);
    }
    $clinics = $clinicsQuery->withCount(['patients', 'appointments'])->get();

    // Statistiques
    $stats = [];
    if ($role === 'admin') {
        $stats = [
            'total_clinics' => Clinic::count(),
            'total_users' => User::count(),
            'total_appointments' => Appointment::count(),
            'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
        ];
    } else {
        $stats = [
            'today_appointments' => Appointment::where('clinic_id', $user->clinic_id)
                ->whereDate('appointment_date', $today)->count(),
            'total_patients' => Patient::count(), 
        ];
    }

    // Récupération des rendez-vous (Filtrage par patient si nécessaire)
    $appointments = Appointment::with(['patient.user', 'doctor.user', 'clinic', 'service'])
        ->when($role === 'patient', function($q) use ($user) {
            return $q->where('patient_id', $user->patient?->id);
        })
        ->when(in_array($role, ['secretaire', 'medecin']), function($q) use ($user) {
            return $q->where('clinic_id', $user->clinic_id);
        })
        ->latest()->take(10)->get();

    // Journal d'activité
    $activities = ActivityLog::latest()->take(8)->get()->map(fn($log) => [
        'id' => $log->id,
        'user_name' => $log->user_name,
        'action' => $log->action,
        'target' => $log->target,
        'time' => $log->created_at->diffForHumans(),
    ]);

    return Inertia::render('Dashboard', [
        'clinics' => $clinics,
        'appointments' => $appointments,
        'activities' => $activities,
        'stats' => $stats,
        'filters' => $request->only(['search']),
        'userRole' => $role,
        // CRUCIAL : Transmission du profil patient complet pour les liens
        'patient' => $user->patient ? $user->patient()->with('clinic')->first() : null,
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
        'description' => 'nullable|string|max:5000',
    ]);

    // On fusionne les données du formulaire avec l'ID de l'admin connecté
    $clinic = Clinic::create([
        'name' => $request->name,
        'description' => $request->description,
        'user_id' => Auth::id(), // Ajout crucial pour corriger l'erreur 1364
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

        // Sécurité : Un non-admin ne peut voir que sa propre clinique
        if ($role !== 'admin' && $user->clinic_id !== $clinic->id) {
             if ($role !== 'patient') abort(403);
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

    public function edit(Clinic $clinic)
    {
        return Inertia::render('Clinics/Edit', [
            'clinic' => $clinic
        ]);
    }

    public function update(Request $request, Clinic $clinic)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $clinic->update($request->only('name', 'description'));

        return redirect()->route('dashboard')->with('success', 'Clinique mise à jour.');
    }

    public function destroy(Clinic $clinic)
    {
        $clinic->delete();
        return redirect()->route('dashboard')->with('success', 'Clinique supprimée.');
    }
}