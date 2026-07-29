<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiredRafaksiMail extends Mailable {
    use Queueable, SerializesModels;
    public $rafaksis, $user;

    public function __construct($rafaksis, $user) {
        $this->rafaksis = $rafaksis; $this->user = $user;
    }
    public function build() {
        return $this->subject('URGENT: Rafaksi Kadaluarsa - ')
                    ->view('emails.expired_rafaksi_reminder'); 
    }
}