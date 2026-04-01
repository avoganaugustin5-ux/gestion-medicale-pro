<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    public function index(Request $request, Clinic $clinic)
    {
        $search = $request->input('search');

        return Inertia::render('Clinics/Patients/Index', [
            'clinic' => $clinic,
            'patients' => $clinic->patients()
                ->when($search, function ($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('last_name', 'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->get(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Clinic $clinic, Patient $patient)
    {
        $user = Auth::user();

        // SÉCURITÉ MISE À JOUR : 
        // On autorise si : l'utilisateur est admin 
        // OU si le patient appartient à la clinique 
        // OU si le patient connecté regarde son propre dossier
        $isOwner = ($user->role === 'patient' && $patient->user_id === $user->id);
        $isAdmin = $user->hasRole('admin');
        $isClinicStaff = ($patient->clinic_id === $clinic->id);

        if (!$isOwner && !$isAdmin && !$isClinicStaff) {
            abort(403, 'ACCÈS NON AUTORISÉ À CE DOSSIER PATIENT.');
        }

        return Inertia::render('Clinics/Patients/Show', [
            'clinic' => $clinic,
            'patient' => $patient->load('user'),
            'consultations' => $patient->consultations()
                ->with('doctor.user:id,name') 
                ->latest()
                ->get(),
            'appointments' => $patient->appointments()
                ->with('doctor.user:id,name')
                ->latest()
                ->get(),
        ]);
    }

    public function downloadMedicalRecord(Patient $patient)
    {
        // On charge les relations et on récupère les consultations
        $patient->load(['user']);
        $consultations = $patient->consultations()->with('doctor.user')->latest()->get();

        // Correction cruciale : on passe explicitement 'consultations' à la vue
        $pdf = Pdf::loadView('pdf.medical-record', [
            'patient' => $patient,
            'consultations' => $consultations
        ]);

        return $pdf->download("Carnet-Sante-{$patient->last_name}.pdf");
    }

    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'role' => 'required|in:admin,medecin,secretaire,patient',
        'telephone' => 'required|string|max:20',
        'specialite' => 'required_if:role,medecin|nullable|string|max:255',
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

    // Découpage du nom (Prénom Nom)
    $nameParts = explode(' ', $user->name, 2);
    $fName = $nameParts[1] ?? $nameParts[0]; // On essaie de prendre le 2ème mot comme prénom
    $lName = $nameParts[0];

    // 2. AUTOMATISATION DES PROFILS
    if ($request->role === 'medecin') {
        Doctor::create([
            'user_id'    => $user->id,
            'first_name' => $fName,
            'last_name'  => $lName,
            'specialty'  => $request->specialite,
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
            'clinic_id'  => $request->clinic_id ?? 1, // On lie par défaut à la clinique 1
        ]);
    }

    event(new Registered($user));
    Auth::login($user);

    return redirect(route('dashboard'));
}
}