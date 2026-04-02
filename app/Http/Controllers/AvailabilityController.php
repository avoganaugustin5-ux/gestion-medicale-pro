<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ImprevuMedecinNotification;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; 

class AvailabilityController extends Controller
{
    public function index()
{
    $availabilities = Availability::where('user_id', Auth::id())
        ->get()
        ->map(function ($event) {
            // S'assurer que la date est un objet Carbon (grâce au cast dans le modèle)
            $dateStr = is_string($event->date) ? $event->date : $event->date->format('Y-m-d');
            
            // On nettoie le format de l'heure (parfois SQL renvoie 07:00:00)
            $startTime = date('H:i:s', strtotime($event->start_time));
            $endTime = date('H:i:s', strtotime($event->end_time));

            return [
                'id' => $event->id,
                'title' => $event->status === 'cancelled' ? '❌ ANNULÉ' : ($event->is_booked ? '📅 RDV Occupé' : '✅ Disponible'),
                // Format ISO8601 strict : YYYY-MM-DDTHH:mm:ss
                'start' => $dateStr . 'T' . $startTime,
                'end' => $dateStr . 'T' . $endTime,
                'backgroundColor' => $event->status === 'cancelled' ? '#ef4444' : ($event->is_booked ? '#3b82f6' : '#10b981'),
                'borderColor' => 'transparent',
                'allDay' => false, // Crucial pour l'affichage dans la grille horaire
                'extendedProps' => [
                    'status' => $event->status,
                    'note' => $event->note,
                    'is_booked' => (bool)$event->is_booked
                ]
            ];
        });

    return Inertia::render('Doctor/Schedule', [
        'events' => $availabilities
    ]);
}

    public function store(Request $request)
{
    // Validation des données entrantes
    $request->validate([
        'slots' => 'required|array',
        'slots.*.date' => 'required|date',
        'slots.*.start_time' => 'required',
        'slots.*.end_time' => 'required',
    ]);

    $user = Auth::user();
    $doctor = $user->doctor; 

    if (!$doctor) {
        return back()->with('error', 'Profil docteur introuvable.');
    }

    foreach ($request->slots as $slot) {
        Availability::create([
            'user_id'    => $user->id,
            'clinic_id'  => $user->clinic_id ?? $doctor->clinic_id,
            'date'       => $slot['date'],
            'start_time' => $slot['start_time'],
            'end_time'   => $slot['end_time'],
            'status'     => 'available',
        ]);
    }

    return back()->with('message', 'Créneau enregistré avec succès.');
}

    public function update(Request $request, Availability $availability)
    {
        $request->validate([
            'status' => 'required|in:available,cancelled',
            'note' => 'nullable|string'
        ]);

        $availability->update([
            'status' => $request->status,
            'note' => $request->note
        ]);

        if ($request->status === 'cancelled') {
            $doctorUser = Auth::user();
            $secretaries = $doctorUser->secretaries; 

            if ($secretaries && $secretaries->count() > 0) {
                Notification::send($secretaries, new ImprevuMedecinNotification($availability, $doctorUser));
            }
        }

        return back()->with('message', 'Statut mis à jour.');
    }

    public function destroy(Availability $availability)
    {
        if ($availability->is_booked) {
            return back()->with('error', 'Impossible de supprimer un créneau réservé.');
        }

        $availability->delete();
        return back()->with('message', 'Créneau supprimé.');
    }

    public function exportPdf()
    {
        $availabilities = Availability::where('user_id', Auth::id())
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $data = [
            'doctor' => Auth::user(),
            'availabilities' => $availabilities,
            'date_gen' => now()->format('d/m/Y H:i'),
            'university' => 'Université Thomas SANKARA (UTS)'
        ];

        $pdf = Pdf::loadView('pdf.schedule', $data);
        return $pdf->download('Planning_AKASUTS_' . Auth::user()->name . '.pdf');
    }
}