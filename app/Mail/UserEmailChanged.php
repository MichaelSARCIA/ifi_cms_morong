<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserEmailChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $oldEmail;
    public $newEmail;
    public $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $oldEmail, $newEmail)
    {
        $this->user = $user;
        $this->oldEmail = $oldEmail;
        $this->newEmail = $newEmail;
        $this->settings = \App\Models\SystemSetting::pluck('value', 'key')->toArray();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: Your Account Email Has Been Changed - ' . ($this->settings['system_short_name'] ?? 'IFI CMS'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user_email_changed',
            with: [
                'user' => $this->user,
                'oldEmail' => $this->oldEmail,
                'newEmail' => $this->newEmail,
                'settings' => $this->settings,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
