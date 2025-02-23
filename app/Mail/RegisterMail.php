<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegisterMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $activation_code;

    /**
     * Create a new message instance.
     */

    // public User $user;


    public function __construct(User $user, string $activation_code)
    {
        $this->user = $user;
        $this->activation_code = $activation_code;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Register Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.register',
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




    public function build()
    {
        return $this->subject('🔑 Activation de votre compte - ' . config('app.name'))
            ->markdown('emails.register')
            ->with([
                'user' => $this->user,
                'activation_code' => $this->activation_code, // Assurez-vous qu'il est bien passé
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
            ]);
    }
}
