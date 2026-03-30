<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PatientController extends Controller
{
    /**
     * Liste des patients d'une clinique avec filtrage dynamique.
     */
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

    /**
     * Afficher le dossier complet d'un patient (Consultations + RDV).
     */
    public function show(Clinic $clinic, Patient $patient)
    {
        // Sécurité : on vérifie que le patient appartient bien à cette clinique
        if ($patient->clinic_id !== $clinic->id) {
            abort(403, 'Ce patient ne fait pas partie de cet établissement.');
        }

        return Inertia::render('Clinics/Patients/Show', [
            'clinic' => $clinic,
            'patient' => $patient,
            // On récupère les consultations avec les infos du médecin (table users)
            'consultations' => $patient->consultations()
                ->with('doctor.user:id,name') 
                ->latest()
                ->get(),
            // On récupère l'historique des rendez-vous
            'appointments' => $patient->appointments()
                ->latest('date_heure')
                ->get(),
        ]);
    }

    /**
     * Enregistrer un nouveau patient.
     */
    public function store(Request $request, Clinic $clinic)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // On force l'id de la clinique lors de la création
        $clinic->patients()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'clinic_id' => $clinic->id,
        ]);

        return redirect()->route('clinics.patients.index', $clinic->id)
            ->with('success', 'Patient enregistré avec succès.');
    }
}