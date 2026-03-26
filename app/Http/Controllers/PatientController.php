<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PatientController extends Controller
{
    // Liste des patients d'une clinique
    public function index(Clinic $clinic)
    {
        return Inertia::render('Clinics/Patients/Index', [
            'clinic' => $clinic,
            'patients' => $clinic->patients()->latest()->get()
        ]);
    }

    // Enregistrer un nouveau patient
    public function store(Request $request, Clinic $clinic)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $clinic->patients()->create($request->all());

        return redirect()->back()->with('success', 'Patient enregistré avec succès.');
    }
}