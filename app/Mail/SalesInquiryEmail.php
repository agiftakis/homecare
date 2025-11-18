<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SalesInquiryEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The inquiry data from the contact form.
     *
     * @var array
     */
    public $inquiryData;

    /**
     * Create a new message instance.
     *
     * @param  array  $inquiryData
     * @return void
     */
    public function __construct(array $inquiryData)
    {
        $this->inquiryData = $inquiryData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // ✅ FIXED: Removed hardcoded 'from' address. 
            // It will now use MAIL_FROM_ADDRESS from your .env (the verified email).
            subject: 'New VitaLink Agency Inquiry from: ' . $this->inquiryData['agency_name'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // We will create this view in the next step
        return new Content(
            view: 'emails.sales-inquiry', 
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