<?php
namespace Laraspace\Http\Controllers;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request; 
use Laraspace\Vendor;
use Laraspace\CustomerRegister;
use Laraspace\Mail\ContactUs;
use Laraspace\CustomerContactUs;
use Laraspace\DefaultConfiguration;
use Laraspace\Helpers\Helper;
use Laraspace\PasswordResets;
use Mail;
use Validator;
use Carbon\Carbon;

class FrontendController extends Controller
{
    public function home()
    {
        return view('front.index');
    }
	
	/*public function about_us()
    {
        return view('front.aboutus');
    }*/



    public function privacy_policypage()
    {
        return view('front.privacypolicy');
    }
    public function delete_account()
    {
        return view('front.delete_account');
    }


    public function feedback_customer(Request $request)
    {
        //$vendorId=($request->vendorId)?($request->vendorId):0;
        $customerId=($request->customerId)?($request->customerId):0;
        //$contactphone=($request->phone)?($request->phone):"";

        return view('front.feedback_customer',compact('customerId'));
    }
     

    public function submit_feedback_customer(Request $request) {
       // print_r( $request->all());
       // exit();
        $url=url('/');

        
        $message=($request->message)?($request->message):"";
        $message1=($request->message)?($request->message):"";
        //$vendorId=($request->vendorId)?($request->vendorId):0;
        $customerId=($request->customerId)?($request->customerId):0;
        $contactname=($request->contactname)?($request->contactname):"";
        $contactemail=($request->contactemail)?($request->contactemail):"";
        $contactphone=($request->contactphone)?($request->contactphone):"";

        $objDemo = new \stdClass();
        
        $objDemo->message = $message;
           
           $emailto = 'gvilmont@mysytadel.com';
            //$recipient = "gvilmont@mysytadel.com";
            //$recipient .= "CC: kalpesh@webtechsystem.com". "\r\n";
            //$recipient .= "BCC: maunik@webtechsystem.com". "\r\n";   
            $subject = "Thank you for feedback";
            //echo $message;
            //exit;

            //$headers = "MIME-Version: 1.0" . "\r\n";
            //$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            // More headers
            //$headers .= 'From: Quick serv <contact@quickserv.com>' . "\r\n";
            //$headers .= 'Cc: myboss@example.com' . "\r\n";

            //$chk = sendMsg($emailto, $subject, $message);
           // $chk =mail($emailto,$subject,$message,$headers);


        //Mail::to($contactemail)->send(new ContactUs($objDemo));

            $msg1='';
          
          
          if ($contactname!='') {
             $msg1 .='<div class="action" style="padding-top: 30px;"><span><strong>Contact Name:</strong></span><span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">'.$contactname.'</span></div>';
          }

           
          if ($contactemail!='') {
             $msg1 .='<div class="action" style="padding-top: 30px;"><span><strong>Contact Email:</strong></span><span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">'.$contactemail.'</span></div>';
          }
           
          if ($contactphone!='') {
             $msg1 .='<div class="action" style="padding-top: 30px;"><span><strong>Contact Phone:</strong></span><span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">'.$contactphone.'</span></div>';
          }

          if ($message!='') {
             $msg1 .='<div class="action" style="padding-top: 30px;padding-bottom: 30px;"><span><strong>Comments:</strong></span><span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">'.$message.'</span></div>';
          }

           $msg="<!DOCTYPE html>
<html>
<head>
    <title>Email Templates</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>

</head>
<body>
<div class='wrapper' style='background-color: #f8f8f8;text-align: center;width: 100%;padding: 20px;'>
     <div style='max-width: 700px;margin: auto;'><img src='".$url."/assets/admin/img/site-logo.png' style='width: 703px;height: 390px;'></div>
    <div class='mail'
         style='background-color: #ffffff;max-width: 603px;margin: auto;text-align: center;color: #74787e;margin-top: 30px;padding: 30px 50px 30px 50px;'>
        <p style='color: #807d7d;line-height: 1.5;'>Thank you for contact us</p>

        ".$msg1."
       
    </div>

</div>
</body>
</html>";

          //$emailto = $contactemail;
            $subject = "Thank you for contact us";

            $from_email="gvilmont@mysytadel.com";
      $reply_to_email="gvilmont@mysytadel.com";

            
        

          $tmp_name    = $_FILES['photo']['tmp_name']; // get the temporary file name of the file on the server 
          $name        = $_FILES['photo']['name'];  // get the name of the file 
          $size        = $_FILES['photo']['size'];  // get size of the file for size validation 
          $type        = $_FILES['photo']['type'];  // get type of the file 
          $error       = $_FILES['photo']['error']; // get the error (if any) 

          $tmpName = $_FILES['photo']['tmp_name']; 
          $fileType = $_FILES['photo']['type']; 
          $fileName = $_FILES['photo']['name']; 

          $attachment="";
          if ($tmp_name!='') {
          $attachment = chunk_split(base64_encode(file_get_contents($tmp_name)));
             }

           $filename = $name;   


        $boundary =md5(date('r', time())); 

        $headers = "From: contact@sytadel.com\r\nReply-To: contact@sytadel.com";
        //$headers .= "\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"_1_$boundary\"";

        
          //$headers = "MIME-Version: 1.0" . "\r\n";
            //$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            // More headers
            //$headers .= 'From: Quick serv <contact@quickserv.com>' . "\r\n";

           

       

                    //$boundary =md5(date('r', time())); 

                    //$headers = "From: contact@sytadel.com\r\nReply-To: contact@sytadel.com";
                    //$headers .= "\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"_1_$boundary\"";

        
          
                    /*if ($attachment!='') {
                         $message="This is a multi-part message in MIME format.

                    --_1_$boundary
                    Content-Type: multipart/alternative; boundary=\"_2_$boundary\"

                    --_2_$boundary
                    Content-Type: text/html; charset=\"iso-8859-1\"
                    Content-Transfer-Encoding: 7bit

                    $msg

                    --_2_$boundary--
                    --_1_$boundary
                    Content-Type: application/octet-stream; name=\"$filename\" 
                    Content-Transfer-Encoding: base64 
                    Content-Disposition: attachment 

                    $attachment
                    --_1_$boundary--";
                    } else {
                    $message="This is a multi-part message in MIME format.

                    --_1_$boundary
                    Content-Type: multipart/alternative; boundary=\"_2_$boundary\"

                    --_2_$boundary
                    Content-Type: text/html; charset=\"iso-8859-1\"
                    Content-Transfer-Encoding: 7bit

                    $msg

                    --_2_$boundary--
                    --_1_$boundary
                    --_1_$boundary--";
                    }
            */

                           /*if ($attachment!='') {
                                 $message="This is a multi-part message in MIME format.

                            --_1_$boundary
                            Content-Type: multipart/alternative; boundary=\"_2_$boundary\"

                            --_2_$boundary
                            Content-Type: text/html; charset=\"iso-8859-1\"
                            Content-Transfer-Encoding: 7bit

                            $msg

                            --_2_$boundary--
                            --_1_$boundary
                            Content-Type: application/octet-stream; name=\"$filename\" 
                            Content-Transfer-Encoding: base64 
                            Content-Disposition: attachment 

                            $attachment
                            --_1_$boundary--";
                            } else {
                            $message="This is a multi-part message in MIME format.

                            --_1_$boundary
                            Content-Type: multipart/alternative; boundary=\"_2_$boundary\"

                            --_2_$boundary
                            Content-Type: text/html; charset=\"iso-8859-1\"
                            Content-Transfer-Encoding: 7bit

                            $msg

                            --_2_$boundary--
                            --_1_$boundary
                            --_1_$boundary--";
                            }*/

                            if ($tmpName!='' && (file($tmpName))) { 
  /* Reading file ('rb' = read binary)  */
                                  $file = fopen($tmpName,'rb'); 
                                  $data = fread($file,filesize($tmpName)); 
                                  fclose($file); 

                                  /* a boundary string */
                                  $randomVal = md5(time()); 
                                  $mimeBoundary = "==Multipart_Boundary_x{$randomVal}x"; 

                                  /* Header for File Attachment */
                                  $headers .= "\nMIME-Version: 1.0\n"; 
                                  $headers .= "Content-Type: multipart/mixed;\n" ;
                                  $headers .= " boundary=\"{$mimeBoundary}\""; 

                                  /* Multipart Boundary above message */
                                  $message = "This is a multi-part message in MIME format.\n\n" . 
                                  "--{$mimeBoundary}\n" . 
                                  "Content-Type: text/html; charset=\"iso-8859-1\"\n" . 
                                  "Content-Transfer-Encoding: 7bit\n\n" . 
                                  $msg . "\n\n"; 

                                  /* Encoding file data */
                                  $data = chunk_split(base64_encode($data)); 

                                  /* Adding attchment-file to message*/
                                  $message .= "--{$mimeBoundary}\n" . 
                                  "Content-Type: {$fileType};\n" . 
                                  " name=\"{$fileName}\"\n" . 
                                  "Content-Transfer-Encoding: base64\n\n" . 
                                  $data . "\n\n" . 
                                  "--{$mimeBoundary}--\n"; 
                                } else {
                                  
                                  $message=$msg;
                                  $headers = "MIME-Version: 1.0" . "\r\n";
                                  $headers .= "Content-Type:text/html; charset=iso-8859-1" . "\r\n";
                                  $headers .= "Content-Transfer-Encoding:7bit" . "\r\n";

                                }    
           //echo $message;
            //exit();
           
           $chck=@mail($emailto, $subject, $message, $headers);
             
             /*$vendorpaymentType=DB::table('tblvendorfeedback')->insert(
               ['vendorId'=>$vendorId,'customerId'=>$customerId,'feedback'=>$message1,'createdDate'=>date('Y-m-d H:i:s')]);*/

        flash()->success('Thank you for your feedback.');
             return redirect()->to('/feedback_customer');

         //       $objDemo->url = $reseturl;
    }  


