<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\User;
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
                return [
                    'id' => $event->id,
                    'title' => $event->status === 'cancelled' ? '❌ ANNULÉ' : ($event->is_booked ? '📅 RDV Occupé' : '✅ Disponible'),
                    'start' => $event->date . 'T' . $event->start_time,
                    'end' => $event->date . 'T' . $event->end_time,
                    'backgroundColor' => $event->status === 'cancelled' ? '#ef4444' : ($event->is_booked ? '#3b82f6' : '#10b981'),
                    'borderColor' => 'transparent',
                    'extendedProps' => [
                        'status' => $event->status,
                        'note' => $event->note,
                        'is_booked' => $event->is_booked
                    ]
                ];
            });

        return Inertia::render('Doctor/Schedule', [
            'events' => $availabilities
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'slots' => 'required|array',
            'slots.*.date' => 'required|date',
            'slots.*.start_time' => 'required',
            'slots.*.end_time' => 'required',
        ]);

        $user = Auth::user();

        foreach ($request->slots as $slot) {
            Availability::create([
                'user_id' => $user->id,
                'clinic_id' => $user->clinic_id,
                'date' => $slot['date'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'status' => 'available',
            ]);
        }

        return back()->with('message', 'Planning de la semaine enregistré.');
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
            $doctor = Auth::user();
            // On s'assure que la relation 'secretaries' existe dans le modèle User
            $secretaries = $doctor->secretaries; 

            if ($secretaries && $secretaries->count() > 0) {
                Notification::send($secretaries, new ImprevuMedecinNotification($availability, $doctor));
            }
        }

        return back()->with('message', 'Statut mis à jour et secrétaire notifiée.');
    }

    public function destroy(Availability $availability)
    {
        if ($availability->is_booked) {
            return back()->with('error', 'Impossible de supprimer un créneau ayant déjà un rendez-vous.');
        }

        $availability->delete();
        return back()->with('message', 'Créneau supprimé.');
    }

    public function exportPdf()
    {
        // On récupère les créneaux de la semaine en cours
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

        // On charge la vue 'pdf.schedule' que nous créerons ensemble
        $pdf = Pdf::loadView('pdf.schedule', $data);

        return $pdf->download('Planning_AKASUTS_' . Auth::user()->last_name . '.pdf');
    }
}