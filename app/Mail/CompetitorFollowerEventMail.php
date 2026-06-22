<?php

namespace App\Mail;

use App\Models\CompetitorFollowEvent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompetitorFollowerEventMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CompetitorFollowEvent $event,
        public User $user
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->event->title . ' | Rei do Rodeio'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.competitors.follow-event'
        );
    }
}
