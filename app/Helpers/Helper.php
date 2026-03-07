<?php 
  
namespace Laraspace\Helpers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;
use Laraspace\EmailTemplate;
use Laraspace\DefaultConfiguration;
class Helper 
{

    public static function tpayAuth()
    {
        $client = new Client();

        try {
            $response = $client->post('https://api.tpay.com/oauth/auth', [
                'form_params' => [
                    'client_id' => '01G6MZ2S53BFWRVVDYBH8VFHP0-01HQ0R8YK1MS8AWQ774EGKN7XK',
                    'client_secret' => '9abaf6b301806cac058ec4b884a23b160b601322a07c373656c2c07ea1b12262',
                    'scope' => 'read write'
                ]
            ]);

            $body = $response->getBody();
            $data = json_decode($body);

            $accessToken = $data->access_token;
            return $accessToken;
            // Now you can use $accessToken to make authenticated requests to the API
        } catch (\Exception $e) {
            // Handle exception
            return $e->getMessage();
        }
    }

     public static function createTransaction($data,$accessToken)
    {
        $client = new Client();

        try {
            $response = $client->post('https://api.tpay.com/transactions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    "amount" => $data['total_amount'],
                    "description" => "Create transactions",
                     "hiddenDescription" => $data['order_id'],
                    "payer" => [
                        "email" => $data['email'],
                        "name" => $data['name'],
                        
                    ],
                    "lang" => "en",
                    "callbacks" => [
                        'notification' => [
                            'url' => "https://medfellows.app/payment/notification",
                            'email' =>"archanaabbacus@gmail.com"
                        ],
                        "payerUrls" => [
                            "success" => "https://medfellows.app/payment/success",
                            "error" => "https://medfellows.app/payment/error"
                        ]
                        
                    ]
                ]
            ]);

            $body = $response->getBody();
            $data = json_decode($body);

            return $data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    /*Email send functionality*/
    public static function getEmailContent($id) {
        $emailvalue =EmailTemplate::where('id',$id)->first();
        return $emailvalue;
    }

     public static function sendemail($subject,$email,$emailtype,$content,$cc = "kalpesh@abbacus.com", $bcc = null)
    {
        
        file_put_contents(resource_path('views/emails/mail_template.blade.php'), $content);

        


        try {
            Mail::send('emails.mail_template', [], function ($message) use ($email, $cc, $bcc, $subject) {
                $message->to($email)->subject($subject);

                // Add Cc recipients
                if (!empty($cc) && is_array($cc)) {
                    $message->cc($cc);
                } elseif (!empty($cc)) {
                    $message->cc(explode(',', $cc));
                }

                // Add Bcc recipients
                if (!empty($bcc)) {
                    $message->bcc($bcc);
                }
            });

            // Log successful mail sending
            Log::info("Email sent successfully to: " . json_encode($email));
        } catch (\Exception $e) {
            // Log error message
            Log::error("Error sending email: " . $e->getMessage());
        }




/*
        Mail::send('emails.mail_template',[], function ($message) use ($email, $cc, $bcc, $subject) {
            $message->to($email)->subject($subject);

            // Add Cc recipients
           if (!empty($cc) && is_array($cc)) {
                $message->cc($cc);
            } elseif (!empty($cc)) {
                $message->cc(explode(',', $cc));
            }

            // Add Bcc recipients
            if ($bcc) {
                $message->bcc($bcc);
            }
        });*/
    }
     /*Dynamic smtp*/
    public static function smtp(){
        $config = DefaultConfiguration::first();
        return Config::set([
            'mail.host' => $config->mail_host,
            'mail.port' => $config->mail_port,
            'mail.username' => $config->mail_username,
            'mail.password' => $config->mail_password,
            'mail.encryption' => $config->mail_encryption,
            'mail.from.address' => $config->mail_from_address,
            'mail.from.name' => $config->mail_from_name,
            'mail.sendmail' => '/usr/sbin/sendmail -bs',
        ]);
    }


    /*Facebook, Google and Api URL login time first image download and store*/
    public static  function downloadAndStoreImage($url, $filename) {
        // Download image from URL
        $image = file_get_contents($url);
        // Store image locally
        $path = 'customerregisterphoto/thumbnail_images/'.$filename;
        file_put_contents(public_path($path), $image);
        return url($path);
    }