    public function contact_us_customer(Request $request)
    {
        return view('front.contactus_customer');
    }

     public function submit_contact_us(Request $request) 
    {
        $url=url('/');

        $contactname=($request->contactname)?($request->contactname):"";
        $contactemail=($request->contactemail)?($request->contactemail):"";
        $contactphone=($request->contactphone)?($request->contactphone):"";
        $message=($request->message)?($request->message):"";

        $create = new CustomerContactUs();
        $create->contactname = $contactname;
        $create->contactemail = $contactemail;
        $create->contactphone = $contactphone;
        $create->message = $message;
        $create->save();
        /*$objDemo = new \stdClass();
        $objDemo->contactname = $contactname;
        $objDemo->contactemail = $contactemail;
        $objDemo->contactphone = $contactphone;
        $objDemo->message = $message;*/
        //Mail::to($contactemail)->send(new ContactUs($objDemo));  
           //mail($emailto, $subject, $message, $headers);
       /* try {
            $toemail = 'janusz.skrzypecki@gmail.com';
            $ccemail = 'kalpesh@abbacus.com';

            Mail::to($toemail)
                ->cc($ccemail) // Add CC recipient
                ->send(new ContactUs($objDemo));
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            
        }  */
        /*Mail 24-4-2024 */
        $mail = Helper::getEmailContent(1);
        if (!empty($mail)) {
            $Data = [
                'contactname' => $contactname,
                'contactemail' => $contactemail,
                'contactphone' => $contactphone,
                'logo' => url('/assets/admin/img/logo.svg'),
                'message' => $message
            ];
            $mailDescription = str_replace(
                ['#logourl', '#contactname', '#contactemail', '#contactphone', '#message'],
                [$Data['logo'],$Data['contactname'],$Data['contactemail'],$Data['contactphone'], $Data['message']],
                $mail->description
            );

            try {
                // Dynamic SMTP configuration
                $config = Helper::smtp();
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
        session()->flash('success','Thank you for contact us.');
         return view('front.contactus_customer');

         //       $objDemo->url = $reseturl;
    }

    public function contact_us_vendor(Request $request)
    {
        //print_r($request->name);

        $contactname=($request->name)?($request->name):"";
        $contactemail=($request->email)?($request->email):"";
        $contactphone=($request->phone)?($request->phone):"";
        //echo $contactphone;
        //exit;
        return view('front.contactus_vendor',compact('contactname','contactemail','contactphone'));
    }

    public function submit_vendor(Request $request) {
       // print_r( $request->all());
       // exit();
        $url=url('/');

        $contactname=($request->contactname)?($request->contactname):"";
        $contactemail=($request->contactemail)?($request->contactemail):"";
        $contactphone=($request->contactphone)?($request->contactphone):"";
        $howcanwehelpyou=($request->howcanwehelpyou)?($request->howcanwehelpyou):"";
        $message=($request->message)?($request->message):"";

        $objDemo = new \stdClass();
        $objDemo->contactname = $contactname;
        $objDemo->contactemail = $contactemail;
        $objDemo->contactphone = $contactphone;
        $objDemo->howcanwehelpyou = $howcanwehelpyou;
        $objDemo->message = $message;

       



        // $photo=$request->file('photo');
         // $extension = $photo->getClientOriginalExtension();

        // $file_name = $FILES['attachment']['name'];  
   //$temp_name = $FILES['attachment']['tmp_name'];  
   //$file_type = $FILES['attachment']['type'];

         //$file = $temp_name;
       //$content = chunk_split(base64_encode(file_get_contents($file)));
      // $uid = md5(uniqid(time()));
          
          $msg1='';
          if ($contactname!='') {
             $msg1 .='<div class="action" style="padding-top: 30px;"><span><strong>Contact Name:</strong></span><span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">'.$contactname.'</span></div>';
          }

           
          if ($contactemail!='') {
             $msg1 .='<div class="action" style="padding-top: 30px;"><span><strong>Contact Email:</strong></span><span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">'.$contactemail.'</span></div>';
          }
           
          if ($contactphone!='') {
             $msg1 .='<div class="action" style="padding-top: 30px;"><span><strong>Contact Phone:</strong></span><span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">'.$contactphone.'</span></div>';
          }
           
          if ($message!='') {
             $msg1 .='<div class="action" style="padding-top: 30px;"><span><strong>Comments:</strong></span><span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">'.$message.'</span></div>';
          }

           $msg="<!DOCTYPE html>
<html>
<head>
    <title>Email Templates</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>

</head>
<body>
<div class='wrapper' style='background-color: #f8f8f8;text-align: left;width: 100%;padding: 20px;'>
     <div style='padding:20px;background-color: #672f90;max-width: 700px;margin: auto;'><img src='".$url."/assets/admin/img/long-logo.png'></div>
    <div class='mail'
         style='background-color: #ffffff;max-width: 600px;margin: auto;text-align: left;padding-top: 40px;color: #74787e;margin-top: 30px;padding: 30px;'>
        <p style='color: #807d7d;line-height: 1.5;'>Thank you for contact us</p>

        ".$msg1."
       
    </div>

</div>
</body>
</html>";

          $emailto = $contactemail;
            $subject = "Thank you for contact us";
            //echo $message;
            //exit;

          
            //$headers = "MIME-Version: 1.0" . "\r\n";
            //$headers .="Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
       //$headers .="This is a multi-part message in MIME format.\r\n";

       //plain txt part

       //$headers .= "--".$uid."\r\n";
        //$headers .= "Content-type:text/plain; charset=iso-8859-1\r\n";
       //$headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";

            //$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            //$headers .= "--".$uid."\r\n";
       //$headers .= "Content-Type: ".$file_type."; name=\"".$file_name."\"\r\n";
       //$headers .= "Content-Transfer-Encoding: base64\r\n";
       //$headers .= "Content-Disposition: attachment; filename=\"".$file_name."\"\r\n\r\n";
       //$headers .= $content."\r\n\r\n";  //chucked up 64 encoded attch

            // More headers
            //$headers .= 'From: Quick serv <contact@quickserv.com>' . "\r\n";
            //$headers .= 'Cc: myboss@example.com' . "\r\n";

             //$headers = "MIME-Version: 1.0" . "\r\n";
            //$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            // More headers
            //$headers .= 'From: Quick serv <contact@quickserv.com>' . "\r\n";
      $from_email="contact@sytadel.com";
      $reply_to_email="contact@sytadel.com";

    $tmp_name    = $_FILES['photo']['tmp_name']; // get the temporary file name of the file on the server 
    $name        = $_FILES['photo']['name'];  // get the name of the file 
    $size        = $_FILES['photo']['size'];  // get size of the file for size validation 
    $type        = $_FILES['photo']['type'];  // get type of the file 
    $error       = $_FILES['photo']['error']; // get the error (if any) 

     $attachment="";
    if ($tmp_name!='') {
    $attachment = chunk_split(base64_encode(file_get_contents($tmp_name)));
       }

        $filename = $name;

        $boundary =md5(date('r', time())); 

        $headers = "From: contact@sytadel.com\r\nReply-To: contact@sytadel.com";
        $headers .= "\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"_1_$boundary\"";

        
          
if ($attachment!='') {
     $message="This is a multi-part message in MIME format.

--_1_$boundary
Content-Type: multipart/alternative; boundary=\"_2_$boundary\"

--_2_$boundary
Content-Type: text/html; charset=\"iso-8859-1\"
Content-Transfer-Encoding: 7bit

$msg

--_2_$boundary--
--_1_$boundary
Content-Type: application/octet-stream; name=\"$filename\" 
Content-Transfer-Encoding: base64 
Content-Disposition: attachment 

$attachment
--_1_$boundary--";
} else {
$message="This is a multi-part message in MIME format.

--_1_$boundary
Content-Type: multipart/alternative; boundary=\"_2_$boundary\"

--_2_$boundary
Content-Type: text/html; charset=\"iso-8859-1\"
Content-Transfer-Encoding: 7bit

$msg

--_2_$boundary--
--_1_$boundary
--_1_$boundary--";
}


  
     // echo $tmp_name;
      //exit();
            //$chk = sendMsg($emailto, $subject, $message);
           $emailto='kalpesh.abbacus@gmail.com';

           //mail($emailto, $subject, $message, $headers);
            $chk =mail($emailto,$subject,$message,$headers);
           /* if ($chk) {
              echo "Sent";
            } else {
              echo "Not Sent";
            }
            exit();*/

       // Mail::to($contactemail)->send(new ContactUs($objDemo));
        flash()->success('Thank you for contact us.');
             return redirect()->to('/contact_us_vendor?name='.$contactname.'&email='.$contactemail.'&phone='.$contactphone);

         //       $objDemo->url = $reseturl;
    }

    public function about_us()
    {
    	$cmsData = DB::table('tblcms')->where([['id', '=', 5]])->first();
        return view('front.cmspage',compact('cmsData'));
    }

    public function terms()
    {
    	$cmsData = DB::table('tblcms')->where([['id', '=', 7]])->first();
        return view('front.cmspage',compact('cmsData'));
    }

    public function about_us_vendor(Request $request)
    {
      $langId=($request->langId)?($request->langId):1;
       
       $cmsData=DB::table('tblcmstranslation')->where([['cmsId', '=',5],['langId','=',$langId]])->first();
       //echo "<pre>"; print_r($cmsData);
       //exit();
    	//$cmsData = DB::table('tblcms')->where([['id', '=', 5]])->first();
        return view('front.cmspage',compact('cmsData'));
    }

    public function terms_vendor(Request $request)
    {
      
      $langId=($request->langId)?($request->langId):1;
       
       $cmsData=DB::table('tblcmstranslation')->where([['cmsId', '=',3],['langId','=',$langId]])->first();
    	//$cmsData = DB::table('tblcms')->where([['id', '=', 3]])->first();
        return view('front.cmspage',compact('cmsData'));
    }

    public function about_us_customer(Request $request)
    {
      $langId=($request->langId)?($request->langId):1;
       
       $cmsData=DB::table('tblcmstranslation')->where([['cmsId', '=',6],['langId','=',$langId]])->first();
    	//$cmsData = DB::table('tblcms')->where([['id', '=', 6]])->first();
        return view('front.cmspage',compact('cmsData'));
    }

    public function terms_customer(Request $request)
    {
    	
      $langId=($request->langId)?($request->langId):1;
       
       $cmsData=DB::table('tblcmstranslation')->where([['cmsId', '=',4],['langId','=',$langId]])->first();
      //$cmsData = DB::table('tblcms')->where([['id', '=', 4]])->first();
        return view('front.cmspage',compact('cmsData'));
    }

    public function privacy_policy_vendor(Request $request)
    {
      $langId=($request->langId)?($request->langId):1;
       
       $cmsData=DB::table('tblcmstranslation')->where([['cmsId', '=',7],['langId','=',$langId]])->first();
        //$cmsData = DB::table('tblcms')->where([['id', '=', 7]])->first();
        return view('front.cmspage',compact('cmsData'));
    }

    public function faq_vendor(Request $request)
    {

       $langId=($request->langId)?($request->langId):1;
       
       $cmsData=DB::table('tblcmstranslation')->where([['cmsId', '=',9],['langId','=',$langId]])->first();
        //$cmsData = DB::table('tblcms')->where([['id', '=', 9]])->first();
        return view('front.cmspage',compact('cmsData'));
    }

    public function privacy_policy(Request $request)
    {
        
        $langId=($request->langId)?($request->langId):1;
       
       $cmsData=DB::table('tblcmstranslation')->where([['cmsId', '=',6],['langId','=',$langId]])->first();

        //$cmsData = DB::table('tblcms')->where([['id', '=', 8]])->first();
        return view('front.cmspage',compact('cmsData'));
    }

    public function faq_customer(Request $request)
    {

        $langId=($request->langId)?($request->langId):1;
       
       $cmsData=DB::table('tblcmstranslation')->where([['cmsId', '=',10],['langId','=',$langId]])->first();
        //$cmsData = DB::table('tblcms')->where([['id', '=', 10]])->first();
        return view('front.cmspage',compact('cmsData'));
    }

	public function sucess() {
	   return view('admin.sessions.forgot-password.vendor-reset');
	}
	
	public function getReset($token = null) {
		
		if (is_null($token)) {
            throw new NotFoundHttpException;
        }
		if ($token!='') {
			$uid = explode(':',base64_decode($token));
			$uid = $uid[1];
		    $user=Vendor::where([['id', '=',$uid]])->count();
			if ($user > 0) {
				//echo "222333";
		//exit;
			return view('admin.sessions.forgot-password.vendor-reset')->with('token', $uid);
			} else {
				flash()->error('Invalid Request.');
				return redirect()->to('/resetpassword/');
			}
		}
	}
	public function postReset(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:6|max:16',
            'id' => 'required',
            'password_confirmation' => 'required|same:password'
        ]);
        $user = Vendor::where('id', $request->id)->first();
        $user->password = bcrypt($request->password);
        $user->save();
        $rupw = base64_encode("rupw:".$request->id);
		//$reseturl = $url."/resetpassword/$rupw";
        flash()->success('Your Password has been reset successfully. you can login now.');
        return redirect()->to('/resetpassword/');
    }
	
