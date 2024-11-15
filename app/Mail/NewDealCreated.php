<?php

namespace App\Mail;

use App\Models\Deal;
use App\Enums\DealTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewDealCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $frontUrl;

    public Deal $deal;
    public string $type;

    /**
     * Create a new message instance.
     */
    public function __construct($deal)
    {
        $this->deal = $deal;
        $this->type =  ($deal->type == DealTypeEnum::Oportunidad->value
            ? 'Oportunidad'
            : 'Cotización');

        $frontUrl = env('FRONTEND_URL') . '/panel/deals/' .
            ($deal->type == DealTypeEnum::Oportunidad->value
                ? 'oportunidades'
                : 'cotizados') . '/' . $this->deal->id;

        $this->frontUrl = $frontUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nueva {$this->type}: " . $this->deal->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->deal->load(['owner', 'creator']);
        return new Content(
            markdown: 'emails.new-deal-created',
            with: [
                'frontUrl' => $this->frontUrl,
                'deal' => $this->deal,
                'type' => $this->type,
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
