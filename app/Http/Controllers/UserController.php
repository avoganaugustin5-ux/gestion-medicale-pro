<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        return Inertia::render('Admin/Users', [
            'users' => User::query()
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                })
                ->get(),
            'clinics' => Clinic::all(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function updateClinic(Request $request, User $user)
    {
        $user->update(['clinic_id' => $request->clinic_id]);
        return redirect()->back();
    }

    public function updateRole(Request $request, User $user)
    {
        $user->update(['role' => $request->role]);
        return redirect()->back()->with('success', 'Rôle mis à jour !');
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
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        User::where('id', $request->user_id)->update(['clinic_id' => $request->clinic_id]);
        return redirect()->back()->with('success', "L'affectation a été effectuée.");
    }

    public function detachAssignment(User $user)
    {
        $user->update(['clinic_id' => null]);
        return redirect()->back()->with('success', "L'agent a été détaché de sa clinique.");
    }
}