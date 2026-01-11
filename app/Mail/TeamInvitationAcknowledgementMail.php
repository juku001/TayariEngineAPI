<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationAcknowledgementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public TeamInvitation $invitation,
        public ?string $password = null
    ) {}

    /**
     * Email subject
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->password
                ? 'Welcome to the Team on Tayari 🎉'
                : 'You’ve Been Added to a Team on Tayari'
        );
    }

    /**
     * Email content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.team-invitation-acknowledgement',
            with: [
                'user' => $this->user,
                'invitation' => $this->invitation,
                'password' => $this->password, // nullable
            ]
        );
    }

    /**
     * Attachments
     */
    public function attachments(): array
    {
        return [];
    }
}
