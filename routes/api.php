use App\Http\Controllers\PatientAppointmentController;

Route::get('/services/{service}/clinics/{clinic}/doctors', [PatientAppointmentController::class, 'getDoctorsByService']);