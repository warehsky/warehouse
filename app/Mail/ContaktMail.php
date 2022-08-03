<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContaktMail extends Mailable
{
    use Queueable, SerializesModels;
    public $contakt;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($contakt)
    {
        $this->contakt = $contakt;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.contakt');
    }
}
