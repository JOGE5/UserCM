<?php

namespace App\Mail;

use App\Models\Admin_notificaciones;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $notificacion;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin_notificaciones $notificacion)
    {
        $this->notificacion = $notificacion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notificacion->Titulo_Notificacion,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-notification',
            with: [
                'titulo' => $this->notificacion->Titulo_Notificacion,
                'mensaje' => $this->notificacion->Mensaje_Notificacion,
                'imagen' => $this->notificacion->imgen,
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
        $attachments = [];
        
        if ($this->notificacion->imgen) {
            $path = storage_path('app/public/' . $this->notificacion->imgen);
            if (file_exists($path)) {
                $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromPath($path)
                    ->as('adjunto_notificacion.' . pathinfo($path, PATHINFO_EXTENSION))
                    ->withMime(mime_content_type($path));
            }
        }
        
        return $attachments;
    }
}
