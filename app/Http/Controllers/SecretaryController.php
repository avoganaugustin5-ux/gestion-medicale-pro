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
     * Affiche les rendez-vous d'une clinique spécifique pour la secrétaire.
     */
    public function index(Clinic $clinic)
    {
        // Vérification de sécurité : La secrétaire appartient-elle à cette clinique ?
        // (Optionnel selon ta logique d'affectation)

        return Inertia::render('Secretary/Dashboard', [
            'clinic' => $clinic,
            'appointments' => Appointment::where('clinic_id', $clinic->id)
                ->with(['patient.user', 'doctor.user', 'service']) 
                ->orderBy('appointment_date', 'asc')
                ->get(),
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

        // 2. SÉCURITÉ (Vérifier que la secrétaire travaille bien pour la clinique du RDV)
        // Note : Si tu n'as pas encore de relation 'clinic_id' sur l'utilisateur, 
        // tu peux ignorer cette vérification pour le moment.
        if (Auth::user()->role === 'secretaire' && Auth::user()->clinic_id !== $appointment->clinic_id) {
            // Optionnel : Décommenter si tu as configuré les clinic_id sur les users
            // return redirect()->back()->with('error', 'Action non autorisée pour cet établissement.');
        }

        // 3. MISE À JOUR DU RENDEZ-VOUS
        $appointment->update([
            'status' => $request->status,
            // On enregistre le motif uniquement si le statut est 'cancelled'
            'cancel_reason' => $request->status === 'cancelled' ? $request->cancel_reason : null,
            'secretary_id' => Auth::id(),
        ]);

        // 4. ENVOI DE LA NOTIFICATION PAR EMAIL
        // On récupère l'utilisateur lié au patient pour lui envoyer la notification
        if ($appointment->patient && $appointment->patient->user) {
            $appointment->patient->user->notify(new AppointmentStatusUpdated($appointment));
        }

        // 5. RETOUR AVEC MESSAGE FLASH
        return redirect()->back()->with('success', 'Le statut a été mis à jour et le patient a été averti par email.');
    }
}