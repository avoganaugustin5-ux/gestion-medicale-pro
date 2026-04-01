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
        // Sécurité : on vérifie l'appartenance ou le rôle admin
        if ($patient->clinic_id !== $clinic->id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Accès non autorisé à ce dossier patient.');
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

    /**
     * Générer le carnet de santé PDF.
     */
    public function downloadMedicalRecord(Patient $patient)
    {
        $patient->load(['consultations.doctor.user', 'user']);
        $pdf = Pdf::loadView('pdf.medical-record', compact('patient'));
        return $pdf->download("Carnet-Sante-{$patient->last_name}.pdf");
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