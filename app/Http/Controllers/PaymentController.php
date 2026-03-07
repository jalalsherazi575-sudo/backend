<?php

namespace Laraspace\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Laraspace\Helpers\Helper;
use phpseclib3\File\X509;
use Laraspace\TransactionMaster;
use Laraspace\TransactionDetails;
use Laraspace\CustomerRegister;
use Laraspace\PlanPackage;
use Laraspace\Subject;
use Laraspace\LevelManagement;

class PaymentController extends Controller
{
     public function handleNotification(Request $request)
    {
         // Get Request X-JWS-Signature header
        $jws = $request->header('X-JWS-Signature');
       
        // Specify the file path
        $file_path = '/home/medfelu/www/public/file.txt'; // Update this with the actual file path
        // Check if the file exists
       
        if (null === $jws) {
            return response('FALSE - Missing JSW header', 400);
        }
        // Extract JWS header properties
        $jwsData = explode('.', $jws);
        $headers = isset($jwsData[0]) ? $jwsData[0] : null;
        $signature = isset($jwsData[2]) ? $jwsData[2] : null;
        if (null === $headers || null === $signature) {
            return response('FALSE - Invalid JWS header', 400);
        }
         // Decode received headers json string from base64_url_safe
        $headersJson = base64_decode(strtr($headers, '-_', '+/'));
        // Get x5u header from headers json
        $headersData = json_decode($headersJson, true);
        $x5u = isset($headersData['x5u']) ? $headersData['x5u'] : null;
        if (null === $x5u) {
            return response('FALSE - Missing x5u header', 400);
        }
        // Check certificate url
        $prefix = 'https://secure.tpay.com';
        if (strpos($x5u, $prefix) !== 0) {
            return response('FALSE - Wrong x5u url', 400);
        }
        // Get JWS sign certificate from x5u uri
        $certificate = file_get_contents($x5u);
        // Verify JWS sign certificate with Tpay CA certificate
        // Get Tpay CA certificate to verify JWS sign certificate. CA certificate be cached locally.
        $trusted = file_get_contents('https://secure.tpay.com/x509/tpay-jws-root.pem');
        // in php7.4+ with ext-openssl you can use openssl_x509_verify
        if (1 !== openssl_x509_verify($certificate, $trusted)) {
            return response('FALSE - Signing certificate is not signed by Tpay CA certificate', 400);
        }
         // or using phpseclib
        $x509 = new \phpseclib3\File\X509();
        $x509->loadX509($certificate);
        $x509->loadCA($trusted);
        if (!$x509->validateSignature()) {
            return response('FALSE - Signing certificate is not signed by Tpay CA certificate', 400);
        }
        // Get request body
        $body = $request->getContent();
        // Encode body to base64_url_safe
        $payload = str_replace('=', '', strtr(base64_encode($body), '+/', '-_'));
        // Decode received signature from base64_url_safe
        $decodedSignature = base64_decode(strtr($signature, '-_', '+/'));
        // Verify RFC 7515: JSON Web Signature (JWS) with ext-openssl
        // Get public key from certificate
        $publicKey = openssl_pkey_get_public($certificate);
        if (1 !== openssl_verify($headers . '.' . $payload, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256)) {
            return response('FALSE - Invalid JWS signature', 400);
        }
        // or using phpseclib
        $publicKey = $x509->getPublicKey()
            ->withHash('sha256')
            ->withPadding(\phpseclib3\Crypt\RSA::SIGNATURE_PKCS1);
        if (!$publicKey->verify($headers . '.' . $payload, $decodedSignature)) {
            return response('FALSE - Invalid JWS signature', 400);
        }
        // JWS signature verified successfully.
        // Process request data and send valid response to notification service.
        $transactionData = $request->post();
          \Log::info('Success: ' . json_encode($transactionData));
        
        if($transactionData['tr_status'] == 'TRUE'){
            \Log::info('tr_status ' . 1);
            $update = TransactionMaster::where('transaction_order_id',$transactionData['tr_crc'])->first();
            if(!empty($update)){
                \Log::info('TransactionMaster ' . 'if');
                $update->transaction_id = $transactionData['tr_id'];
                $update->payment_status = '1';
                $update->paymentDate = $transactionData['tr_date'];
                $update->transaction_detail = json_encode($transactionData);
                $update->save();
                    \Log::info('TransactionMaster detail ' .json_encode($update));
                /*Transction Detail update*/
                $updatedetails = TransactionDetails::where('transaction_id',$update->id)->get();
                $serviceDetail = array();
                $services =array();
                if(count($updatedetails) != 0){
                     \Log::info('TransactionDetails ' .json_encode($updatedetails));

                     foreach($updatedetails as $key => $updatedetail){
                        $planMonth =  $updatedetail->plan_month;
                        $updatedetail->status = '1';
                        $endDate = now()->addMonths($planMonth); 
                        \Log::info('planMonth ' .$planMonth);
                        \Log::info('enddate ' .$endDate);
                        $updatedetail->end_date = $endDate;
                        $updatedetail->save();

                        $plan = PlanPackage::find($updatedetail->plan_id);
                        $planSubject = Subject::find($updatedetail->subject_id);
                        $LevelManagement = LevelManagement::find($updatedetail->category_id);

                        \Log::info('PlanPackage Detail : ' .json_encode($plan));
                        \Log::info('Subject Detail : ' .json_encode($planSubject));
                        \Log::info('LevelManagement Detail : ' .json_encode($LevelManagement));
                        $amount = 0;
                        $AmountWithoutVAT = 0;
                        $net_price = 0;

                        if ($plan != "") {
                            //$amount = $plan->packagePrice;
                            //$AmountWithoutVAT = ($amount)/(1+(23/100));

                            /*16-05-25 suggested by client*/
                            /*$AmountWithoutVAT =  $amount-($amount*0.23);
                            $net_price = $AmountWithoutVAT*100;*/

                            // netto = brutto / (1 + VAT%)

                            $vatRate = 23; // procent
                            $amount   = round($plan->packagePrice, 2);
                            $net_price = round($amount / (1 + $vatRate / 100), 2);
                            
                            $amount = $amount*100;
                            $net_price = $net_price*100;


                        }                       

                        $subjectName = "";
                        if ($planSubject != "") {
                            $subjectName = $planSubject->subjectName;
                        }
                        $levelName = "";
                        if ($LevelManagement != "") {
                            $levelName = $LevelManagement->levelName;
                        }

                        $packageName = "";
                        $packagePeriodInMonth = "";

                        if ($plan != "") {
                            $packageName = $plan->packageName;
                            $packagePeriodInMonth = $plan->packagePeriodInMonth;
                        }


                        $ItemName = $packageName . "  (ważność: " . $packagePeriodInMonth . " miesiąc) (" . $levelName . "-" . $subjectName . ") ";

                        \Log::info('ItemName : ' .json_encode($ItemName));

                        $services[$key] = array(
                                'name' => $ItemName,
                                //'net_price'=> $net_price,
                                'net_price'=> $net_price,
                                'pkwiu' => 0,
                                'unit_net_price' => $net_price,
                                'gross_price' => $amount,
                                'tax_symbol' => 23 
                            );
                        \Log::info('services : ' .json_encode($services[$key]));

                     }
                }

                \Log::info('serviceDetail : ' .json_encode($services));
                 /*24-07-24 Invoice create*/
                $customer = CustomerRegister::find($update->customer_id);
                \Log::info('CustomerRegister Detail : ' .json_encode($customer));
                $customerdata = array();
                if(!empty($customer)){
                 $customerdata = $customer;   
                }
                $amount = $update->total_amount ?? 0;
                $transactionDetail =TransactionDetails::where('transaction_id',$update->id)->first();
                \Log::info('TransactionDetails Detail : ' .json_encode($transactionDetail));
                $plan = PlanPackage::find($transactionDetail->plan_id);
                \Log::info('PlanPackage Detail : ' .json_encode($plan));
                $plandata = array();
                if(!empty($plan)){
                 $plandata = $plan;   
                }

                $cname =($customerdata->companyname == null || empty($customerdata->companyname)) ? $customerdata->name : $customerdata->companyname;

                
                $invoice = Helper::infakt_integration_get_invoice_data($customerdata,$amount,$plandata,json_encode($services));

                \Log::info('Helper infakt_integration_get_invoice_data fuction response : ' .json_encode($invoice));

                /*Mail to admin for new payment done by customer*/
                $mail = Helper::getEmailContent(6);
                if (!empty($mail)) {
                    $Data = [
                        'customername' => "Admin",
                        'name' => $cname,
                        'email' => $transactionData['tr_email'],
                        'logourl' => url('/assets/admin/img/logo.svg'),
                        'tr_id' => $transactionData['tr_id']
                    ];
                    $mailDescription = str_replace(
                        ['#logourl', '#customername','#name', '#email','#transaction_id'],
                        [$Data['logourl'],$Data['customername'],$Data['name'], $Data['email'],$Data['tr_id']],
                        $mail->description
                    );

                    try {
                        // Dynamic SMTP configuration
                        $config = Helper::smtp();
                        // Send the email

                        $sendemail = Helper::sendemail($mail->subject, $mail->mail_to, 1, $mailDescription, $mail->mail_cc, $mail->mail_bcc);

                        // Check if email was sent successfully
                        if ($sendemail) {
                            \Log::info('Email sent successfully to admin after infakt payment');
                        } else {
                            \Log::error('Failed to send email to admin after infakt payment');
                        }
                    } catch (\Exception $e) {
                        \Log::error('Exception occurred while sending email: ' . $e->getMessage());
                    }
                }


                
            }

        }

         \Log::info('Success Notification Received: ' . json_encode($transactionData));
        
        return response('TRUE', 200);
        // Respond with success message
       // return response()->json(['message' => 'Notification received and data inserted successfully']);
    }
    public function handleSuccessCallback(Request $request) {
        \Log::info('Success ' .json_encode($request->all()));

     return view('front.paymentsuccess'); }

