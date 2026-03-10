<?php
namespace Laraspace\Http\Controllers\API;

use Laraspace\Http\Controllers\CommanController;
use Laraspace\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laraspace\Mail\CustomerForgotPassword;
use Illuminate\Support\Facades\Log;
use Laraspace\Mail\EmailTemplateMail;
use Laraspace\Mail\WelcomeMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laraspace\UserSubscriptionPlan;
use Laraspace\CustomerRegister;
use Laraspace\EmailTemplate;
use Laraspace\DeviceToken;
use Laraspace\Country;
use Laraspace\PasswordResets;
use Laraspace\TransactionMaster;
use Laraspace\TransactionDetails;
use Laraspace\Topics;
use Laraspace\Questions;
use Carbon\Carbon;
use Laraspace\Helpers\Helper;
use Validator;
use Hash;
use Config;
use Image;
use Mail;
use DB;


class CustomerApiController extends Controller{
    /*
    |--------------------------------------------------------------------------
    | Customer Functionality Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Config $authenticate){
        $this->_authenticate = config('constant.authenticate');
    }

    /*
     * Generate OTP using random function
     */
    public function generateOTP(){
        $fourRandomDigit = mt_rand(1000,9999);
        return $fourRandomDigit;
    }

    /*
     * Generate refferal code of length 8.
     */
    public function generateRefferalCode($length_of_string) { 
        // String of all alphanumeric character 
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
        return substr(str_shuffle($str_result), 0, $length_of_string); 
    } 
    /**
     * Customer Login
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request){  

        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;

        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $deviceType = ($request->appType) ? ($request->appType) : 0;
        $deviceToken = ($request->deviceToken) ? ($request->deviceToken) : "";
        $deviceDetails = ($request->deviceDetails) ? ($request->deviceDetails) : "";
        $loginType = ($request->loginType) ? ($request->loginType) : 1;
        $email_address = isset($request->email_address) ? (ltrim($request->email_address)) : "" ;

        $OTPGenerate = $this->generateOTP();

        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                    [
                        'email_address' => ['required'], 
                        'password' => ['required']
                    ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            }else{
                $checkcustomer = CustomerRegister::where([['email', '=', $email_address]])->count();
                if ($checkcustomer == 0){
                    $arr['result'] = (object)array();
                    $arr['message'] = $common->get_msg("customer_user_not_found", $langId) 
                                    ? $common->get_msg("customer_user_not_found", $langId) 
                                    : 'Customer user not found.';
                    $arr['status'] = 0;
                }else{
                    $customerData = CustomerRegister::where('email',$email_address)->first();


                    if($customerData->OTP !=""){
                        //$customerData->OTP = (string)($customerData->OTP);
                    }

                    $customerPassword = $customerData->password;
                    $customerId = $customerData->id;
                    $customerStatus = $customerData->isActive;
                    $isVerified = $customerData->isVerified;
                    $verifiedDate = $customerData->verifiedDate;
                    
                    if (!Hash::check($request->password, $customerPassword)){
                        $arr['result'] = (object)array();
                        $arr['message'] = $common->get_msg("invalid_password", $langId) 
                                        ? $common->get_msg("invalid_password", $langId) 
                                        : 'Invalid Password.';
                        $arr['status'] = 0;

                    }else if ($customerStatus == 0) {
                        $arr['result'] = (object)array();
                        $arr['message'] = $common->get_msg("account_inactive", $langId) 
                                        ? $common->get_msg("account_inactive", $langId) 
                                        : 'Your account is inactive. Do you want to reactivate your account?';
                        $arr['status'] = 2;
                    } else {

                        $user = CustomerRegister::find($customerId);
                        $user->loginStatus = 1;
                        $user->lastLoginDate = date('Y-m-d H:i:s');
                        $user->deviceType = $deviceType;
                        $user->deviceDetails = $deviceDetails;
                        $user->deviceToken = $deviceToken;
                        $user->lastLoginDate = gmdate("Y-m-d h:i:s");
                        $user->save();

                        $loggedInData =  CustomerRegister::select('*')->where('id',$customerId)->first(); 

                        $custData=$loggedInData->toArray();
                        $packageData=$common->getUserSubscriptionPlanPackage($customerId);
                        $CustomerSubscriptionArr=array_merge($custData,$packageData);                       

                        /*if($loggedInData->OTP !=""){
                            $loggedInData->OTP = (string)($loggedInData->OTP);
                        }*/

