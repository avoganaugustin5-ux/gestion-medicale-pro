<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs et des cliniques pour l'administration.
     */
    public function index()
    {
        // Récupération de tous les utilisateurs avec leurs cliniques (Eager Loading)
        $users = User::with('clinics')->orderBy('created_at', 'desc')->get();
        
        // CORRECTION ICI : Utilisation de 'name' au lieu de 'nom' pour correspondre à ta migration
        $clinics = Clinic::select('id', 'name')->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'clinics' => $clinics
        ]);
    }

    /**
     * Met à jour le rôle d'un utilisateur.
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|in:admin,medecin,secretaire,patient',
        ]);

        $user->update(['role' => $request->role]);

        return back()->with('message', 'Rôle mis à jour avec succès.');
    }

    /**
     * Affiche la page des affectations (Personnel <-> Clinique).
     */
    public function assignments()
    {
        $users = User::whereIn('role', ['medecin', 'secretaire'])->with('clinics')->get();
        $clinics = Clinic::select('id', 'name')->get(); // Correction 'name' ici aussi

        return Inertia::render('Admin/Assignments/Index', [
            'users' => $users,
            'clinics' => $clinics
        ]);
    }

    /**
     * Lie un utilisateur (médecin/secrétaire) à une clinique.
     */
    public function storeAssignment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        $user = User::findOrFail($request->user_id);
        
        // syncWithoutDetaching évite les doublons dans la table pivot
        $user->clinics()->syncWithoutDetaching([$request->clinic_id]);

        return back()->with('message', 'Affectation réussie.');
    }

    /**
     * Retire un utilisateur d'une clinique.
     */
    public function detachAssignment(Request $request, User $user)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        $user->clinics()->detach($request->clinic_id);

        return back()->with('message', 'Affectation retirée.');
    }
}