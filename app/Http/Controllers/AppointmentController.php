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
    /**
     * Liste des rendez-vous pour une clinique spécifique.
     */
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

    /**
     * Formulaire de création de rendez-vous (Vue Patient).
     */
    public function create()
    {
        return Inertia::render('Clinics/Appointments/Create', [
            'clinics' => Clinic::all(),
            'services' => Service::with('doctors.user')->get(),
            'doctors' => Doctor::with(['user:id,name', 'service'])->get(),
        ]);
    }

    /**
     * API interne pour filtrer les médecins par clinique et service.
     */
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

    /**
     * Enregistrement du rendez-vous.
     */
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
     * Mise à jour du statut par la secrétaire ou le médecin.
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        // Correction de la validation : assure-toi que le front envoie 'confirmed' ou 'cancelled'
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'cancel_reason' => 'nullable|string'
        ]);

        // Mise à jour sécurisée
        $appointment->update([
            'status' => $request->status,
            'cancel_reason' => $request->cancel_reason,
            'secretary_id' => Auth::user()->secretary?->id // Enregistre qui a fait l'action
        ]);

        $msg = $request->status === 'confirmed' ? 'Rendez-vous validé avec succès !' : 'Statut mis à jour.';
        
        return redirect()->back()->with('success', $msg);
    }

    /**
     * Génération du ticket PDF.
     */
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