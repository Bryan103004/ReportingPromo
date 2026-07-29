<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class RafaksiMail extends Mailable
{
    use Queueable, SerializesModels;

    public $rafaksis;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($rafaksis, $user)
    {
        //
        $this->rafaksis = $rafaksis;
        $this->user = $user;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pemberitahuan atau Reminder Rafaksi')->view('emails.rafaksi');
    }
}