                        //$arr['result'] = $loggedInData;
                        $arr['result'] = $CustomerSubscriptionArr;
                        $arr['message'] = $common->get_msg("customer_login_success", $langId) 
                                        ? $common->get_msg("customer_login_success", $langId) 
                                        : 'Login successfully.';
                        $arr['status'] = 1;
                    }
                }
            }
        }else{
            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }
        return response()->json($arr);
    }

    /**
     * Customer logout
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request){
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $userId = ($request->userId) ? ($request->userId) : 0;


        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                    [
                        'userId' => ['required','exists:tblcustomerregister,id'],
                        //'deviceType' => ['required'],
                    ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();
                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            }else{
                $customerCheck = CustomerRegister::where('id',$userId)->first();
                if ($customerCheck) {
                    $customerCheckDeviceType = CustomerRegister::where('id',$userId)->first();
                    if ($customerCheckDeviceType) {
                        CustomerRegister::where('id',$request->userId)->update(['deviceToken'=>""]);
                        $customerDeviceToken = DeviceToken::where('customerId',$userId)->first();
                        if ($customerDeviceToken) {
                            DeviceToken::where('customerId',$userId)->update(['deviceToken'=>""]);
                        }

                        $arr['result'] = (object)array();
                        $arr['message'] = $common->get_msg("logout",$langId)
                                        ? $common->get_msg("logout",$langId)
                                        : 'Logout successfully.';
                        $arr['status'] = 1;
                        
                    }else{
                        $arr['result'] = (object)array();
                        $arr['message'] = $common->get_msg("check_device_type", $langId) 
                                        ? $common->get_msg("check_device_type", $langId) 
                                        : 'Please check device type you entered.';
                        $arr['status'] = 0;
                    }
                        
                }else{
                    $arr['result'] = (object)array();
                    $arr['message'] = $common->get_msg("customer_user_not_found", $langId) 
                                    ? $common->get_msg("customer_user_not_found", $langId) 
                                    : 'Customer user not found.';
                    $arr['status'] = 0;
                }
            }  

        }else{
            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }

        return response()->json($arr);
    }

    /**
     * Customer Register
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request){


          //echo "<pre>";print_r($request->all());exit();
        

        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $appType = ($request->appType) ? ($request->appType) : 0;
        $deviceToken = ($request->deviceToken) ? ($request->deviceToken) : "";
        $deviceDetails = ($request->deviceDetails) ? ($request->deviceDetails) : "";
        $loginType = ($request->loginType) ? ($request->loginType) : 1;
        $email_address = isset($request->email_address) ? (ltrim($request->email_address)) : "" ;

        if (in_array($apiauthenticate, $authenticate)){

            /** Validation For app register (loginType 1) **/
            if($loginType == 1) {
                $validator = Validator::make($request->all() , 
                [
                    'fullname' => ['required'],
                    'password' => ['required'],
                    'isTerms' => ['required'],
                    'mobile_number' => ['required', 'digits_between:9,13','numeric']
                ]);
            }

            /** Validation For facebook - loginType 2 , googleplus - loginType 3, twitter - loginType 4 register (loginType 1) **/
            if($loginType == 2 || $loginType == 3 || $loginType == 4) {
                $validator = Validator::make($request->all() , 
                [
                    //'fullname' => ['required'],
                    'socialId' => ['required'],
                    // 'mobile_number' => ['unique:tblcustomerregister,phone']
                ]);
            }

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            }else{

                /** For app register (loginType 1) **/
                if($loginType == 1) {
                    
                    $filename='';
                    $profile_image='';
                    if ($request->file('profilepicture')) {

                        $file = $request->file('profilepicture');
                        $size = getimagesize($file);
                        $ratio = $size[0]/$size[1];
                        $common=new CommanController;
                        $profileimagewidth=100;
                        $profileimageheight=100;

                        if($size[0] > $profileimagewidth || $size[1] > $profileimageheight){
                            if( $ratio > 1) {
                                $width = $profileimagewidth;
                                $height = $profileimagewidth/$ratio;
                            }else if( $ratio < 1) {
                                $width = $profileimageheight/$ratio;
                                $height = $profileimageheight;
                            }else {
                                $width = $profileimagewidth;
                                $height = $profileimageheight;
                            }
                        }else{
                            $width = $profileimagewidth;
                            $height = $profileimageheight;
                        }

                        $extension = $file->getClientOriginalExtension();
                        $profile_image=time().$file->getClientOriginalName();
                        $profile_destinationPath = public_path('/customerregisterphoto/thumbnail_images');
                        $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
                        $thumb_img->save($profile_destinationPath.'/'.$profile_image,80);
                         
                        // print_r($file->getClientOriginalName());exit();
                        $filename=$file->getClientOriginalName();
                        $destinationPath = 'customerregisterphoto';
                        $file->move($destinationPath,$profile_image);
                    }

                    $OTPGenerate = $this->generateOTP();
                    $registeredCustomer = CustomerRegister::where('phone',$request->mobile_number)->first();

                    $registeredCustomerEmail = CustomerRegister::where('email',$email_address)->first();

                    if(!empty($registeredCustomer)) {
                        
                        if($registeredCustomer->OTP !="") {
                            //$registeredCustomer->OTP = (string)($registeredCustomer->OTP);
                        }

                        $customerID=isset($registeredCustomer->id)?($registeredCustomer->id):0;
                        $custData=$registeredCustomer->toArray();
                        $packageData=$common->getUserSubscriptionPlanPackage($customerID);
                        $arr=array('isNewUser'=>0);
                        $CustomerSubscriptionArr=array_merge($custData,$packageData,$arr);

                        //$arr['result'] = $registeredCustomer;
                        $arr['result'] = $CustomerSubscriptionArr;
                        $arr['message'] = $common->get_msg("customer_already_registered", $langId) 
                                        ? $common->get_msg("customer_already_registered", $langId) 
                                        : 'The phone has already been taken.';
                        $arr['status'] = 0;

                    } elseif (!empty($registeredCustomerEmail)) {

                        $customerID=isset($registeredCustomerEmail->id)?($registeredCustomerEmail->id):0;
                        $custData=$registeredCustomerEmail->toArray();
                        $packageData=$common->getUserSubscriptionPlanPackage($customerID);
                        $arr=array('isNewUser'=>0);
                        $CustomerSubscriptionArr=array_merge($custData,$packageData,$arr);

                        //$arr['result'] = $registeredCustomerEmail;
                        $arr['result'] = $CustomerSubscriptionArr;
                        $arr['message'] = $common->get_msg("customer_already_registered_email", $langId) 
                                        ? $common->get_msg("customer_already_registered_email", $langId) 
                                        : 'The email address has already been taken.';
                        $arr['status'] = 0;
                    
                    }  else {

                        $customerRegister = CustomerRegister::create([
                            'name' => (ltrim($request->fullname)),
                            'phone' => isset($request->mobile_number)?(ltrim($request->mobile_number)):"",
                            'email' => (ltrim($request->email_address)),
                            'password' => (ltrim(bcrypt($request->password))),
                            'gender' => isset($request->gender)?(ltrim($request->gender)):0,
                            'birthDate' => isset($request->birthDate)?(ltrim(date("Y-m-d",strtotime($request->birthDate)))):"",
                            'isActive' => 1,
                            //'OTP' => $OTPGenerate,
                            'remember_token' => Str::random(60),
                            'loginType' => $loginType,
                            'deviceType'=>$appType,
                            'deviceToken' => $deviceToken,
                            'deviceDetails' => $deviceDetails,
                            'phoneVerificationSentRequestTime' => date('Y-m-d H:i:s'),
                            'photo' => $profile_image,
                            'isTerms' => (ltrim($request->isTerms)),
                            'isMarketingConsent' => (ltrim($request->isMarketingConsent)),
                            'createdDate' => date('Y-m-d H:i:s'),
                            'updatedDate' => date('Y-m-d H:i:s'),
                        ]);

                        $customerID = $customerRegister->id;

                        if($customerID) {
                            $tokenCreateArr = [ 'customerId'=>$customerID,
                                                    'deviceType'=>$appType,
                                                    'deviceToken'=>$deviceToken,
                                                    'loginStatus'=>1,
                                                    'deviceDetails'=>$deviceDetails,
                                                    'tokenDate'=>date('Y-m-d H:i:s')
                                                ];

                            DeviceToken::create($tokenCreateArr);

                            $customerData = CustomerRegister::where('id',$customerID)->first();


                            if($customerData->OTP !="") {
                               // $customerData->OTP = (string)($customerData->OTP);
                            }

                            $custData=$customerData->toArray();
                            $packageData=$common->getUserSubscriptionPlanPackage($customerID);
                            $arr=array('isNewUser'=>1);
                            $CustomerSubscriptionArr=array_merge($custData,$packageData,$arr);

                            //$arr['result'] = $customerData;
                            $arr['result'] = $CustomerSubscriptionArr;
                            $arr['message'] = $common->get_msg("customer_added", $langId) ? $common->get_msg("customer_added", $langId) 
                                                : 'Your have successfully registered.';
                            $arr['status'] = 1;
                             /* 24-22-2024Mail Send customer*/
                            $mail = Helper::getEmailContent(3);
                            if (!empty($mail)) {
                                $Data = [
                                    'logourl'       => url('/assets/admin/img/logo.svg'),
                                    'customername'  => $request->fullname,
                                    'email_address' => $request->email_address,
                                    'password'  => $request->password,
                                ];
                                $mailDescription = str_replace(
                                    ['#logourl', '#customername','#email','#password'],
                                    [$Data['logourl'],$Data['customername'],$Data['email_address'],$Data['password']],
                                    $mail->description
                                );

                                try {
                                    // Dynamic SMTP configuration
                                    $config = Helper::smtp();
                                    // Send the email
                                    $sendemail = Helper::sendemail($mail->subject, $request->email_address, 1, $mailDescription, $mail->mail_cc, $mail->mail_bcc);
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
                            /*End*/
                        } else {
                            $arr['result'] = (object)array();
                            $arr['message'] = $common->get_msg("something_went_wrong", $langId) 
                                            ? $common->get_msg("something_went_wrong", $langId) 
                                            : 'Oops, Something went wrong.';
                            $arr['status'] = 0;
                        }
                    }
                }

                /** For facebook - loginType 2 , googleplus - loginType 3, twitter - loginType 4 register (loginType 1) **/
                if($loginType == 2 || $loginType == 3 || $loginType == 4) {
                    $registeredCustomerSocialNotVerified = CustomerRegister::where('socialId',$request->socialId)->first();
                    $notverifiedMobileNumber = CustomerRegister::where('phone',$request->mobile_number)->first();
                    $registeredCustomerSocialVerified = CustomerRegister::where('socialId',$request->socialId)->first();
                    $registeredCustomerEmail = CustomerRegister::where('email',$email_address)->first();
                    $OTPGenerate = $this->generateOTP();

                    if(!empty($registeredCustomerSocialNotVerified)) {
                        if($registeredCustomerSocialNotVerified->OTP !="") {
                            //$registeredCustomerSocialNotVerified->OTP = (string)($registeredCustomerSocialNotVerified->OTP);
                        }
                        $customerID=isset($registeredCustomerSocialNotVerified->id)?($registeredCustomerSocialNotVerified->id):0;

                        $custData=$registeredCustomerSocialNotVerified->toArray();
                        $packageData=$common->getUserSubscriptionPlanPackage($customerID);
                        $arr=array('isNewUser'=>0);
                        $CustomerSubscriptionArr=array_merge($custData,$packageData,$arr);
                        /*Update Data 25-4-2024 */
                        $cust = Helper::updateCust($request,$registeredCustomerSocialNotVerified);
                        /*End*/
                        //$arr['result'] = $registeredCustomerSocialNotVerified;
                        $arr['result'] = $CustomerSubscriptionArr;
                        $arr['message'] = $common->get_msg("social_id_exists", $langId) 
                                        ? $common->get_msg("social_id_exists", $langId) 
                                        : 'The Social Id has already been taken.';
                        $arr['status'] = 1;

                    } else if(!empty($notverifiedMobileNumber)) {
                        if($notverifiedMobileNumber->OTP !="") {
                           // $notverifiedMobileNumber->OTP = (string)($notverifiedMobileNumber->OTP);
                        }

                        $customerID=isset($notverifiedMobileNumber->id)?($notverifiedMobileNumber->id):0;
                        /*Update Data 25-4-2024 */
                        $cust = Helper::updateCust($request,$notverifiedMobileNumber);
                        /*End*/
                        $custData=$notverifiedMobileNumber->toArray();
                        $packageData=$common->getUserSubscriptionPlanPackage($customerID);
                        $arr=array('isNewUser'=>0);
                        $CustomerSubscriptionArr=array_merge($custData,$packageData,$arr);

                        //$arr['result'] = $notverifiedMobileNumber;
                        $arr['result'] = $CustomerSubscriptionArr;
                        $arr['message'] = $common->get_msg("customer_already_registered", $langId) 
                                        ? $common->get_msg("customer_already_registered", $langId) 
                                        : 'The phone has already been taken.';
                        $arr['status'] = 1;
                    
                    }  else if(!empty($registeredCustomerSocialVerified)) {
                        if($registeredCustomerSocialVerified->OTP !="") {
                           // $registeredCustomerSocialVerified->OTP = (string)($registeredCustomerSocialVerified->OTP);
                        }
                        $customerID=isset($registeredCustomerSocialVerified->id)?($registeredCustomerSocialVerified->id):0;
                        /*Update Data 25-4-2024 */
                        $cust = Helper::updateCust($request,$registeredCustomerSocialVerified);
                        /*End*/
                        $custData=$registeredCustomerSocialVerified->toArray();
                        $packageData=$common->getUserSubscriptionPlanPackage($customerID);
                        $arr=array('isNewUser'=>0);
                        $CustomerSubscriptionArr=array_merge($custData,$packageData,$arr);

                        //$arr['result'] = $registeredCustomerSocialVerified;
                        $arr['result'] = $CustomerSubscriptionArr;
                        $arr['message'] = $common->get_msg("social_login", $langId) 
                                        ? $common->get_msg("social_login", $langId) 
                                        : 'login';
                        $arr['status'] = 1;

                    } else if(!empty($registeredCustomerEmail) && $email_address!='') {
                        /*Update Data 25-4-2024 */
                        $cust = Helper::updateCust($request,$registeredCustomerEmail);
                        /*End*/
                        $customerId=isset($registeredCustomerEmail->id)?($registeredCustomerEmail->id):0;
                
                        //$loggedInData =  CustomerRegister::select('*',DB::raw("CONCAT('".url('/')."/customerregisterphoto/thumbnail_images/', photo) as photo"))->where('id',$customerId)->first();  
                         // Fixed: Use $request->socialId instead of $this->socialId
                         $loggedInData =  DB::table('tblcustomerregister')
                                -> select('*',
                                    DB::raw(
                                        empty($request->socialId)
                                            ? "CONCAT('" . url('/') . "/customerregisterphoto/thumbnail_images/', photo) as photo"
                                            : "photo as photo"
                                    )
                                )->where('id',$customerId)->first(); 
                         $custData= (array) $loggedInData; 
                         $packageData=$common->getUserSubscriptionPlanPackage($customerId);
                         $arr=array('isNewUser'=>0);
                         $CustomerSubscriptionArr=array_merge($custData,$packageData,$arr);

                         //$arr['result'] = $loggedInData;
                         $arr['result'] = $CustomerSubscriptionArr;
                         $arr['message'] = $common->get_msg("customer_already_registered_email", $langId) 
                                        ? $common->get_msg("customer_already_registered_email", $langId) 
                                        : 'The email address has already been taken.';
                         $arr['status'] = 1;    

                    } else {
                        $profilePictureUrl = isset($request->profilepicture) ? $request->profilepicture : '';
                        $extension = 'jpg';
                        $filename = '';
                        if ($profilePictureUrl && filter_var($profilePictureUrl, FILTER_VALIDATE_URL)) {
                            $filename = Str::random(20) . '.' . $extension;
                            // Download and store the image locally
                            $profileImagePath = Helper::downloadAndStoreImage($profilePictureUrl,$filename);
                        }


                        $customerRegisterSocial = CustomerRegister::create([
                            'name' => (ltrim($request->fullname)),
                            'phone' => isset($request->mobile_number)?(ltrim($request->mobile_number)):"",
                            'email' => (ltrim($request->email_address)),
                            'password' => "",
                            'gender' => isset($request->gender)?(ltrim($request->gender)):0,
                            'birthDate' => isset($request->birthDate)?(ltrim(date("Y-m-d",strtotime($request->birthDate)))):"",
                            'isActive' => 1,
                            //'OTP' => $this->generateOTP(),
                            'socialId' => isset($request->socialId)?(ltrim($request->socialId)):"",
                            'photo' => $filename,
                            'remember_token' => Str::random(60),
                            'loginType' => $loginType,
                            'deviceType'=>$appType,
                            'deviceToken' => $deviceToken,
                            'deviceDetails' => $deviceDetails,
                            'isTerms' => (ltrim($request->isTerms)),
                            'isMarketingConsent' => trim($request->isMarketingConsent),
                            'phoneVerificationSentRequestTime' => date('Y-m-d H:i:s'),
                            'createdDate' => date('Y-m-d H:i:s'),
                            'updatedDate' => date('Y-m-d H:i:s'),
                        ]);

                        $customerIDSocial = $customerRegisterSocial->id;

                        if($customerIDSocial) { 

                            $tokenCreateArrSocial = [ 'customerId'=>$customerIDSocial,
                                                'deviceType'=>$appType,
                                                'deviceToken'=>$deviceToken,
                                                'loginStatus'=>1,
                                                'deviceDetails'=>$deviceDetails,
                                                'tokenDate'=>date('Y-m-d H:i:s')
                                            ];

                            DeviceToken::create($tokenCreateArrSocial);

                            $customerDataSocial = CustomerRegister::where('id',$customerIDSocial)->first();

                            if($customerDataSocial->OTP !="") {
                                //$customerDataSocial->OTP = (string)($customerDataSocial->OTP);
                            }

                            $custData=$customerDataSocial->toArray();
                            $packageData=$common->getUserSubscriptionPlanPackage($customerIDSocial);
                            $arr=array('isNewUser'=>1);
                            $CustomerSubscriptionArr=array_merge($custData,$packageData,$arr);

                            //$arr['result'] = $customerDataSocial;
                            $arr['result'] = $CustomerSubscriptionArr;
                            $arr['message'] = $common->get_msg("customer_added", $langId) 
                                            ? $common->get_msg("customer_added", $langId) 
                                            : 'Your have successfully registered.';
                            $arr['status'] = 1;

                        } else {
                            $arr['result'] = (object)array();
                            $arr['message'] = $common->get_msg("something_went_wrong", $langId) 
                                            ? $common->get_msg("something_went_wrong", $langId) 
                                            : 'Oops, Something went wrong.';
                            $arr['status'] = 0;
                        }
                    }
                }
            }
        }else{
            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }
        return response()->json($arr);
    }

     /**
     * Update customer profile
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function profileUpdate(Request $request){
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        

        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                        [
                            'userId' => ['required'],
                            'fullname' => ['required']
                            //'email_address' => ['unique:tblcustomerregister,email,'.$request->userId]
                        ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
                
            } else {

                $customer=CustomerRegister::where('id',$request->userId)->first();

                if ($customer) {
                     $customerUpdateArr = [
                        'name' => ($request->fullname)?$request->fullname:"",
                        //'surname' => (ltrim($surname)),
                        'email' => ($request->email_address)?$request->email_address:"",
                        'birthDate' => isset($request->birthDate)?(ltrim(date("Y-m-d",strtotime($request->birthDate)))):"",
                        'phone' => isset($request->mobile_number)?(ltrim($request->mobile_number)):"",
                        
                        'updatedDate' => date('Y-m-d H:i:s')
                    ];
                    if ($request->file('profilePic')) 
                    {
                        $file = $request->file('profilePic');
                        $size = getimagesize($file);
                        $ratio = $size[0]/$size[1];
                        $common=new CommanController;
                        $profileimagewidth=100;
                        $profileimageheight=100;

                        if($size[0] > $profileimagewidth || $size[1] > $profileimageheight){
                            if( $ratio > 1) {
                                $width = $profileimagewidth;
                                $height = $profileimagewidth/$ratio;
                            }else if( $ratio < 1) {
                                $width = $profileimageheight/$ratio;
                                $height = $profileimageheight;
                            }else {
                                $width = $profileimagewidth;
                                $height = $profileimageheight;
                            }
                        }else{
                            $width = $profileimagewidth;
                            $height = $profileimageheight;
                        }

                        $extension = $file->getClientOriginalExtension();
                        $profile_image=time().$file->getClientOriginalName();
                        $profile_destinationPath = public_path('/customerregisterphoto/thumbnail_images');
                        $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
                        $thumb_img->save($profile_destinationPath.'/'.$profile_image,80);
                         
                        // print_r($file->getClientOriginalName());exit();
                        $filename=$file->getClientOriginalName();
                        $destinationPath = 'customerregisterphoto';
                        $file->move($destinationPath,$profile_image);
                          $customerUpdateArr['photo'] = $profile_image;
                    }

                    CustomerRegister::where('id',$request->userId)->update($customerUpdateArr);

                    $getCustomerDetail = CustomerRegister::where('id',$request->userId)->first();

                    if($getCustomerDetail->OTP !="") {
                        //$getCustomerDetail->OTP = (string)($getCustomerDetail->OTP);
                    }
                 
                    $arr['result'] = $getCustomerDetail;
                    $arr['message'] = $common->get_msg("profile_update", $langId) 
                                    ? $common->get_msg("profile_update", $langId) 
                                    : 'Profile updated successfully.';
                    $arr['status'] = 1;


                }else{
                    $arr['result'] = (object)array();
                    $arr['message'] = $common->get_msg("customer_user_not_found", $langId) 
                                    ? $common->get_msg("customer_user_not_found", $langId) 
                                    : 'Customer user not found.';
                    $arr['status'] = 0;
                }
            }
        }else{

            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }
        return response()->json($arr);
    }


    public function changePassword(Request $request) {
        $common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $newpassword=($request->newpassword)?($request->newpassword):"";
        $oldpassword=($request->oldpassword)?($request->oldpassword):"";
        $userId=($request->userId)?($request->userId):0;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
         
        if (in_array($apiauthenticate,$authenticate)) {
            if (!$userId) {
                $myarray['result']=array();                   
                $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
                $myarray['status']=0;
            }  else if (!$oldpassword) {
                $myarray['result']=array();                   
                $myarray['message']=$common->get_msg("blank_old_pass",$langId)?$common->get_msg("blank_old_pass",$langId):"Please enter old password.";
                $myarray['status']=0;
            } else if (!$newpassword) {
                $myarray['result']=array();                   
                $myarray['message']=$common->get_msg("blank_new_pass",$langId)?$common->get_msg("blank_new_pass",$langId):"Please enter new password.";
                $myarray['status']=0;
            } else {
                $customer = DB::table('tblcustomerregister')->where('id', '=',$userId)->first();         
                if ($customer) {
                    $customerpassword=($customer->password)?($customer->password):"";
                    if (!Hash::check($oldpassword,$customerpassword)) {
                        $myarray['result']=(object)array();
                        $myarray['message']=$common->get_msg("invalid_old_password",$langId)?$common->get_msg("invalid_old_password",$langId):'Invalid old password.';
                        $myarray['status']=0;
                    } else {
                        $customer = CustomerRegister::find($userId);
                        $customer->password=(bcrypt($newpassword));
                        $customer->save();
                        $myarray['result']=(object)array();
                        $myarray['message']=$common->get_msg("updated_password",$langId)?$common->get_msg("updated_password",$langId):'Your Password has been updated successfully.';
                        $myarray['status']=1;
                    }
                } else {
                    $myarray['result']=(object)array(); 
                    $myarray['status']=0;
                    $myarray['message']=$common->get_msg("invalid_customerId",$langId)?$common->get_msg("invalid_customerId",$langId):'Invalid customerId.';
                }
            }
        } else {
            $myarray['result']=(object)array();                 
            $myarray['message']="Invalid Authentication.";
            $myarray['status']=0;
        } 
        return response()->json($myarray);
    }

    /**
     * Forgot Password
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request)
    {   
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        if (in_array($apiauthenticate, $authenticate))
        {
            $validator = Validator::make($request->all() , 
                        [
                            'email_address' => ['required']                            
                        ]);
            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
                
            } else {
                $customer=CustomerRegister::where('email',$request->email_address)->first();
                if ($customer) {
                   
                    //$resArr = ['otp'=>(string)$customer->OTP];
                    $name=isset($customer->name)?($customer->name):'';
                    $email=isset($customer->email)?($customer->email):'';
                    $userId=isset($customer->id)?($customer->id):0;

                    $url=url('/');
                    $rupw = $this->generateRefferalCode(10); ;
                    $reseturl = $url."/appresetpasswordcustomer/$rupw";
                    $logourl = url('/assets/admin/img/favicon.png');
                    $remember_token = CustomerRegister::where( 'email',$request->email_address)->update(['remember_token'  => $rupw]);
                    $expiresAt = now()->addMinutes(30);
                    DB::table('password_resets')->insert([
                        'email' => $request->email_address,
                        'token' => $rupw,
                        'expires_at' => $expiresAt,
                        'created_at' => now(),
                    ]);

                    $objDemo = new \stdClass();
                    $objDemo->name = $name;
                    $objDemo->url = $reseturl;
                    $objDemo->logo = $logourl;
                        
                    //Mail::to($email)->send(new CustomerForgotPassword($objDemo));
                    /*Mail 24-4-2024*/
                    $mail = Helper::getEmailContent(2);
                    if (!empty($mail)) {
                        $Data = [
                            'customername' => $name,
                            'url' => $reseturl,
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

                            $sendemail = Helper::sendemail($mail->subject, $email, 1, $mailDescription, $mail->mail_cc, $mail->mail_bcc);

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
                    $forgotPasswordMsg = $common->get_msg("forgotPasswordMsg",$langId) ? $common->get_msg("forgotPasswordMsg",$langId) : 'Reset password link sent on your registered email';

                    //$msg = $common->get_msg("otp_sent",$langId) ? str_replace('{$mobile_number}',$request->mobile_number,$common->get_msg("otp_sent",$langId)) : 'OTP has been sent successfully on your {$request->mobile_number}.';

                    $arr['result'] = (object)array();
                    $arr['message'] = $forgotPasswordMsg;
                    $arr['status']=1;
                } else {
                    $forgotPasswordMsg = $common->get_msg("forgotPasswordUserNotFound",$langId) ? $common->get_msg("forgotPasswordUserNotFound",$langId) : 'User does not exist!';

                    $arr['result'] = (object)array();
                    $arr['message'] = $forgotPasswordMsg;
                    $arr['status'] = 0;
                }
            }
        }else{
            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }
        return response()->json($arr);
    }

    /**
     * Reset Password
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;

        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all(), [
                    'token' => ['required'],
                    'new_password' => ['required'],
                    //'new_password_confirmation' => ['required', 'same:new_password']
                ]);
            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
                
            } else {

                 // Set the current time
                $currentTime = Carbon::now();
                // Retrieve the email associated with the reset token
                $resetToken = PasswordResets::where('token', $request->token)
                            ->where('expires_at', '>', $currentTime)
                            ->first();
                if ($resetToken) {
                    $password = bcrypt($request->new_password);
                    $user = CustomerRegister::where('email', $resetToken->email)->first();
                    if($user){
                        $user->update([
                            'password' => $password,
                        ]);
                        $resetToken->delete();
                        $arr['result'] = $user;
                        $arr['message'] = $common->get_msg("reset_password_message", $langId) 
                                        ? $common->get_msg("reset_password_message", $langId) 
                                        : 'Your password has been reset successfully.';
                        $arr['status'] = 1;
                    }else {
                        $arr['result'] = (object)array();
                        $arr['message'] = $common->get_msg("user_invalid", $langId) 
                                        ? $common->get_msg("user_invalid", $langId) 
                                        : 'Customer Not Found';
                        $arr['status'] = 0;  
                    }
                   /* $resetArr=CustomerRegister::where('OTP',$request->otp)->where('phone',$request->mobile_number)->select('*',DB::raw("CONCAT('".url('/')."/customerregisterphoto/thumbnail_images/', photo) as photo"))->first();*/
                   
                    /*if($resetArr->OTP !=""){
                        $resetArr->OTP = (string)($resetArr->OTP);
                    }*/
                } else {
                    $arr['result'] = (object)array();
                    $arr['message'] = $common->get_msg("token_invalid", $langId) 
                                    ? $common->get_msg("token_invalid", $langId) 
                                    : 'Invalid or expired token';
                    $arr['status'] = 0;
                }
            }

        }else{

            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }
        return response()->json($arr);
    }

    /**
     * Delete customer profile
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteUser(Request $request){
        


        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        

        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                        [
                            'userId' => ['required'],
                            //'email_address' => ['unique:tblcustomerregister,email,'.$request->userId]
                        ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
                
            } else {

                $customer=CustomerRegister::where('id',$request->userId)->first();

                if ($customer) {

                    $customerUpdateArr = [
                        'name' => "Annonimous",
                        //'surname' => (ltrim($surname)),
                        'email' => "annonimous@gmail.com",
                        'phone' => "Annonimous",
                        'socialId' => Null,
                        'updatedDate' => date('Y-m-d H:i:s')
                    ];

                    CustomerRegister::where('id',$request->userId)->update($customerUpdateArr);
                    
                    $arr['result'] = (object)array();
                    $arr['message'] = 'Profile has been deleted successfully.';
                    $arr['status'] = 1;


                }else{
                    $arr['result'] = (object)array();
                    $arr['message'] = $common->get_msg("customer_user_not_found", $langId) 
                                    ? $common->get_msg("customer_user_not_found", $langId) 
                                    : 'Customer user not found.';
                    $arr['status'] = 0;
                }
            }
        }else{

            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }
        return response()->json($arr);
    }


     /*Customer Plan History*/
    public function customerPlanHistory(Request $request)
    {
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $userId = ($request->userId) ? ($request->userId) : 0;
        $Display_Level_Id=$common->get_msg('Display_Level_Id') ? $common->get_msg('Display_Level_Id') :1;
        $curDate=date("Y-m-d");

        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                [ 
                    'userId' => ['required']
                ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            }else{
                $userSubscriptionData = TransactionDetails::with([
                    'customer' => function ($query) {
                        $query->select('id', 'name'); // Select only the 'id' and 'name' columns from the customers table
                    },
                    'category' => function ($query) {
                        $query->select('levelId', 'levelName'); 
                    },
                    'subject' => function ($query) {
                        $query->select('id', 'categoryId','subjectName'); 
                    },
                    'planpackage'
                ])
                ->where('customer_id', $userId)
                ->orderBy('id', 'DESC')
                ->get();
                /*
                $userSubscriptionData = UserSubscriptionPlan::select('id','packageId','packageName','packagePrice','packagePeriodInMonth','isActive','createdDate','expiryDate','cancelDate','fromDate','androidPlanKey','iosPlanKey')->where('userId',$userId)->orderby('expiryDate','desc')->get();*/
                $userSubscriptionArr=[];
                if(count($userSubscriptionData) > 0) {
                   /* foreach($userSubscriptionData as $subData) {
                        $usersubscriptionId=isset($subData->id)?($subData->id):0;
                        $userpackageId=isset($subData->packageId)?($subData->packageId):0;
                        $packageName=isset($subData->packageName)?($subData->packageName):"";
                        $createdDate=isset($subData->createdDate)?($subData->createdDate):"";
                        $packagePrice=isset($subData->packagePrice)?($subData->packagePrice):0;
                        $packagePeriodInMonth=isset($subData->packagePeriodInMonth)?($subData->packagePeriodInMonth):0;
                        $expiryDate=isset($subData->expiryDate)?($subData->expiryDate):'';
                        $androidPlanKey=isset($subData->androidPlanKey)?($subData->androidPlanKey):'';
                        $iosPlanKey=isset($subData->iosPlanKey)?($subData->iosPlanKey):'';
                        $createdDate=isset($subData->createdDate)?($subData->createdDate):"";
                        $cancelDate=isset($subData->cancelDate)?($subData->cancelDate):"";
                        $isActive=isset($subData->isActive)?($subData->isActive):0;
                        $fromDate=isset($subData->fromDate)?($subData->fromDate):"";
                          
                        if ($isActive==1 && $expiryDate >= $curDate ) {
                            $statusName='Active';
                        } elseif($isActive==0) {
                            $statusName='InActive';
                        } elseif($isActive==2) {
                            $statusName='Expired';
                        } else {
                            $statusName='InActive';
                        }  
                        $userSubscriptionArr[]=array("usersubscriptionId"=>$usersubscriptionId,"packageId"=>(int)$userpackageId,"packageName"=>$packageName,"packagePrice"=>$packagePrice,"packagePeriodInMonth"=>$packagePeriodInMonth,"androidPlanKey"=>$androidPlanKey,"iosPlanKey"=>$iosPlanKey,"createdDate"=>$createdDate,"cancelDate"=>$cancelDate,"expiryDate"=>$expiryDate,"packageStatus"=>(int)$isActive,"packageStatusName"=>$statusName,"fromDate"=>$fromDate);
                    }*/
                    $arr['result'] = $userSubscriptionData;
                    $arr['message'] = $common->get_msg("purchased_plan_list", $langId) ? $common->get_msg("purchased_plan_list", $langId) : 'Purchased Plan List.';
                    $arr['status'] = 1;  
                } else {
                    $arr['result'] = (object)array();
                   $arr['message'] = $common->get_msg("no_purchased_plan_list", $langId) ? $common->get_msg("purchased_plan_list", $langId) : "You haven't purchased any plan.";
                   $arr['status'] = 1;  
                } 
            }

        } else {
            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }
        return response()->json($arr);
    } 

    /*Customer Plan Purches*/
    public function customerPurchase(Request $request)
    {

       // echo "<pre>";print_r($request->all());exit;
        
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $userId = ($request->userId) ? ($request->userId) : 0;
        $Display_Level_Id=$common->get_msg('Display_Level_Id') ? $common->get_msg('Display_Level_Id') :1;
        $curDate=date("Y-m-d");

        if (in_array($apiauthenticate, $authenticate))
        { 
            $validator = Validator::make($request->all() , 
            [ 
                'customer_id' => ['required']
            ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            } else {

                $user = CustomerRegister::find($request->customer_id);

                if ($user == null) {
                    $arr['result'] = (object)array();
                    $arr['message'] ="User not found.";
                    $arr['status'] = 0;
                    return response()->json($arr);
                }

                $create = new TransactionMaster;
                $create->transaction_id = Null;
                $create->customer_id = $request->customer_id;
                $create->transaction_order_id = $request->customer_id.'_'.now();
                $create->transaction_type = Null;
                $create->total_amount = $request->total_amount;
                $create->payment_status =  0;
                $create->paymentDate = Null;
                $create->save();

                /*Detail*/
                $purchaseList = $request->input('purchase_list');
                 // Check if decoding was successful and if $purchaseList is an array
                if (is_array($purchaseList)) {
                    if ($request->total_amount == "0.00") {
                        foreach ($purchaseList as $purchaseItem) {

                            $transactionDetails = new TransactionDetails;
                            $transactionDetails->transaction_id = $create->id;
                            $transactionDetails->customer_id = $request->customer_id;
                            $transactionDetails->transaction_order_id = $request->customer_id . '_' . now();
                            $transactionDetails->category_id = $purchaseItem['category_id'];
                            $transactionDetails->subject_id = $purchaseItem['subject_id'];
                            $transactionDetails->plan_id = $purchaseItem['plan_id'];
                            $transactionDetails->plan_month = $purchaseItem['plan_month'];
                            $transactionDetails->plan_amount = $purchaseItem['plan_amount'];
                            $transactionDetails->start_date = date('Y-m-d H:i:s');
                            $transactionDetails->end_date = date('Y-m-d H:i:s', strtotime('+'.$purchaseItem['plan_month'].' month', strtotime(now())));
                            $transactionDetails->status = '1';
                            $transactionDetails->save();
                        }
                    }else{
                       foreach ($purchaseList as $purchaseItem) {
                            $transactionDetails = new TransactionDetails;
                            $transactionDetails->transaction_id = $create->id;
                            $transactionDetails->customer_id = $request->customer_id;
                            $transactionDetails->transaction_order_id = $request->customer_id . '_' . now();
                            $transactionDetails->category_id = $purchaseItem['category_id'];
                            $transactionDetails->subject_id = $purchaseItem['subject_id'];
                            $transactionDetails->plan_id = $purchaseItem['plan_id'];
                            $transactionDetails->plan_month = $purchaseItem['plan_month'];
                            $transactionDetails->plan_amount = $purchaseItem['plan_amount'];
                            $transactionDetails->start_date = now();
                            $transactionDetails->end_date = null;
                            $transactionDetails->status = 0;
                            $transactionDetails->save();
                        } 
                    }


                } else {
                    // Handle JSON decoding error
                    echo "Error decoding JSON or the decoded value is not an array.";
                }

                if ($request->total_amount != "0.00") {
                    /*create Transction*/
                    $accessToken = Helper::tpayAuth();
                    /*Trascation Data*/
                    $data = array();
                    $user = CustomerRegister::find($request->customer_id);
                    $data['total_amount'] = $request->total_amount;
                    $data['email'] = $user->email;
                    $data['name'] = $user->name;
                    $data['order_id'] = $create->transaction_order_id;
                    $transactionResult = Helper::createTransaction($data, $accessToken);
                    $tpayUrl = $transactionResult->transactionPaymentUrl;
                    if(!empty($tpayUrl)){
                        //return redirect()->away($tpayUrl);
                         $words = explode(' ', $user->name);
                        // Get the last word
                        $lastWord = end($words);
                        
                         /*if((empty($lastWord) || empty($user->lastname)) && !empty($user->companyname) && !empty($user->servicename)){
                           $url = $tpayUrl;
                        } else {
                             $tapy =base64_encode($tpayUrl);
                            $url = url('customerupdatepayment/'.$request->customer_id .'/'. $tapy);
                            
                        }*/
                        $tapy =base64_encode($tpayUrl);
                        $url = url('customerupdatepayment/'.$request->customer_id .'/'. $tapy);
                        
                        $arr['result'] =  $url;
                        $arr['message'] = $common->get_msg("create_payment_link", $langId) 
                                        ? $common->get_msg("create_payment_link", $langId) 
                                        : 'Creaet payment link successfully.';
                        $arr['status'] = 1;
                     } else {
                        $arr['result'] = (object)array();
                        $arr['message'] = $common->get_msg("invalid_authentication_tpay", $langId) 
                                        ? $common->get_msg("invalid_authentication_tpay", $langId) 
                                        : 'Invalid Authentication.';
                        $arr['status'] = 0;
                     }
                }else{

                   // echo "<pre>";print_r(url('api/customer/successfully_payment'));exit;
                    
                    $arr['result'] = url('/api/customer/successfully_payment');
                    $arr['message'] = $common->get_msg("create_payment_link", $langId) 
                                    ? $common->get_msg("create_payment_link", $langId) 
                                    : 'Creaet payment link successfully.';
                    $arr['status'] = 1;



                    //echo "<pre>";print_r($arr);exit;
                    
                }

            }
        } else {
            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }
        return response()->json($arr);
    }

    public function successfully_payment()
    {
        
        return view('front.paymentsuccess');
    }
    /*Customer serch*/
    public function search(Request $request)
    {
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = $request->header('AUTHENTICATE') ?? 1;
        $langId = $request->header('langId') ?? 1;
        $searchTerm = $request->search;

        // Get customer purchased subjects
        $purchasedSubjects = CustomerRegister::findOrFail($request->cust_id)
            ->transactionDetails()
            ->pluck('subject_id');

        // Get questions related to topics in purchased subjects with eager loading
        $questions = Questions::whereHas('topics.subject', function ($query) use ($purchasedSubjects) {
            $query->whereIn('subjectId', $purchasedSubjects);
        })
        ->where('question', 'LIKE', '%' . $searchTerm . '%')
        ->with(['topics.subject.category'])
        ->get();

        $results = $questions->map(function ($question) {
            return $question->topics->map(function ($topic) use ($question) {
                return [
                    'question_id' => $question->questionId,
                    'question' => $question->question,
                    'topic' => $topic->topicName,
                    'subject' => $topic->subject->subjectName,
                    'category' => $topic->subject->category->levelName
                ];
            });
        })->flatten(1);

        if ($results->isNotEmpty()) {
            $arr['result'] = $results;
            $arr['message'] = $common->get_msg("search", $langId) 
                                    ? $common->get_msg("search", $langId) 
                                    : 'Question List.';
            $arr['status'] = 1;
        } else {
            $arr['result'] = (object)[];
            $arr['message'] = $common->get_msg("search_error", $langId) 
                            ? $common->get_msg("search_error", $langId) 
                            : 'Question not found.';
            $arr['status'] = 0;
        }
        
        return response()->json($arr);
    }
}

