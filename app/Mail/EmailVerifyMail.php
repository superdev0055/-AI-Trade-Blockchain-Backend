<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $verifyCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(int $verifyCode)
    {
        $this->verifyCode = $verifyCode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.customer.auth.verifyEmail');
    }
}
