<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Service;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PatientAppointmentController extends Controller
{
    public function create()
    {
        $patient = Patient::where('user_id', Auth::id())->first();
        
        $hasPendingAppointment = Appointment::where('patient_id', $patient->id ?? 0)
            ->where('status', 'pending')
            ->exists();

        return Inertia::render('Patient/Appointments/Create', [
            'clinics' => Clinic::all(),
            'services' => Service::all(),
            'hasPendingAppointment' => $hasPendingAppointment,
        ]);
    }

    public function store(Request $request)
    {
        $patient = Patient::where('user_id', Auth::id())->first();

        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:today',
            'reason' => 'nullable|string|max:500',
        ]);

        // Protection : pas de double demande pending
        if (Appointment::where('patient_id', $patient->id)->where('status', 'pending')->exists()) {
            return redirect()->back()->with('error', 'Vous avez déjà une demande en cours.');
        }

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $validated['doctor_id'],
            'clinic_id' => $validated['clinic_id'],
            'service_id' => $validated['service_id'],
            'appointment_date' => $validated['appointment_date'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard')->with('success', 'Votre demande a été envoyée !');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,cancelled',
            'cancel_reason' => 'nullable|string|max:255'
        ]);

        $appointment->update([
            'status' => $validated['status'],
            'cancel_reason' => $validated['cancel_reason'] ?? null
        ]);

        return redirect()->back()->with('success', 'Statut du rendez-vous mis à jour.');
    }
}