<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiredLocMail extends Mailable {
    use Queueable, SerializesModels;
    public $locs, $user;

    public function __construct($locs, $user) {
        $this->locs = $locs; $this->user = $user;
    }
    public function build() {
        return $this->subject('URGENT: Loc Kadaluarsa - ')
                    ->view('emails.expired_loc_reminder'); 
    }
}