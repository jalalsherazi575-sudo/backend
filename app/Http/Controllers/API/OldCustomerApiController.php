<?php
namespace Laraspace\Http\Controllers\API;

use Laraspace\Http\Controllers\CommanController;
use Laraspace\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laraspace\Mail\CustomerForgotPassword;
use Laraspace\Mail\EmailTemplateMail;
use Laraspace\Mail\WelcomeMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laraspace\CustomerRegister;
use Laraspace\EmailTemplate;
use Laraspace\DeviceToken;
use Laraspace\Country;
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
     * Customer Register
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request){
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
                        
                         $customerId=isset($registeredCustomerEmail->id)?($registeredCustomerEmail->id):0;
                
                         $loggedInData =  CustomerRegister::select('*',DB::raw("CONCAT('".url('/')."/customerregisterphoto/thumbnail_images/', photo) as photo"))->where('id',$customerId)->first();  

                         $custData=$loggedInData->toArray();
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
                            'remember_token' => Str::random(60),
                            'loginType' => $loginType,
                            'deviceType'=>$appType,
                            'deviceToken' => $deviceToken,
                            'deviceDetails' => $deviceDetails,
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
     * Customer resend verification code
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resendVerificationCode(Request $request){
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;

        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                    [
                        'mobile_number' => ['required', 'digits_between:9,13','numeric'], 
                    ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();
                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            }else{
                if(!empty($request->isNewNumber) && $request->isNewNumber == 1) {
                    $OTP = $this->generateOTP();
                    $sendArr = ['otp'=>(string)$OTP];

                    $arr['result'] = $sendArr;
                    $arr['message'] = $common->get_msg("verification_code_sent", $langId) 
                                    ? $common->get_msg("verification_code_sent", $langId) 
                                    : 'Verification code has been sent successfully.';
                    $arr['status'] = 1;
                } else {
                    $getCustomerDetails = CustomerRegister::where('phone',$request->mobile_number)->first();
                    if($getCustomerDetails) {
                        $updateArr = [
                            'OTP' => $this->generateOTP(),
                            'phoneVerificationSentRequestTime' => date('Y-m-d H:i:s'),
                        ];

                        $updatedCustomerDetail = CustomerRegister::where('phone',$request->mobile_number)->update($updateArr);
                        $getUpdatedCustomerDetail= CustomerRegister::where('phone',$request->mobile_number)->first();
                        if($getUpdatedCustomerDetail->OTP !=""){
                            $getUpdatedCustomerDetail->OTP = (string)($getUpdatedCustomerDetail->OTP);
                        }
                        $arr['result'] = $getUpdatedCustomerDetail;
                        $arr['message'] = $common->get_msg("verification_code_sent", $langId) 
                                        ? $common->get_msg("verification_code_sent", $langId) 
                                        : 'Verification code has been sent successfully.';
                        $arr['status'] = 1;

                    } else {
                        $arr['result'] = (object)array();
                        $arr['message'] = $common->get_msg("check_phone_number", $langId) 
                                        ? $common->get_msg("check_phone_number  ", $langId) 
                                        : 'Please check your phone number.';
                        $arr['status'] = 0;
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
     * Customer verification
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyCustomerCode(Request $request){
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;

        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                    [
                        'phone' => ['required', 'digits_between:9,13','numeric'],
                        'OTP' => ['required', 'digits:4','numeric'], 
                    ]
                    ,[
                        'OTP.required' => 'The OTP field is required.',
                        'OTP.digits' => 'The OTP must be 4 digits.',
                        'OTP.numeric' => 'The OTP must be a number.',
                    ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();
                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            }else{
                $getCustomer = CustomerRegister::where('phone',$request->phone)->first();
                if($getCustomer){
                    $getCustomerDetails = CustomerRegister::where('phone',$request->phone)->where('OTP',$request->OTP)->first();
                    if($getCustomerDetails){
                        $name = $getCustomerDetails->name;
                        $email = $getCustomerDetails->email;
                        $loginurl = url('/login');
                        
                        $objMail = new \stdClass();
                        $objMail->name = $name;

                        Mail::to($email)->send(new WelcomeMail($objMail));
                        

                        $updateArr = [
                            'isVerified' => 1,
                            'verifiedDate' => date('Y-m-d H:i:s'),
                        ];

                        $updatedCustomerDetail = CustomerRegister::where('phone',$request->phone)->update($updateArr);

                        $getUpdatedCustomerDetail= CustomerRegister::where('phone',$request->phone)->first();
                        
                        if($getUpdatedCustomerDetail->OTP !=""){
                            $getUpdatedCustomerDetail->OTP = (string)($getUpdatedCustomerDetail->OTP);
                        }

                        $arr['result'] = $getUpdatedCustomerDetail;
                        $arr['message'] = $common->get_msg("verifycode_success",$langId)
                                        ? $common->get_msg("verifycode_success",$langId)
                                        : "Your number has been verified successfully.";
                        $arr['status'] = 1;
                     

                    }else{
                        $arr['result'] = (object)array();
                        $arr['message'] = $common->get_msg("check_otp",$langId)
                                            ? $common->get_msg("check_otp",$langId)
                                            : "Please check your OTP.";
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

                        $loggedInData =  CustomerRegister::select('*',DB::raw("CONCAT('".url('/')."/customerregisterphoto/thumbnail_images/', photo) as photo"))->where('id',$customerId)->first(); 

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
     * Forgot Password
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request){
       
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;

        if (in_array($apiauthenticate, $authenticate)){
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
                    $rupw = base64_encode("rupw:".$userId);
                    $reseturl = $url."/resetpasswordcustomer/$rupw";
                    
                    $objDemo = new \stdClass();
                    $objDemo->name = $name;
                    $objDemo->url = $reseturl;
                        
                    Mail::to($email)->send(new CustomerForgotPassword($objDemo));
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
    public function resetPassword(Request $request){
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;

        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                        [
                            'otp' => ['required'],
                            'mobile_number' => ['required', 'digits_between:9,13','numeric'],
                            'new_password' => ['required','min:6','max:16']
                        ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
                
            } else {
                $customer=CustomerRegister::where('phone',$request->mobile_number)->first();
                if ($customer) {
                    $password = bcrypt($request->new_password);
                    $customerUpdate = CustomerRegister::where('OTP',$request->otp)->where('phone',$request->mobile_number)
                                    ->update(['password'=>$password]);

                    $resetArr=CustomerRegister::where('OTP',$request->otp)->where('phone',$request->mobile_number)->select('*',DB::raw("CONCAT('".url('/')."/customerregisterphoto/thumbnail_images/', photo) as photo"))->first();
                   
                    /*if($resetArr->OTP !=""){
                        $resetArr->OTP = (string)($resetArr->OTP);
                    }*/

                    $arr['result'] = $resetArr;
                    $arr['message'] = $common->get_msg("reset_password_message", $langId) 
                                    ? $common->get_msg("reset_password_message", $langId) 
                                    : 'Your password has been reset successfully.';
                    $arr['status'] = 1;

                } else {
                    $arr['result'] = (object)array();
                    $arr['message'] = $common->get_msg("token_invalid", $langId) 
                                    ? $common->get_msg("token_invalid", $langId) 
                                    : 'Please check OTP and phone number.';
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
                    }
                    $customerUpdateArr = [
                        'name' => ($request->fullname)?$request->fullname:"",
                        //'surname' => (ltrim($surname)),
                        'email' => ($request->email_address)?$request->email_address:"",
                        'birthDate' => isset($request->birthDate)?(ltrim(date("Y-m-d",strtotime($request->birthDate)))):"",
                        'phone' => isset($request->mobile_number)?(ltrim($request->mobile_number)):"",
                        'photo' => $profile_image,
                        'updatedDate' => date('Y-m-d H:i:s')
                    ];

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

    /**
     * Change Mobile NUmber
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyNumber(Request $request){
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        
        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                        [
                            'phone' => ['required', 'digits_between:9,13','numeric','unique:tblcustomerregister,phone,'.$request->userId]
                        ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            } else {
                $OTPGenerate = $this->generateOTP();
                $sendArr = ['otp'=>(string)$OTPGenerate];

                $arr['result'] = $sendArr;
                $arr['message'] = $common->get_msg("change_number", $langId) 
                                ? str_replace("{mobile_number}", $request->phone, $common->get_msg("change_number", $langId))
                                : 'OTP has been sent successfully on '.$request->phone.'.';
                $arr['status'] = 1; 
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
     * Verify Mobile Number
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function confirmOTP(Request $request){
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;

        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                        [
                            'mobile_number' => ['required', 'digits_between:9,13','numeric']
                        ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
                
            } else{
                $customerData = CustomerRegister::where('id',$request->userId)->first();

                $updateArr = [
                    'OTP' => $request->otp,
                    'phone' => $request->mobile_number,
                    'isVerified' => 1,
                    'verifiedDate' => date('Y-m-d H:i:s')
                ];

                if($customerData) {

                    CustomerRegister::where('id',$request->userId)->update($updateArr);

                    $customerUpdatedData = CustomerRegister::where('id',$request->userId)->first();
                    
                    if($customerUpdatedData->OTP !=""){
                        $customerUpdatedData->OTP = (string)($customerUpdatedData->OTP);
                    }

                    $arr['result'] = $customerUpdatedData;
                    $arr['message'] = $common->get_msg("updated_mobile_number", $langId) 
                                    ? $common->get_msg("updated_mobile_number", $langId) 
                                    : 'Mobile number has been changed successfully.';
                    $arr['status'] = 1; 

                } else {
                    
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
     * Choose Prodile Image
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function chooseProfileImage(Request $request){
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        

        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                        [
                            'userId' => ['required','exists:tblcustomerregister,id']
                        ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            } else{
                $filename='';
                $profile_image='';
                if ($request->file('image')) {
                    $file = $request->file('image');
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

                CustomerRegister::where('id',$request->userId)->update(['photo'=>$profile_image]);

                $customerInfo = CustomerRegister::select('*',DB::raw("CONCAT('".url('/')."/customerregisterphoto/thumbnail_images/', photo) as photo"))->where('id',$request->userId)->first();

                if($customerInfo->OTP !=""){
                    //$customerInfo->OTP = (string)($customerInfo->OTP);
                }

                $arr['result'] = $customerInfo;
                $arr['message'] = $common->get_msg("profile_image_update", $langId) 
                                    ? $common->get_msg("profile_image_update", $langId) 
                                    : 'Profile image has been updated successfully.';
                $arr['status'] = 1;                        
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
     * Get user detail 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getUserDetails(Request $request){
        $common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $userId = ($request->userId) ? ($request->userId) : 0;


        if (in_array($apiauthenticate, $authenticate)){
            $validator = Validator::make($request->all() , 
                    [
                        'userId' => ['required','exists:tblcustomerregister,id']
                    ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();
                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            
            }else{
                $loggedInData =  CustomerRegister::select('*',DB::raw("CONCAT('".url('/')."/customerregisterphoto/thumbnail_images/', photo) as photo"))->where('id',$userId)->first();                        

                if($loggedInData->OTP !=""){
                    //$loggedInData->OTP = (string)($loggedInData->OTP);
                }

                //$arr['result'] = $getCustomerDetail;
                $arr['result'] =$loggedInData;
                $arr['message'] = "";
                $arr['status'] = 1;
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
}

