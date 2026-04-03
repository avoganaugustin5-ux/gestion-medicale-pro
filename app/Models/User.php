<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'sexe',
        'nationalite', 'dateNaissance', 'adresse',
        'numeroTelephone', 'imageProfil', 'clinic_id', 
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- RELATIONS ---

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class);
    }

    // Relation : Si cet utilisateur est un Docteur, il a des secrétaires
    public function secretaries(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'doctor_secretary', 'doctor_id', 'secretary_id');
    }

    // Relation : Si cet utilisateur est une Secrétaire, elle a des docteurs affectés
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'doctor_secretary', 'secretary_id', 'doctor_id');
    }

    // --- LOGIQUE DE RÔLES ---
    public function isAdmin() { return strtolower($this->role) === 'admin'; }
    public function isMedecin() { return strtolower($this->role) === 'medecin'; }
    public function isSecretaire() { return strtolower($this->role) === 'secretaire'; } 
    public function isPatient() { return strtolower($this->role) === 'patient'; }

    // --- NOTIFICATIONS DE MOT DE PASSE ---
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new class($token) extends ResetPassword {
            public function toMail($notifiable)
            {
                return (new MailMessage)
                    ->subject('Réinitialisation de votre mot de passe - UTS Santé')
                    ->greeting('Bonjour ' . $notifiable->name . ' !')
                    ->line('Une demande de réinitialisation de mot de passe a été faite pour votre compte.')
                    ->action('Réinitialiser mon mot de passe', url(config('app.url').route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false)))
                    ->line('Ce lien expirera dans 60 minutes.')
                    ->salutation('Cordialement, L\'équipe UTS Santé');
            }
        });
    }
}