<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Userregister;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(Userregister $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        $url = config('app.url') . '/email/verify/' . $this->user->email_verification_token;
        return $this->subject('Verify Your Email Address')
                    ->view('verify-email')
                    ->with([
                        'url' => $url,
                        'name' => $this->user->firstname,
                    ]);
    }
}
