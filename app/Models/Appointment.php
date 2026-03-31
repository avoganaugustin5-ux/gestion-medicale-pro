<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    // Harmonisation avec les ENUM de ta migration
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'clinic_id', 
        'doctor_id', 
        'patient_id', 
        'secretary_id', 
        'service_id',
        'appointment_date', 
        'reason', 
        'status',
    ];

    // Relations
    public function doctor(): BelongsTo { 
        return $this->belongsTo(Doctor::class, 'doctor_id'); 
    }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function secretary(): BelongsTo { return $this->belongsTo(Secretary::class); }
    public function clinic(): BelongsTo { return $this->belongsTo(Clinic::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
}