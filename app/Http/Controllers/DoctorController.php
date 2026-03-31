<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Availability;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DoctorController extends Controller
{
    /**
     * PARTIE ADMIN : Gérer les médecins d'une clinique
     */
    public function index(Clinic $clinic)
    {
        return Inertia::render('Clinics/Doctors/Index', [
            'clinic' => $clinic,
            'doctors' => $clinic->doctors()->with('service')->get(),
            'services' => Service::all(),
        ]);
    }

    public function store(Request $request, Clinic $clinic)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
        ]);

        $doctor = $clinic->doctors()->create($validated);

        // Audit Log pour l'admin
        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'action' => 'Ajout Médecin',
            'target' => $validated['first_name'] . ' ' . $validated['last_name'],
            'clinic_name' => $clinic->name
        ]);

        return back()->with('message', 'Médecin ajouté avec succès !');
    }

    public function destroy(Clinic $clinic, Doctor $doctor)
    {
        $doctor->delete();
        return back()->with('message', 'Le médecin a été retiré avec succès.');
    }

    /**
     * PARTIE MÉDECIN : Gérer ses propres disponibilités (Cahier des charges)
     */
    public function myPlanning()
    {
        $availabilities = Availability::where('user_id', Auth::id())
            ->orderBy('date', 'asc')
            ->get();

        return Inertia::render('Doctor/Planning', [
            'availabilities' => $availabilities
        ]);
    }

    public function storeAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        Availability::create([
            'user_id' => Auth::id(),
            'clinic_id' => Auth::user()->clinic_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        // Audit Log pour le médecin
        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'action' => 'Disponibilité',
            'target' => 'Créneau le ' . $request->date,
            'clinic_name' => Auth::user()->clinic?->name ?? 'N/A'
        ]);

        return back()->with('success', 'Votre créneau de disponibilité a été ajouté !');
    }

    public function destroyAvailability(Availability $availability)
    {
        if ($availability->user_id !== Auth::id()) {
            abort(403);
        }

        $availability->delete();
        return back()->with('success', 'Créneau supprimé.');
    }
}