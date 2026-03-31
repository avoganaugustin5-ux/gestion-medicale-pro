<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        return Inertia::render('Admin/Users', [
            'users' => User::query()
                ->with('clinic') // Important pour afficher le nom de la clinique
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                })
                ->latest()
                ->get(),
            'clinics' => Clinic::all(['id', 'nom']), // On ne prend que le nécessaire
            'filters' => $request->only(['search'])
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,medecin,secretaire,patient',
        ]);

        $user->update(['role' => $request->role]);
        return Redirect::back()->with('success', 'Le rôle de ' . $user->name . ' a été mis à jour.');
    }

    public function assignments(Request $request)
    {
        $search = $request->input('search');

        return Inertia::render('Admin/Assignments', [
            'staff' => User::whereIn('role', ['medecin', 'secretaire'])
                ->with('clinic')
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                })
                ->get(),
            'clinics' => Clinic::all(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->update(['clinic_id' => $validated['clinic_id']]);

        return Redirect::back()->with('success', "Affectation réussie pour " . $user->name);
    }

    public function detachAssignment(User $user)
    {
        $user->update(['clinic_id' => null]);
        return Redirect::back()->with('success', "L'agent a été détaché avec succès.");
    }
}