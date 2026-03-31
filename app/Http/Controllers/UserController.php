<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('clinics')->orderBy('created_at', 'desc')->get();
        $clinics = Clinic::select('id', 'name')->get();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'clinics' => $clinics,
            'filters' => request()->all(['search'])
        ]);
    }

    // LIGNE 25 CORRIGÉE ICI : Ajout du $ devant user
    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|string|in:admin,medecin,secretaire,patient']);
        $user->update(['role' => $request->role]);
        return back();
    }

    public function assignments()
    {
        // 1. Pour l'affectation Clinique
        $staff = User::whereIn('role', ['medecin', 'secretaire'])->with('clinics')->get();
        $clinics = Clinic::select('id', 'name')->get();

        // 2. Pour l'affectation Secrétaire <-> Médecin
        $doctors = User::where('role', 'medecin')->get();
        $secretaries = User::where('role', 'secretaire')->get();

        // 3. Liste des affectations secrétaires actuelles
        $currentAssignments = DB::table('doctor_secretary')
            ->join('users as doctors', 'doctor_secretary.doctor_id', '=', 'doctors.id')
            ->join('users as secretaries', 'doctor_secretary.secretary_id', '=', 'secretaries.id')
            ->select(
                'doctor_secretary.id',
                'doctors.name as doctor_name',
                'secretaries.name as secretary_name'
            )
            ->get();

        return Inertia::render('Admin/Assignments', [
            'staff' => $staff,
            'clinics' => $clinics,
            'doctors' => $doctors,
            'secretaries' => $secretaries,
            'currentAssignments' => $currentAssignments
        ]);
    }

    public function storeClinicAssignment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'clinic_id' => 'required|exists:clinics,id',
        ]);
        $user = User::findOrFail($request->user_id);
        $user->clinics()->sync([$request->clinic_id]);
        return back()->with('message', 'Affectation clinique réussie.');
    }

    public function storeSecretaryAssignment(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'secretary_id' => 'required|exists:users,id',
        ]);

        DB::table('doctor_secretary')->updateOrInsert(
            ['doctor_id' => $request->doctor_id],
            ['secretary_id' => $request->secretary_id, 'created_at' => now()]
        );

        return back()->with('message', 'Secrétaire affectée au médecin.');
    }

    // LIGNE 81 CORRIGÉE ICI : Ajout du $ devant user
    public function detachClinic(User $user)
    {
        $user->clinics()->detach();
        return back()->with('message', 'Agent détaché de la clinique.');
    }

    public function detachSecretary($id)
    {
        DB::table('doctor_secretary')->where('id', $id)->delete();
        return back()->with('message', 'Liaison médecin-secrétaire supprimée.');
    }
}