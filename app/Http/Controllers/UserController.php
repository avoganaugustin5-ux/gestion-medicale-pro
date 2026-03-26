<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Users', [
            'users' => \App\Models\User::all(),
            'clinics' => \App\Models\Clinic::all() // On envoie les cliniques
        ]);
    }

    public function updateClinic(Request $request, \App\Models\User $user)
    {
        $user->update(['clinic_id' => $request->clinic_id]);
        return redirect()->back();
    }

    public function updateRole(Request $request, User $user)
    {
        $user->update(['role' => $request->role]);
        return redirect()->back()->with('success', 'Rôle mis à jour !');
    }
}