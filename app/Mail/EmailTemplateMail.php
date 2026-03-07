<?php
namespace Laraspace\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

	 public $objMail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($objMail)
    {
		
        $this->objMail = $objMail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->objMail->subject)->view('emails.email-template');
    }
}
