<?php

namespace App\Mail;

use App\Enums\DealTypeEnum;
use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class DealOpportunityCreatedByLeadQualifier extends Mailable
{
    use Queueable, SerializesModels;

    public $frontUrl;
    public $type;
    public Deal $deal;

    /**
     * Create a new message instance.
     */
    public function __construct($deal)
    {
        $this->deal = $deal;

        // $frontUrl = env('FRONTEND_URL') . '/panel/deals/' .
        //     ($deal->type == DealTypeEnum::Oportunidad->value
        //     ? 'oportunidades'
        //     : 'cotizados') . '/list';

        // $this-> type = $this->deal->type == DealTypeEnum::Oportunidad->value
        //     ? 'oportunidad'
        //     : 'cotización';

        $this->type = 'Proyecto';

        $this->frontUrl = env('FRONTEND_URL') . '/panel/deals/cotizados/' . $this->deal->id;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nuevo $this->type asignado",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-deal',
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
