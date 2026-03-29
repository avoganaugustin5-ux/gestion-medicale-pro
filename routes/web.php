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
use App\Http\Controllers\DashboardController; // <--- AJOUTÉ : Import du nouveau contrôleur
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. PUBLIC : Redirection automatique vers le login
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. ROUTES PROTÉGÉES (Connexion obligatoire)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- MODIFICATION PRO : Utilisation du DashboardController ---
    // C'est ici que la magie opère pour envoyer les bonnes données à ton Dashboard.vue
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- SECTION ADMINISTRATEUR UNIQUEMENT ---
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/clinics/create', [ClinicController::class, 'create'])->name('clinics.create');
        Route::post('/clinics', [ClinicController::class, 'store'])->name('clinics.store');
        
        // Administration des utilisateurs
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::patch('/admin/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.updateRole');
        
        // Services globaux
        Route::resource('services', ServiceController::class);
    });

    // --- SECTION MÉDECIN & SECRÉTAIRE ---
    Route::middleware(['role:medecin,secretaire,admin'])->group(function () {
        Route::prefix('clinics/{clinic}')->group(function () {
            // Note: On garde show ici pour voir les détails d'une clinique spécifique
            Route::get('/', [ClinicController::class, 'show'])->name('clinics.show');
            Route::get('/edit', [ClinicController::class, 'edit'])->name('clinics.edit');
            Route::patch('/', [ClinicController::class, 'update'])->name('clinics.update');
            Route::delete('/', [ClinicController::class, 'destroy'])->name('clinics.destroy');

            Route::get('/secretary', [SecretaryController::class, 'index'])->name('secretary.index');

            Route::resource('doctors', DoctorController::class)->names('clinics.doctors');
            Route::resource('patients', PatientController::class)->names('clinics.patients');
            Route::resource('appointments', AppointmentController::class)->names('clinics.appointments');
        });

        // ACTION SECRÉTAIRE : Validation des rendez-vous
        Route::put('/appointments/{appointment}/status', [SecretaryController::class, 'updateStatus'])
            ->name('appointments.updateStatus');
        
        // Autres validations techniques
        Route::patch('/appointments/{appointment}/validate', [AppointmentController::class, 'validateStatus'])
            ->name('appointments.validate');
    });

    // --- SECTION PATIENT ---
    Route::middleware(['role:patient,admin'])->group(function () {
        Route::get('/appointments/create', [PatientAppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [PatientAppointmentController::class, 'store'])->name('appointments.store');
    });

    // --- SECTION PROFIL (Accessible à TOUS) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';