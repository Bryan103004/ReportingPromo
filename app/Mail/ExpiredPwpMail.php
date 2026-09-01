<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiredPwpMail extends Mailable {
    use Queueable, SerializesModels;
    public $pwps, $user;

    public function __construct($pwps, $user) {
        $this->pwps = $pwps; $this->user = $user;
    }
    public function build() {
        return $this->subject('URGENT: PWP Kadaluarsa - ')
                    ->view('emails.expired_pwp_reminder');
    }
}
