<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs (Gestion Utilisateurs)
     */
    public function index()
    {
        $users = User::with('clinics')->orderBy('created_at', 'desc')->get();
        
        // Correction 'name' pour la base de données
        $clinics = Clinic::select('id', 'name')->get();

        // Correction du chemin : pointe vers Admin/Users.vue
        return Inertia::render('Admin/Users', [
            'users' => $users,
            'clinics' => $clinics,
            'filters' => request()->all(['search'])
        ]);
    }

    /**
     * Affiche la page des affectations (Personnel & Affectations)
     */
    public function assignments()
    {
        // On récupère le personnel (médecins et secrétaires) avec leur clinique
        $staff = User::whereIn('role', ['medecin', 'secretaire'])->with('clinics')->get();
        $clinics = Clinic::select('id', 'name')->get();

        // Correction du chemin : pointe vers Admin/Assignments.vue
        // Note : On envoie 'staff' car c'est ce que ton script Vue attend en props
        return Inertia::render('Admin/Assignments', [
            'staff' => $staff,
            'clinics' => $clinics
        ]);
    }

    /**
     * Logique pour lier un agent à une clinique
     */
    public function storeAssignment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        $user = User::findOrFail($request->user_id);
        
        // Utilisation de sync pour mettre à jour l'affectation
        $user->clinics()->sync([$request->clinic_id]);

        return back()->with('message', 'Agent affecté avec succès.');
    }

    /**
     * Logique pour détacher un agent
     */
    public function detachAssignment(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->clinics()->detach();

        return back()->with('message', 'Agent détaché de la clinique.');
    }
}