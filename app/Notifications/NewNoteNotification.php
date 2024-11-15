<?php

namespace App\Notifications;

use App\Models\Deal;
use App\Models\Note;
use App\Enums\DealTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewNoteNotification extends Notification
{
    use Queueable;

    public Note $note;

    /**
     * Create a new notification instance.
     */
    public function __construct(Note $note)
    {
        $this->note = $note;
    }

    public function via($notifiable) {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = $this->note->toArray();

        $type = strtolower(
            collect(
                explode('\\', $this->note->noteable_type)
            )->pop()
        );

        if ($type == 'deal') {
            $deal = Deal::find($this->note->noteable_id);

            $type = $deal->type;
        }

        $data['type'] = $type;
        $data['name'] = $this->note->user->name;
        $data['noteable_name'] = $this->note->noteable->name;

        return $data;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontUrl = env('FRONTEND_URL') . '/';
        $type = '';

        if ($this->note->noteable_type == 'App\\Models\\Deal') {

            $frontUrl .= '/panel/deals/' .
                ($this->note->noteable->name->type == DealTypeEnum::Oportunidad->value
                ? 'oportunidades'
                : 'cotizados') . '/list';

            $type =  ($this->note->noteable->name->type == DealTypeEnum::Oportunidad->value
                ? 'Oportunidad'
                : 'Cotización') . '/list';

        } elseif ($this->note->noteable_type == 'App\\Models\\Customer') {
            $type = 'Contacto';
            $frontUrl .= 'panel/customers/' . $this->note->noteable->id;
        } elseif ($this->note->noteable_type == 'App\\Models\\Lead') {
            $type = 'Prospecto';
            $frontUrl .= 'panel/leads/' . $this->note->noteable->id;
        }

        return (new MailMessage)
            ->subject("{$this->note->user->name} dejó una nota")
            ->greeting("{$this->note->user->name} dejó una nota")
            ->line("{$type}: {$this->note->noteable->name}")
            ->line("Mensaje: {$this->note->content}")
            ->action("Ver", $frontUrl);
    }
}
