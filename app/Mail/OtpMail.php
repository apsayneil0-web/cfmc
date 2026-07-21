<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $otp)
    {
    }

    public function build()
    {
        return $this->subject('Your CFMC Password Reset Code')
            ->view('emails.otp')
            ->text('emails.otp-text');
    }
}
