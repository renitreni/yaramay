<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulaluhanReservationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->bcc(['yaramayservices@gmail.com'])
            ->from('do-no-reply@example.com', 'Bulaluhan Reservation')
            ->subject('New Reservation')
            ->view('emails.reservation');
    }
}
