<?php

namespace App\Http\Controllers;

use App\Notifications\AppointmentStatusUpdated;
use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SecretaryController extends Controller
{
    /**
     * Affiche les rendez-vous des médecins affectés à la secrétaire.
     */
    public function index(Clinic $clinic)
    {
        // 1. Récupérer les IDs des médecins affectés à cette secrétaire
        $assignedDoctorIds = Auth::user()->doctors()->pluck('doctor_id');

        // 2. Filtrer les rendez-vous : 
        // Doivent appartenir à la clinique ET aux médecins affectés
        $appointments = Appointment::where('clinic_id', $clinic->id)
            ->whereIn('doctor_id', $assignedDoctorIds)
            ->with(['patient.user', 'doctor.user', 'service']) 
            ->orderBy('appointment_date', 'asc')
            ->get();

        return Inertia::render('Secretary/Dashboard', [
            'clinic' => $clinic,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Mise à jour du statut d'un rendez-vous (Validation/Refus).
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        // 1. VALIDATION DES DONNÉES ENTRANTES
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'cancel_reason' => 'nullable|string|max:500', 
        ]);

        // 2. SÉCURITÉ (Vérifier que le médecin du RDV est bien lié à cette secrétaire)
        $assignedDoctorIds = Auth::user()->doctors()->pluck('doctor_id');
        
        if (! $assignedDoctorIds->contains($appointment->doctor_id)) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisée à gérer les rendez-vous de ce médecin.');
        }

        // 3. MISE À JOUR DU RENDEZ-VOUS
        $appointment->update([
            'status' => $request->status,
            'cancel_reason' => $request->status === 'cancelled' ? $request->cancel_reason : null,
            'secretary_id' => Auth::id(),
        ]);

        // 4. ENVOI DE LA NOTIFICATION PAR EMAIL
        if ($appointment->patient && $appointment->patient->user) {
            $appointment->patient->user->notify(new AppointmentStatusUpdated($appointment));
        }

        // 5. RETOUR AVEC MESSAGE FLASH
        return redirect()->back()->with('success', 'Le statut a été mis à jour et le patient a été averti.');
    }
}