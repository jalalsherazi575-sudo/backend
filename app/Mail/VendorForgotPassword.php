<?php
namespace Laraspace\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorForgotPassword extends Mailable
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
		//echo print_r($demo);
		//exit;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		//echo '<pre>'; print_r($demo);
		//exit;
        return $this->subject('Forgotten Password Reset')->view('emails.vendor-forgot-password');
    }
}
