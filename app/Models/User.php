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
        'name',
        'email',
        'password',
        'role',
        'sexe',
        'nationalite',
        'dateNaissance',
        'adresse',
        'numeroTelephone',
        'imageProfil',
        'clinic_id', // ID de la clinique pour les médecins/secrétaires
    ];

    /**
     * RELATION POUR L'ADMIN : 
     * Récupère les cliniques créées par cet utilisateur (Admin).
     */
    public function clinics()
    {
        return $this->hasMany(Clinic::class);
    }

    /**
     * RELATION POUR L'EMPLOYÉ : 
     * Récupère la clinique à laquelle le médecin/secrétaire appartient.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Vérification des rôles
     */
    public function isAdmin() { return $this->role === 'admin'; }
    public function isMedecin() { return $this->role === 'medecin'; }
    public function isSecretaire() { return $this->role === 'secretaire'; } 
    public function isPatient() { return $this->role === 'patient'; }

    /**
     * Notification personnalisée pour le mot de passe oublié
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new class($token) extends ResetPassword {
            public function toMail($notifiable)
            {
                return (new MailMessage)
                    ->subject('Réinitialisation de votre mot de passe - Gestion_RVMC')
                    ->greeting('Bonjour ' . $notifiable->name . ' !')
                    ->line('Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte sur la plateforme Gestion_RVMC.')
                    ->action('Réinitialiser mon mot de passe', url(config('app.url').route('password.reset', [
                        'token' => $this->token,
                        'email' => $notifiable->getEmailForPasswordReset(),
                    ], false)))
                    ->line('Ce lien de réinitialisation expirera dans 60 minutes.')
                    ->line('Si vous n\'avez pas demandé de réinitialisation, aucune action supplémentaire n\'est requise.')
                    ->salutation('Cordialement, L\'équipe Gestion_RVMC');
            }
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}