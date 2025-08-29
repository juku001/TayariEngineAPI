<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $inviteLink;
    public ?string $teamName;
    public string $inviterName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $inviteLink, ?string $teamName, string $inviterName)
    {
        $this->inviteLink = $inviteLink;
        $this->teamName = $teamName;
        $this->inviterName = $inviterName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("You're Invited to Join {$this->teamName}")
                    ->view('emails.team-invite')
                    ->with([
                        'inviteLink' => $this->inviteLink,
                        'teamName'   => $this->teamName,
                        'inviterName'=> $this->inviterName,
                    ]);
    }
}
