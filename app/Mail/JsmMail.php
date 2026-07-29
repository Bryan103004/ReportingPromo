<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class JsmMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $jsms;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $jsms)
    {
        //
        $this->user = $user;
        $this->jsms = $jsms;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pemberitahuan atau Reminder JSM')->view('emails.jsm');
    }
}
