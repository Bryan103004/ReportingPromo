<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class PwpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pwps;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pwps, $user)
    {
        $this->pwps = $pwps;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pemberitahuan atau Reminder PWP')->view('emails.pwp');
    }
}
