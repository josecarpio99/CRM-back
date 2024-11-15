<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewLeadCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $frontUrl;

    public Lead $lead;

    /**
     * Create a new message instance.
     */
    public function __construct($lead)
    {
        $this->lead = $lead;

        $frontUrl = env('FRONTEND_URL') . '/panel/leads/' . $lead->id;

        $this->frontUrl = $frontUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo Prospecto: ' . $this->lead->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->lead->load(['owner', 'creator']);
        return new Content(
            markdown: 'emails.new-lead-created',
            with: [
                'frontUrl' => $this->frontUrl,
                'lead' => $this->lead
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
