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
use App\Http\Controllers\ConsultationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes - UTS Santé / Gestion Médicale Pro
|--------------------------------------------------------------------------
*/

// 1. PUBLIC : Redirection automatique vers la connexion
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. ROUTES PROTÉGÉES (Authentification requise)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard dynamique : Redirige l'utilisateur selon son rôle
    Route::get('/dashboard', [ClinicController::class, 'index'])->name('dashboard');

    // --- SECTION ADMINISTRATEUR CENTRAL ---
    Route::middleware(['role:admin'])->group(function () {
        // Gestion des établissements (Cliniques/Centres de santé)
        Route::get('/clinics/create', [ClinicController::class, 'create'])->name('clinics.create');
        Route::post('/clinics', [ClinicController::class, 'store'])->name('clinics.store');

        // Gestion des Utilisateurs & Rôles
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::patch('/admin/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.updateRole');

        // Affectations du personnel aux cliniques
        Route::get('/admin/assignments', [UserController::class, 'assignments'])->name('admin.assignments.index');
        Route::post('/admin/assignments', [UserController::class, 'storeAssignment'])->name('admin.assignments.store');
        Route::post('/admin/assignments/{user}/detach', [UserController::class, 'detachAssignment'])->name('admin.assignments.detach'); 
    });

    // --- SECTION SERVICES (Admin & Secrétaire) ---
    // Cette route est cruciale pour ton onglet "Espace Secrétariat"
    Route::middleware(['role:admin,secretaire'])->group(function () {
        Route::resource('services', ServiceController::class);
    });

    // --- SECTION MÉDECIN ---
    Route::middleware(['role:medecin'])->group(function () {
        Route::get('/doctor/planning', [DoctorController::class, 'myPlanning'])->name('doctor.planning');
        Route::post('/doctor/availabilities', [DoctorController::class, 'storeAvailability'])->name('doctor.availabilities.store');
        Route::delete('/doctor/availabilities/{availability}', [DoctorController::class, 'destroyAvailability'])->name('doctor.availabilities.destroy');
    });

    // --- SECTION GESTION CLINIQUE (Médecin, Secrétaire & Admin) ---
    Route::middleware(['role:medecin,secretaire,admin'])->group(function () {
        Route::prefix('clinics/{clinic}')->group(function () {
            // Détails et édition de la clinique
            Route::get('/', [ClinicController::class, 'show'])->name('clinics.show');
            Route::get('/edit', [ClinicController::class, 'edit'])->name('clinics.edit');
            Route::patch('/', [ClinicController::class, 'update'])->name('clinics.update');
            Route::delete('/', [ClinicController::class, 'destroy'])->name('clinics.destroy');

            // Dashboard spécifique au secrétariat de la clinique
            Route::get('/secretary', [SecretaryController::class, 'index'])->name('secretary.index');

            // Ressources médicales de la clinique
            Route::resource('doctors', DoctorController::class)->names('clinics.doctors');
            Route::resource('patients', PatientController::class)->names('clinics.patients');
            Route::resource('appointments', AppointmentController::class)->names('clinics.appointments');

            // Gestion des Consultations & Dossiers Médicaux
            Route::controller(ConsultationController::class)->group(function () {
                Route::post('/patients/{patient}/consultations', 'store')->name('clinics.patients.consultations.store');
                Route::get('/patients/{patient}/consultations/{consultation}/pdf', 'downloadPDF')->name('consultations.pdf');
                Route::patch('/patients/{patient}/consultations/{consultation}', 'update')->name('consultations.update');
                Route::delete('/patients/{patient}/consultations/{consultation}', 'destroy')->name('consultations.destroy');
            });
        });

        // Mise à jour rapide du statut d'un rendez-vous (Validation/Refus)
        Route::put('/appointments/{appointment}/status', [SecretaryController::class, 'updateStatus'])->name('appointments.updateStatus');
    });

    // --- SECTION PATIENT ---
    Route::middleware(['role:patient,admin'])->group(function () {
        // Prise de rendez-vous côté patient
        Route::get('/appointments/create', [PatientAppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [PatientAppointmentController::class, 'store'])->name('appointments.store');
    });

    // --- SECTION PROFIL (Commun à tous) ---
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';