    public function handleErrorCallback(Request $request){  \Log::info('handlerror ' .json_encode($request->all())); return view('front.paymentredirect'); }




    public function checktransaction(){        

        //For testing infakt invoice in production environment replace transaction_order_id
      
        $update = TransactionMaster::where('transaction_order_id',"##transaction_order_id##")->first();

          //echo "<pre>asdasdas";print_r($update);exit();
        
        \Log::info('TransactionMaster detail : ' .json_encode($update));
        if(!empty($update)){
            /*Transction Detail update*/
            $updatedetails = TransactionDetails::where('transaction_id',$update->id)->get();
            
            \Log::info('TransactionDetails : ' .json_encode($updatedetails));
            
            $serviceDetail = array();
            $services =array();
            if(count($updatedetails) != 0){ 
                 foreach($updatedetails as $key => $updatedetail){
                    $planMonth =  $updatedetail->plan_month;
                    $updatedetail->status = '1';
                    $endDate = now()->addMonths($planMonth);                    
                    
                    $updatedetail->end_date = $endDate;                   

                    //$updatedetail->save();

                    $plan = PlanPackage::find($updatedetail->plan_id);
                    $planSubject = Subject::find($updatedetail->subject_id);
                    $LevelManagement = LevelManagement::find($updatedetail->category_id);

                    \Log::info('PlanPackage Detail : ' .json_encode($plan));
                    \Log::info('Subject Detail : ' .json_encode($planSubject));
                    \Log::info('LevelManagement Detail : ' .json_encode($LevelManagement));
                    $amount = 0;
                    $AmountWithoutVAT = 0;
                    $net_price = 0;

                    if ($plan != "") {
                        /*$amount = $plan->packagePrice;
                        $AmountWithoutVAT = ($amount)/(1+(23/100));
                        $net_price = $AmountWithoutVAT*100;*/


                        $vatRate = 23; // procent
                        $amount   = round($plan->packagePrice, 2);
                        $net_price = round($amount / (1 + $vatRate / 100), 2);

                        $amount = $amount*100;
                        $net_price = $net_price*100;


                    }

                    

                    $subjectName = "";
                    if ($planSubject != "") {
                        $subjectName = $planSubject->subjectName;
                    }
                    $levelName = "";
                    if ($LevelManagement != "") {
                        $levelName = $LevelManagement->levelName;
                    }

                    $packageName = "";
                    $packagePeriodInMonth = "";

                    if ($plan != "") {
                        $packageName = $plan->packageName;
                        $packagePeriodInMonth = $plan->packagePeriodInMonth;
                    }


                    $ItemName = $packageName . "  (ważność: " . $packagePeriodInMonth . " miesiąc) (" . $levelName . "-" . $subjectName . ") ";

                    \Log::info('ItemName : ' .json_encode($ItemName));

                    //$services[$key] = array('name' => $ItemName,'net_price'=> $net_price,'pkwiu' => 0,'unit_net_price' =>$net_price,'tax_symbol' => 23 );

                    $services[$key] = array(
                                'name' => $ItemName,
                                //'net_price'=> $net_price,
                                'net_price'=> $net_price,
                                'pkwiu' => 0,
                                'unit_net_price' => $net_price,
                                'gross_price' => $amount,
                                'tax_symbol' => 23 
                            );


                    \Log::info('services : ' .json_encode($services[$key]));

                 }
            }

           // $serviceDetail = array('services' => $services);
                    \Log::info('serviceDetail : ' .json_encode($services));
             /*24-07-24 Invoice create*/
            $customer = CustomerRegister::find($update->customer_id);
                    \Log::info('CustomerRegister Detail : ' .json_encode($customer));
            $customerdata = array();
            if(!empty($customer)){
             $customerdata = $customer;   
            }
            $amount = $update->total_amount ?? 0;
            $transactionDetail =TransactionDetails::where('transaction_id',$update->id)->first();
                    \Log::info('TransactionDetails Detail : ' .json_encode($transactionDetail));
            $plan = PlanPackage::find($transactionDetail->plan_id);
                    \Log::info('PlanPackage Detail : ' .json_encode($plan));
            $plandata = array();
            if(!empty($plan)){
             $plandata = $plan;   
            }
            $invoice = Helper::infakt_integration_get_invoice_data($customerdata,$amount,$plandata,json_encode($services));
                    \Log::info('Helper infakt_integration_get_invoice_data fuction response : ' .json_encode($invoice));
        }
    }

   
}