<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SecretaryController extends Controller
{
    /**
     * Cette méthode peut servir si tu décides d'avoir une page 
     * dédiée uniquement à la secrétaire (/clinics/{clinic}/secretary).
     */
    public function index(Clinic $clinic)
    {
        return Inertia::render('Secretary/Dashboard', [
            'clinic' => $clinic,
            'appointments' => Appointment::where('clinic_id', $clinic->id)
                ->with(['patient.user', 'doctor', 'service']) 
                ->orderBy('date_rdv', 'asc')
                ->get(),
        ]);
    }

    /**
     * MISE À JOUR DU STATUT
     * Cette méthode est celle appelée par ton SecretaryDashboard.vue
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        // 1. Validation : On s'aligne sur les valeurs envoyées par le JS (confirmed/cancelled)
        // Note : J'ai gardé 'pending' au cas où tu voudrais remettre en attente
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        // 2. Sécurité : On vérifie que la secrétaire ne modifie pas un RDV d'une autre clinique
        if (Auth::user()->role === 'secretaire' && $appointment->clinic_id !== Auth::user()->clinic_id) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        // 3. Mise à jour en base de données
        $appointment->update([
            'status' => $request->status
        ]);

        // 4. Message de retour pour le flash message du Dashboard
        $message = "Le rendez-vous a été " . ($request->status === 'confirmed' ? 'confirmé' : 'annulé') . " avec succès.";

        return redirect()->back()->with('message', $message);
    }
}