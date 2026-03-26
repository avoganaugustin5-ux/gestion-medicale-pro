<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Service;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PatientAppointmentController extends Controller
{
    public function create()
    {
        return Inertia::render('Patient/Appointments/Create', [
            'clinics' => Clinic::all(),
            'services' => Service::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date_rdv' => 'required|date|after:today',
            'motif' => 'nullable|string|max:500',
        ]);

        Appointment::create([
            'patient_id' => Auth::user()->id, // L'ID du patient connecté
            'doctor_id' => $validated['doctor_id'],
            'clinic_id' => $validated['clinic_id'],
            'date_rdv' => $validated['date_rdv'],
            'status' => 'en_attente', // Statut par défaut avant validation secrétaire
        ]);

        return redirect()->route('dashboard')->with('message', 'Votre demande de rendez-vous a été envoyée !');
    }

    // Méthode API pour charger les médecins selon le service choisi
    public function getDoctorsByService(Service $service, Clinic $clinic)
    {
        return response()->json(
            Doctor::where('service_id', $service->id)
                  ->where('clinic_id', $clinic->id)
                  ->get()
        );
    }

    // Ajoute cette méthode à la fin de ton fichier PatientAppointmentController.php

public function updateStatus(Request $request, Appointment $appointment)
{
    // Sécurité : Seule la secrétaire ou l'admin de la clinique peut valider
    $this->authorize('update', $appointment); // Si tu as des Policies, sinon utilise un if simple

    $validated = $request->validate([
        'status' => 'required|in:confirmed,cancelled',
    ]);

    $appointment->update([
        'status' => $validated['status']
    ]);

    $message = $validated['status'] === 'confirmed' 
        ? 'Le rendez-vous a été confirmé !' 
        : 'Le rendez-vous a été annulé.';

    return redirect()->back()->with('message', $message);
}

}