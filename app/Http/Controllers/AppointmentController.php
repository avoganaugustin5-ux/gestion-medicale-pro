<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index(Clinic $clinic)
    {
        return Inertia::render('Clinics/Appointments/Index', [
            'clinic' => $clinic,
            'appointments' => $clinic->appointments()
                ->with(['doctor.user', 'patient.user', 'service'])
                ->latest()
                ->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clinics/Appointments/Create', [
            'clinics' => Clinic::all(),
            'services' => Service::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ]);

        $patient = Auth::user()->patient;

        Appointment::create([
            'clinic_id' => $request->clinic_id,
            'service_id' => $request->service_id,
            'doctor_id' => $request->doctor_id,
            'patient_id' => $patient->id,
            'appointment_date' => $request->appointment_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard')->with('success', 'Votre demande de rendez-vous a été envoyée à la secrétaire.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'cancel_reason' => 'nullable|string'
        ]);

        $appointment->update([
            'status' => $request->status,
            'cancel_reason' => $request->cancel_reason,
            'secretary_id' => Auth::user()->secretary?->id
        ]);

        $msg = $request->status === 'confirmed' ? 'Rendez-vous confirmé.' : 'Rendez-vous refusé.';
        return redirect()->back()->with('success', $msg);
    }

    public function downloadTicket(Appointment $appointment)
    {
        if ($appointment->status !== 'confirmed') {
            abort(403, 'Ticket non disponible.');
        }

        $pdf = Pdf::loadView('pdf.appointment-ticket', compact('appointment'));
        return $pdf->download("Ticket-RDV-{$appointment->id}.pdf");
    }
}