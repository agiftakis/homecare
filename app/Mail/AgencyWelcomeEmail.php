<?php

namespace App\Mail;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgencyWelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $agency;
    public $user;
    public $password;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Agency $agency The newly created agency.
     * @param \App\Models\User $user The new admin user.
     * @param string $password The plain-text password for the user.
     */
    public function __construct(Agency $agency, User $user, string $password)
    {
        $this->agency = $agency;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // ✅ THE *REAL* FIX: Pass the email as an array.
            to: [$this->user->email],
            subject: 'Welcome to VitalLink - Your Agency Account is Ready',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // This line correctly points to the Blade file you already created
        return new Content(
            view: 'emails.agency-welcome',
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
