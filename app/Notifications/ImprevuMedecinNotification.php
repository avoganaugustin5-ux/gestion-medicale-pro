<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ImprevuMedecinNotification extends Notification
{
    use Queueable;

    protected $availability;
    protected $doctor;

    public function __construct($availability, $doctor)
    {
        $this->availability = $availability;
        $this->doctor = $doctor;
    }

    public function via($notifiable) { return ['database', 'mail']; }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('⚠️ Imprévu Médical - ' . $this->doctor->name)
            ->line('Le Dr. ' . $this->doctor->name . ' a déclaré un imprévu pour le ' . $this->availability->date)
            ->line('Heure : ' . $this->availability->start_time)
            ->action('Gérer les rendez-vous', url('/dashboard'))
            ->line('Merci de contacter les patients concernés.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Imprévu déclaré par Dr. ' . $this->doctor->name,
            'availability_id' => $this->availability->id,
            'date' => $this->availability->date
        ];
    }
}