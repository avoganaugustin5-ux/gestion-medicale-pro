<?php

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\PatientAppointmentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan; // <-- Import nécessaire pour la route de secours
use Inertia\Inertia;

// 1. PUBLIC : Redirection automatique vers le login
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. ROUTES PROTÉGÉES (Connexion obligatoire)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard global géré par le ClinicController
    Route::get('/dashboard', [ClinicController::class, 'index'])->name('dashboard');

    // --- SECTION ADMINISTRATEUR ---
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/clinics/create', [ClinicController::class, 'create'])->name('clinics.create');
        Route::post('/clinics', [ClinicController::class, 'store'])->name('clinics.store');
        
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::patch('/admin/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.updateRole');
        
        Route::resource('services', ServiceController::class);
    });

    // --- SECTION MÉDECIN & SECRÉTAIRE ---
    Route::middleware(['role:medecin,secretaire,admin'])->group(function () {
        Route::prefix('clinics/{clinic}')->group(function () {
            Route::get('/', [ClinicController::class, 'show'])->name('clinics.show');
            Route::get('/edit', [ClinicController::class, 'edit'])->name('clinics.edit');
            Route::patch('/', [ClinicController::class, 'update'])->name('clinics.update');
            Route::delete('/', [ClinicController::class, 'destroy'])->name('clinics.destroy');

            Route::get('/secretary', [SecretaryController::class, 'index'])->name('secretary.index');

            Route::resource('doctors', DoctorController::class)->names('clinics.doctors');
            Route::resource('patients', PatientController::class)->names('clinics.patients');
            Route::resource('appointments', AppointmentController::class)->names('clinics.appointments');
        });

        Route::put('/appointments/{appointment}/status', [SecretaryController::class, 'updateStatus'])
            ->name('appointments.updateStatus');
        
        Route::patch('/appointments/{appointment}/validate', [AppointmentController::class, 'validateStatus'])
            ->name('appointments.validate');
    });

    // --- SECTION PATIENT ---
    Route::middleware(['role:patient,admin'])->group(function () {
        Route::get('/appointments/create', [PatientAppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [PatientAppointmentController::class, 'store'])->name('appointments.store');
    });

    // --- SECTION PROFIL ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- ROUTE DE SECOURS (A supprimer après utilisation) ---
// Cette route permet de remplir la base Aiven directement depuis le navigateur
Route::get('/force-seed-uts-2026', function () {
    try {
        Artisan::call('db:seed', ['--force' => true]);
        return "Succès : La base Aiven a été initialisée avec l'admin Augustin et la clinique démo !";
    } catch (\Exception $e) {
        return "Erreur lors du seeding : " . $e->getMessage();
    }
});

require __DIR__.'/auth.php';