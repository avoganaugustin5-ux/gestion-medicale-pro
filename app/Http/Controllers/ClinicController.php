<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClinicController extends Controller
{
    /**
     * Affiche le Dashboard selon le rôle.
     */
    

public function index(Request $request)
{
    $user = auth()->user();
    $search = $request->input('search');

    // 1. On récupère les cliniques avec filtre si recherche
    $clinics = Clinic::query()
        ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        })
        ->get();

    // 2. Logique des rendez-vous (déjà en place)
    $appointments = collect();
    if ($user->role === 'secretaire') {
        $appointments = Appointment::with(['patient.user', 'doctor', 'service'])
            ->where('clinic_id', $user->clinic_id)
            ->where('status', 'pending')
            ->get();
    } elseif ($user->role === 'medecin') {
        $appointments = Appointment::with(['patient.user', 'service'])
            ->where('doctor_id', $user->id)
            ->where('status', 'confirmed')
            ->get();
    } elseif ($user->role === 'patient') {
        $appointments = Appointment::with(['doctor', 'clinic', 'service'])
            ->where('user_id', $user->id)
            ->latest()->get();
    }

    return Inertia::render('Dashboard', [
        'clinics' => $clinics,
        'appointments' => $appointments,
        'filters' => $request->only(['search']) // On renvoie le filtre pour l'input
    ]);
}

    public function create()
    {
        // Seul l'admin devrait pouvoir créer (tu peux ajouter un gate ici)
        return Inertia::render('Clinics/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // On lie la clinique à l'admin qui la crée
        Auth::user()->clinics()->create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('dashboard')->with('message', 'Clinique créée avec succès !');
    }

    public function show(Clinic $clinic)
    {
        $user = Auth::user();

        // Sécurité : Un médecin/secrétaire ne peut voir que SA clinique
        if (!$user->isAdmin() && $user->clinic_id !== $clinic->id && !$user->isPatient()) {
            abort(403, 'Accès non autorisé à cette clinique.');
        }

        return Inertia::render('Clinics/Show', [
            'clinic' => $clinic,
            'stats' => [
                'patients_count' => $clinic->patients()->count(),
                'doctors_count' => $clinic->doctors()->count(),
                // 'now()' fonctionne parfaitement ici pour le jour J
                'appointments_today' => $clinic->appointments()
                    ->whereDate('appointment_date', now()->toDateString())
                    ->count(),
            ]
        ]);
    }
}