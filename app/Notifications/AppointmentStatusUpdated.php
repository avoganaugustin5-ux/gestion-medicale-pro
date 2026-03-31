<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentStatusUpdated extends Notification
{
    use Queueable;

    public $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $statusLabel = $this->appointment->status === 'confirmed' ? 'Accepté ✅' : 'Refusé ❌';
        
        $message = (new MailMessage)
            ->subject("Mise à jour de votre rendez-vous - UTS")
            ->greeting("Bonjour " . $notifiable->name . ",")
            ->line("Le statut de votre rendez-vous du " . date('d/m/Y à H:i', strtotime($this->appointment->appointment_date)) . " a été mis à jour.");

        if ($this->appointment->status === 'confirmed') {
            $message->line("Bonne nouvelle ! Votre rendez-vous est maintenant confirmé.")
                    ->action('Voir mon planning', url('/dashboard'))
                    ->success();
        } else {
            $message->line("Malheureusement, votre demande n'a pas pu être retenue.")
                    ->line("Motif : " . ($this->appointment->cancel_reason ?? "Non précisé"))
                    ->error();
        }

        return $message->line("Merci d'utiliser notre plateforme de santé.");
    }
}