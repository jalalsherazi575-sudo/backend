<?php
namespace Laraspace\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Laraspace\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Mail;
use Laraspace\Mail\ForgotPassword;
use Laraspace\Helpers\Helper;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->subject = "Laraspace password Reset";
    }

    public function getEmail()
    {
        return view('admin.sessions.forgot-password.index');
    }

    public function postEmail(Request $request)
    {
		
        $this->validate($request, ['email' => 'required']);
		$checkduplicate = User::where([['email', '=',$request->email]])->count();

        if ($checkduplicate==0) {
			 flash()->error('Invalid Email Address.');
			 return redirect()->to('/forgot-password/');
		} else {
			$user=User::where([['email', '=',$request->email]])->first();
			$name=$user->name;
			$ID=$user->id;
			$rand=rand(1,100000);
			$userupdate = User::find($ID);
		    $userupdate->email_token=$rand;
		    $userupdate->save();
			//$data = array('name'=>"Virat Gandhi");
			//$email = new ForgotPassword($data);
			
			$objDemo = new \stdClass();
            $objDemo->name = $name;
			$objDemo->token = $rand;
			
			$url=url('/')."/password/reset/".$rand;
			$objDemo->url = $url;
			
			//$email = new ForgotPassword(new User(['email_token' => $user->email_token, 'name' => $meta[0]->firstname.' '.$meta[0]->lastname,'email'=>$user->email]));
			
		    //Mail::to($request->email)->send(new ForgotPassword($objDemo));

		    /*24-4-2024 Mail send*/
		    $mail = Helper::getEmailContent(2);
            if (!empty($mail)) {
                $Data = [
                    'customername' => $name,
                    'url' => $url,
                    'logourl' => url('/assets/admin/img/logo.svg'),
                ];
                $mailDescription = str_replace(
                    ['#logourl', '#customername', '#url'],
                    [$Data['logourl'],$Data['customername'],$Data['url']],
                    $mail->description
                );

                try {
                    // Dynamic SMTP configuration
                    $config = Helper::smtp();
                    // Send the email

                    $sendemail = Helper::sendemail($mail->subject, $mail->mail_to, 1, $mailDescription, $mail->mail_cc, $mail->mail_bcc);

                    // Check if email was sent successfully
                    if ($sendemail) {
                        Log::info('Email sent successfully');
                    } else {
                        Log::error('Failed to send email');
                    }
                } catch (\Exception $e) {
                    Log::error('Exception occurred while sending email: ' . $e->getMessage());
                }
            }
            flash()->success('Your reset pasword request has been taken please check your email.');
            return redirect()->to('/forgot-password/');
		}
		
		/* $request->session()->put('email', $request->email);
        $response = Password::sendResetLink($request->only('email'), function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                flash()->success('Password Reset link has been sent to your mail id');
                return redirect()->back()->with('status', 'No User Is asoociated with this account');
                
            case Password::INVALID_USER:
                return redirect()->back()->withErrors(['email' => trans($response)]);
        } */
    }

    public function getReset($token = null)
    {
		
        if (is_null($token)) {
            throw new NotFoundHttpException;
        }
		if ($token!='') {
		$user=User::where([['email_token', '=',$token]])->count();
		if ($user > 0) {
		return view('admin.sessions.forgot-password.reset')->with('token', $token);
		  } else {
		    flash()->error('Invalid Request.');
			return redirect()->to('/forgot-password/');
		  }
		
		}
		
        
    }

    public function postReset(Request $request)
    {
		//echo '<pre>'; print_r($request->email);
		//exit;
        $this->validate($request, [
            'password' => 'required|confirmed|min:6|max:16',
            'token' => 'required',
            'password_confirmation' => 'required|same:password'
        ]);
        $user = User::where('email_token', $request->token)->first();
        $user->password = bcrypt($request->password);
        $user->save();
        \Auth::login($user, true);
        flash()->success('Your Password Updated Success Fully');
        
        return redirect()->to('/login');
    }
}
