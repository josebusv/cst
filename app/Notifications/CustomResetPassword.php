<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPassword extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
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
    public function toMail($notifiable)
    {
        // Construir la URL del frontend Angular
        $frontendUrl = config('app.frontend_url', 'http://localhost:4200');
        $url = $frontendUrl . '/auth/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $expiration = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');
        $appName = config('app.name');

        return (new MailMessage)
            ->subject('Restablecimiento de Contraseña - ' . $appName)
            ->view('emails.password-reset', [
                'user' => $notifiable,
                'url' => $url,
                'expiration' => $expiration,
                'appName' => $appName
            ]);
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