    /*Update Customer Register*/
    public static function updateCust(Request $request,$cust){
        $appType = ($request->appType) ? ($request->appType) : 0;
        $deviceToken = ($request->deviceToken) ? ($request->deviceToken) : "";
        $deviceDetails = ($request->deviceDetails) ? ($request->deviceDetails) : "";
        $loginType = ($request->loginType) ? ($request->loginType) : 1;
        $profilePictureUrl = isset($request->profilepicture) ? $request->profilepicture : '';
        $filename = $cust->photo;
        if ($profilePictureUrl && filter_var($profilePictureUrl, FILTER_VALIDATE_URL)) {
            $extension = 'jpg';
            $filename = Str::random(20) . '.' . $extension;
            // Download and store the image locally
            $profileImagePath = Helper::downloadAndStoreImage($profilePictureUrl,$filename);
        }
        // cust the attributes as needed
        $cust->name = isset($request->fullname) ? trim($request->fullname) : $cust->name;
        $cust->phone = isset($request->mobile_number) ? trim($request->mobile_number) : $cust->phone;
        $cust->email = isset($request->email_address) ? trim($request->email_address) : $cust->email;
        $cust->gender = isset($request->gender) ? trim($request->gender) : $cust->gender;
        $cust->socialId = isset($request->socialId) ? trim($request->socialId) : $cust->socialId;
        $cust->birthDate = isset($request->birthDate) ? date("Y-m-d", strtotime($request->birthDate)) : $cust->birthDate;
        $cust->photo = $filename;
        $cust->remember_token = Str::random(60);
        $cust->loginType = $loginType;
        $cust->deviceType = $appType;
        $cust->deviceToken = $deviceToken;
        $cust->deviceDetails = $deviceDetails;
        $cust->phoneVerificationSentRequestTime = date('Y-m-d H:i:s');
        $cust->updatedDate = date('Y-m-d H:i:s');
        $cust->save();
        return $cust;
    }
        /*Payment Invoice Create*/
    public static function infakt_integration_get_invoice_data($customerdata,$amount,$plandata,$serviceDetail) {
        $cname = "Medfellows";
        $fname = "Medfellows";
        $lname = "Medfellows";
        $sname = "Medfellows";
        if(!empty($customerdata)){
            $fname =$customerdata->invoice_fname;
            $lname =$customerdata->invoice_lname;
            $cname =($customerdata->companyname == null || empty($customerdata->companyname)) ? $fname.' '.$lname : $customerdata->companyname;
            $sname =$customerdata->servicename;

            $street =$customerdata->street;
            $street_number =$customerdata->street_number;
            $flat_number =$customerdata->flat_number;
            $city =$customerdata->city;
            $post_code =$customerdata->post_code;
            $country =$customerdata->country;
            //$tax_code =$customerdata->tax_code;
            $nip =$customerdata->nip;



            /*$words = explode(' ', $customerdata->name);
            // Get the last word
            $lastWord = end($words);
            $lastname = $lastWord;
            if(!empty($lastname)) {
                $lname =$lastname;
            } else {
                 $lname =$customerdata->lastname;
            }*/
        }

        //(19-09-24)The logic for calculating the VAT 23%
        Log::info('Amount before VAT: ' . json_encode($amount));
        //$AmountWithoutVAT = ($amount)/(1+(23/100));

        /*16-05-25 suggested by client*/
        $AmountWithoutVAT =  $amount-($amount*0.23);



        Log::info('Amount before VAT: ' . json_encode($AmountWithoutVAT));
        $paid_date = now()->format('Y-m-d');
        $api_key = env('INFAKT_API_KEY', ''); // Fetch API key from environment or configuration
            $invoice_data = [
                "invoice" => [
                    "payment_method" => "cash",
                    "client_company_name" => $cname,
                    "client_first_name" => $fname,
                    "client_last_name" => $lname,
                    "client_business_activity_kind" => "",
                    "client_street" => $street,
                    "client_street_number" => $street_number,
                    "client_flat_number" => $flat_number,
                    "client_city" => $city,
                    "client_post_code" => $post_code,
                    "client_tax_code" => "",
                    "client_country" => $country,
                    "status" => "paid",
                    "paid_date" => $paid_date,
                    "nip" => $nip,
                    "services" => json_decode($serviceDetail),
                ]
            ];
        $json_data =  json_encode($invoice_data);


              //echo "<pre>";print_r($json_data);exit();
            
          Log::info('infakt Invoce data: ' . $json_data);
        $url = 'https://api.infakt.pl/api/v3/async/invoices.json';
        // Set cURL options
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-inFakt-ApiKey: ' . $api_key
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json_data
        ));
        $response = curl_exec($ch);

        $response_data = json_decode($response);

        Log::info('Success Notification Received infakt before mail: ' . json_encode($response));


         Log::info('Success Notification Received infakt: ' . json_encode($response_data));
        if (curl_errno($ch)) {
            error_log('cURL Error: ' . curl_error($ch));
            Log::info('error Notification Received infakt: ' . json_encode($ch));
        }
        curl_close($ch);
        return $response;
    }
}
