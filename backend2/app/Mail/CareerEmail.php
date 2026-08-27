<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CareerEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $cvFile;

    public function __construct($data, $cvFile)
    {
        $this->data = $data;
        $this->cvFile = $cvFile;
    }

    public function build()
    {
        $email = $this->subject('New Career Application from ' . $this->data['fullname'])
            ->view('careermail')
            ->with(['data' => $this->data]);

        // Attach CV
        if ($this->cvFile) {
            $filePath =  dirname(dirname(dirname(dirname(__FILE__)))).'/backend/public/uploads/career/' . $this->cvFile;
            $email->attach($filePath);
        }

        return $email;
    }
}
