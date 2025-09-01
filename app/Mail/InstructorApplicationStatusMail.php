<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstructorApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $status;
    protected $password;

    /**
     * Create a new message instance.
     */
    public function __construct($application, $status, $password=null)
    {
        $this->application = $application;
        $this->status = $status;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tayari Team - Instructor Application',
            from: $this->data['email'] ?? '',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.instructor-application-status',
            with: ['application' => $this->application, 'status' => $this->status, 'pass'=> $this->password]
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
