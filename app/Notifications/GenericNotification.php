<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A single flexible notification for every in-app event (ticket replies,
 * notes, reminders, renewals, invoices, maintenance...) rather than one
 * near-identical class per event type. The "type" field lets the frontend
 * pick an icon/route without the backend needing a class per case, and
 * also doubles as the category used for the client's per-category email
 * preference (the part before the first dot, e.g. "ticket.reply" -> "ticket").
 */
class GenericNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $body = null,
        public readonly ?string $url = null,
    ) {}

    public function category(): string
    {
        return explode('.', $this->type)[0];
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Admin-side users (no client_id) always get email; client-side
        // users can opt individual categories out from their Account page.
        $wantsEmail = ! ($notifiable instanceof User && $notifiable->client_id)
            || $notifiable->wantsEmailFor($this->category());

        if ($wantsEmail) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting($this->title);

        if ($this->body) {
            $mail->line($this->body);
        }

        if ($this->url) {
            $mail->action('Άνοιγμα στο Weberry Portal', $this->url);
        }

        return $mail;
    }
}
