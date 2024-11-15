<?php

namespace App\Mail;

use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewAAACotizado extends Mailable
{
    use Queueable, SerializesModels;

    public $dealUrl;

    public Deal $deal;

    /**
     * Create a new message instance.
     */
    public function __construct($deal)
    {
        $this->deal = $deal;

        $this->dealUrl = env('FRONTEND_URL') . '/panel/deals/cotizados/' . $this->deal->id;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Cotización AAA Agregada',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-aaa-cotizado',
            with: [
                'dealUrl' => $this->dealUrl,
                'deal' => $this->deal
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
