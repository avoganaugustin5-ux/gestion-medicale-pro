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
            'appointments' => $clinic->appointments()
                ->with(['doctor', 'patient'])
                ->latest()
                ->get(),
            'doctors' => $clinic->doctors()->select('id', 'last_name', 'first_name', 'specialty')->get(),
            'patients' => $clinic->patients()->select('id', 'last_name', 'first_name')->get(),
        ]);
    }

    public function store(Request $request, Clinic $clinic)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ]);

        $clinic->appointments()->create(array_merge($request->all(), ['status' => 'pending']));

        return redirect()->back()->with('success', 'Rendez-vous enregistré.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        $appointment->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Statut du rendez-vous mis à jour.');
    }
}