<?php
namespace Laraspace\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUs extends Mailable
{
    use Queueable, SerializesModels;

	 public $demo;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($demo)
    {
		
        $this->demo = $demo;
	}

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		
        return $this->subject('Thank you for contact us')->view('emails.contactus-email');
    }
}
