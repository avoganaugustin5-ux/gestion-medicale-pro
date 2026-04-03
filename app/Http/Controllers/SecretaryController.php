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
    public function index(Clinic $clinic)
    {
        $authUserId = Auth::id();

        // 1. Récupérer les médecins liés à cette secrétaire
        $assignedDoctorUserIds = DB::table('doctor_secretary')
            ->where('secretary_id', $authUserId)
            ->pluck('doctor_id')
            ->toArray();

        // 2. Filtrer les rendez-vous
        $appointments = Appointment::where('clinic_id', $clinic->id)
            ->whereIn('doctor_id', $assignedDoctorUserIds)
            ->with(['patient.user', 'doctor.user', 'service']) 
            ->orderBy('appointment_date', 'asc')
            ->get();

        // 3. Calculer les statistiques UNIQUEMENT pour ses médecins
        $todayCount = Appointment::where('clinic_id', $clinic->id)
            ->whereIn('doctor_id', $assignedDoctorUserIds)
            ->whereDate('appointment_date', today())
            ->count();

        return Inertia::render('Secretary/Dashboard', [
            'clinic' => $clinic,
            'appointments' => $appointments,
            // Ces stats écraseront les fausses valeurs sur le dashboard
            'stats' => [
                'today' => $todayCount,
                'patients' => $appointments->unique('patient_id')->count(),
                'interactions' => $appointments->count(),
            ]
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'cancel_reason' => 'nullable|string|max:500', 
        ]);

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

        return redirect()->back()->with('success', 'Statut mis à jour.');
    }
}