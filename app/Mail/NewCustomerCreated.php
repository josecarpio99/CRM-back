<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCustomerCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $frontUrl;

    public Customer $customer;

    /**
     * Create a new message instance.
     */
    public function __construct($customer)
    {
        $this->customer = $customer;

        $frontUrl = env('FRONTEND_URL') . '/panel/customers/' . $customer->id;

        $this->frontUrl = $frontUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo Contacto: ' . $this->customer->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->customer->load(['owner', 'creator']);
        return new Content(
            markdown: 'emails.new-customer-created',
            with: [
                'frontUrl' => $this->frontUrl,
                'customer' => $this->customer
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
