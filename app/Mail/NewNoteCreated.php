<?php

namespace App\Mail;

use App\Models\Note;
use App\Enums\DealTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewNoteCreated extends Mailable
{
    use Queueable, SerializesModels;

    public Note $note;

    /**
     * Create a new message instance.
     */
    public function __construct($note)
    {
        $this->note = $note;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->note->user->name} dejó una nota",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $frontUrl = env('FRONTEND_URL') . '/';
        $type = '';

        if ($this->note->noteable_type == 'App\\Models\\Deal') {

            $frontUrl .= 'panel/deals/' .
                ($this->note->noteable->type == DealTypeEnum::Oportunidad->value
                ? 'oportunidades'
                : 'cotizados') . '/' . $this->note->noteable->id;

            $type =  ($this->note->noteable->type == DealTypeEnum::Oportunidad->value
                ? 'Oportunidad'
                : 'Cotización');

        } elseif ($this->note->noteable_type == 'App\\Models\\Customer') {
            $type = 'Contacto';
            $frontUrl .= 'panel/customers/' . $this->note->noteable->id;
        } elseif ($this->note->noteable_type == 'App\\Models\\Lead') {
            $type = 'Prospecto';
            $frontUrl .= 'panel/leads/' . $this->note->noteable->id;
        }

        return new Content(
            markdown: 'emails.new-note-created',
            with: [
                'type' => $type,
                'frontUrl' => $frontUrl,
                'note' => $this->note
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
