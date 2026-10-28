<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Welcomes a team member added in Users & Roles and lets them choose their own password. */
class DashboardAccountCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token, public string $invitedBy) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]);

        return (new MailMessage)
            ->subject('Your Yemi Adebanjo dashboard account')
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->invitedBy} has created a dashboard account for you.")
            ->action('Set your password', $url)
            ->line('This link expires in '.config('auth.passwords.users.expire').' minutes. If it expires, use “Forgot password” on the sign-in page.')
            ->line('Sign in at: '.route('login'));
    }
}
