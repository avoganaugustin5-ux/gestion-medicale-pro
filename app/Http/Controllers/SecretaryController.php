<?php

namespace App\Http\Controllers;

use App\Notifications\AppointmentStatusUpdated;
use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SecretaryController extends Controller
{
    /**
     * Affiche les rendez-vous des médecins affectés à la secrétaire connectée.
     */
    public function index(Clinic $clinic)
    {
        // 1. Récupérer l'ID de l'utilisateur connecté (le compte de la secrétaire)
        $authUserId = Auth::id();

        // 2. Récupérer les IDs des COMPTES UTILISATEURS des médecins liés à cette secrétaire
        // On interroge directement la table pivot pour éviter tout conflit d'ID de profil
        $assignedDoctorUserIds = DB::table('doctor_secretary')
            ->where('secretary_id', $authUserId)
            ->pluck('doctor_id')
            ->toArray();

        // 3. Récupérer les rendez-vous filtrés
        // On vérifie que le doctor_id (qui est un user_id dans ta table appointments) 
        // figure bien dans la liste des médecins autorisés.
        $appointments = Appointment::where('clinic_id', $clinic->id)
            ->whereIn('doctor_id', $assignedDoctorUserIds)
            ->with(['patient.user', 'doctor.user', 'service']) 
            ->orderBy('appointment_date', 'asc')
            ->get();

        return Inertia::render('Secretary/Dashboard', [
            'clinic' => $clinic,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Mise à jour du statut d'un rendez-vous.
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'cancel_reason' => 'nullable|string|max:500', 
        ]);

        // Sécurité : Vérifier l'appartenance avant modification
        $assignedDoctorUserIds = DB::table('doctor_secretary')
            ->where('secretary_id', Auth::id())
            ->pluck('doctor_id')
            ->toArray();
        
        if (!in_array($appointment->doctor_id, $assignedDoctorUserIds)) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $appointment->update([
            'status' => $request->status,
            'cancel_reason' => $request->status === 'cancelled' ? $request->cancel_reason : null,
            'secretary_id' => Auth::id(),
        ]);

        if ($appointment->patient && $appointment->patient->user) {
            $appointment->patient->user->notify(new AppointmentStatusUpdated($appointment));
        }

        return redirect()->back()->with('success', 'Statut mis à jour avec succès.');
    }
}