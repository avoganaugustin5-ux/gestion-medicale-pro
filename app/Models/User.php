<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'sexe',
        'nationalite', 'dateNaissance', 'adresse',
        'numeroTelephone', 'imageProfil', 'clinic_id', 
    ];

    /**
     * RELATION POUR L'ADMIN : Les cliniques qu'il a créées.
     */
    public function clinics()
    {
        return $this->hasMany(Clinic::class);
    }

    /**
     * RELATION POUR L'EMPLOYÉ : La clinique de rattachement.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * RELATION MÉDECIN ↔ SECRÉTAIRE
     */
    public function secretary()
    {
        return $this->belongsToMany(User::class, 'doctor_secretary', 'doctor_id', 'secretary_id');
    }

    public function doctors()
    {
        return $this->belongsToMany(User::class, 'doctor_secretary', 'secretary_id', 'doctor_id');
    }

    // Vérifications de rôles
    public function isAdmin() { return strtolower($this->role) === 'admin'; }
    public function isMedecin() { return strtolower($this->role) === 'medecin'; }
    public function isSecretaire() { return strtolower($this->role) === 'secretaire'; } 
    public function isPatient() { return strtolower($this->role) === 'patient'; }

    /**
     * Notification de réinitialisation de mot de passe (UTS Santé)
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new class($token) extends ResetPassword {
            public function toMail($notifiable)
            {
                return (new MailMessage)
                    ->subject('Réinitialisation de votre mot de passe - UTS Santé')
                    ->greeting('Bonjour ' . $notifiable->name . ' !')
                    ->line('Une demande de réinitialisation de mot de passe a été faite pour votre compte Gestion_RVMC.')
                    ->action('Réinitialiser mon mot de passe', url(config('app.url').route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false)))
                    ->line('Ce lien expirera dans 60 minutes.')
                    ->salutation('Cordialement, L\'équipe UTS Santé');
            }
        });
    }

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}