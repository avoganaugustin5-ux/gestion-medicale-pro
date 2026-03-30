<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'clinic_id', // Clinique habituelle ou d'inscription
    ];

    /**
     * Lien avec le compte utilisateur (pour l'email et le login)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Liste de TOUS les rendez-vous du patient (acceptés, en attente, refusés)
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->orderBy('date_heure', 'desc');
    }

    /**
     * Uniquement les rendez-vous en attente (pour le carrousel de suivi)
     */
    public function pendingAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->where('status', 'en_attente');
    }

    /**
     * Uniquement les rendez-vous validés (pour le carrousel des rappels)
     */
    public function validatedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->where('status', 'valide');
    }

    /**
            * Historique médical du patient
    */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class)->latest();
    }
}