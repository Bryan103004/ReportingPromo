<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiredJsmMail extends Mailable {
    use Queueable, SerializesModels;
    public $jsms, $user;

    public function __construct($jsms, $user) {
        $this->jsms = $jsms; $this->user = $user;
    }
    public function build() {
        return $this->subject('URGENT: Jsm Kadaluarsa - ')
                    ->view('emails.expired_jsm_reminder'); 
    }
}