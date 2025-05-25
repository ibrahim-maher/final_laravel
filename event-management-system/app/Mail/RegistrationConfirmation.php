<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;

    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    public function build()
    {
        return $this->subject('Registration Confirmation - ' . $this->registration->event->name)
                   ->view('emails.registration-confirmation')
                   ->with([
                       'registration' => $this->registration,
                       'user' => $this->registration->user,
                       'event' => $this->registration->event,
                       'ticket' => $this->registration->ticketType,
                   ]);
    }
}