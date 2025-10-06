<?php

namespace App\Notifications;

use App\Models\DeviceAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDeviceAccessRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public DeviceAccessRequest $request;

    /**
     * Create a new notification instance.
     */
    public function __construct(DeviceAccessRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nieuw verzoek om apparaattoegang')
            ->greeting('Hallo!')
            ->line("Er is een nieuw verzoek om toegang tot een apparaat ingediend door {$this->request->user->name}.")
            ->line("📱 Telefoonnummer: {$this->request->phone_number}")
            ->lineIf($this->request->message, "💬 Bericht: {$this->request->message}")
            ->line('U kunt dit verzoek bekijken en beheren in het beheerderspaneel.');
    }

    /**
     * Get the array representation of the notification (for database or broadcast).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->request->user_id,
            'phone_number' => $this->request->phone_number,
            'message' => $this->request->message,
        ];
    }
}