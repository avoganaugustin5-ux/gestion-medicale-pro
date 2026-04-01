<?php

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AvailabilityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', [ClinicController::class, 'index'])->name('dashboard');

    // SECTION ADMIN
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/clinics/create', [ClinicController::class, 'create'])->name('clinics.create');
        Route::post('/clinics', [ClinicController::class, 'store'])->name('clinics.store');
        
        // Gestion des Utilisateurs
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::patch('/admin/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.updateRole');

        // Gestion des Affectations
        Route::get('/admin/assignments', [UserController::class, 'assignments'])->name('admin.assignments.index');
        Route::post('/admin/assignments/clinic', [UserController::class, 'storeClinicAssignment'])->name('admin.assignments.clinic.store');
        Route::post('/admin/assignments/doctor-secretary', [UserController::class, 'storeSecretaryAssignment'])->name('admin.assignments.secretary.store');
        Route::delete('/admin/assignments/clinic/{user}', [UserController::class, 'detachClinic'])->name('admin.assignments.clinic.detach');
        Route::delete('/admin/assignments/secretary/{id}', [UserController::class, 'detachSecretary'])->name('admin.assignments.secretary.detach');
    });

    // SECTION MÉDECIN (Planning & Imprévus)
    Route::middleware(['role:medecin'])->group(function () {
        Route::get('/doctor/schedule', [AvailabilityController::class, 'index'])->name('doctor.availabilities.index');
        Route::post('/doctor/schedule', [AvailabilityController::class, 'store'])->name('doctor.availabilities.store');
        Route::patch('/doctor/schedule/{availability}', [AvailabilityController::class, 'update'])->name('doctor.availabilities.update');
        Route::delete('/doctor/schedule/{availability}', [AvailabilityController::class, 'destroy'])->name('doctor.availabilities.destroy');
        // Route alignée sur le nom 'export'
        Route::get('/doctor/schedule/export', [AvailabilityController::class, 'exportPdf'])->name('doctor.availabilities.export');
    });

    // SECTION SERVICES
    Route::middleware(['role:admin,secretaire'])->group(function () {
        Route::resource('services', ServiceController::class);
    });

    // SECTION GESTION CLINIQUE
    Route::middleware(['role:medecin,secretaire,admin'])->group(function () {
        Route::prefix('clinics/{clinic}')->group(function () {
            Route::get('/', [ClinicController::class, 'show'])->name('clinics.show');
            Route::get('/edit', [ClinicController::class, 'edit'])->name('clinics.edit');
            Route::patch('/', [ClinicController::class, 'update'])->name('clinics.update');
            Route::delete('/', [ClinicController::class, 'destroy'])->name('clinics.destroy');
            Route::resource('doctors', DoctorController::class)->names('clinics.doctors');
            Route::resource('patients', PatientController::class)->names('clinics.patients');
            Route::resource('appointments', AppointmentController::class)->names('clinics.appointments');
        });
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';