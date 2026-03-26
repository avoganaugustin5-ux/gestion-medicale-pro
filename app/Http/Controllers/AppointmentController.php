<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index(Clinic $clinic)
    {
        return Inertia::render('Clinics/Appointments/Index', [
            'clinic' => $clinic,
            // On récupère les RDV avec les noms des docteurs et patients
            'appointments' => $clinic->appointments()
                ->with(['doctor', 'patient'])
                ->latest()
                ->get(),
            // On envoie la liste pour le formulaire
            'doctors' => $clinic->doctors()->select('id', 'last_name', 'first_name', 'specialty')->get(),
            'patients' => $clinic->patients()->select('id', 'last_name', 'first_name')->get(),
        ]);
    }

    public function store(Request $request, Clinic $clinic)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date|after:now', // Empêche les dates passées
            'reason' => 'nullable|string|max:500',
        ]);

        $clinic->appointments()->create($request->all());

        return redirect()->back()->with('success', 'Rendez-vous enregistré et en attente de validation.');
    }

    public function validateStatus(Appointment $appointment)
    {
        $appointment->update(['status' => 'confirmed']);
        return redirect()->back();
    }
}