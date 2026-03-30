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

// 1. PUBLIC
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. ROUTES PROTÉGÉES
Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', [ClinicController::class, 'index'])->name('dashboard');

    // --- SECTION ADMINISTRATEUR ---
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/clinics/create', [ClinicController::class, 'create'])->name('clinics.create');
        Route::post('/clinics', [ClinicController::class, 'store'])->name('clinics.store');
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::patch('/admin/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.updateRole');
        Route::resource('services', ServiceController::class);
        Route::get('/admin/assignments', [UserController::class, 'assignments'])->name('admin.assignments.index');
        Route::post('/admin/assignments', [UserController::class, 'storeAssignment'])->name('admin.assignments.store');
        Route::post('/admin/assignments/{user}/detach', [UserController::class, 'detachAssignment'])->name('admin.assignments.detach'); 
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

            // Gestion des Consultations & Ordonnances
            Route::post('/patients/{patient}/consultations', [ConsultationController::class, 'store'])->name('clinics.patients.consultations.store');
            Route::get('/patients/{patient}/consultations/{consultation}/pdf', [ConsultationController::class, 'downloadPDF'])->name('consultations.pdf');
            Route::patch('/patients/{patient}/consultations/{consultation}', [ConsultationController::class, 'update'])->name('consultations.update');
            Route::delete('/patients/{patient}/consultations/{consultation}', [ConsultationController::class, 'destroy'])->name('consultations.destroy');
        });

        Route::put('/appointments/{appointment}/status', [SecretaryController::class, 'updateStatus'])->name('appointments.updateStatus');
        Route::patch('/appointments/{appointment}/validate', [AppointmentController::class, 'validateStatus'])->name('appointments.validate');
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

require __DIR__.'/auth.php';