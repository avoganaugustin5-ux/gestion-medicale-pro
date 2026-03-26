<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    // Constantes pour gérer les statuts proprement
    const STATUS_PENDING = 'en_attente';
    const STATUS_CONFIRMED = 'valide';
    const STATUS_CANCELLED = 'refuse';

    protected $fillable = [
        'clinic_id', 
        'doctor_id', 
        'patient_id', 
        'secretary_id', 
        'appointment_date', 
        'reason', 
        'status',
        'dateheure',    // Ajouté selon ta structure SQL
        'service_id'    // Ajouté selon ta structure SQL
    ];

    // Relations
    public function doctor() { return $this->belongsTo(Doctor::class); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function secretary() { return $this->belongsTo(Secretary::class); }
    public function clinic() { return $this->belongsTo(Clinic::class); }
}