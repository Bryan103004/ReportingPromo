<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class LocMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $locs;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $locs)
    {
        //
        $this->user = $user;
        $this->locs = $locs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pemberitahuan atau Reminder LOC')->view('emails.loc');
    }
}
