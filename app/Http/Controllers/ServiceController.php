<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Doctor;

class ServiceController extends Controller
{
    public function index()
{
    return Inertia::render('Services/Index', [
        // On récupère les services avec leurs docteurs rattachés
        'services' => \App\Models\Service::with('doctors')->get(),
        // On récupère les docteurs qui n'ont PAS encore de service (service_id est NULL)
        'unassignedDoctors' => \App\Models\Doctor::whereNull('service_id')->get(),
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'categorie' => 'required|string|max:1', // 'A', 'B', 'C' selon ton schéma
        ]);

        Service::create($validated);

        return redirect()->back()->with('message', 'Service créé avec succès !');
    }

    public function attachDoctor(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'service_id' => 'required|exists:services,id',
        ]);

        $doctor = \App\Models\Doctor::findOrFail($request->doctor_id);
        $doctor->update(['service_id' => $request->service_id]);

        return redirect()->back()->with('message', 'Médecin rattaché au service avec succès !');
    }

}