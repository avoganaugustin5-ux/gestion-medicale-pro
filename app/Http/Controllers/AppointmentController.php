<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Doctor;
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
            'services' => Service::with('doctors.user')->get(),
            'doctors' => Doctor::with(['user:id,name', 'service'])->get(),
        ]);
    }

    public function getDoctorsByCriteria(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'service_id' => 'required|exists:services,id',
        ]);

        $doctors = Doctor::where('clinic_id', $request->clinic_id)
            ->where('service_id', $request->service_id)
            ->with('user:id,name')
            ->get();

        return response()->json($doctors);
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

        if (!$patient) {
            return redirect()->back()->with('error', 'Profil patient non trouvé.');
        }

        Appointment::create([
            'clinic_id' => $request->clinic_id,
            'service_id' => $request->service_id,
            'doctor_id' => $request->doctor_id,
            'patient_id' => $patient->id,
            'appointment_date' => $request->appointment_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard')->with('success', 'Votre demande de rendez-vous a été envoyée.');
    }

    /**
     * Correction ici : Ajout du paramètre Clinic pour correspondre à la route
     */
    public function updateStatus(Request $request, Clinic $clinic, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'cancel_reason' => 'nullable|string'
        ]);

        $appointment->update([
            'status' => $request->status,
            'cancel_reason' => $request->cancel_reason,
            'updated_by' => Auth::id() 
        ]);

        $msg = $request->status === 'confirmed' ? 'Rendez-vous validé ! Le patient peut maintenant voir sa confirmation.' : 'Statut mis à jour.';
        
        return redirect()->back()->with('success', $msg);
    }

    public function downloadTicket(Appointment $appointment)
    {
        $appointment->load(['doctor.user', 'patient.user', 'service', 'clinic']);

        if ($appointment->status !== 'confirmed') {
            abort(403, 'Le ticket est réservé aux rendez-vous confirmés.');
        }

        $pdf = Pdf::loadView('pdf.appointment-ticket', compact('appointment'));
        return $pdf->download("Ticket-RDV-{$appointment->id}.pdf");
    }
}