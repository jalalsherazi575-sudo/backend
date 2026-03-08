<?php
phpinfo();
exit();
include 'PHPMailerAutoload.php';
$emailto = 'aditya@webtechsystem.com';
$subject = "Email - Verification";
//$message="Testing Cards - 10-05-2019.";
$mail = new PHPMailer;
 $mail->SMTPDebug = 4;
define("SITE_URL","http://motivatecards.com/");
//echo SITE_URL;
//exit;
$code=222223;
$message='<html>
<body>
<center>
<table style="width: 600px; margin: 0 auto; background: #4a515b; border-collapse: collapse; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: 100%; color: #fff; font-size: 16px; line-height: 26px;" cellspacing="0" cellpadding="0" border="0">
  <tbody>
    <tr>
      <td style="text-align: center; padding: 10px 0;"><img src="https://motivatecards.com/images/logo.png"  alt="" style="max-width: 100%; border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;"></td>
    </tr>
    
    <tr>
      <td style="padding: 0 15px; font-family: Open Sans,sans-serif;">
         <table style="width: 100%; background: #fff; line-height: 26px;" cellspacing="0" cellpadding="0" border="0">
          <tbody>
           <tr>
      <td style="padding: 10px 0 10px 20px; font-family: Open Sans,sans-serif; font-size: 14px;color: #7e869b; font-weight: 600;">Hello</td>
    </tr>

            <tr>
              <td style="padding: 25px 20px; font-size: 15px; font-weight: 600; color:#7e869b; text-align: left; font-family: Open Sans,sans-serif;">You are receiving this email because we received a password reset request for your account.</td>
            </tr>
            <tr>
              <td style="text-align: center;"><a href="https://motivatecards.com/" style="color:#6FB144!important;text-decoration: none; font-weight: bold;font-size: 20px;">
              <span style="color:#6FB144;text-decoration: none; font-weight: bold;font-size: 20px;font-family: Open Sans,sans-serif;margin:0;">Reset Password</span></a></td>
            </tr>
            <tr>
              <td style="padding: 25px 20px; font-size: 15px; font-weight: 600; color:#7e869b; text-align: left; font-family: Open Sans,sans-serif;">If you did not request a password reset, no further action is required.</td>
            </tr>
            <tr>
              <td style="padding: 25px 20px; font-size: 15px; font-weight: 600; color:#7e869b; text-align: left; font-family: Open Sans,sans-serif;">Thank you<br/>Regards,<br/>Motivate Team</td>
            </tr>
 
          </tbody>
        </table></td>
    </tr>
    <tr>
      <td style="padding: 18px 15px; font-family: Open Sans;"><table style="width: 100%; color: #fff; font-size: 16px; line-height: 26px;" cellspacing="0" cellpadding="0" border="0">
          <tbody>
            <tr>
              <td style="text-align: center;"><a href="#" style="color: #fff !important; text-decoration: none; font-weight: normal;font-size: 20px;"><span style="color:#fff;"><font color=" #fff">Motivate Cards Limited</font></span></a></td>
            </tr>
          </tbody>
        </table></td>
    </tr>
  </tbody>
</table>
</center>
</body>
</html>';

$message_data = '<html><body style="margin:0; padding:0;">
<table style="margin:0 auto; width:570px; border:2px solid #CF2027; " border="0" cellpadding="0" cellspacing="0">
<thead>
<tr>
<th style="text-align:center; background:#fff; border-bottom:2px solid #CF2027; padding:22px 0 20px;"><img src="'.SITE_URL.'images/logo.png" alt="" style="width:50%;"/></th>
</tr>
</thead>
<tbody>
<tr>
<td style=" font-weight:bold;font-family:Arial, Helvetica, sans-serif; color:#868686; font-size:16px; padding:32px 30px 0;">Hello '.$name.',</td>
</tr>
<tr>
<td style="font-family:Arial, Helvetica, sans-serif; color:#868686; font-size:13px; padding:32px 30px 0;line-height:21px;">Kindly click on the below link to verify your account.</td>
</tr>
<tr>
<td style="font-family:Arial, Helvetica, sans-serif; color:#868686; font-size:13px; line-height:21px;padding:32px 30px 0;"> <a href="'.SITE_URL.'emailverify.php?email='.$emailto.'&status=1" style="color:#6FB144!important;text-decoration: none; font-weight: bold;font-size: 20px;" target="_blank">
<span style="color:#6FB144;text-decoration: none; font-weight: bold;font-size: 20px;font-family: Open Sans,sans-serif;margin:0;">Verify</span></a></td>
</tr>
<tr>
<td style="font-family:Arial, Helvetica, sans-serif; color:#868686; font-size:13px; line-height:21px;padding:32px 30px 0;"> Verification Code: '.$code.'</td>
</tr>
<tr>
<td style="font-family:Arial, Helvetica, sans-serif; color:#868686; font-size:13px; line-height:21px;padding:32px 30px 30px;">Thank you,<br/>Secure That Job Team</td>
</tr>
</tbody>
</table>
</body></html>';

 $mail->isSMTP();
 $mail->Host = 'smtp.gmail.com';
 $mail->SMTPAuth = true;
 $mail->Username = 'maunik@webtechsystem.com';
 $mail->Password = 'abbacus007';
 $mail->Port = 587;
 $mail->SMTPSecure = 'tls';
 $mail->isHTML(true);
 //$mail->setFrom('admin@motivatecards.com', 'Motivate Cards');
 $mail->From = 'contact@mobilegiz.com';
    $mail->FromName ='Motivate Cards';
 $mail->addAddress($emailto); 
// $file_to_attach = $_SERVER['DOCUMENT_ROOT'].'/test/upload/';
 //echo $file_to_attach;
 //exit;
 //$mail->AddAttachment($file_to_attach , '1514554157.jpg' );
 $mail->Subject = $subject;
 $mail->Body    = $message_data;
 if(!$mail->send()) {
	 $error = "Mailer Error: " . $mail->ErrorInfo;
  echo '<p id="para">'.$error.'</p>';
  //return false;
 } else {
  echo "Sent Mail.";
 }
			//$chk = sendMsg($emailto, $subject, $message);
?>