<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');

        // 1. Récupérer les cliniques avec filtre de recherche
        $clinics = Clinic::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        })->get();

        // 2. Récupérer les rendez-vous (Seulement ceux du patient connecté)
        $appointments = [];
        if ($user->role === 'patient') {
            $appointments = Appointment::where('user_id', $user->id)
                ->with(['doctor', 'clinic']) 
                ->orderBy('date_rdv', 'asc')
                ->get();
        }

        return Inertia::render('Dashboard', [
            'clinics' => $clinics,
            'appointments' => $appointments,
            'filters' => ['search' => $search]
        ]);
    }
}