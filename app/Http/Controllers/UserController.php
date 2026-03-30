<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Clinic;

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

    

    public function assignments()
    {
        return Inertia::render('Admin/Assignments', [
            // On charge la relation clinic pour voir l'affectation actuelle
            'staff' => User::whereIn('role', ['medecin', 'secretaire'])
                        ->with('clinic') 
                        ->get(),
            'clinics' => Clinic::all(),
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        User::where('id', $request->user_id)->update(['clinic_id' => $request->clinic_id]);

        return redirect()->back()->with('success', "L'affectation a été effectuée.");
    }

    // NOUVELLE MÉTHODE : Détacher l'utilisateur
    public function detachAssignment(User $user)
    {
        $user->update(['clinic_id' => null]);

        return redirect()->back()->with('success', "L'agent a été détaché de sa clinique.");
    }
}