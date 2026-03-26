<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoctorController extends Controller
{
    // Liste des médecins d'une clinique spécifique
    public function index(Clinic $clinic)
    {
        return Inertia::render('Clinics/Doctors/Index', [
            'clinic' => $clinic,
            'doctors' => $clinic->doctors()->with('service')->get(), // On charge le service lié
            'services' => \App\Models\Service::all(), // On envoie tous les services dispo
        ]);
    }

    // Enregistrement d'un nouveau médecin
    public function store(Request $request, Clinic $clinic)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id', // On vérifie que le service existe
        ]);

        // On crée le médecin lié à cette clinique
        $clinic->doctors()->create($validated);

        return back()->with('message', 'Médecin ajouté avec succès !');
    }

    public function destroy(Clinic $clinic, Doctor $doctor)
    {
        // On vérifie que le médecin appartient bien à cette clinique avant de supprimer
        $doctor->delete();

        return back()->with('message', 'Le médecin a été retiré avec succès.');
    }
}