	public function getResetCustomer($token = null) {
		
		if (is_null($token)) {
            throw new NotFoundHttpException;
        }
		if ($token!='') {
			$uid = explode(':',base64_decode($token));
			$uid = $uid[1];
		    $user=CustomerRegister::where([['id', '=',$uid]])->count();
			if ($user > 0) {
				//echo "222333";
		//exit;
			return view('admin.sessions.forgot-password.customer-reset')->with('token', $uid);
			} else {
				flash()->error('Invalid Request.');
				return redirect()->to('/resetpasswordcustomer/');
			}
		}
	}
	public function postResetCustomer(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:6|max:16',
            'id' => 'required',
            'password_confirmation' => 'required|same:password'
        ]);
        $user = CustomerRegister::where('id', $request->id)->first();
        $user->password = bcrypt($request->password);
        $user->save();
        $rupw = base64_encode("rupw:".$request->id);
		//$reseturl = $url."/resetpassword/$rupw";
        flash()->success('Your Password has been reset successfully. you can login now.');
        return redirect()->to('/resetpasswordcustomer/'.$rupw);
    }
	/*APP Customer Reset*/
    public function getAppResetCustomer($token = null) {
        
        if (is_null($token)) {
            throw new NotFoundHttpException;
        }
        if ($token!='') {
            $user=CustomerRegister::where([['remember_token', '=',$token]])->count();
            if ($user > 0) {
                return view('front.app-customer-reset')->with('token', $token);
            } else {
                flash()->error('Invalid Request.');
                return redirect()->to('/appresetpasswordcustomer/');
            }
        }
    }
    public function postAppResetCustomer(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:6|max:16',
            'token' => 'required',
            'password_confirmation' => 'required|same:password'
        ]);
           $currentTime = Carbon::now();
       $resetToken = PasswordResets::where('token', $request->token)
                        ->where('expires_at', '>', $currentTime)
                        ->first();
        if ($resetToken) {
            $user = CustomerRegister::where('email', $resetToken->email)->first();
            $user->password = bcrypt($request->password);
            $user->save();
            $resetToken->delete();
            session()->flash('success','Your Password has been reset successfully. you can login now.');
            return redirect()->to('/appresetpasswordcustomer/'.$request->token);
        } else {
            session()->flash('success','Invalid or expired token');
            return redirect()->to('/appresetpasswordcustomer/'.$request->token);
        }
        
        session()->flash('success','Your Password has been reset successfully. you can login now.');
        //flash()->success('Your Password has been reset successfully. you can login now.');
        return redirect()->to('/appresetpasswordcustomer/'.$request->token);
    }
    /*App change password*/
    public function changePassword ($id){
        $common=new CommanController;
         $title=$common->get_msg("form_greeting",1)?$common->get_msg("form_greeting",1):"We would love to hear from you!!!";
         $current_password=$common->get_msg("current_pass",1)?$common->get_msg("current_pass",1):"We would love to hear from you!!!";
         $new_password=$common->get_msg("new_pass",1)?$common->get_msg("new_pass",1):"We would love to hear from you!!!";
         $confirm_password=$common->get_msg("confirm_pass",1)?$common->get_msg("confirm_pass",1):"We would love to hear from you!!!";
         $send_message=$common->get_msg("send_msg",1)?$common->get_msg("send_msg",1):"We would love to hear from you!!!";

        return view('front.app-customer-change-password')->with('customerid',$id)->with('title', $title)->with('current_password', $current_password)->with('new_password', $new_password)->with('confirm_password', $confirm_password)->with('send_message', $send_message);
    }
    public function postChangePassword(Request $request) 
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);

        $user = CustomerRegister::findOrFail($request->customerId);

        if (!Hash::check($request->current_password, $user->password)) {
            session()->flash('error','Your current password is incorrect.');
            return redirect()->back()->with('error', 'Your current password is incorrect.');
        }

        $user->password = bcrypt($request->new_password);
        $user->save();
         session()->flash('success','Your password has been changed successfully.');
        return redirect()->back()->with('success', 'Your password has been changed successfully.');
    }
    /*End*/
     /*Payment before customer*/
   public function customerupdatepayment($CustomerId, $tapyurl){
    $customerId = $CustomerId;
    $customer = CustomerRegister::find($CustomerId);
    $tpayurl = base64_decode($tapyurl);
    
    $words = explode(' ', $customer->name);
    



    // Get the last word
    $lastWord = end($words);
    $lastname = $lastWord;


    $invoice_fname = $customer->invoice_fname;
    $invoice_lname = $customer->invoice_lname;
    $street = $customer->street;
    $company_name = $customer->companyname;
    $street_number = $customer->street_number;
    $flat_number = $customer->flat_number;
    $city = $customer->city;
    $post_code = $customer->post_code;
    $country = $customer->country;
    $nip = $customer->nip;


    return view('front.customer_payment_update',compact('company_name','customerId','tpayurl','customer','lastname','street','street_number','flat_number','city','post_code','country','nip','invoice_fname','invoice_lname'));
   }
   public function addCustomerUpdatePayment(Request $request){
     /*$user = CustomerRegister::findOrFail($request->customerId);
    $words = explode(' ', $user->name);
        // Get the last word
        $lastWord = end($words);
        $lastname = $lastWord;
        if(empty($lastname)){
            $rlastname = 'required';
        } else {
            $rlastname = 'nullable';
        }*/

        
        $user = CustomerRegister::findOrFail($request->customerId);

        $request->validate([
            'invoice_fname' => 'required',
            'invoice_lname' => 'required',
            'street' => 'required',
            'street_number' => 'required',
            'flat_number' => 'required',
            'city' => 'required',
            'post_code' => 'required',
            'country' => 'required',
        ],[
            'invoice_fname.required' => 'Please enter the first name.',           
            'invoice_lname.required' => 'Please enter the last name.', 
            'street.required' => 'Please enter the street name.',           
            'street_number.required' => 'Please enter the street number.',           
            'flat_number.required' => 'Please enter the flat number.',           
            'city.required' => 'Please enter the city name.',           
            'post_code.required' => 'Please enter the post code.',           
            'country.required' => 'Please enter the country name.',           
        ]);
   
    $user->name = $request->invoice_fname;
    $user->invoice_fname = $request->invoice_fname;
    $user->invoice_lname = $request->invoice_lname;
    $user->companyname = $request->company_name;
    $user->nip = $request->nip;
   

    $user->street = $request->street;
    $user->street_number = $request->street_number;
    $user->flat_number = $request->flat_number;
    $user->city = $request->city;
    $user->post_code = $request->post_code;
    $user->country = $request->country;
    
    $user->save();
    return redirect()->away($request->tpayurl);
   }
}
