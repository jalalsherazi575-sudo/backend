<?php
namespace Laraspace\Http\Controllers\API;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Session;
use Laraspace\Http\Controllers\Controller; 
use Laraspace\BusinessCategory; 
use Laraspace\IdProof;
use Laraspace\Vendor;
use Laraspace\Customer;
use Laraspace\CustomerLocation;
use Laraspace\VendorBusiness;
use Laraspace\VendorProof;
use Laraspace\ServiceType;
use Laraspace\Language;
use Laraspace\Achievement;
use Laraspace\AchievementPhoto;
use Laraspace\PortFolio;
use Laraspace\PortFolioFiles;
use Laraspace\ProductCategory;
use Laraspace\ProductPhoto;
use Laraspace\Country;
use Laraspace\State;
use Laraspace\City;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Validator;
use Laraspace\Http\Controllers\CommanController;
use Hash;
use Mail;
use Laraspace\Mail\VendorForgotPassword;
use Laraspace\Mail\CustomerForgotPassword;
use Laraspace\Mail\GeneralMail;

use Twilio\Rest\Client;


class MobApiController extends Controller 
{
public $successStatus = 200;
/** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
	 
	 /* Vendor Login */

	public function sendSms( Request $request )
    {
       // Your Account SID and Auth Token from twilio.com/console
       $sid    = env('TWILIO_ACCOUNT_SID');
       $token  = env('TWILIO_AUTH_TOKEN');
       $from   = env('TWILIO_PHONE_NUMBER');
        
       $client = new Client( $sid, $token );

       $validator = Validator::make($request->all(), [
           'numbers' => 'required',
           'message' => 'required'
       ]);

       $numbers=($request->numbers)?($request->numbers):"";
       $message=($request->message)?($request->message):"";

       if ( $validator->passes() ) {
              $messages="";
		        try
		        {
		            $client->messages->create(
		                $numbers,
		           array(
		                 'from' => env('TWILIO_PHONE_NUMBER'),
		                 'body' => $message
		             )
		         );
		        $messages ="success";
			   }catch (\Exception $e)
			        {
			          if($e->getCode() == 21614)
						{
					   	  $messages = $e->getMessage();
					   } else {
			              $messages="Invalid Mobile No.";
					   }
			            
			        }

           echo $messages;
           exit();
           return back()->with( 'success', $count . " messages sent!" );
       } else {
           return back()->withErrors( $validator );
       }
   }
	 public function login(Request $request) { 
	    
		$deviceType=($request->deviceType)?($request->deviceType):0;
	    $deviceToken=($request->deviceToken)?($request->deviceToken):"";
	    $deviceDetails=($request->deviceDetails)?($request->deviceDetails):"";	
	    $langId=($request->header('langId'))?($request->header('langId')):1; 
		
		
		$checkvendor = DB::table('tblvender')->where([['email', '=', $request->email]])->count();
		//$checkvendor = DB::table('tblvender')->where([['email', '=', $request->email]])->count();
		
        $common=new CommanController;

        if (!$request->email) {
		   $myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("email_blank",$langId)?$common->get_msg("email_blank",$langId):"Please enter Email Address.";
		   $myarray['status']=0;
				 
		} elseif (!$request->password) {
		   $myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("password_blank",$langId)?$common->get_msg("password_blank",$langId):"Please enter Password.";
		   $myarray['status']=0;
		} elseif ($checkvendor==0) {
			 $myarray['result']=(object)array();
			 $myarray['message']=$common->get_msg("invalid_email",$langId)?$common->get_msg("invalid_email",$langId):'Invalid Email Address.';
			 $myarray['status']=0;
			
		} else {
			
			$url=url('/');
		    $vendorData = DB::table('tblvender')->where([['email', '=', $request->email]])->first();
		    $vendorPassword=$vendorData->password;
			$vendorId=$vendorData->id;
			$vendorStatus=$vendorData->isActive;
			$vendorfname=($vendorData->fname)?($vendorData->fname):'';
			$vendorlname=($vendorData->lname!='')?($vendorData->lname):'';
			$vendoremail=($vendorData->email!='')?($vendorData->email):'';
			$vendorgender=($vendorData->gender)?($vendorData->gender):'';
			$vendoraboutUs=($vendorData->aboutUs)?($vendorData->aboutUs):'';
			$vendorbirthDate=($vendorData->birthDate)?($vendorData->birthDate):'';
			$vendorphoto=($vendorData->photo)?($vendorData->photo):'';
			$vendorphone=($vendorData->phone)?($vendorData->phone):'';
			$vendorbusinessCategoryId=$vendorData->businessCategoryId;
			
			if ($vendorgender!='' && $vendorgender==1) {
			  $vendorgender='Male';
			}
			if ($vendorgender!='' && $vendorgender==2) {
			  $vendorgender='Female';
			}
			
			if ($vendorphoto!='') {
			    $vendorphoto=$url."/public/vendorphoto/".$vendorphoto;
			}
			
		   //exit;
		   
		   if (!Hash::check($request->password, $vendorPassword)) {
			 $myarray['result']=(object)array();
			 $myarray['message']=$common->get_msg("invalid_password",$langId)?$common->get_msg("invalid_password",$langId):'Invalid Password';
			 $myarray['status']=0;
		   } else if ($vendorStatus==0) {
		     $myarray['result']=(object)array();
			 $myarray['message']=$common->get_msg("account_inactive",$langId)?$common->get_msg("account_inactive",$langId):'Your account is inactive. Do you want to reactivate your account?';
			 $myarray['status']=2;
		   } else {
			   $user = Vendor::find($vendorId);
			   $user->loginStatus=1;
			   $user->lastLoginDate=date('Y-m-d H:i:s');
			   $user->deviceType=$deviceType;
			   $user->deviceDetails=$deviceDetails;
			   $user->deviceToken=$deviceToken;
			   $user->save();
			   
			   $suserId=0;
			   $VendorDetails=$common->VendorDetailsNew($vendorId,$suserId,$langId); 
			   
			 $myarray['result']=$VendorDetails;
			 $myarray['message']=$common->get_msg("vendor_detail",$langId)?$common->get_msg("vendor_detail",$langId):'Vendor Details.';
			 $myarray['status']=1;
           }
		   
		}
		//echo Hash::needsRehash($request->password);
		//echo $checkvendor;
		//exit;
		
		return response()->json($myarray); 
	 
        
    }
	
	/* Vendor Details */
	
	public function vendorDetails(Request $request){
	  
	  $common=new CommanController;
      $langId=($request->header('langId'))?($request->header('langId')):1; 

	  if (!$request->vendorId) {
		   $myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
		   $myarray['status']=0;
				 
		} else {	
		  $vendorDataCount = DB::table('tblvender')->where([['id', '=', $request->vendorId]])->count();
		  if ($vendorDataCount > 0) {
			$userId=($request->userId)?($request->userId):0;
			$serviceTypeId=($request->serviceTypeId)?($request->serviceTypeId):0;
			$VendorDetails=$common->VendorDetailsNew($request->vendorId,$userId,$langId,$serviceTypeId);
		    $myarray['result']=$VendorDetails;
			$myarray['message']=$common->get_msg("vendor_detail",$langId)?$common->get_msg("vendor_detail",$langId):'Vendor Details.';
			$myarray['status']=1;
		  } else {
			$myarray['result']=(object)array();
			$myarray['message']=$common->get_msg("no_vendor_found",$langId)?$common->get_msg("no_vendor_found",$langId):"No Vendor Details Found.";
			$myarray['status']=1;
		  }
	  }
	  return response()->json($myarray); 
	}
	
	/* Customer Login */
	
    public function customerlogin(Request $request){ 
	   $common=new CommanController;
       $langId=($request->header('langId'))?($request->header('langId')):1; 
	   $deviceType=($request->deviceType)?($request->deviceType):0;
	   $deviceToken=($request->deviceToken)?($request->deviceToken):"";
	   $deviceDetails=($request->deviceDetails)?($request->deviceDetails):"";		  
	   $loginType=($request->loginType)?($request->loginType):1;
	   
	     
		if (!$request->password && !$request->email) {
				   $myarray['result']=(object)array();					
				   $myarray['message']=$common->get_msg("valid_email_password",$langId)?$common->get_msg("valid_email_password",$langId):"Please enter email or password.";
				   $myarray['status']=0;
		}
		
		$checkvendor = DB::table('tblcustomer')->where([['email', '=', $request->email]])->count();
		if ($checkvendor==0) {
			 $myarray['result']=(object)array();
			 $myarray['message']=$common->get_msg("invalid_email",$langId)?$common->get_msg("invalid_email",$langId):'Invalid Email Address.';
			 $myarray['status']=0;
			
		} else {
			
			$url=url('/');
		    $customerData = DB::table('tblcustomer')->where([['email', '=', $request->email]])->first();
		    $customerPassword=$customerData->password;
			$customerId=$customerData->id;
			$customerStatus=$customerData->isActive;
			
		   
		   if (!Hash::check($request->password, $customerPassword)) {
			 $myarray['result']=(object)array();
			 $myarray['message']=$common->get_msg("invalid_password",$langId)?$common->get_msg("invalid_password",$langId):'Invalid Password.';
			 $myarray['status']=0;
		   } else if ($customerStatus==0) {
		     $myarray['result']=(object)array();
			 $myarray['message']=$common->get_msg("account_inactive",$langId)?$common->get_msg("account_inactive",$langId):'Your account is inactive. Do you want to reactivate your account?';
			 $myarray['status']=2;
		   } else {
			   $user = Customer::find($customerId);
			   $user->loginStatus=1;
			   $user->lastLoginDate=date('Y-m-d H:i:s');
			   $user->deviceType=$deviceType;
			   $user->deviceDetails=$deviceDetails;
			   $user->deviceToken=$deviceToken;
			   $user->save();
			   $CustomerDetails=$common->CustomerDetails($customerId,$langId); 
			   $myarray['result']=$CustomerDetails;
			   $myarray['message']=$common->get_msg("cust_info",$langId)?$common->get_msg("cust_info",$langId):'Customer Information.';
			   $myarray['status']=1;
           }
		   
		}
		
		return response()->json($myarray); 
        
    }
	
	/* Business Category List. */
	
    public function getbusinesscategory(Request $request){
		 $common=new CommanController;
         $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if ($request->keyword && $request->keyword!='') {
		  $businesscategory = BusinessCategory::where('name','like',$request->keyword."%")->where('isActive','=',1)->orderBy('name','asc')->get();
		 } else {
		  $businesscategory = BusinessCategory::where('isActive','=',1)->orderBy('name','asc')->get();
		 }	
		 
		 $myarray=array();
		 $arrays=array();
		 
		 if ($businesscategory->count() > 0) {
			 foreach($businesscategory as  $value) {
				 $photo=$value->photo;
				 
				 if ($photo!='') {
				 $photo=url('/')."/businesscategoryphoto/thumbnail_images/".$photo;
				 } else {
				 $photo='';
				 }

				 $businessCategoryId=$value->id;
				 $businessCategoryName=$common->getBusinessCategoryValue($businessCategoryId,$langId);
				 $arrays[]=array('id'=>(int)$businessCategoryId,"name"=>$businessCategoryName,"photo"=>$photo,"noOfVenders"=>(int)$value->noOfVenders);
			 }
			 $myarray['result']=$arrays;
			 $myarray['status']=1;
			 $myarray['message']=$common->get_msg("business_category_list",$langId)?$common->get_msg("business_category_list",$langId):'Business Category List.';
		 } else {
			 $myarray['result']=array();	 
			 $myarray['status']=1;
			 $myarray['message']=$common->get_msg("no_category",$langId)?$common->get_msg("no_category",$langId):'No Business Category Found.';
		 }			 
		 return response()->json($myarray); 
       
    }

    public function changePassword(Request $request) {

    	 $common=new CommanController;
		 $langId=($request->header('langId'))?($request->header('langId')):1;
         $newpassword=($request->newpassword)?($request->newpassword):"";
		 $oldpassword=($request->oldpassword)?($request->oldpassword):"";
		 $isvendor=($request->isvendor)?($request->isvendor):0;
		 $userId=($request->userId)?($request->userId):0;

		 if (!$userId && $isvendor==0) {
              
              $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0;

		 } else if (!$userId && $isvendor==1) {
             
             $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select venderId";
		   $myarray['status']=0;

		 } else if (!$oldpassword) {
              $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_old_pass",$langId)?$common->get_msg("blank_old_pass",$langId):"Please enter old password.";
		   $myarray['status']=0;
		 } else if (!$newpassword) {
              $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_new_pass",$langId)?$common->get_msg("blank_new_pass",$langId):"Please enter new password.";
		   $myarray['status']=0;
		 } else {
              
              if ($isvendor==0) {
                 $customer = DB::table('tblcustomer')->where('id', '=',$userId)->first();
                  if ($customer) {
                     $customerpassword=($customer->password)?($customer->password):"";
                     if (!Hash::check($oldpassword,$customerpassword)) {
                       $myarray['result']=(object)array();
                       $myarray['message']=$common->get_msg("invalid_old_password",$langId)?$common->get_msg("invalid_old_password",$langId):'Invalid old password.';
			           $myarray['status']=0;
                     } else {
                         $customer = Customer::find($userId);
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

              if ($isvendor==1) {
                 $vendor = DB::table('tblvender')->where('id', '=',$userId)->first();
                  if ($vendor) {
                     $vendorpassword=($vendor->password)?($vendor->password):"";
                     if (!Hash::check($oldpassword,$vendorpassword)) {
                       $myarray['result']=(object)array();
                       $myarray['message']=$common->get_msg("invalid_old_password",$langId)?$common->get_msg("invalid_old_password",$langId):'Invalid old password.';
			           $myarray['status']=0;
                     } else {
                         $vendor = Vendor::find($userId);
                         $vendor->password=(bcrypt($newpassword));
                         $vendor->save();
                         $myarray['result']=(object)array();
                       $myarray['message']=$common->get_msg("updated_password",$langId)?$common->get_msg("updated_password",$langId):'Your Password has been updated successfully.';
			           $myarray['status']=1;
                     }

                  } else {
                    $myarray['result']=(object)array();	
					$myarray['status']=0;
					$myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):'Invalid VendorId.';
                  }
              }

		 }
		 return response()->json($myarray);

    }
	
	/* Service Type List. */
	
	public function getservicetype(Request $request){
		 
		 $common=new CommanController;
		 $langId=($request->header('langId'))?($request->header('langId')):1; 

		if (!$request->businessCategoryId) {
				   $myarray['result']=array();					
				   $myarray['message']=$common->get_msg("businesscategoryId_blank",$langId)?$common->get_msg("businesscategoryId_blank",$langId):"Please enter business categoryId.";
				   $myarray['status']=0;
				 
		} else {
		 
			 if ($request->keyword && $request->keyword!='') {
			  $servicetype = ServiceType::where('name','like',$request->keyword."%")->where('businessCategoryId','=',$request->businessCategoryId)->where('isActive','=',1)->orderBy('name','asc')->get();
			 } else {
			  $servicetype = ServiceType::where('isActive','=',1)->where('businessCategoryId','=',$request->businessCategoryId)->orderBy('name','asc')->get();
			 }	
		 
			 $myarray=array();
			 $arrays=array();
		 
			 if ($servicetype->count() > 0) {
				 
				 foreach($servicetype as  $value) {
				  
					 $photo=$value->photo;
				 
					 if ($photo!='') {
					 $photo=url('/')."/servicetypephoto/thumbnail_images/".$photo;
					 } else {
					 $photo='';
					 }
					 $serviceTypeId=$value->id;
					 $serviceName=$common->getServiceTypeValue($serviceTypeId,$langId);
				 
				  $arrays[]=array('id'=>(int)$serviceTypeId,"name"=>$serviceName,"photo"=>$photo,"noOfVenders"=>(int)$value->noOfVenders);
				 
				 }
				 
				 $myarray['result']=$arrays;
				 $myarray['status']=1;
				 $myarray['message']=$common->get_msg("service_type_list",$langId)?$common->get_msg("service_type_list",$langId):'ServiceType List.';
			 
			 } else {
				 
				 $myarray['result']=array();	 
				 $myarray['status']=1;
				 $myarray['message']=$common->get_msg("no_service_type",$langId)?$common->get_msg("no_service_type",$langId):'No Service Type Found.';
			 
			 }
		}		 
		 return response()->json($myarray); 
       
    }
	
	/* Id Proof List. */
	
	public function getidprooftype(Request $request){
		
		 $common=new CommanController;
		 $langId=($request->header('langId'))?($request->header('langId')):1; 

		 $IdProof = IdProof::where('vendorId','=',0)->get();
		 $myarray=array();
		 $arrays=array();
		 if ($IdProof->count() > 0) {
		 
		 foreach($IdProof as  $value) {
		 	$ID=$value->id;
		 	$name=$common->getIdProofValue($ID,$langId);
           $arrays[]=array('id'=>(int)$ID,"name"=>$name,"isActive"=>$value->isActive,"createdDate"=>$value->createdDate,"vendorId"=>(int)$value->vendorId);
		 }

		 $myarray['result']=$arrays;
		 $myarray['status']=1;
		 $myarray['message']=$common->get_msg("proofid_list",$langId)?$common->get_msg("proofid_list",$langId):'Proof ID Type List.';
		 
		 } else {
		 $myarray['result']=$arrays;
		 $myarray['status']=1;
		 $myarray['message']=$common->get_msg("no_proofid_type",$langId)?$common->get_msg("no_proofid_type",$langId):'No Proof ID Type Found.';
		 
		 }			 
		 
		 return response()->json($myarray); 
       
    }
	

	/* Product Category List. */
	
	public function getproductcategory(Request $request){
		
		 $common=new CommanController;
		 $langId=($request->header('langId'))?($request->header('langId')):1; 

		 $ProductCategory = ProductCategory::where('isActive','=',1)->get();
		 $myarray=array();
		 $arrays=array();
		 if ($ProductCategory->count() > 0) {
		 
		 foreach($ProductCategory as  $value) {
		 	$ID=$value->id;
		 	$name=$common->getProductCategoryValue($ID,$langId);
           $arrays[]=array('id'=>(int)$ID,"name"=>$name,"isActive"=>$value->isActive,"createdDate"=>$value->createdDate);
		 }

		 $myarray['result']=$arrays;
		 $myarray['status']=1;
		 $myarray['message']=$common->get_msg("product_category_list",$langId)?$common->get_msg("product_category_list",$langId):'Product category list.';
		 
		 } else {
		 $myarray['result']=$arrays;
		 $myarray['status']=1;
		 $myarray['message']=$common->get_msg("no_product_category",$langId)?$common->get_msg("no_product_category",$langId):'No product category found.';
		 
		 }			 
		 
		 return response()->json($myarray); 
       
    }
	
	
	
/** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
	
    /* Vendor Register */
	
    public function register(Request $request) { 
	   	$common=new CommanController;
	    $langId=($request->header('langId'))?($request->header('langId')):1; 

      	$deviceType=($request->deviceType)?($request->deviceType):0;
		$deviceToken=($request->deviceToken)?($request->deviceToken):"";
		$deviceDetails=($request->deviceDetails)?($request->deviceDetails):"";		  
		$loginType=($request->loginType)?($request->loginType):1;
		$howdidyouknow=($request->howdidyouknow)?($request->howdidyouknow):"";

		$countryId=($request->countryId)?($request->countryId):0;
		$stateId=($request->stateId)?($request->stateId):0;
		$cityId=($request->cityId)?($request->cityId):0;
	   
	   	if (!$request->socialMediaId) {
	   		if (!$request->id) {
				if (!$request->email) {
				   	$myarray['result']=(object)array();					
				   	$myarray['message']=$common->get_msg("email_blank",$langId)?$common->get_msg("email_blank",$langId):"Please Enter Email Address.";
				   	$myarray['status']=0;
				 
			 	} elseif (!$request->password) {
				   	$myarray['result']=(object)array();					
				   	$myarray['message']=$common->get_msg("password_blank",$langId)?$common->get_msg("password_blank",$langId):"Please Enter Password.";
				   	$myarray['status']=0;
				 
				} elseif (!$request->phone) {
				   	$myarray['result']=(object)array();					
				   	$myarray['message']=$common->get_msg("phone_blank",$langId)?$common->get_msg("phone_blank",$langId):"Please Enter Phone.";
				   	$myarray['status']=0;
				 
				} else { 
					$countemail = DB::table('tblvender')->where('email', '=', $request->email)->count();
					$countphone = DB::table('tblvender')->where('phone', '=', $request->phone)->count();
					if ($countemail>0) {
						$myarray['result']=(object)array();	
						$myarray['status']=1;
						$myarray['message']=$common->get_msg("already_email",$langId)?$common->get_msg("already_email",$langId):'This email address has been already taken please try another.';
					} else if ($countphone>0) {
						$myarray['result']=(object)array();
						$myarray['status']=1;
						$myarray['message']=$common->get_msg("already_phone",$langId)?$common->get_msg("already_phone",$langId):'This phone number has been already taken please try another.';
					} else if ($countemail>0 && $countphone>0) {
						$myarray['result']=(object)array();
						$myarray['status']=1;
						$myarray['message']=$common->get_msg("already_email_phone",$langId)?$common->get_msg("already_email_phone",$langId):'This email address & phone number has been already taken please try another.';		
					} else {
						$digits = 4;
                        $phonecode=rand(pow(10, $digits-1), pow(10, $digits)-1);
					$vendor = new Vendor();
					$vendor->fname=($request->firstname)?($request->firstname):"";
					$vendor->lname=($request->lastname)?($request->lastname):"";
					$vendor->email=($request->email)?($request->email):"";
					$vendor->website=($request->website)?($request->website):"";
					$vendor->password=($request->password)?(bcrypt($request->password)):"";
					$vendor->phone=($request->phone)?($request->phone):"";
					$vendor->code=($request->code)?($request->code):"";
					$vendor->charity=($request->charity)?($request->charity):"";
					if ($request->businessCategoryId) {
					//$vendor->businessCategoryId=($request->businessCategoryId)?($request->businessCategoryId):"";
					}
					$vendor->createdDate=date('Y-m-d H:i:s');
					$vendor->deviceType=$deviceType;
					$vendor->deviceToken=$deviceToken;
					$vendor->deviceDetails=$deviceDetails;
					$vendor->howdidyouknow=$howdidyouknow;
					$vendor->loginType=$loginType;
					$vendor->phoneVerificationCode=$phonecode;
					$vendor->phoneVerificationSentRequestTime=date('Y-m-d H:i:s');

                      if (is_numeric($countryId) && $countryId!=0) {
						 $vendor->countryId=$countryId;
					  }
					  
					  if (is_numeric($stateId) && $stateId!=0) {
							$vendor->stateId=$stateId;
					   }
						
					  if (is_numeric($cityId) && $cityId!=0) {
							$vendor->cityId=$cityId;
					   }

					   if (!is_numeric($countryId) && $countryId!='') {
							$countryName = DB::table('tblcountries')->where([['name','=',$countryId]])->first();
							if ($countryName) {
								$countryId=$countryName->id;
								$vendor->countryId=$countryId;
							} else {
								$countryName=$countryId;
								$country=DB::table('tblcountries')->insertGetId(
						['name'=>$countryId,'status'=>1,'currency'=>'$']);
								$countryId=$country;
								$vendor->countryId=$countryId;

								$language = DB::table('language')->where([['status', '=','Active']])->get();
								if ($language) {
									foreach ($language as $value) {
										$insert=DB::table('tblcountrytranslation')->insert(
								['countryId'=>$countryId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$countryName]);
									}
								}
							}
	
                      } 

					if (!is_numeric($stateId) && $stateId!='') {
						$stateName = DB::table('tblstates')->where([['name','=',$stateId]])->first();
						if ($stateName) {
							$stateId=$stateName->id;
							$vendor->stateId=$stateId;
						} else {
							$stateName=$stateId;
							$state=DB::table('tblstates')->insertGetId(
					['name'=>$stateId,'country_id'=>$countryId]);
							$stateId=$state;
							$vendor->stateId=$stateId;

							$language = DB::table('language')->where([['status', '=','Active']])->get();
							if ($language) {

								foreach ($language as $value) {
									$insert=DB::table('tblstatetranslation')->insert(
							['stateId'=>$stateId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$stateName]);
								}
							}
						}
					} 

					if (!is_numeric($cityId) && $cityId!='') {
						$cityName = DB::table('tblcities')->where([['name','=',$cityId]])->first();
						if ($cityName) {
							$cityId=$cityName->id;
							$vendor->cityId=$cityId;
						} else {
							$cityName=$cityId;
							$city=DB::table('tblcities')->insertGetId(
					['name'=>$cityId,'state_id'=>$stateId]);
							$cityId=$city;
							$vendor->cityId=$cityId;

							$language = DB::table('language')->where([['status', '=','Active']])->get();
								if ($language) {

									foreach ($language as $value) {
										$insert=DB::table('tblcitytranslation')->insert(
								['cityId'=>$cityId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$cityName]);
									}
								}
						}
						
					} 

					$vendor->save();
					if ($request->businessCategoryId) {
						$buscatexplode=explode(",",$request->businessCategoryId);
						foreach ($buscatexplode  as  $value) {
							$businesscategory=DB::select( DB::raw("update tblbusinesscategory SET `noOfVenders`=`noOfVenders`+1 where id={$value}"));
					         }
					}
					if ($request->businessCategoryId) {
                       $buscatexplode=explode(",",$request->businessCategoryId);
                       foreach ($buscatexplode as  $value) {
			         $servicetype=DB::select( DB::raw("update tblservicetype SET `noOfVenders`=`noOfVenders`+1 where businessCategoryId={$value}"));
					      }

					}

					if ($request->businessCategoryId) {
                       $buscatexplode=explode(",",$request->businessCategoryId);
                       foreach ($buscatexplode as  $value) {
                          $vendorCategory=DB::select( DB::raw("Insert tblvenderbusinesscategory SET `vendorId`='$vendor->id',businessCategoryId={$value}"));
                       }
                   }
                    
                    $vendorpaymentType=DB::table('tblvenderpaymenttype')->insert(
               ['venderId'=>$vendor->id,'type'=>1,'createdDate'=>date('Y-m-d H:i:s')]);
                     $endDate=date("Y-m-d",strtotime("+30 days"));
                     
                     $checksub = DB::table('tblsubscriptionplans')->where([['id', '=',1]])->first();
                      
                      $PlanName=($checksub->name)?($checksub->name):"";
                      $price=($checksub->price)?($checksub->price):0;
                      $noOfLeadsPerDuration=($checksub->noOfLeadsPerDuration)?($checksub->noOfLeadsPerDuration):0;
                      $description=($checksub->description)?($checksub->description):0;
                      

                     $vendorsubscriptionId=DB::table('tblvendersubscription')->insertGetId(['venderId'=>$vendor->id,'subscriptionPlanId'=>1,'subscriptionName'=>$PlanName,'price'=>0,'startDate'=>date('Y-m-d H:i:s'),'endDate'=>$endDate,"noOfLeadsPerDuration"=>$noOfLeadsPerDuration,"noOfRemainingLeads"=>$noOfLeadsPerDuration,"status"=>1,"createdDate"=>date("Y-m-d"),"subscriptionDesc"=>$description]);

                       $vendorsubscriptionId=DB::table('tblvendersubscriptionhistory')->insert(['subscriptionId'=>$vendorsubscriptionId,'startDate'=>date('Y-m-d H:i:s'),'endDate'=>$endDate]);
                     



					$suserId=0;
				    $VendorDetails=$common->VendorDetailsNew($vendor->id,$suserId,$langId);
					
					

					$emailtype=1;
					$getEmailContent=$common->getEmailContent($emailtype);
                    $content='';
                    $url=url('/');
					if ($getEmailContent!='') {
                     $name=$vendor->fname." ".$vendor->lname;
                     $content=str_replace("#name",$name, $getEmailContent);
                     $content=str_replace("#url",$url, $content);
					}
                    
					//Mail::to($request->email)->send(new GeneralMail($content,$subject));
                    
                    
				      $template_data = ['content' => $content];
				      //send verification code
				      $email=$request->email;
				     /* Mail::send(['html' => 'emails.general-email'], $template_data,
				                function ($message) use ( $email) {
				                   $message->to($email)
				                   ->from('test@yourdamin.com') 
				                   ->subject('Vendor Registration');
				      });*/

				      /* sms code start */
                       /*if ($request->phone && $request->phone!='') {

                       	$sid    = env('TWILIO_ACCOUNT_SID');
				       $token  = env('TWILIO_AUTH_TOKEN');
				       $from   = env('TWILIO_PHONE_NUMBER');
				        $text="".$phonecode." is your Quickserve verification code.";
				       $client = new Client( $sid, $token );

					               try
					               {
						            // Use the client to do fun stuff like send text messages!
						            $client->messages->create(
						            // the number you'd like to send the message to
						                $request->phone,
						           array(
						                 // A Twilio phone number you purchased at twilio.com/console
						                 'from' => env('TWILIO_PHONE_NUMBER'),
						                 // the body of the text message you'd like to send
						                 'body' => $text
						             )
						         );
						            //echo "sucess";
						            //exit;
						            $messages ="success";
						           }
							        catch (\Exception $e)
							        {
							        	if($e->getCode() == 21614)
													{
														
												   	$messages = $e->getMessage();
												    // echo $messages;
												     //exit();
												   } else {
							                          $messages="Invalid Mobile No.";
												   }
							            
							        }

                       }*/

                       /* sms code end */
				        

					$myarray['result']=$VendorDetails;
					$myarray['status']=1;
					$myarray['isFirstTimeReg']=1;
					$myarray['message']=$common->get_msg("register_customer",$langId)?$common->get_msg("register_customer",$langId):'Your have successfully registered.';
					}
				}	
			   } else {
			       
				  $checkValidId = DB::table('tblvender')->where('id', '=', $request->id)->count();
				  if ($checkValidId==0) {
				    $myarray['result']=(object)array();	
					$myarray['status']=1;
					$myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):'Invalid VendorId.';
				  } else {
                     $checkValidBusines = DB::table('tblvenderbusiness')->where('venderId', '=', $request->id)->first();					  
					 if ($checkValidBusines) {
					 $vendorbusiness=VendorBusiness::find($checkValidBusines->id);
                     } else {
					 $vendorbusiness=new VendorBusiness;
					 }						 
				     $user = Vendor::find($request->id);
				     $userphone=$user->phone;

					 $firstname=($request->firstname)?($request->firstname):"";
					 $lastname=($request->lastname)?($request->lastname):"";
					 $gender=($request->gender)?($request->gender):0;
					 $dob=($request->birthdate)?(date("Y-m-d",strtotime($request->birthdate))):"";
					 $proofTypeId=($request->proofTypeId)?($request->proofTypeId):'';
					 $firmName=($request->firmName)?($request->firmName):"";
					 $incorporationDate=($request->incorporationDate)?(date("Y-m-d",strtotime($request->incorporationDate))):"1970-01-01";
					 $location=($request->location)?($request->location):"";
					 $latitude=($request->latitude)?($request->latitude):0;
					 $longitude=($request->longitude)?($request->longitude):0;
					 $pincode=($request->pincode)?($request->pincode):"";
					 $charity=($request->charity)?($request->charity):"";
					 $website=($request->website)?($request->website):"";
					 $email=($request->email)?($request->email):"";
					 $phone=($request->phone)?($request->phone):"";
					 $code=($request->code)?($request->code):"";
					 
					 if ($firstname!='') {
					 $user->fname=$firstname;
					 }

					 if ($lastname!='') {
					 $user->lname=$lastname;
					 }

					 if ($gender!=0) {
					 $user->gender=$gender;
					 }

					 if ($charity!='') {
					 $user->charity=$charity;
					 }

					 if ($website!='') {
					 $user->website=$website;
					 }

					 if ($email!='') {
					 $user->email=$email;
					 }

					 if ($phone!='') {
					 $user->phone=$phone;
					 }

					 if ($dob!='') {
					 $user->birthDate=$dob;
					 }
					 
					 if ($howdidyouknow!='') {
					 $user->howdidyouknow=$howdidyouknow;
					 }

					 if ($code!='') {
					 	$user->code=$code;
					 }

					 if ($request->businessCategoryId) {
					//$user->businessCategoryId=($request->businessCategoryId)?($request->businessCategoryId):"";
					}

					if ($userphone!=$phone) {
					    $user->isPhoneVerified=0;	
					}

					if ($request->businessCategoryId) {
                       $deletebusinessCat=DB::select( DB::raw("delete from tblvenderbusinesscategory where `vendorId`='$request->id'"));
                       $buscatexplode=explode(",",$request->businessCategoryId);
                       foreach ($buscatexplode as  $value) {
                          $vendorCategory=DB::select( DB::raw("Insert tblvenderbusinesscategory SET `vendorId`='$request->id',businessCategoryId={$value}"));
                       }
                   }


					 if($request->hasFile('profilepicture')) {
						 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
					     $file = $request->file('profilepicture');
						 $filename = rand(1,1000000).time().$file->getClientOriginalName();
                         $extension = strtolower($file->getClientOriginalExtension());
						 $check=in_array($extension,$allowedfileExtension);
						 if($check) {
						   $destinationPath = 'vendorphoto';
                           $file->move($destinationPath,$filename);
                           $user->photo=$filename;						   
						 }
					 }
					 
					$user->deviceType=$deviceType;
					$user->deviceToken=$deviceToken;
					$user->deviceDetails=$deviceDetails;
					$user->loginType=$loginType;
					 
					 if (is_numeric($countryId) && $countryId!=0) {
						 $user->countryId=$countryId;
					  }
					  
					  if (is_numeric($stateId) && $stateId!=0) {
							$user->stateId=$stateId;
					   }
						
					  if (is_numeric($cityId) && $cityId!=0) {
							$user->cityId=$cityId;
					   }

					   if (!is_numeric($countryId) && $countryId!='') {
							$countryName = DB::table('tblcountries')->where([['name','=',$countryId]])->first();
							if ($countryName) {
								$countryId=$countryName->id;
								$user->countryId=$countryId;
							} else {
								$countryName=$countryId;
								$country=DB::table('tblcountries')->insertGetId(
						['name'=>$countryId,'status'=>1,'currency'=>'$']);
								$countryId=$country;
								$user->countryId=$countryId;

								$language = DB::table('language')->where([['status', '=','Active']])->get();
								if ($language) {
									foreach ($language as $value) {
										$insert=DB::table('tblcountrytranslation')->insert(
								['countryId'=>$countryId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$countryName]);
									}
								}
							}
	
                      } 

					if (!is_numeric($stateId) && $stateId!='') {
						$stateName = DB::table('tblstates')->where([['name','=',$stateId]])->first();
						if ($stateName) {
							$stateId=$stateName->id;
							$user->stateId=$stateId;
						} else {
							$stateName=$stateId;
							$state=DB::table('tblstates')->insertGetId(
					['name'=>$stateId,'country_id'=>$countryId]);
							$stateId=$state;
							$user->stateId=$stateId;

							$language = DB::table('language')->where([['status', '=','Active']])->get();
							if ($language) {

								foreach ($language as $value) {
									$insert=DB::table('tblstatetranslation')->insert(
							['stateId'=>$stateId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$stateName]);
								}
							}
						}
					} 

					if (!is_numeric($cityId) && $cityId!='') {
						$cityName = DB::table('tblcities')->where([['name','=',$cityId]])->first();
						if ($cityName) {
							$cityId=$cityName->id;
							$user->cityId=$cityId;
						} else {
							$cityName=$cityId;
							$city=DB::table('tblcities')->insertGetId(
					['name'=>$cityId,'state_id'=>$stateId]);
							$cityId=$city;
							$user->cityId=$cityId;

							$language = DB::table('language')->where([['status', '=','Active']])->get();
								if ($language) {

									foreach ($language as $value) {
										$insert=DB::table('tblcitytranslation')->insert(
								['cityId'=>$cityId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$cityName]);
									}
								}
						}
						
					} 
					 $user->save();
					 /* $vendorbusiness->venderId=$request->id;
					 $vendorbusiness->firmName=$firmName;
					 $vendorbusiness->incorporationDate=$incorporationDate;
					 $vendorbusiness->location=$location;
					 $vendorbusiness->latitude=$latitude;
					 $vendorbusiness->longitude=$longitude;
					 $vendorbusiness->pincode=$pincode;
					 $vendorbusiness->createdDate=date('Y-m-d H:i:s');
					 $vendorbusiness->save(); */
					 
					 $vendorBusinessUpdate=0;					
					if ($firmName!='') {
						$vendorBusinessUpdate=1;
					 $vendorbusiness->firmName=$firmName;
					}
                    if ($incorporationDate!='' && $incorporationDate!='1970-01-01') {
					   $vendorBusinessUpdate=1;
					 $vendorbusiness->incorporationDate=$incorporationDate;
					}
                    if ($location!='') {
						$vendorBusinessUpdate=1;
					   $vendorbusiness->location=$location;
					}
                    if ($latitude!='') {
						$vendorBusinessUpdate=1;
					   $vendorbusiness->latitude=$latitude;
					}
                    
                    if ($longitude!='') {
						$vendorBusinessUpdate=1;
					   $vendorbusiness->longitude=$longitude;
					}
                    
                    if ($pincode!='') {
						$vendorBusinessUpdate=1;
					   $vendorbusiness->pincode=$pincode;
					}					
                  	
                    if ($vendorBusinessUpdate==1) {
					     $vendorbusiness->venderId=$request->id;
						 //$vendorbusiness->createdDate=date('Y-m-d H:i:s');
						 $vendorbusiness->save();
					}	
					 
					 /* if($request->hasFile('idproof')) {
					   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
					   $files = $request->file('idproof');
					    foreach($files as $file){
						 $filename = time().$file->getClientOriginalName();
                         $extension = $file->getClientOriginalExtension();
                         $check=in_array($extension,$allowedfileExtension);
						 if($check) {
						   $destinationPath = 'vendorproof';
                           $file->move($destinationPath,$filename); 
						   $vendorproof=new VendorProof;
						   $vendorproof->venderId=$request->id;
						   $vendorproof->photo=$filename;
						   $vendorproof->createdDate=date('Y-m-d H:i:s');
						   $vendorproof->save();
						 }
					   }
                     } */
						 
					 $suserId=0;
				     $VendorDetails=$common->VendorDetailsNew($request->id,$suserId,$langId);
					 $myarray['result']=$VendorDetails;	
					 $myarray['status']=1;
					 $myarray['isFirstTimeReg']=0;
					 $myarray['message']=$common->get_msg("update_business",$langId)?$common->get_msg("update_business",$langId):'Your business details has been updated successfully.';
				  }
			   }				   
				
	   } else {
		   
		 
		  
		  $countsocial = DB::table('tblvender')->where('socialMediaId', '=', $request->socialMediaId)->count();
		  
		  if ($countsocial==0) {
		     $countemail = DB::table('tblvender')->where('email', '=', $request->email)->count();
		     if ($countemail > 0) {
			   $email = DB::table('tblvender')->where('email', '=', $request->email)->first();
			   $userId=$email->id;
			   
			   DB::table('tbldevicetoken')->insert(
               ['customerId'=>$userId,'deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'loginStatus'=>1,'deviceDetails'=>$deviceDetails,'tokenDate'=>date('Y-m-d H:i:s'),'isCustomer'=>0]);
			   $charity=($request->charity)?($request->charity):"";
			   
			    DB::table('tblvender')->where('id',$userId)->update(
               ['deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'loginStatus'=>1,'deviceDetails'=>$deviceDetails,'socialMediaId'=>$request->socialMediaId,'loginType'=>$loginType,'charity'=>$charity]); 
			   
			   $suserId=0;
			   $VendorDetails=$common->VendorDetailsNew($userId,$suserId,$langId);
				 $myarray['result']=$VendorDetails;
                 $myarray['isFirstTimeReg']=0;				 
				 $myarray['status']=1;
				 $myarray['message']=$common->get_msg("vendor_detail",$langId)?$common->get_msg("vendor_detail",$langId):'Your registration details.';
			   
			 } else {

			 	    $digits = 4;
                    $phonecode=rand(pow(10, $digits-1), pow(10, $digits)-1);

				    $vendor = new Vendor();
			        $vendor->fname=($request->firstname && $request->firstname!='')?($request->firstname):'';
					$vendor->lname=($request->lastname && $request->lastname!='')?($request->lastname):'';
					$vendor->email=($request->email && $request->email!='')?($request->email):'';
			        $vendor->phone=($request->phone && $request->phone!='')?($request->phone):'';
			        $vendor->charity=($request->charity)?($request->charity):"";
					$vendor->website=($request->website)?($request->website):"";
					$vendor->deviceType=$deviceType;
					$vendor->deviceToken=$deviceToken;
					$vendor->deviceDetails=$deviceDetails;
					$vendor->loginStatus=1;
					$vendor->socialMediaId=$request->socialMediaId;
					$vendor->lastLoginDate=date('Y-m-d H:i:s');
					$vendor->loginType=$loginType;
					$vendor->createdDate=date('Y-m-d H:i:s');
					$vendor->isActive=1;
					$vendor->howdidyouknow=$howdidyouknow;
                    $vendor->code=($request->code)?($request->code):"";
					$vendor->phoneVerificationCode=$phonecode;
                    $vendor->phoneVerificationSentRequestTime=date('Y-m-d H:i:s');
					

					if ($request->businessCategoryId) {
					//$vendor->businessCategoryId=($request->businessCategoryId)?($request->businessCategoryId):"";
					}
					if($request->hasFile('profilepicture')) {
						 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
					     $file = $request->file('profilepicture');
						 $filename = rand(1,1000000).time().$file->getClientOriginalName();
                         $extension = strtolower($file->getClientOriginalExtension());
						 $check=in_array($extension,$allowedfileExtension);
						 if($check) {
						   $destinationPath = 'vendorphoto';
                           $file->move($destinationPath,$filename);
                           $vendor->photo=$filename;						   
						 }
					 }

					 if (is_numeric($countryId) && $countryId!=0) {
						 $vendor->countryId=$countryId;
					  }
					  
					  if (is_numeric($stateId) && $stateId!=0) {
							$vendor->stateId=$stateId;
					   }
						
					  if (is_numeric($cityId) && $cityId!=0) {
							$vendor->cityId=$cityId;
					   }

					   if (!is_numeric($countryId) && $countryId!='') {
							$countryName = DB::table('tblcountries')->where([['name','=',$countryId]])->first();
							if ($countryName) {
								$countryId=$countryName->id;
								$vendor->countryId=$countryId;
							} else {
								$countryName=$countryId;
								$country=DB::table('tblcountries')->insertGetId(
						['name'=>$countryId,'status'=>1,'currency'=>'$']);
								$countryId=$country;
								$vendor->countryId=$countryId;

								$language = DB::table('language')->where([['status', '=','Active']])->get();
								if ($language) {
									foreach ($language as $value) {
										$insert=DB::table('tblcountrytranslation')->insert(
								['countryId'=>$countryId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$countryName]);
									}
								}
							}
	
                      } 

					if (!is_numeric($stateId) && $stateId!='') {
						$stateName = DB::table('tblstates')->where([['name','=',$stateId]])->first();
						if ($stateName) {
							$stateId=$stateName->id;
							$vendor->stateId=$stateId;
						} else {
							$stateName=$stateId;
							$state=DB::table('tblstates')->insertGetId(
					['name'=>$stateId,'country_id'=>$countryId]);
							$stateId=$state;
							$vendor->stateId=$stateId;

							$language = DB::table('language')->where([['status', '=','Active']])->get();
							if ($language) {

								foreach ($language as $value) {
									$insert=DB::table('tblstatetranslation')->insert(
							['stateId'=>$stateId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$stateName]);
								}
							}
						}
					} 

					if (!is_numeric($cityId) && $cityId!='') {
						$cityName = DB::table('tblcities')->where([['name','=',$cityId]])->first();
						if ($cityName) {
							$cityId=$cityName->id;
							$vendor->cityId=$cityId;
						} else {
							$cityName=$cityId;
							$city=DB::table('tblcities')->insertGetId(
					['name'=>$cityId,'state_id'=>$stateId]);
							$cityId=$city;
							$vendor->cityId=$cityId;

							$language = DB::table('language')->where([['status', '=','Active']])->get();
								if ($language) {

									foreach ($language as $value) {
										$insert=DB::table('tblcitytranslation')->insert(
								['cityId'=>$cityId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$cityName]);
									}
								}
						}
						
					} 
					
					 $vendor->save();


					 if ($request->businessCategoryId) {
						
						$buscatexplode=explode(",",$request->businessCategoryId);
						foreach ($buscatexplode  as  $value) {
							$businesscategory=DB::select( DB::raw("update tblbusinesscategory SET `noOfVenders`=`noOfVenders`+1 where id={$value}"));
					         }
					}
					if ($request->businessCategoryId) {
                       $buscatexplode=explode(",",$request->businessCategoryId);
                       foreach ($buscatexplode as  $value) {
			         $servicetype=DB::select( DB::raw("update tblservicetype SET `noOfVenders`=`noOfVenders`+1 where businessCategoryId={$value}"));
					      }

					}

					if ($request->businessCategoryId) {
                       $buscatexplode=explode(",",$request->businessCategoryId);
                       foreach ($buscatexplode as  $value) {
                          $vendorCategory=DB::select( DB::raw("Insert tblvenderbusinesscategory SET `vendorId`='$vendor->id',businessCategoryId={$value}"));
                       }
                   }

                    $checksub = DB::table('tblsubscriptionplans')->where([['id', '=',1]])->first();
                      
                      $PlanName=($checksub->name)?($checksub->name):"";
                      $price=($checksub->price)?($checksub->price):0;
                      $noOfLeadsPerDuration=($checksub->noOfLeadsPerDuration)?($checksub->noOfLeadsPerDuration):0;
                      $description=($checksub->description)?($checksub->description):0;
                      

                     $vendorsubscriptionId=DB::table('tblvendersubscription')->insertGetId(
               ['venderId'=>$vendor->id,'subscriptionPlanId'=>1,'subscriptionName'=>$PlanName,'price'=>0,'startDate'=>date('Y-m-d H:i:s'),'endDate'=>$endDate,"noOfLeadsPerDuration"=>$noOfLeadsPerDuration,"noOfRemainingLeads"=>$noOfLeadsPerDuration,"status"=>1,"createdDate"=>date("Y-m-d"),"subscriptionDesc"=>$description]);

                       $vendorsubscriptionId=DB::table('tblvendersubscriptionhistory')->insert(['subscriptionId'=>$vendorsubscriptionId,'startDate'=>date('Y-m-d H:i:s'),'endDate'=>$endDate]);
					 
					 $emailtype=1;
					 $getEmailContent=$common->getEmailContent($emailtype);
                     $content='';
                     $url=url('/');
					 if ($getEmailContent!='') {
	                     $name=$vendor->fname." ".$vendor->lname;
	                     $content=str_replace("#name",$name, $getEmailContent);
	                     $content=str_replace("#url",$url, $content);
					 }
                    
					//Mail::to($request->email)->send(new GeneralMail($content,$subject));
                    
                    
				      $template_data = ['content' => $content];
				      //send verification code
				      $email=$request->email;
				      /*Mail::send(['html' => 'emails.general-email'], $template_data,
				                function ($message) use ( $email) {
				                   $message->to( $email)
				                   ->from('test@yourdamin.com') 
				                   ->subject('Vendor Registration');
				      });*/

					 $vendorbusiness=new VendorBusiness;
					 $firmName=($request->firmName)?($request->firmName):"";
					 $incorporationDate=($request->incorporationDate)?(date("Y-m-d",strtotime($request->incorporationDate))):"1970-01-01";
					 $location=($request->location)?($request->location):"";
					 $latitude=($request->latitude)?($request->latitude):0;
					 $longitude=($request->longitude)?($request->longitude):0;
					 $pincode=($request->pincode)?($request->pincode):"";
					if ($firmName!='') {
					 $vendorbusiness->venderId=$vendor->id;
					 $vendorbusiness->firmName=$firmName;
					 $vendorbusiness->incorporationDate=$incorporationDate;
					 $vendorbusiness->location=$location;
					 $vendorbusiness->latitude=$latitude;
					 $vendorbusiness->longitude=$longitude;
					 $vendorbusiness->pincode=$pincode;
					 $vendorbusiness->createdDate=date('Y-m-d H:i:s');
					 $vendorbusiness->save();
					}
					
                    /* if($request->hasFile('idproof')) {
					   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
					   $files = $request->file('idproof');
					    foreach($files as $file){
						 $filename = time().$file->getClientOriginalName();
                         $extension = $file->getClientOriginalExtension();
                         $check=in_array($extension,$allowedfileExtension);
						 if($check) {
						   $destinationPath = 'vendorproof';
                           $file->move($destinationPath,$filename); 
						   $vendorproof=new VendorProof;
						   $vendorproof->venderId=$vendor->id;
						   $vendorproof->photo=$filename;
						   $vendorproof->createdDate=date('Y-m-d H:i:s');
						   $vendorproof->save();
						 }
					   }
                     }	 */				
					 
					DB::table('tbldevicetoken')->insert(
                    ['customerId'=>$vendor->id,'deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'loginStatus'=>1,'deviceDetails'=>$deviceDetails,'tokenDate'=>date('Y-m-d H:i:s'),'isCustomer'=>0]);
			       
				    $suserId=0;
				    $VendorDetails=$common->VendorDetailsNew($vendor->id,$suserId,$langId);
					$myarray['result']=$VendorDetails;
					$myarray['status']=1;
					$myarray['isFirstTimeReg']=1;
					$myarray['message']=$common->get_msg("register_customer",$langId)?$common->get_msg("register_customer",$langId):'You have successfully registerd.';
			 }
		  
		  } else {
			  
			  $social=DB::table('tblvender')->where('socialMediaId', '=', $request->socialMediaId)->first();
		      $userId=$social->id;
			  DB::table('tbldevicetoken')->insert(
               ['customerId'=>$userId,'deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'loginStatus'=>1,'deviceDetails'=>$deviceDetails,'tokenDate'=>date('Y-m-d H:i:s'),'isCustomer'=>1]);
			   
			         //$vendorbusiness=new VendorBusiness;
                     $checkValidBusines = DB::table('tblvenderbusiness')->where('venderId', '=', $userId)->first();					  
					 if ($checkValidBusines) {
					 $vendorbusiness=VendorBusiness::find($checkValidBusines->id);
                     } else {
					 $vendorbusiness=new VendorBusiness;
					 }
					 
					 $firmName=($request->firmName)?($request->firmName):"";
					 $incorporationDate=($request->incorporationDate)?(date("Y-m-d",strtotime($request->incorporationDate))):"1970-01-01";
					 $location=($request->location)?($request->location):"";
					 $latitude=($request->latitude)?($request->latitude):0;
					 $longitude=($request->longitude)?($request->longitude):0;
					 $pincode=($request->pincode)?($request->pincode):"";
                     $dob=($request->birthdate)?(date("Y-m-d",strtotime($request->birthdate))):"";
					 $proofTypeId=($request->proofTypeId)?($request->proofTypeId):'';					 
				     $businessCategoryId=($request->businessCategoryId)?($request->businessCategoryId):0;
					 $phone=($request->phone)?($request->phone):'';
					 $charity=($request->charity)?($request->charity):"";
					 $website=($request->website)?($request->website):"";
					 $email=($request->email)?($request->email):"";
					 $firstname=($request->firstname)?($request->firstname):"";
					 $lastname=($request->lastname)?($request->lastname):"";
					 $gender=($request->gender)?($request->gender):0;
					 $code=($request->code)?($request->code):"";
					 /* $user = Vendor::find($userId);
					 $firstname=($request->firstname)?($request->firstname):"";
					 $lastname=($request->lastname)?($request->lastname):"";
					 $gender=($request->gender)?($request->gender):0;
					 $dob=($request->birthdate)?(date("Y-m-d",strtotime($request->birthdate))):"";
					 $proofTypeId=($request->proofTypeId)?($request->proofTypeId):'';
					 $firmName=($request->firmName)?($request->firmName):"";
					 $incorporationDate=($request->incorporationDate)?(date("Y-m-d",strtotime($request->incorporationDate))):"1970-01-01";
					 $location=($request->location)?($request->location):"";
					 $latitude=($request->latitude)?($request->latitude):0;
					 $longitude=($request->longitude)?($request->longitude):0;
					 $pincode=($request->pincode)?($request->pincode):"";
					 $user->fname=$firstname;
					 $user->lname=$lastname;
					 $user->gender=$gender;
					 $user->birthDate=$dob;
					 $user->proofTypeId=$proofTypeId;
					 $user->deviceType=$deviceType;
					 $user->deviceToken=$deviceToken;
					 $user->deviceDetails=$deviceDetails;
					 $user->loginStatus=1;
					 $user->loginType=$loginType;
					 
					 if($request->hasFile('profilepicture')) {
						 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
					     $file = $request->file('profilepicture');
						 $filename = $file->getClientOriginalName();
                         $extension = $file->getClientOriginalExtension();
						 $check=in_array($extension,$allowedfileExtension);
						 if($check) {
						   $destinationPath = 'vendorphoto';
                           $file->move($destinationPath,$filename);
                           $user->photo=$filename;						   
						 }
					 }
					 $user->save(); */
					 
					/* if ($firmName!='') { 
					 $vendorbusiness->venderId=$userId;
					 $vendorbusiness->firmName=$firmName;
					 $vendorbusiness->incorporationDate=$incorporationDate;
					 $vendorbusiness->location=$location;
					 $vendorbusiness->latitude=$latitude;
					 $vendorbusiness->longitude=$longitude;
					 $vendorbusiness->pincode=$pincode;
					 $vendorbusiness->createdDate=date('Y-m-d H:i:s');
					 $vendorbusiness->save();
					} */
					
                    $vendorBusinessUpdate=0;					
					if ($firmName!='') {
						$vendorBusinessUpdate=1;
					 $vendorbusiness->firmName=$firmName;
					}
                    if ($incorporationDate!='' && $incorporationDate!='1970-01-01') {
					   $vendorBusinessUpdate=1;
					 $vendorbusiness->incorporationDate=$incorporationDate;
					}
                    if ($location!='') {
						$vendorBusinessUpdate=1;
					   $vendorbusiness->location=$location;
					}
                    if ($latitude!='') {
						$vendorBusinessUpdate=1;
					   $vendorbusiness->latitude=$latitude;
					}
                    
                    if ($longitude!='') {
						$vendorBusinessUpdate=1;
					   $vendorbusiness->longitude=$longitude;
					}
                    
                    if ($pincode!='') {
						$vendorBusinessUpdate=1;
					   $vendorbusiness->pincode=$pincode;
					}					
                  	
                    if ($vendorBusinessUpdate==1) {
					     $vendorbusiness->venderId=$userId;
						 $vendorbusiness->createdDate=date('Y-m-d H:i:s');
						 $vendorbusiness->save();
					}						
					 
					/*if ($request->businessCategoryId) {
					$businesscategory=DB::select( DB::raw("update tblbusinesscategory SET `noOfVenders`=`noOfVenders`+1 where id={$request->businessCategoryId}"));
					}
					
					if ($request->businessCategoryId) {
			        $servicetype=DB::select( DB::raw("update tblservicetype SET `noOfVenders`=`noOfVenders`+1 where businessCategoryId={$request->businessCategoryId}"));
			        }*/

			        if ($request->businessCategoryId) {
						
						$buscatexplode=explode(",",$request->businessCategoryId);
						foreach ($buscatexplode  as  $value) {
							$businesscategory=DB::select( DB::raw("update tblbusinesscategory SET `noOfVenders`=`noOfVenders`+1 where id={$value}"));
					         }
					}
					if ($request->businessCategoryId) {
                       $buscatexplode=explode(",",$request->businessCategoryId);
                       foreach ($buscatexplode as  $value) {
			         $servicetype=DB::select( DB::raw("update tblservicetype SET `noOfVenders`=`noOfVenders`+1 where businessCategoryId={$value}"));
					      }

					}
					
					 /* if($request->hasFile('idproof')) {
					   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
					   $files = $request->file('idproof');
					    foreach($files as $file){
						 $filename = time().$file->getClientOriginalName();
                         $extension = $file->getClientOriginalExtension();
                         $check=in_array($extension,$allowedfileExtension);
						 if($check) {
						   $destinationPath = 'vendorproof';
                           $file->move($destinationPath,$filename); 
						   $vendorproof=new VendorProof;
						   $vendorproof->venderId=$userId;
						   $vendorproof->photo=$filename;
						   $vendorproof->createdDate=date('Y-m-d H:i:s');
						   $vendorproof->save();
						 }
					   }
                     } */
			   
			   $user = Vendor::find($userId);
			   $userphone=$user->phone;

			   if ($deviceType!='') {
			   $user->deviceType=$deviceType;
			   }
			   if ($deviceToken!='') {
			   $user->deviceToken=$deviceToken;
			   }
			   $user->loginStatus=1;
			   if ($charity!='') {
			   $user->charity=$charity;
			   }
			   if ($deviceDetails!='') {
			   $user->deviceDetails=$deviceDetails;
			   }
			   if ($request->socialMediaId) {
			   $user->socialMediaId=$request->socialMediaId;
			   }
			   if ($request->gender && $request->gender!='') {
			   $user->gender=$request->gender;
			   }
			   //$user->loginType=$loginType;
			   //$user->createdDate=date('Y-m-d H:i:s');
			   /* if ($proofTypeId) {
			   $user->proofTypeId=$proofTypeId;
			   } */
			   if ($businessCategoryId && $businessCategoryId!=0) {
			   //$user->businessCategoryId=$businessCategoryId;
			   }
			   if ($phone!='') {
			   $user->phone=$phone;
			   }

               if ($code!='') {
               	 $user->code=$code;
               }

			   if ($website!='') {
			   $user->website=$website;
			   }
			   if ($email!='') {
			   $user->email=$email;
			   }
			   if ($dob) {
			   $user->birthDate=$dob;
			   }
			   if ($firstname!='') {
			   $user->fname=$firstname;
			   }
			   if ($lastname!='') {
			   $user->lname=$lastname;
			   }
			   if ($gender!=0) {
				$user->gender=$gender;
			   }
			   
			   if ($howdidyouknow!='') {
					 $user->howdidyouknow=$howdidyouknow;
				}

				if ($userphone!=$phone) {
					    //$user->isPhoneVerified=0;	
				}

			   if($request->hasFile('profilepicture')) {
						 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
					     $file = $request->file('profilepicture');
						 $filename = rand(1,1000000).time().$file->getClientOriginalName();
                         $extension = strtolower($file->getClientOriginalExtension());
						 $check=in_array($extension,$allowedfileExtension);
						 if($check) {
						   $destinationPath = 'vendorphoto';
                           $file->move($destinationPath,$filename);
                           $user->photo=$filename;						   
						 }
					 }
			   
			   $user->deviceType=$deviceType;
			   $user->deviceToken=$deviceToken;
			   $user->deviceDetails=$deviceDetails;
			   $user->loginType=$loginType;

			   if (is_numeric($countryId) && $countryId!=0) {
						 $user->countryId=$countryId;
					  }
					  
					  if (is_numeric($stateId) && $stateId!=0) {
							$user->stateId=$stateId;
					   }
						
					  if (is_numeric($cityId) && $cityId!=0) {
							$user->cityId=$cityId;
					   }

					   if (!is_numeric($countryId) && $countryId!='') {
							$countryName = DB::table('tblcountries')->where([['name','=',$countryId]])->first();
							if ($countryName) {
								$countryId=$countryName->id;
								$user->countryId=$countryId;
							} else {
								$countryName=$countryId;
								$country=DB::table('tblcountries')->insertGetId(
						['name'=>$countryId,'status'=>1,'currency'=>'$']);
								$countryId=$country;
								$user->countryId=$countryId;

								$language = DB::table('language')->where([['status', '=','Active']])->get();
								if ($language) {
									foreach ($language as $value) {
										$insert=DB::table('tblcountrytranslation')->insert(
								['countryId'=>$countryId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$countryName]);
									}
								}
							}
	
                      } 

					if (!is_numeric($stateId) && $stateId!='') {
						$stateName = DB::table('tblstates')->where([['name','=',$stateId]])->first();
						if ($stateName) {
							$stateId=$stateName->id;
							$user->stateId=$stateId;
						} else {
							$stateName=$stateId;
							$state=DB::table('tblstates')->insertGetId(
					['name'=>$stateId,'country_id'=>$countryId]);
							$stateId=$state;
							$user->stateId=$stateId;

							$language = DB::table('language')->where([['status', '=','Active']])->get();
							if ($language) {

								foreach ($language as $value) {
									$insert=DB::table('tblstatetranslation')->insert(
							['stateId'=>$stateId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$stateName]);
								}
							}
						}
					} 

					if (!is_numeric($cityId) && $cityId!='') {
						$cityName = DB::table('tblcities')->where([['name','=',$cityId]])->first();
						if ($cityName) {
							$cityId=$cityName->id;
							$user->cityId=$cityId;
						} else {
							$cityName=$cityId;
							$city=DB::table('tblcities')->insertGetId(
					['name'=>$cityId,'state_id'=>$stateId]);
							$cityId=$city;
							$user->cityId=$cityId;

							$language = DB::table('language')->where([['status', '=','Active']])->get();
								if ($language) {

									foreach ($language as $value) {
										$insert=DB::table('tblcitytranslation')->insert(
								['cityId'=>$cityId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$cityName]);
									}
								}
						}
						
					} 
			   $user->save();

			   if ($request->businessCategoryId) {
                       $deletebusinessCat=DB::select( DB::raw("delete from tblvenderbusinesscategory where `vendorId`='$userId'"));
                       $buscatexplode=explode(",",$request->businessCategoryId);
                       foreach ($buscatexplode as  $value) {
                          $vendorCategory=DB::select( DB::raw("Insert tblvenderbusinesscategory SET `vendorId`='$userId',businessCategoryId={$value}"));
                       }
                   }
			    /* DB::table('tblvender')->where('id',$userId)->update(
               ['deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'loginStatus'=>1,'deviceDetails'=>$deviceDetails,'socialMediaId'=>$request->socialMediaId,'loginType'=>$loginType,'createdDate'=>date('Y-m-d H:i:s'),'proofTypeId'=>$proofTypeId,'businessCategoryId'=>$businessCategoryId,'phone'=>$phone,'birthDate'=>$dob]); */
			   
				
			     $suserId=0;
			     $VendorDetails=$common->VendorDetailsNew($userId,$suserId,$langId);
				 $myarray['result']=$VendorDetails;	
				 $myarray['status']=1;
				 $myarray['isFirstTimeReg']=0;
				 $myarray['message']=$common->get_msg("vendor_detail",$langId)?$common->get_msg("vendor_detail",$langId):'Vendor Details.';
		  }
	   
	   }
	   
		 return response()->json($myarray); 
    }
	
	 /* Message List */
	
	public function getMessages(Request $request) {
	    $common=new CommanController;
	    $lang_id=($request->lang_id)?($request->lang_id):1;
	    $userId=($request->userId)?($request->userId):0;
	    $isVendor=($request->isVendor)?($request->isVendor):0;
        $langId=($request->header('langId'))?($request->header('langId')):1; 
       
        if ($isVendor==1 && $userId!=0) {
          $updatelang=DB::select( DB::raw("update tblvender SET `langId`='{$lang_id}' where id='{$userId}'"));
        }

        if ($isVendor==0 && $userId!=0) {
        	
        	$updatelang=DB::select( DB::raw("update tblcustomer SET `langId`='{$lang_id}' where id='{$userId}'"));
        } 

		$data=DB::select( DB::raw("SELECT tblgeneralmessage.title_key,tblgeneralmessagetranslation.title_value from tblgeneralmessagetranslation
	inner join tblgeneralmessage on tblgeneralmessage.id=tblgeneralmessagetranslation.general_message_id where tblgeneralmessagetranslation.lang_id=$lang_id and tblgeneralmessage.is_app_msg = '1'") ); 
	    $vals='';
	   if ($data) {
	      foreach ($data as $values) {
		   $title_value=$values->title_value;
		   $title_key=$values->title_key;
		   $messageList[]=array("msgKey"=>$title_key,"msgValue"=>$title_value);
		  }
		   $myarray['result']=$messageList;					
		   $myarray['message']=$common->get_msg("message_list",$langId)?$common->get_msg("message_list",$langId):"Message List.";
		   $myarray['status']=1;
	   }
	   else {
	       $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("no_message_list",$langId)?$common->get_msg("no_message_list",$langId):"No App Messages Found.";
		   $myarray['status']=1;
	   }
	   return response()->json($myarray);
	}
	
	/* Favourite Vendor */
	
	public function favouritevendor(Request $request) {
	   
	     $common=new CommanController;
         $langId=($request->header('langId'))?($request->header('langId')):1; 
	  
		 if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select venderId";
		   $myarray['status']=0;
		 
		 } else if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0;
		 
		 } else {
		   $count=DB::table('tblcustomerfavourite')->where([['venderId', '=', $request->venderId],['customerId', '=', $request->customerId]])->count();
		   if ($count > 0) {
		     DB::table('tblcustomerfavourite')->where([['venderId', '=', $request->venderId],['customerId', '=', $request->customerId]])->update(
               ['isFavourite'=>$request->isFavourite,'createdDate'=>date('Y-m-d H:i:s')]);
		      $myarray['result']=(object)array();					
		      $myarray['message']=$common->get_msg("favourite_updated",$langId)?$common->get_msg("favourite_updated",$langId):"You have favourite updated successfully.";
		     $myarray['status']=1;
		   } else {
		     DB::table('tblcustomerfavourite')->insert(
               ['customerId'=>$request->customerId,'venderId'=>$request->venderId,'isFavourite'=>$request->isFavourite,'createdDate'=>date('Y-m-d H:i:s')]);
		     $myarray['result']=(object)array();					
		     $myarray['message']=$common->get_msg("favourite_added",$langId)?$common->get_msg("favourite_added",$langId):"You have favourite added successfully.";
		     $myarray['status']=1;
		   }
		 }			 
	   return response()->json($myarray); 
	}
	
	/* Service Provider List */
	
	public function providerList(Request $request) {
		$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1; 
		$currDate=date('Y-m-d');	

		if (!$request->serviceTypeId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_serviceTypeId",$langId)?$common->get_msg("blank_serviceTypeId",$langId):"Please select serviceTypeId.";
		   $myarray['status']=0;
		 
		 } else if (!$request->lat) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_lat",$langId)?$common->get_msg("blank_lat",$langId):"Please enter latitude.";
		   $myarray['status']=0;
		} else if (!$request->long) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_long",$langId)?$common->get_msg("blank_long",$langId):"Please select longitude.";
		   $myarray['status']=0;   
		} else {
		$keywordsearch='';	
		if ($request->keyword && $request->keyword!='') {
		  $keywordsearch=" and ((tblvenderbusiness.firmName like '".$request->keyword."%') OR (tblvender.fname like '".$request->keyword."%') OR (tblvender.lname like '".$request->keyword."%')) ";
		}

            /*echo "Select tblvender.isVarified,tblvender.charity,tblvender.website,tblvender.id,tblvender.fname,tblvender.lname,tblvender.email,tblvender.aboutUs,tblvender.businessCategoryId,tblvenderbusiness.firmName,tblvenderbusiness.incorporationDate,tblvenderbusiness.location,tblvenderbusiness.latitude,tblvenderbusiness.longitude,tblvenderbusiness.pincode,tblvender.avgRate,tblvender.photo,tblvender.isOnline,IF(tblvender.showCurrentLocation = 1,IFNULL(111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(tblvender.currentLatitude))
 * COS(RADIANS(tblvender.currentLongitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(tblvender.currentLatitude)))),0),IFNULL(111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(tblvenderbusiness.latitude))
 * COS(RADIANS(tblvenderbusiness.longitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(tblvenderbusiness.latitude)))),0)) AS distance_in_km
  from tblvender
 INNER JOIN tblvenderbusiness ON tblvender.id=tblvenderbusiness.venderId
 INNER JOIN tblvenderservicetype ON tblvender.id=tblvenderservicetype.venderId
 INNER JOIN tblvendersubscription as subscription ON tblvender.id=subscription.venderId
 Where tblvenderservicetype.serviceTypeId={$request->serviceTypeId} and tblvender.isActive=1 and subscription.status=1 and subscription.endDate >= '$currDate' and subscription.noOfRemainingLeads > 0  $keywordsearch group by tblvender.id HAVING distance_in_km < 100 ORDER BY distance_in_km ASC";
       exit();*/
		/*$data=DB::select( DB::raw("Select tblvender.isVarified,tblvender.charity,tblvender.website,tblvender.id,tblvender.fname,tblvender.lname,tblvender.email,tblvender.aboutUs,tblvender.businessCategoryId,tblvenderbusiness.firmName,tblvenderbusiness.incorporationDate,tblvenderbusiness.location,tblvenderbusiness.latitude,tblvenderbusiness.longitude,tblvenderbusiness.pincode,tblvender.avgRate,tblvender.photo,tblvender.isOnline,111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(tblvenderbusiness.latitude))
 * COS(RADIANS(tblvenderbusiness.longitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(tblvenderbusiness.latitude))))
 AS distance_in_km from tblvender
 INNER JOIN tblvenderbusiness ON tblvender.id=tblvenderbusiness.venderId
 INNER JOIN tblvenderservicetype ON tblvender.id=tblvenderservicetype.venderId
 Where tblvenderservicetype.serviceTypeId={$request->serviceTypeId} and tblvender.isActive=1 $keywordsearch  HAVING distance_in_km < 100 ORDER BY distance_in_km ASC"));*/
		
	  $cityName=($request->cityName)?($request->cityName):"";
      
      $isCity=0;

      if ($cityName!='') {
          $isCity=1;
      	  $data=DB::select( DB::raw("Select tblvender.isVarified,tblvender.charity,tblvender.website,tblvender.id,tblvender.fname,tblvender.lname,tblvender.email,tblvender.aboutUs,tblvender.businessCategoryId,tblvenderbusiness.firmName,tblvenderbusiness.incorporationDate,tblvenderbusiness.location,tblvenderbusiness.latitude,tblvenderbusiness.longitude,tblvenderbusiness.pincode,tblvender.avgRate,tblvender.photo,tblvender.isOnline,IF(tblvender.showCurrentLocation = 1,IFNULL(111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(tblvender.currentLatitude))
 * COS(RADIANS(tblvender.currentLongitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(tblvender.currentLatitude)))),0),IFNULL(111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(tblvenderbusiness.latitude))
 * COS(RADIANS(tblvenderbusiness.longitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(tblvenderbusiness.latitude)))),0)) AS distance_in_km
  from tblvender
 INNER JOIN tblvenderbusiness ON tblvender.id=tblvenderbusiness.venderId
 INNER JOIN tblvenderservicetype ON tblvender.id=tblvenderservicetype.venderId
 INNER JOIN tblvendersubscription as subscription ON tblvender.id=subscription.venderId
 INNER JOIN tblcities as cities ON tblvender.cityId=cities.id
 INNER JOIN tblcitytranslation as citytranslation ON cities.id=citytranslation.cityId
 INNER JOIN tblvenderpaymenttype as venderpaymenttype  ON tblvender.id=venderpaymenttype.venderId
 INNER JOIN tblvenderproof as venderproof ON tblvender.id=venderproof.venderId
 INNER JOIN tblvenderbusinesscategory as venderbusinesscategory ON tblvender.id=venderbusinesscategory.vendorId
 

 Where tblvenderservicetype.serviceTypeId={$request->serviceTypeId} and tblvender.isActive=1 and subscription.status=1 and subscription.endDate >= '$currDate' and subscription.noOfRemainingLeads > 0 and citytranslation='".$cityName."'   $keywordsearch and tblvenderbusiness.firmName!='' and tblvenderbusiness.location!='' and tblvender.isPhoneVerified=1 group by tblvender.id  ORDER BY distance_in_km ASC"));

      } 

          if ($isCity==0) {
                  
          	      $data=DB::select( DB::raw("Select tblvender.isVarified,tblvender.charity,tblvender.website,tblvender.id,tblvender.fname,tblvender.lname,tblvender.email,tblvender.aboutUs,tblvender.businessCategoryId,tblvenderbusiness.firmName,tblvenderbusiness.incorporationDate,tblvenderbusiness.location,tblvenderbusiness.latitude,tblvenderbusiness.longitude,tblvenderbusiness.pincode,tblvender.avgRate,tblvender.photo,tblvender.isOnline,IF(tblvender.showCurrentLocation = 1,IFNULL(111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(tblvender.currentLatitude))
 * COS(RADIANS(tblvender.currentLongitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(tblvender.currentLatitude)))),0),IFNULL(111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(tblvenderbusiness.latitude))
 * COS(RADIANS(tblvenderbusiness.longitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(tblvenderbusiness.latitude)))),0)) AS distance_in_km
  from tblvender
 INNER JOIN tblvenderbusiness ON tblvender.id=tblvenderbusiness.venderId
 INNER JOIN tblvenderservicetype ON tblvender.id=tblvenderservicetype.venderId
 INNER JOIN tblvendersubscription as subscription ON tblvender.id=subscription.venderId
 INNER JOIN tblvenderpaymenttype as venderpaymenttype  ON tblvender.id=venderpaymenttype.venderId
 INNER JOIN tblvenderproof as venderproof ON tblvender.id=venderproof.venderId
 INNER JOIN tblvenderbusinesscategory as venderbusinesscategory ON tblvender.id=venderbusinesscategory.vendorId

 Where tblvenderservicetype.serviceTypeId={$request->serviceTypeId} and tblvender.isActive=1 and subscription.status=1 and subscription.endDate >= '$currDate' and subscription.noOfRemainingLeads > 0  $keywordsearch and tblvenderbusiness.firmName!='' and tblvenderbusiness.location!='' and tblvender.isPhoneVerified=1 group by tblvender.id HAVING distance_in_km < 500 ORDER BY distance_in_km ASC"));
          
          } else {
                
		                
			                 if (count($data)==0) {
			                      $data=DB::select( DB::raw("Select tblvender.isVarified,tblvender.charity,tblvender.website,tblvender.id,tblvender.fname,tblvender.lname,tblvender.email,tblvender.aboutUs,tblvender.businessCategoryId,tblvenderbusiness.firmName,tblvenderbusiness.incorporationDate,tblvenderbusiness.location,tblvenderbusiness.latitude,tblvenderbusiness.longitude,tblvenderbusiness.pincode,tblvender.avgRate,tblvender.photo,tblvender.isOnline,IF(tblvender.showCurrentLocation = 1,IFNULL(111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
			 * COS(RADIANS(tblvender.currentLatitude))
			 * COS(RADIANS(tblvender.currentLongitude) - RADIANS($request->long))
			 + SIN(RADIANS($request->lat))
			 * SIN(RADIANS(tblvender.currentLatitude)))),0),IFNULL(111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
			 * COS(RADIANS(tblvenderbusiness.latitude))
			 * COS(RADIANS(tblvenderbusiness.longitude) - RADIANS($request->long))
			 + SIN(RADIANS($request->lat))
			 * SIN(RADIANS(tblvenderbusiness.latitude)))),0)) AS distance_in_km
			  from tblvender
			 INNER JOIN tblvenderbusiness ON tblvender.id=tblvenderbusiness.venderId
			 INNER JOIN tblvenderservicetype ON tblvender.id=tblvenderservicetype.venderId
			 INNER JOIN tblvendersubscription as subscription ON tblvender.id=subscription.venderId
			 INNER JOIN tblvenderpaymenttype as venderpaymenttype  ON tblvender.id=venderpaymenttype.venderId
             INNER JOIN tblvenderproof as venderproof ON tblvender.id=venderproof.venderId
             INNER JOIN tblvenderbusinesscategory as venderbusinesscategory ON tblvender.id=venderbusinesscategory.vendorId
			 Where tblvenderservicetype.serviceTypeId={$request->serviceTypeId} and tblvender.isActive=1 and subscription.status=1 and subscription.endDate >= '$currDate' and subscription.noOfRemainingLeads > 0  $keywordsearch and tblvenderbusiness.firmName!='' and tblvenderbusiness.location!='' and tblvender.isPhoneVerified=1 group by tblvender.id HAVING distance_in_km < 500 ORDER BY distance_in_km ASC"));
			                 }
		           


          }

           
           
           
             	
      
	  
		 
		 $i=0;
	     
	     if ($data) {
			 $service=array();
			 
			 foreach ($data as $values) {
			 
			 $businessCategoryId=($values->businessCategoryId)?($values->businessCategoryId):0;
			 $BusinessCategory='';
			 
			 if ($businessCategoryId!=0 && $businessCategoryId!='') {
			   $vendorBusinessCategory = DB::table('tblbusinesscategory')->where([['id', '=', $businessCategoryId]])->first();
			   $BusinessCategory=$common->getBusinessCategoryValue($businessCategoryId,$langId);
			 }
			 
			 $userId=($request->userId)?($request->userId):0;
			 
			 $VendorId=($values->id)?($values->id):0;
			 
			 $isFavorite=0;
             
             if ($userId!=0 && $VendorId!=0) {
			  $vendorDataFavouriteCount = DB::table('tblcustomerfavourite')->where([['venderId', '=', $VendorId],['customerId', '=', $userId],['isFavourite','=',1]])->count();
				 if ($vendorDataFavouriteCount > 0) {
					$isFavorite=1; 
				 }
			 }
			 
			$ServiceList=array();
			$SubService=array();
			$VendorServiceType = DB::table('tblvenderservicetype')->where([['venderId', '=', $VendorId]])->get();
				 if ($VendorServiceType->count() > 0) {
				    foreach ($VendorServiceType as $ServiceType) {
					  $serviceTypeId=($ServiceType->serviceTypeId)?($ServiceType->serviceTypeId):"";
					  if ($serviceTypeId!='') {
					    $VendorServiceName = DB::table('tblservicetype')->where([['id', '=', $serviceTypeId]])->first();
					    if ($VendorServiceName) {
						//$ServiceName=($VendorServiceName->name)?($VendorServiceName->name):"";
					    $ServiceName=$common->getServiceTypeValue($serviceTypeId,$langId);
					    $ServiceList[]=array("serviceTypeId"=>$ServiceType->serviceTypeId,"serviceName"=>$ServiceName);
						}
						$VendorSubServiceType = DB::table('tblvenderservice')->where([['venderServiceTypeId', '=',$ServiceType->serviceTypeId],['isActive','=',1]])->get();
					    if ($VendorSubServiceType->count() > 0) {
						   $currency=$common->getCurrency();
						   foreach ($VendorSubServiceType as $SubServices) {
						$SubService[]=array("serviceTypeId"=>$ServiceType->serviceTypeId,"serviceName"=>$ServiceName,"id"=>$SubServices->id,"name"=>$SubServices->name,"price"=>$SubServices->price,"currency"=>$currency);
						   }
						}
					  }
					}
				 }
			 
			 $vendorFname=($values->fname)?($values->fname):"";
			 $vendorLname=($values->lname)?($values->lname):"";
			 $vendorEmail=($values->email)?($values->email):"";
			 $vendoraboutUs=($values->aboutUs)?($values->aboutUs):"";
			 $firmName=($values->firmName)?($values->firmName):"";
			 $incorporationDate=($values->incorporationDate)?($values->incorporationDate):"";
			 $location=($values->location)?($values->location):"";
			 $latitude=($values->latitude)?($values->latitude):"";
			 $longitude=($values->longitude)?($values->longitude):"";
			 $pincode=($values->pincode)?($values->pincode):"";
			 $distance_in_km=($values->distance_in_km)?(number_format(($values->distance_in_km),2)):0;
			 
			 //$avgRate=($values->avgRate)?($values->avgRate):0;
			 $avgRate=$common->getAverageRatingVendor($VendorId);

			 $vendorphoto=($values->photo!='')?($values->photo):'';
			 $isVarified=($values->isVarified)?($values->isVarified):0;
			 $isOnline=($values->isOnline)?($values->isOnline):0;
			 
			 
			 $verificationTitle='';
			$verificationContent='';
			if ($isVarified==0) {
			  $verificationTitle=$common->get_msg('profile_verification_title');
			  $verificationContent=$common->get_msg('profile_verification_desc');  
			}
			 
			 $url=url('/');
			 if ($vendorphoto!='') {
			    $vendorphoto=$url."/vendorphoto/".$vendorphoto;
			 }
			 $website=($values->website)?($values->website):"";
			 
			 $charity=($values->charity)?($values->charity):"";
			 $distanceUnit=$common->get_msg("distance_unit_km",$langId)?$common->get_msg("distance_unit_km",$langId):'Km';
			 $businessCategoryList=$common->vendorBusinessCategoryListData($values->id,$langId);

			 $service[]=array('vendorId'=>$values->id,"vendorFirstName"=>$vendorFname,"vendorLastName"=>$vendorLname,"vendorEmail"=>$vendorEmail,"vendorAboutUs"=>$vendoraboutUs,"vendorBusinessCategoryId"=>$businessCategoryId,"vendorBusinessCategory"=>$BusinessCategory,"vendorFirmName"=>$firmName,"vendorIncorporationDate"=>$incorporationDate,"vendorLocation"=>$location,"vendorLatitude"=>$latitude,"vendorLongitude"=>$longitude,"vendorPincode"=>$pincode,"distanceInKms"=>$distance_in_km,"ratting"=>$avgRate,'vendorPhoto'=>$vendorphoto,"distanceUnit"=>$distanceUnit,"vendorWebsite"=>$website,"vendorCharity"=>$charity,"isFavorite"=>$isFavorite,"vendorService"=>$ServiceList,"vendorSubService"=>$SubService,"isVarified"=>$isVarified,"verificationTitle"=>$verificationTitle,"verificationContent"=>$verificationContent,"isOnline"=>(int)$isOnline,"businessCategoryList"=>$businessCategoryList);
			 $i++;
			 }
			 $myarray['result']=$service;
             $myarray['serviceProviderCount']=$i;			 
		     $myarray['message']=$common->get_msg("service_provider",$langId)?$common->get_msg("service_provider",$langId):"Service Provider List.";
		     $myarray['status']=1;
		 
		 } else {
		  $myarray['result']=array();
          $myarray['serviceProviderCount']=$i;		  
		  $myarray['message']=$common->get_msg("no_service_provider",$langId)?$common->get_msg("no_service_provider",$langId):"No Service Provider Found.";
		  $myarray['status']=1;
		 }
		
		}
		return response()->json($myarray);
	}
	
	/* customer registration */
	public function customerregister(Request $request) { 
        $howdidyouknow=($request->howdidyouknow)?($request->howdidyouknow):"";
        $deviceType=($request->deviceType)?($request->deviceType):0;
		$deviceToken=($request->deviceToken)?($request->deviceToken):"";
		$deviceDetails=($request->deviceDetails)?($request->deviceDetails):"";		  
		$loginType=($request->loginType)?($request->loginType):1; 
    	$common=new CommanController;
    	$langId=($request->header('langId'))?($request->header('langId')):1; 
        $countryId=($request->countryId)?($request->countryId):0;
        $stateId=($request->stateId)?($request->stateId):0;
        $cityId=($request->cityId)?($request->cityId):0;

        if (!$request->socialMediaId) {
        	if (!$request->id) {
				if (!$request->email) {
			   		$myarray['result']=(object)array();					
				   	$myarray['message']=$common->get_msg("email_blank",$langId)?$common->get_msg("email_blank",$langId):"Please Enter Email Address.";
				   	$myarray['status']=0;
				 
				} elseif (!$request->password) {
				   	$myarray['result']=(object)array();					
				   	$myarray['message']=$common->get_msg("password_blank",$langId)?$common->get_msg("password_blank",$langId):"Please Enter Password.";
				   	$myarray['status']=0;
				 
				} elseif (!$request->phone) {
				   	$myarray['result']=(object)array();					
				   	$myarray['message']=$common->get_msg("phone_blank",$langId)?$common->get_msg("phone_blank",$langId):"Please Enter Phone.";
				   	$myarray['status']=0;
				 
				} else { 
					$countemail = DB::table('tblcustomer')->where('email', '=', $request->email)->count();
					$countphone = DB::table('tblcustomer')->where('phone', '=', $request->phone)->count();
					if ($countemail>0) {
						$myarray['result']=(object)array();	
						$myarray['status']=1;
						$myarray['message']=$common->get_msg("already_email",$langId)?$common->get_msg("already_email",$langId):'This email address has been already taken please try another.';
					} else if ($countphone>0) {
						$myarray['result']=(object)array();
						$myarray['status']=1;
						$myarray['message']=$common->get_msg("already_phone",$langId)?$common->get_msg("already_phone",$langId):'This phone number has been already taken please try another.';
					} else if ($countemail>0 && $countphone>0) {
						$myarray['result']=(object)array();
						$myarray['status']=1;
						$myarray['message']=$common->get_msg("already_email_phone",$langId)?$common->get_msg("already_email_phone",$langId):'This email address & phone number has been already taken please try another.';		
					} else {
						$customer = new Customer();
						$customer->fname=($request->firstname && $request->firstname!='')?($request->firstname):'';
						$customer->lname=($request->lastname && $request->lastname!='')?($request->lastname):'';
						$customer->email=($request->email && $request->email!='')?($request->email):'';
						$customer->password=($request->password && $request->password!='')?(bcrypt($request->password)):'';
						$customer->phone=($request->phone && $request->phone!='')?($request->phone):'';
						$customer->createdDate=date('Y-m-d H:i:s');
	                    $customer->howdidyouknow=$howdidyouknow;
						$customer->deviceType=$deviceType;
						$customer->deviceToken=$deviceToken;
						$customer->deviceDetails=$deviceDetails;
	                    
	                    if (is_numeric($countryId) && $countryId!=0) {
	                    	$customer->countryId=$countryId;
	                    }
	                    if (is_numeric($stateId) && $stateId!=0) {
	                    	$customer->stateId=$stateId;
	                    }
	                    if (is_numeric($cityId) && $cityId!=0) {
	                    	$customer->cityId=$cityId;
	                    }

                    	if (!is_numeric($countryId) && $countryId!='') {
                    		$countryName = DB::table('tblcountries')->where([['name','=',$countryId]])->first();
                    		if ($countryName) {
	                    		$countryId=$countryName->id;
	                    		$customer->countryId=$countryId;
                    		} else {
	                    		$countryName=$countryId;
	                    		$country=DB::table('tblcountries')->insertGetId(['name'=>$countryId,'status'=>1,'currency'=>'$']);
	                    		$countryId=$country;
	                    		$customer->countryId=$countryId;

                    			$language = DB::table('language')->where([['status', '=','Active']])->get();
                				if ($language) {
									foreach ($language as $value) {
                						$insert=DB::table('tblcountrytranslation')->insert(['countryId'=>$countryId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$countryName]);
                					}
                				}
                    		}
                    	} 

                    	if (!is_numeric($stateId) && $stateId!='') {
                    		$stateName = DB::table('tblstates')->where([['name','=',$stateId]])->first();
                    		if ($stateName) {
                    			$stateId=$stateName->id;
                    			$customer->stateId=$stateId;
                    		} else {
                    			$stateName=$stateId;
                    			$state=DB::table('tblstates')->insertGetId(['name'=>$stateId,'country_id'=>$countryId]);
                    			$stateId=$state;
                    			$customer->stateId=$stateId;

                    			$language = DB::table('language')->where([['status', '=','Active']])->get();
                				if ($language) {
									foreach ($language as $value) {
                						$insert=DB::table('tblstatetranslation')->insert(['stateId'=>$stateId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$stateName]);
                					}
                				}
                    		}
                    	} 

                    	if (!is_numeric($cityId) && $cityId!='') {
                    		$cityName = DB::table('tblcities')->where([['name','=',$cityId]])->first();
                    		if ($cityName) {
                    			$cityId=$cityName->id;
                    			$customer->cityId=$cityId;
                    		} else {
                    			$cityName=$cityId;
                    			$city=DB::table('tblcities')->insertGetId(['name'=>$cityId,'state_id'=>$stateId]);
	                    		$cityId=$city;
	                    		$customer->cityId=$cityId;

                    			$language = DB::table('language')->where([['status', '=','Active']])->get();
                				if ($language) {
                					foreach ($language as $value) {
                						$insert=DB::table('tblcitytranslation')->insert(['cityId'=>$cityId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$cityName]);
                					}
                				}
                    		}
                    	} 

						if($request->hasFile('profilepicture')) {
						 	$allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
					     	$file = $request->file('profilepicture');
						 	$filename = rand(1,1000000).time().$file->getClientOriginalName();
                         	$extension = strtolower($file->getClientOriginalExtension());
						 	$check=in_array($extension,$allowedfileExtension);
						 	if($check) {
						   		$destinationPath = 'customerphoto';
                           		$file->move($destinationPath,$filename);
                           		$customer->photo=$filename;						   
						 	}
					 	}
						$customer->loginStatus=0;
						$customer->isActive=1;
						$customer->lastLoginDate=date('Y-m-d H:i:s');
						$customer->save();
					
					    $CustomerDetails=$common->CustomerDetails($customer->id,$langId);
						$myarray['result']=$CustomerDetails;
						$myarray['status']=1;
						$myarray['message']=$common->get_msg("register_customer",$langId)?$common->get_msg("register_customer",$langId):'You have successfully registerd.';
					}
				}	
		   	} else {
			    $checkValidId = DB::table('tblcustomer')->where('id', '=', $request->id)->count();
				if ($checkValidId==0) {
				    $myarray['result']=(object)array();	
					$myarray['status']=0;
					$myarray['message']=$common->get_msg("invalid_customerId",$langId)?$common->get_msg("invalid_customerId",$langId):'Invalid customerId.';
				} else {
					$customer = Customer::find($request->id);
					 
					$customer->fname=($request->firstname && $request->firstname!='')?($request->firstname):'';
					$customer->lname=($request->lastname && $request->lastname!='')?($request->lastname):'';
					$customer->email=($request->email && $request->email!='')?($request->email):'';
					if ($request->password && $request->password!='') {
						$customer->password=($request->password && $request->password!='')?(bcrypt($request->password)):'';
					}
					$customer->phone=($request->phone && $request->phone!='')?($request->phone):'';
					if ($howdidyouknow!='') {
						$customer->howdidyouknow=$howdidyouknow;
					}
                     
                    if (is_numeric($countryId) && $countryId!=0) {
                    	$customer->countryId=$countryId;
                    }
                    if (is_numeric($stateId) && $stateId!=0) {
                    	$customer->stateId=$stateId;
                    }
                    if (is_numeric($cityId) && $cityId!=0) {
                    	$customer->cityId=$cityId;
                    }

                    if (!is_numeric($countryId) && $countryId!='') {
                    	$countryName = DB::table('tblcountries')->where([['name','=',$countryId]])->first();
                    	if ($countryName) {
                    		$countryId=$countryName->id;
                    		$customer->countryId=$countryId;
                    	} else {
                    		$countryName=$countryId;
                    		$country=DB::table('tblcountries')->insertGetId(['name'=>$countryId,'status'=>1,'currency'=>'$']);
                    		$countryId=$country;
                    		$customer->countryId=$countryId;

                    		$language = DB::table('language')->where([['status', '=','Active']])->get();
                			if ($language) {
								foreach ($language as $value) {
                					$insert=DB::table('tblcountrytranslation')->insert(['countryId'=>$countryId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$countryName]);
                				}
                			}
                    	}
                    } 

                    if (!is_numeric($stateId) && $stateId!='') {
                    	$stateName = DB::table('tblstates')->where([['name','=',$stateId]])->first();
                    	if ($stateName) {
                    		$stateId=$stateName->id;
                    		$customer->stateId=$stateId;
                    	} else {
                    		$stateName=$stateId;
                    		$state=DB::table('tblstates')->insertGetId(['name'=>$stateId,'country_id'=>$countryId]);
                    		$stateId=$state;
                    		$customer->stateId=$stateId;

                    		$language = DB::table('language')->where([['status', '=','Active']])->get();
                			if ($language) {
								foreach ($language as $value) {
                					$insert=DB::table('tblstatetranslation')->insert(['stateId'=>$stateId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$stateName]);
                				}
                			}
                    	}
                    } 

                    if (!is_numeric($cityId) && $cityId!='') {
                    	$cityName = DB::table('tblcities')->where([['name','=',$cityId]])->first();
                    	if ($cityName) {
                    		$cityId=$cityName->id;
                    		$customer->cityId=$cityId;
                    	} else {
                    		$cityName=$cityId;
                    		$city=DB::table('tblcities')->insertGetId(['name'=>$cityId,'state_id'=>$stateId]);
                    		$cityId=$city;
                    		$customer->cityId=$cityId;
                             
                            $language = DB::table('language')->where([['status', '=','Active']])->get();
                			if ($language) {
								foreach ($language as $value) {
                					$insert=DB::table('tblcitytranslation')->insert(['cityId'=>$cityId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$cityName]);
                				}
                			}
                    	}
                    } 

				 	if($request->hasFile('profilepicture')) {
						$allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
					    $file = $request->file('profilepicture');
						$filename = rand(1,1000000).time().$file->getClientOriginalName();
                        extension = strtolower($file->getClientOriginalExtension());
						$check=in_array($extension,$allowedfileExtension);
						if($check) {
						   $destinationPath = 'customerphoto';
                           $file->move($destinationPath,$filename);
                           $customer->photo=$filename;						   
						}
					}
					$customer->isActive=1;
					$customer->save();
						 
					$common=new CommanController;
				    $CustomerDetails=$common->CustomerDetails($request->id,$langId);
					$myarray['result']=$CustomerDetails;	
					$myarray['status']=1;
					$myarray['message']=$common->get_msg("update_profile",$langId)?$common->get_msg("update_profile",$langId):'Your profile has been updated successfully.';
				}
			}				   
		} else {
			$countsocial = DB::table('tblcustomer')->where('socialMediaId', '=', $request->socialMediaId)->count();
		  
		  	if ($countsocial==0) {
		     	$countemail = DB::table('tblcustomer')->where('email', '=', $request->email)->count();
		     	if ($countemail > 0) {
			   		$email = DB::table('tblcustomer')->where('email', '=', $request->email)->first();
			   		$userId=$email->id;
			   
		   			if (!is_numeric($countryId) && $countryId!='') {
                    	$countryName = DB::table('tblcountries')->where([['name','=',$countryId]])->first();
                    	if ($countryName) {
                    		$countryId=$countryName->id;
                    		$customer->countryId=$countryId;
                    	} else {
                    		$countryName=$countryId;
                    		$country=DB::table('tblcountries')->insertGetId(['name'=>$countryId,'status'=>1,'currency'=>'$']);
                    		$countryId=$country;
                    		$customer->countryId=$countryId;
                    		$language = DB::table('language')->where([['status', '=','Active']])->get();
                			if ($language) {
								foreach ($language as $value) {
                					$insert=DB::table('tblcountrytranslation')->insert(['countryId'=>$countryId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$countryName]);
                				}
                			}
                    	}
                    } 

                    if (!is_numeric($stateId) && $stateId!='') {
                    	$stateName = DB::table('tblstates')->where([['name','=',$stateId]])->first();
                    	if ($stateName) {
                    		$stateId=$stateName->id;
                    		$customer->stateId=$stateId;
                    	} else {
                    		$stateName=$stateId;
                    		$state=DB::table('tblstates')->insertGetId(['name'=>$stateId,'country_id'=>$countryId]);
                    		$stateId=$state;
                    		$customer->stateId=$stateId;
                    		$language = DB::table('language')->where([['status', '=','Active']])->get();
                			if ($language) {
								foreach ($language as $value) {
                					$insert=DB::table('tblstatetranslation')->insert(['stateId'=>$stateId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$stateName]);
                				}
                			}
                    	}
                    } 

                    if (!is_numeric($cityId) && $cityId!='') {
                    	$cityName = DB::table('tblcities')->where([['name','=',$cityId]])->first();
                    	if ($cityName) {
                    		$cityId=$cityName->id;
                    		$customer->cityId=$cityId;
                    	} else {
                    		$cityName=$cityId;
                    		$city=DB::table('tblcities')->insertGetId(['name'=>$cityId,'state_id'=>$stateId]);
                    		$cityId=$city;
                    		$customer->cityId=$cityId;
                    		$language = DB::table('language')->where([['status', '=','Active']])->get();
                			if ($language) {
								foreach ($language as $value) {
                					$insert=DB::table('tblcitytranslation')->insert(['cityId'=>$cityId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$cityName]);
                				}
                			}	
                    	}
                    } 

			   		DB::table('tbldevicetoken')->insert(['customerId'=>$userId,'deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'loginStatus'=>1,'deviceDetails'=>$deviceDetails,'tokenDate'=>date('Y-m-d H:i:s')]);
			   		DB::table('tblcustomer')->where('id',$userId)->update(['deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'loginStatus'=>1,'deviceDetails'=>$deviceDetails,'socialMediaId'=>$request->socialMediaId,'loginType'=>$loginType,'createdDate'=>date('Y-m-d H:i:s'),'howdidyouknow'=>$howdidyouknow,'email'=>$request->email,'countryId'=>$countryId,'stateId'=>$stateId,'cityId'=>$cityId]);
			   
			   		$CustomerDetails=$common->CustomerDetails($userId,$langId);
			 		$myarray['result']=$CustomerDetails;	
				 	$myarray['status']=1;
				 	$myarray['message']=$common->get_msg("registration_detail",$langId)?$common->get_msg("registration_detail",$langId):'Your registration details.';
			   
			 	} else {
				    $customer = new Customer();
			        $customer->fname=($request->firstname && $request->firstname!='')?($request->firstname):'';
					$customer->lname=($request->lastname && $request->lastname!='')?($request->lastname):'';
					$customer->email=($request->email && $request->email!='')?($request->email):'';
			        $customer->phone=($request->phone && $request->phone!='')?($request->phone):'';
			        $customer->deviceType=$deviceType;
					$customer->deviceToken=$deviceToken;
					$customer->deviceDetails=$deviceDetails;
					$customer->loginStatus=1;
					$customer->socialMediaId=$request->socialMediaId;
					$customer->lastLoginDate=date('Y-m-d H:i:s');
					$customer->loginType=$loginType;
					$customer->createdDate=date('Y-m-d H:i:s');
					$customer->isActive=1;
					if ($howdidyouknow!='') {
						$customer->howdidyouknow=$howdidyouknow;
					}

					if (is_numeric($countryId) && $countryId!=0) {
                    	$customer->countryId=$countryId;
                    }
                    if (is_numeric($stateId) && $stateId!=0) {
                    	$customer->stateId=$stateId;
                    }
                    if (is_numeric($cityId) && $cityId!=0) {
                    	$customer->cityId=$cityId;
                    }

                    if (!is_numeric($countryId) && $countryId!='') {
                    	$countryName = DB::table('tblcountries')->where([['name','=',$countryId]])->first();
                    	if ($countryName) {
                    		$countryId=$countryName->id;
                    		$customer->countryId=$countryId;
                    	} else {
                    		$countryName=$countryId;
                    		$country=DB::table('tblcountries')->insertGetId(['name'=>$countryId,'status'=>1,'currency'=>'$']);
                    		$countryId=$country;
                    		$customer->countryId=$countryId;
                    		$language = DB::table('language')->where([['status', '=','Active']])->get();
                			if ($language) {

                				foreach ($language as $value) {
                					$insert=DB::table('tblcountrytranslation')->insert(['countryId'=>$countryId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$countryName]);
                				}
                			}
                    	}
                    } 

                    if (!is_numeric($stateId) && $stateId!='') {
                    	$stateName = DB::table('tblstates')->where([['name','=',$stateId]])->first();
                    	if ($stateName) {
                    		$stateId=$stateName->id;
                    		$customer->stateId=$stateId;
                    	} else {
                    		$stateName=$stateId;
                    		$state=DB::table('tblstates')->insertGetId(['name'=>$stateId,'country_id'=>$countryId]);
                    		$stateId=$state;
                    		$customer->stateId=$stateId;
                    		$language = DB::table('language')->where([['status', '=','Active']])->get();
		                	if ($language) {
		                		foreach ($language as $value) {
		                			$insert=DB::table('tblstatetranslation')->insert(['stateId'=>$stateId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$stateName]);
		                		}
		                	}
                    	}
                    } 

                    if (!is_numeric($cityId) && $cityId!='') {
                    	$cityName = DB::table('tblcities')->where([['name','=',$cityId]])->first();
                    	if ($cityName) {
                    		$cityId=$cityName->id;
                    		$customer->cityId=$cityId;
                    	} else {
                    		$cityName=$cityId;
                    		$city=DB::table('tblcities')->insertGetId(['name'=>$cityId,'state_id'=>$stateId]);
                    		$cityId=$city;
                    		$customer->cityId=$cityId;
                    		$language = DB::table('language')->where([['status', '=','Active']])->get();
                			if ($language) {
								foreach ($language as $value) {
                					$insert=DB::table('tblcitytranslation')->insert(['cityId'=>$cityId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$cityName]);
                				}
                			}
                    	}
                    } 
					$customer->save();
					
					DB::table('tbldevicetoken')->insert(['customerId'=>$customer->id,'deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'loginStatus'=>1,'deviceDetails'=>$deviceDetails,'tokenDate'=>date('Y-m-d H:i:s')]);
			       
				    $common=new CommanController;
				    $CustomerDetails=$common->CustomerDetails($customer->id,$langId);
					$myarray['result']=$CustomerDetails;
					$myarray['status']=1;
					$myarray['message']=$common->get_msg("register_customer",$langId)?$common->get_msg("register_customer",$langId):'You have successfully registerd.';
			 	}
		  
		  	} else {
			  	$social=DB::table('tblcustomer')->where('socialMediaId', '=', $request->socialMediaId)->first();
		      	$userId=$social->id;
		       	if (!is_numeric($countryId) && $countryId!='') {
                	$countryName = DB::table('tblcountries')->where([['name','=',$countryId]])->first();
                	if ($countryName) {
                		$countryId=$countryName->id;
                		$customer->countryId=$countryId;
                	} else {
                		$countryName=$countryId;
                		$country=DB::table('tblcountries')->insertGetId(['name'=>$countryId,'status'=>1,'currency'=>'$']);
                		$countryId=$country;
                		$customer->countryId=$countryId;
                		$language = DB::table('language')->where([['status', '=','Active']])->get();
                		if ($language) {
							foreach ($language as $value) {
                				$insert=DB::table('tblcountrytranslation')->insert(['countryId'=>$countryId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$countryName]);
                			}
                		}
                	}
                } 

                if (!is_numeric($stateId) && $stateId!='') {
                	$stateName = DB::table('tblstates')->where([['name','=',$stateId]])->first();
                    if ($stateName) {
                		$stateId=$stateName->id;
                		$customer->stateId=$stateId;
                	} else {
                		$stateName=$stateId;
                		$state=DB::table('tblstates')->insertGetId(['name'=>$stateId,'country_id'=>$countryId]);
                    		$stateId=$state;
                    		$customer->stateId=$stateId;
                    		$language = DB::table('language')->where([['status', '=','Active']])->get();
	                	if ($language) {

	                		foreach ($language as $value) {
	                			$insert=DB::table('tblstatetranslation')->insert(['stateId'=>$stateId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$stateName]);
	                		}
	                	}
	            	}
	            } 

	            if (!is_numeric($cityId) && $cityId!='') {
	            	$cityName = DB::table('tblcities')->where([['name','=',$cityId]])->first();
	                if ($cityName) {
	                	$cityId=$cityName->id;
	                    $customer->cityId=$cityId;
	            	} else {
	                	$cityName=$cityId;
	                    $city=DB::table('tblcities')->insertGetId(['name'=>$cityId,'state_id'=>$stateId]);
	            		$cityId=$city;
	            		$customer->cityId=$cityId;
	            		$language = DB::table('language')->where([['status', '=','Active']])->get();
	                	if ($language) {
	                		foreach ($language as $value) {
	                			$insert=DB::table('tblcitytranslation')->insert(['cityId'=>$cityId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$cityName]);
	                		}
	                	}		
	            	}
	                    	
	            } 
				DB::table('tbldevicetoken')->insert(['customerId'=>$userId,'deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'loginStatus'=>1,'deviceDetails'=>$deviceDetails,'tokenDate'=>date('Y-m-d H:i:s')]);
			   	DB::table('tblcustomer')->where('id',$userId)->update(['deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'loginStatus'=>1,'deviceDetails'=>$deviceDetails,'socialMediaId'=>$request->socialMediaId,'loginType'=>$loginType,'createdDate'=>date('Y-m-d H:i:s'),'howdidyouknow'=>$howdidyouknow,'email'=>$request->email,'countryId'=>$countryId,'stateId'=>$stateId,'cityId'=>$cityId]);
			   	$common=new CommanController;
				$CustomerDetails=$common->CustomerDetails($userId,$langId);
				$myarray['result']=$CustomerDetails;	
				$myarray['status']=1;
				$myarray['message']=$common->get_msg("registration_detail",$langId)?$common->get_msg("registration_detail",$langId):'Your registration details.';
		  	}
		}
	 	return response()->json($myarray); 
	}
	
	/* Check Version */
	
	public function checkVersion(Request $request) {
	   
	   //echo "asdfsdf";
	  // exit;

	    $isUpdateAvailable = 0;
		$Url = '';
		$my_array=array();
		$msg='';
		$updateMessage='';
		$culture_code='';
	    $user_info=array();
	    $common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1; 

        $deviceType=($request->deviceType)?($request->deviceType):0;
		$deviceToken=($request->deviceToken)?($request->deviceToken):"";
		$deviceDetails=($request->deviceDetails)?($request->deviceDetails):"";	

	           if (!$request->deviceType) {
				   $myarray['result']=(object)array();					
				   $myarray['message']=$common->get_msg("blank_device_type",$langId)?$common->get_msg("blank_device_type",$langId):"Please Enter DeviceType.";
				   $myarray['status']=0;
				 
				 } elseif (!$request->appType) {
				   $myarray['result']=(object)array();					
				   $myarray['message']=$common->get_msg("blank_app_type",$langId)?$common->get_msg("blank_app_type",$langId):"Please Enter App Type.";
				   $myarray['status']=0;
				 
				 } elseif (!$request->version) {
				   $myarray['result']=(object)array();					
				   $myarray['message']=$common->get_msg("blank_app_version",$langId)?$common->get_msg("blank_app_version",$langId):"Please Enter App Version.";
				   $myarray['status']=0;
				 
				 } else {
			

					$url=url('/');
					$aboutusLink=$url."/about_us";
					$termsLink=$url."/terms";
				    $Version = DB::table('tblversion')->where([['app_type', '=', $request->deviceType],['app_name','=',$request->appType]])->first();
					$contactusemail=$common->getSettingValue('contact_us_email');
				    
				    if ($Version) {
					 	$culture_code = $Version->culture_code;
				        $isUpdateAvailable = intval($Version->is_update_available);
						$is_approved = intval($Version->is_approved);
					}
		            $GraterVersion= DB::table('tblversion')->where([['app_type', '=', $request->deviceType],['app_name','=',$request->appType],['app_version','>',$request->version]])->first();			
					
					$Terms= DB::table('tblcms')->where([['id', '=', 1]])->first();	
					
					$AppLink='';
					if ($request->deviceType && $request->deviceType==1) {
					  $AppLink=$common->getAndroidAppLink();
					}
					if ($request->deviceType && $request->deviceType==2) {
					  $AppLink=$common->getIosAppLink();
					}
					
					if ($GraterVersion) {
						$Url=$GraterVersion->app_url;
						$updateMessage=$common->get_msg("new_version_available",$langId)?$common->get_msg("new_version_available",$langId):'';
					}
					$totalUnreadCount=0;
					if (($request->userId) && $request->userId!=0 && $request->appType==1) {
						$suserId=0;

						   $user = Vendor::find($request->userId);
						   $user->loginStatus=1;
						   $user->lastLoginDate=date('Y-m-d H:i:s');
						   $user->deviceType=$deviceType;
						   $user->deviceDetails=$deviceDetails;
						   $user->deviceToken=$deviceToken;
						   $user->save();

					  $user_info=$common->VendorDetailsNew($request->userId,$suserId,$langId);

					       $leadCount=$common->leadUnreadMsgVendorListCount($request->userId);
					       $productCount=$common->productUnreadMsgListCount($request->userId);
					       $notificationCount=$common->NotificationCountVendor($request->userId);
					       $totalUnreadCount=$leadCount+$productCount+$notificationCount;
					}
					if (($request->userId) && $request->userId!=0 && $request->appType==2) {

						   $user = Customer::find($request->userId);
						   $user->loginStatus=1;
						   $user->lastLoginDate=date('Y-m-d H:i:s');
						   $user->deviceType=$deviceType;
						   $user->deviceDetails=$deviceDetails;
						   $user->deviceToken=$deviceToken;
						   $user->save();

						   $leadCount=$common->leadUnreadMsgCustomerListCount($request->userId);
					       $productCount=$common->productUnreadMsgListCount($request->userId);
					       $notificationCount=$common->NotificationCountCustomer($request->userId);
					       $totalUnreadCount=$leadCount+$productCount+$notificationCount;

					  $user_info=$common->CustomerDetails($request->userId,$langId);
					}
					$UnderConstContent = $common->get_msg("errorMsgShortMaintananceBreak",$langId) ? $common->get_msg("errorMsgShortMaintananceBreak",$langId) : '';
				    $termsTitle='';
					$termsDescription='';
					if ($Terms) {
					  $termsTitle=$Terms->name;
					  $termsDescription=$Terms->description;
					}
					
					$privacyLink="";
                    $faqLink="";
                    $contactusLink="";
                    $feedbackLink="";
					if ($request->appType==1) {
						$aboutusLink=$url."/about_us_vendor";
						$termsLink=$url."/terms_vendor";
						$privacyLink=$url."/privacy_policy_vendor";
						$faqLink=$url."/faqs_vendor";
						$contactusLink=$url."/contact_us_vendor";
						$feedbackLink=$url."/feedback_vendor";
					}

					if ($request->appType==2) {
						$aboutusLink=$url."/about_us_customer";
						$termsLink=$url."/terms_customer";
						$privacyLink=$url."/privacy_policy_customer";
						$faqLink=$url."/faqs_customer";
						$contactusLink=$url."/contact_us_customer";
						$feedbackLink=$url."/feedback_vendor";
					}
					

				$my_array = array("isUpdateAvailable"=>$isUpdateAvailable,"updateMessage"=>$updateMessage,"isApprovedApp"=>$is_approved,'maintenanceMsg'=>$UnderConstContent,"Url"=>$Url,"userInfo"=>(object)$user_info,"termsTitle"=>$termsTitle,"termsDescription"=>$termsDescription,"appLink"=>$AppLink,"contactUsEmail"=>$contactusemail,"aboutUsLink"=>$aboutusLink,"termsLink"=>$termsLink,"privacyLink"=>$privacyLink,"faqLink"=>$faqLink,"contactusLink"=>$contactusLink,"feedbackLink"=>$feedbackLink);
		    	$final_array = array("result"=>$my_array,"message"=>"",'status'=>1);					
			//$myarray['message']="";
			//$myarray['status']=1;
			return response()->json($final_array);
		}			
	}
	
	public function forgotpassword(Request $request){
	   
		$common=new CommanController;
		$langId=($request->header('langId'))?($request->header('langId')):1; 

		if (!$request->email) { 
		    $myarray['result']=(object)array();
			$myarray['message']=$common->get_msg("email_blank",$langId)?$common->get_msg("email_blank",$langId):"Please enter Email Address.";
			$myarray['status']=0;
		} else {
		   $vendorcount=Vendor::where('email',$request->email)->count();
		   if ($vendorcount>0) {
			   $vendor=Vendor::select('id','fname','lname')->where('email', $request->email)->first();
			    $userId = $vendor['id'];
				$name = $vendor['fname']." ".$vendor['lname'];
				$email = $request->email;
				$url=url('/');
				$rupw = base64_encode("rupw:".$userId);
			    $reseturl = $url."/resetpassword/$rupw";
				
				$objDemo = new \stdClass();
				$objDemo->name = $name;
				$objDemo->url = $reseturl;
			    Mail::to($request->email)->send(new VendorForgotPassword($objDemo));
				
				$forgotPasswordMsg = $common->get_msg("forgotPasswordMsg",$langId) ? $common->get_msg("forgotPasswordMsg",$langId) : 'Reset password link sent on your registered email';
				$myarray['result']=(object)array();
				$myarray['message']=$forgotPasswordMsg;
				$myarray['status']=1;
		   } else {
		        $forgotPasswordMsg = $common->get_msg("forgotPasswordUserNotFound",$langId) ? $common->get_msg("forgotPasswordUserNotFound",$langId) : 'User does not exist!';
				$myarray['result']=(object)array();
				$myarray['message']=$forgotPasswordMsg;
				$myarray['status']=1;
		   }
		}
		return response()->json($myarray);
	}		
	
	
	public function forgotpasswordCustomer(Request $request){
	   
		$common=new CommanController;
		$langId=($request->header('langId'))?($request->header('langId')):1; 

		if (!$request->email) { 
		    $myarray['result']=(object)array();
			$myarray['message']=$common->get_msg("email_blank",$langId)?$common->get_msg("email_blank",$langId):"Please enter Email Address.";
			$myarray['status']=0;
		} else {
		   $customercount=Customer::where('email',$request->email)->count();
		   if ($customercount>0) {
			   $customer=Customer::select('id','fname','lname')->where('email', $request->email)->first();
			    $userId = $customer['id'];
				$name = $customer['fname']." ".$customer['lname'];
				$email = $request->email;
				$url=url('/');
				$rupw = base64_encode("rupw:".$userId);
			    $reseturl = $url."/resetpasswordcustomer/$rupw";
				
				$objDemo = new \stdClass();
				$objDemo->name = $name;
				$objDemo->url = $reseturl;
				
			    Mail::to($request->email)->send(new CustomerForgotPassword($objDemo));
				$forgotPasswordMsg = $common->get_msg("forgotPasswordMsg",$langId) ? $common->get_msg("forgotPasswordMsg",$langId) : 'Reset password link sent on your registered email';
				$myarray['result']=(object)array();
				$myarray['message']=$forgotPasswordMsg;
				$myarray['status']=1;
		   } else {
		    $forgotPasswordMsg = $common->get_msg("forgotPasswordUserNotFound",$langId) ? $common->get_msg("forgotPasswordUserNotFound",$langId) : 'User does not exist!';
				$myarray['result']=(object)array();
				$myarray['message']=$forgotPasswordMsg;
				$myarray['status']=1;
		   }
		}
		return response()->json($myarray);
	}	
	
	/* addVendorAchievement */
	public function addVendorAchievement(Request $request) {
	   
	     $common=new CommanController;
	     $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select venderId";
		   $myarray['status']=0;
		 } else {
		   $count=DB::table('tblvender')->where([['id', '=', $request->venderId],['isActive', '=',1]])->count();
		   if ($count > 0) {
		   
		   
		   
		   
		   $name=($request->name)?($request->name):"";
		   $subject=($request->subject)?($request->subject):"";
		   $description=($request->description)?($request->description):"";

			   if (!$request->achievementId) { 
			   
				   $achievement=new Achievement;
				   $achievement->venderId=$request->venderId;
				   $achievement->name=$name;
				   $achievement->subject=$subject;
				   $achievement->description=$description;
				   $achievement->createdDate=date('Y-m-d H:i:s');
				   $achievement->save();
				 
				   if($request->hasFile('photo')) {
							   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
							   $files = $request->file('photo');
							    foreach($files as $file){
								 $filename = rand(1,1000000).time().$file->getClientOriginalName();
		                         $extension = strtolower($file->getClientOriginalExtension());
		                         $check=in_array($extension,$allowedfileExtension);
								 if($check) {
								   $destinationPath = 'achivementphoto';
		                           $file->move($destinationPath,$filename); 
								   $achivementphoto=new AchievementPhoto;
								   $achivementphoto->achievementId=$achievement->id;
								   $achivementphoto->photo=$filename;
								   $achivementphoto->createdDate=date('Y-m-d H:i:s');
								   $achivementphoto->save();
								 }
							   }
		                     }
			   
			   $myarray['result']=(object)array();					
			   $myarray['message']=$common->get_msg("added_achievment",$langId)?$common->get_msg("added_achievment",$langId):"You have achievment added successfully.";
			   $myarray['status']=1;
			    
			    } else {
                   
                   $achievement=Achievement::find($request->achievementId);
				   $achievement->venderId=$request->venderId;
				   $achievement->name=$name;
				   $achievement->subject=$subject;
				   $achievement->description=$description;
				   $achievement->createdDate=date('Y-m-d H:i:s');
				   $achievement->save();
				 
				   if($request->hasFile('photo')) {
							   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
							   $files = $request->file('photo');
							    foreach($files as $file){
								 $filename = rand(1,1000000).time().$file->getClientOriginalName();
		                         $extension = strtolower($file->getClientOriginalExtension());
		                         $check=in_array($extension,$allowedfileExtension);
								 if($check) {
								   $destinationPath = 'achivementphoto';
		                           $file->move($destinationPath,$filename); 
								   $achivementphoto=new AchievementPhoto;
								   $achivementphoto->achievementId=$request->achievementId;
								   $achivementphoto->photo=$filename;
								   $achivementphoto->createdDate=date('Y-m-d H:i:s');
								   $achivementphoto->save();
								 }
							   }
		                     }

		                     
		                 $myarray['result']=(object)array();					
			            $myarray['message']=$common->get_msg("update_achievement",$langId)?$common->get_msg("update_achievement",$langId):"Your achievement updated succesfully.";
			            $myarray['status']=1;     
			    
			    }


		   } else {
		     $myarray['result']=(object)array();					
		     $myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):"Invalid VendorId.";
		     $myarray['status']=1;
		   }
		 }			 
	   return response()->json($myarray); 
	}
	
	/* addVendorPortFolio */
	public function addVendorPortFolio(Request $request) {
	     $common=new CommanController;
	     $langId=($request->header('langId'))?($request->header('langId')):1; 
		 
		 if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select venderId";
		   $myarray['status']=0;
		 
		 } else {
		   $count=DB::table('tblvender')->where([['id', '=', $request->venderId],['isActive', '=',1]])->count();
		   
		   if ($count > 0) {
		   
		   
		   
			   $name=($request->name)?($request->name):"";
			   $subject=($request->subject)?($request->subject):"";
			   $description=($request->description)?($request->description):"";
		   
		      if (!$request->portfolioId) {

				   $portfolio=new PortFolio;
				   $portfolio->venderId=$request->venderId;
				   $portfolio->name=$name;
				   $portfolio->subject=$subject;
				   $portfolio->description=$description;
				   $portfolio->createdDate=date('Y-m-d H:i:s');
				   $portfolio->save();
			 
		           if($request->hasFile('file')) {
					   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','xls','xlsx','csv','heic'];
					   $files = $request->file('file');
					    foreach($files as $file){
						 $filename = rand(1,1000000).time().$file->getClientOriginalName();
                         $extension = strtolower($file->getClientOriginalExtension());
                         $check=in_array($extension,$allowedfileExtension);
						 if($check) {
						   $destinationPath = 'portfoliofiles';
                           $file->move($destinationPath,$filename); 
						   $portfoliofiles=new PortFolioFiles;
						   $portfoliofiles->portfolioId=$portfolio->id;
						   $portfoliofiles->file=$filename;
						   if (($extension=='pdf') ||  ($extension=='xlsx') || ($extension=='csv') || ($extension=='doc') || ($extension=='docx') || ($extension=='xls')) {
						     $portfoliofiles->type=2;
						   } else {
						     $portfoliofiles->type=1;
						   }
						   $portfoliofiles->createdDate=date('Y-m-d H:i:s');
						   $portfoliofiles->save();
						 }
					   }
                     }
		   
				   $myarray['result']=(object)array();					
				   $myarray['message']=$common->get_msg("added_portfolio",$langId)?$common->get_msg("added_portfolio",$langId):"You have portfolio added successfully.";
				   $myarray['status']=1;
	            
	            } else {

                   $portfolio=PortFolio::find($request->portfolioId);
				   $portfolio->venderId=$request->venderId;
				   $portfolio->name=$name;
				   $portfolio->subject=$subject;
				   $portfolio->description=$description;
				   $portfolio->createdDate=date('Y-m-d H:i:s');
				   $portfolio->save();
			 
		           if($request->hasFile('file')) {
					   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','xls','xlsx','csv','heic'];
					   $files = $request->file('file');
					    foreach($files as $file){
						 $filename = rand(1,1000000).time().$file->getClientOriginalName();
                         $extension = strtolower($file->getClientOriginalExtension());
                         $check=in_array($extension,$allowedfileExtension);
						 if($check) {
						   $destinationPath = 'portfoliofiles';
                           $file->move($destinationPath,$filename); 
						   $portfoliofiles=new PortFolioFiles;
						   $portfoliofiles->portfolioId=$request->portfolioId;
						   $portfoliofiles->file=$filename;
						   if (($extension=='pdf') ||  ($extension=='xlsx') || ($extension=='csv') || ($extension=='doc') || ($extension=='docx') || ($extension=='xls')) {
						     $portfoliofiles->type=2;
						   } else {
						     $portfoliofiles->type=1;
						   }
						   $portfoliofiles->createdDate=date('Y-m-d H:i:s');
						   $portfoliofiles->save();
						 }
					   }
                     }

	               $myarray['result']=(object)array();					
				   $myarray['message']=$common->get_msg("portfolio_updated",$langId)?$common->get_msg("portfolio_updated",$langId):"Your portfolio updated successfully.";
				   $myarray['status']=1;

	            }



		   } else {
			     $myarray['result']=(object)array();					
			     $myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):"Invalid VendorId.";
			     $myarray['status']=0;
		   }
		 }			 
	   return response()->json($myarray); 
	}
	
	public function favouriteVendorList(Request $request) {
	    $common=new CommanController;
		$langId=($request->header('langId'))?($request->header('langId')):1; 
		$vendor=array();			
		if (!$request->userId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
		   $myarray['status']=0;
		 
		 } else {
		   
		   
		   $i=0;
		   $data=DB::select( DB::raw("select custfav.venderId from tblcustomerfavourite as custfav INNER JOIN tblvender as vender ON custfav.venderId=vender.id where vender.isActive=1 and custfav.customerId='{$request->userId}' and custfav.isFavourite=1"));
		    $service=array();
		    $vendorList=array();
			if ($data) {
			  foreach ($data as $valuesvendor) {
			   $venderId=$valuesvendor->venderId;
			   
				   $customer=CustomerLocation::where('customerId',$request->userId)->orderBy('id', 'desc')->take(1)->first();
				   $customerlatitude=0;
				   $customerlongitude=0;
				   if ($customer) {
				   $customerlatitude=$customer->latitude;
				   $customerlongitude=$customer->longitude;
				   }
                

			   $vendordata=DB::select( DB::raw("Select tblvender.isVarified,tblvender.charity,tblvender.website,tblvender.id,tblvender.fname,tblvender.lname,tblvender.email,tblvender.aboutUs,tblvender.businessCategoryId,tblvenderbusiness.firmName,tblvenderbusiness.incorporationDate,tblvenderbusiness.location,tblvenderbusiness.latitude,tblvenderbusiness.longitude,tblvenderbusiness.pincode,tblvender.avgRate,tblvender.photo,tblvender.isOnline,111.045 * DEGREES(ACOS(COS(RADIANS($customerlatitude))
 * COS(RADIANS(tblvenderbusiness.latitude))
 * COS(RADIANS(tblvenderbusiness.longitude) - RADIANS($customerlongitude))
 + SIN(RADIANS($customerlatitude))
 * SIN(RADIANS(tblvenderbusiness.latitude))))
 AS distance_in_km from tblvender
 INNER JOIN tblvenderbusiness ON tblvender.id=tblvenderbusiness.venderId
 
 Where tblvender.id={$venderId} and tblvender.isActive=1  ORDER BY distance_in_km ASC"));
			   
			   if ($vendordata) {
			 
			 foreach ($vendordata as $values) {

						 $businessCategoryId=($values->businessCategoryId)?($values->businessCategoryId):0;
						 $BusinessCategory='';
						 if ($businessCategoryId!=0 && $businessCategoryId!='') {
						   $vendorBusinessCategory = DB::table('tblbusinesscategory')->where([['id', '=', $businessCategoryId]])->first();
						   //$BusinessCategory=$vendorBusinessCategory->name;
						   $BusinessCategory=$common->getBusinessCategoryValue($businessCategoryId,$langId);
						 }
			 
				       $userId=($request->userId)?($request->userId):0;
				       //$VendorId=($values->id)?($values->id):0;
				       $VendorId=$venderId;

						 $isFavorite=0;
			             if ($userId!=0 && $VendorId!=0) {
						  $vendorDataFavouriteCount = DB::table('tblcustomerfavourite')->where([['venderId', '=', $VendorId],['customerId', '=', $userId],['isFavourite','=',1]])->count();
							 if ($vendorDataFavouriteCount > 0) {
								$isFavorite=1; 
							 }
						 }
			 
			           $vendorphoto=($values->photo!='')?($values->photo):'';
			           $url=url('/');
					 if ($vendorphoto!='') {
					    $vendorphoto=$url."/vendorphoto/".$vendorphoto;
					 }

				    $ServiceList=array();
				     $SubService=array();
				    $VendorServiceType = DB::table('tblvenderservicetype')->where([['venderId', '=', $VendorId]])->get();
					 if ($VendorServiceType->count() > 0) {
					    foreach ($VendorServiceType as $ServiceType) {
						  $serviceTypeId=($ServiceType->serviceTypeId)?($ServiceType->serviceTypeId):"";
						  $businessCategoryId=($ServiceType->businessCategoryId)?($ServiceType->businessCategoryId):0;
						  if ($serviceTypeId!='') {
						         $VendorServiceName = DB::table('tblservicetype')->where([['id', '=', $serviceTypeId]])->first();
							    if ($VendorServiceName) {
								//$ServiceName=($VendorServiceName->name)?($VendorServiceName->name):"";
							    $ServiceName=$common->getServiceTypeValue($serviceTypeId,$langId);
							    $servicephoto=$VendorServiceName->photo;
							    if ($servicephoto!='') {
                                  $servicephoto=$url."/servicetypephoto/".$servicephoto;
							    }


							    $ServiceList[]=array("serviceTypeId"=>$ServiceType->serviceTypeId,"serviceName"=>$ServiceName,'servicePhoto'=>$servicephoto,"businessCategoryId"=>$businessCategoryId);
								}
							$VendorSubServiceType = DB::table('tblvenderservice')->where([['venderServiceTypeId', '=',$ServiceType->serviceTypeId],['isActive','=',1]])->get();
						      if ($VendorSubServiceType->count() > 0) {
							   $currency=$common->getCurrency();
								   foreach ($VendorSubServiceType as $SubServices) {
								$SubService[]=array("serviceTypeId"=>$ServiceType->serviceTypeId,"serviceName"=>$ServiceName,"id"=>$SubServices->id,"name"=>$SubServices->name,"price"=>$SubServices->price,"currency"=>$currency);
								   }
							   }
						   }
						}
					 }
			 
					 $vendorFname=($values->fname)?($values->fname):"";
					 $vendorLname=($values->lname)?($values->lname):"";
					 $vendorEmail=($values->email)?($values->email):"";
					 $vendoraboutUs=($values->aboutUs)?($values->aboutUs):"";
					 $firmName=($values->firmName)?($values->firmName):"";
					 $incorporationDate=($values->incorporationDate)?($values->incorporationDate):"";
					 $location=($values->location)?($values->location):"";
					 $latitude=($values->latitude)?($values->latitude):"";
					 $longitude=($values->longitude)?($values->longitude):"";
					 $pincode=($values->pincode)?($values->pincode):"";
					 $distance_in_km=($values->distance_in_km)?(number_format(($values->distance_in_km),2)):"";
					 $avgRate=($values->avgRate)?($values->avgRate):0;
					
					 $isOnline=($values->isOnline)?($values->isOnline):0;
					 $isVarified=($values->isVarified)?($values->isVarified):0;
			 
					   $verificationTitle='';
					    $verificationContent='';
						if ($isVarified==0) {
						  $verificationTitle=$common->get_msg('profile_verification_title');
						  $verificationContent=$common->get_msg('profile_verification_desc');  
						}
			 
					 
			        $website=($values->website)?($values->website):"";
			 
			 $charity=($values->charity)?($values->charity):"";
			 $distanceUnit=$common->get_msg("distance_unit_km",$langId)?$common->get_msg("distance_unit_km",$langId):"Km";

			 $vendorList[]=array('vendorId'=>$values->id,"vendorFirstName"=>$vendorFname,"vendorLastName"=>$vendorLname,"vendorEmail"=>$vendorEmail,"vendorAboutUs"=>$vendoraboutUs,"vendorBusinessCategoryId"=>$businessCategoryId,"vendorBusinessCategory"=>$BusinessCategory,"vendorFirmName"=>$firmName,"vendorIncorporationDate"=>$incorporationDate,"vendorLocation"=>$location,"vendorLatitude"=>$latitude,"vendorLongitude"=>$longitude,"vendorPincode"=>$pincode,"distanceInKms"=>$distance_in_km,"ratting"=>$avgRate,'vendorPhoto'=>$vendorphoto,"distanceUnit"=>$distanceUnit,"vendorWebsite"=>$website,"vendorCharity"=>$charity,"isFavorite"=>$isFavorite,"vendorService"=>$ServiceList,"vendorSubService"=>$SubService,"isVarified"=>$isVarified,"verificationTitle"=>$verificationTitle,"verificationContent"=>$verificationContent,"isOnline"=>$isOnline);
			// echo '<pre>'; print_r($vendorList);
			 //exit;
			 }
			 /* $myarray['result']=$service;					
		     $myarray['message']="Your Favourite Vendor List.";
		     $myarray['status']=1; */
		 
		  $i++;
		} 
			   
			   //$vendor[]=$common->VendorDetails($venderId);
			 
			  }
			  $myarray['result']=$vendorList;
              $myarray['serviceProviderCount']=$i;			  
		      $myarray['message']=$common->get_msg("favourite_list",$langId)?$common->get_msg("favourite_list",$langId):"Your Favourite Vendor List.";
		      $myarray['status']=1;
			} else {
			$myarray['result']=array();	
            $myarray['serviceProviderCount']=$i;			
		    $myarray['message']=$common->get_msg("no_favourite",$langId)?$common->get_msg("no_favourite",$langId):"No Favourite Vendors Found.";
		    $myarray['status']=1;
			}
		 }
       return response()->json($myarray);		 
	}
	
	/* add vendor proof */
	
	 public function addVendorProof(Request $request) {
	   
	      $common=new CommanController;
	      $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
		   $myarray['status']=0;
		 
		 } elseif (!$request->proofTypeId && !$request->proofTypeName) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_proof_type_id",$langId)?$common->get_msg("blank_proof_type_id",$langId):"Please select proofTypeId.";
		   $myarray['status']=0;
		 
		 } elseif (!$request->idproof) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("select_id_proof",$langId)?$common->get_msg("select_id_proof",$langId):"Please select Id Proof.";
		   $myarray['status']=0;
		 
		 } else {
		   //$checkvendorproof = DB::table('tblvendorproofid')->where([['vendorId', '=', $request->venderId],['proofId', '=', $request->proofTypeId]])->count();
		   
		     $url=url('/');
		     $proofTypeId=($request->proofTypeId)?($request->proofTypeId):0;
             $VendorId=$request->venderId;

		     if ($request->proofTypeId==0 && $request->proofTypeName!='') {
                $dataproof=DB::select( DB::raw("select * from tblidprooftype where name='{$request->proofTypeName}' and vendorId='{$VendorId}' LIMIT 1"));
                if (count($dataproof)==0) {
                	$proofTypeId=DB::table('tblidprooftype')->insertGetId(
               ['vendorId'=>$VendorId,'isActive'=>1,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$request->proofTypeName]);

                	$language = DB::table('language')->where([['status', '=','Active']])->get();
                	if ($language) {

                		foreach ($language as $value) {
                			$insert=DB::table('tblidprooftypetranslation')->insert(
               ['proofIdType'=>$proofTypeId,'langId'=>$value->id,'createdDate'=>date('Y-m-d H:i:s'),'name'=>$request->proofTypeName]);
                		}
                	}

                } else {
                	 foreach ($dataproof as $proofs) {
                        $proofTypeId=$proofs->id;
                	 }
                }	
            }

			 $id=DB::table('tblvendorproofid')->insertGetId(
               ['vendorId'=>$request->venderId,'proofId'=>$proofTypeId,'createdDate'=>date('Y-m-d H:i:s')]);
			 
			 $isUploaded=0;
			 $status=0;
			 if($request->hasFile('idproof')) {
				
					   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
					   $files = $request->file('idproof');
					    foreach($files as $file){
						 $rand=rand(10,1000);
                         $extension = strtolower($file->getClientOriginalExtension());
						 $filename = $rand.time().".".$extension;
                         $check=in_array($extension,$allowedfileExtension);
						 if($check) {
							 
						   $destinationPath = 'vendorproof';
                           $file->move($destinationPath,$filename); 
						   $isUploaded=1;
						   $vendorproof=new VendorProof;
						   $vendorproof->venderId=$request->venderId;
						   $vendorproof->photo=$filename;
						   $vendorproof->createdDate=date('Y-m-d H:i:s');
						   $vendorproof->proofId=$proofTypeId;
						   $vendorproof->proofTypeId=$id;
						   $vendorproof->save();
						 }
					   }
				 
				 $msg=$common->get_msg("valid_file",$langId)?$common->get_msg("valid_file",$langId):"Please upload valid file.";
				 if ($isUploaded==1) {
				 $msg=$common->get_msg("upload_id_proof",$langId)?$common->get_msg("upload_id_proof",$langId):"Your Id Proof has been successfully uploaded.";
				 $status=1;
                 }
			  } else {
			    $msg=$common->get_msg("select_id_proof",$langId)?$common->get_msg("select_id_proof",$langId):"Please select Id Proof.";
			  }
			 
             

            

			 
			 $dataproof=DB::select( DB::raw("select * from tblvendorproofid where vendorId=$VendorId group by proofId"));
			 $VendorProof=array();
			 
			 if ($dataproof) {
			    foreach ($dataproof as $proofs) {
					$VendorProofs = DB::table('tblvenderproof')->where([['venderId', '=', $VendorId],['proofId','=',$proofs->proofId]])->get();
					$VendorProofPhoto=array();
					$vendorProofName = DB::table('tblidprooftype')->where([['id', '=',$proofs->proofId],['isActive','=',1]])->first();
					$proofName='';
					if ($vendorProofName) {
					  //$proofName=$vendorProofName->name;
						$proofName=$common->getIdProofValue($proofs->proofId,$langId);
					}
					if ($VendorProofs) {
					   foreach($VendorProofs as $proof) {
					      $photo=$proof->photo;
						  $proofId=$proof->id;
						  if ($photo!='') {
						   $photo=$url."/vendorproof/".$photo;
						  }
						  $VendorProofPhoto[]=array("id"=>(int)$proofId,"proof"=>$photo);
					   }
					}
				$VendorProof[]=array("id"=>(int)$proofs->id,"proofTypeId"=>$proofs->proofId,"proofName"=>$proofName,"proofPhoto"=>$VendorProofPhoto);
			    }
			 }
			 
			 $myarray['result']=$VendorProof;					
		     $myarray['message']=$msg;
		     $myarray['status']=$status;
		   //}
		 }			 
	   return response()->json($myarray); 
	} 
	
	/* add vendor services */
	
	 public function addVendorServices(Request $request) {
	   
	     $common=new CommanController;
	     $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
		   $myarray['status']=0;
		 
		 } else {
			 
             $vendorServices=$common->vendorServices($request->venderId,$langId);
			 $myarray['result']=$vendorServices;					
		     $myarray['message']=$common->get_msg("service",$langId)?$common->get_msg("service",$langId):"Your Service List.";
		     $myarray['status']=1;

			 if($request->serviceTypeId) {
					   
			  $serviceTypeId = explode(",",$request->serviceTypeId);
				 foreach($serviceTypeId as $TypeId) {

				       $count=DB::table('tblvenderservicetype')->where([['venderId', '=', $request->venderId],['serviceTypeId', '=', $TypeId]])->count();	 
				       $serviceType = DB::table('tblservicetype')->where([['id', '=',$TypeId],['isActive','=',1]])->first();		
				
						 $serviceTypeName="";
						 if ($serviceType) {
						 	$serviceTypeName=$common->getServiceTypeValue($TypeId,$langId);
						 	//$serviceTypeName=($serviceType->name)?($serviceType->name):"";
						 }
                          
                          $businessCategoryId=($request->businessCategoryId)?($request->businessCategoryId):0;
							if ($count==0) {
							$id=DB::table('tblvenderservicetype')->insertGetId(
			               ['venderId'=>$request->venderId,'serviceTypeId'=>$TypeId,'serviceTypeName'=>$serviceTypeName,'createdDate'=>date('Y-m-d H:i:s'),'businessCategoryId'=>$businessCategoryId]);
							}	   
					   }

					     $vendorServices=$common->vendorServices($request->venderId,$langId);
						 $myarray['result']=$vendorServices;					
					     $myarray['message']=$common->get_msg("service",$langId)?$common->get_msg("service",$langId):"Your Service List.";
					     $myarray['status']=1;
              }

              if($request->vendorServiceTypeId) {
                    $vendorServiceTypeId=($request->vendorServiceTypeId)?($request->vendorServiceTypeId):0;
                   
                   $servicecount=DB::select( DB::raw("select * from tblvenderservicetype where serviceTypeId IN ($vendorServiceTypeId) and venderId=$request->venderId"));

                   if ($servicecount) {
                            
                            $delete=DB::delete("delete from tblvenderservicetype where serviceTypeId IN ($vendorServiceTypeId) and venderId=$request->venderId");
						 	$vendorServices=$common->vendorServices($request->venderId,$langId);
						 	$myarray['result']=$vendorServices;					
						    $myarray['message']=$common->get_msg("delete_services",$langId)?$common->get_msg("delete_services",$langId):"Your selected services has been deleted successfully.";
						    $myarray['status']=1;
		       
		             } else {

		                   $myarray['result']=array();					
					       $myarray['message']=$common->get_msg("invalid_service_type",$langId)?$common->get_msg("invalid_service_type",$langId):"Invalid Service Type.";
					       $myarray['status']=0;
			        }
              }
			 		 
			 
		   //}
		 }			 
	   return response()->json($myarray); 
	} 
	
	/* get Vendor Services */
	public function getVendorServices(Request $request) {
	   
	    $common=new CommanController;
	    $langId=($request->header('langId'))?($request->header('langId')):1; 

		if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
		   $myarray['status']=0;
		 
		 } else {			

		     $businessCategoryId=($request->businessCategoryId)?($request->businessCategoryId):0; 		 
			 $vendorServices=$common->vendorServices($request->venderId,$langId,$businessCategoryId);			
		     $msg=$common->get_msg("no_service",$langId)?$common->get_msg("no_service",$langId):"No Service List.";
			 if ($vendorServices) {
			 $msg=$common->get_msg("service",$langId)?$common->get_msg("service",$langId):"Your Service List.";
			 }
			 $myarray['result']=$vendorServices;					
		     $myarray['message']=$msg;
		     $myarray['status']=1;
		 }
		 return response()->json($myarray); 
	}
	
	/* add vendor services */
	
	 public function addVendorSubServices(Request $request) {
	   
	     $common=new CommanController;
         $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if (!$request->venderId) {
		    $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
		   $myarray['status']=0;
		 
		 } else {
			 
			 if($request->venderServiceTypeId) {
					   
			  $venderServiceTypeId = $request->venderServiceTypeId;
             $count=DB::table('tblvenderservicetype')->where([['venderId', '=', $request->venderId],['id', '=', $venderServiceTypeId]])->count();							
				
				

				if ($count==0) {
				 $myarray['result']=array();					
		         $myarray['message']=$common->get_msg("invalid_service_type",$langId)?$common->get_msg("invalid_service_type",$langId):"Invalid Service Type.";
		         $myarray['status']=0;
				} else {

					

					$serviceType = DB::table('tblvenderservicetype')->where([['id', '=',$venderServiceTypeId]])->first();		
			
					 $serviceTypeName="";
					 if ($serviceType) {
					 	$serviceTypeName=($serviceType->serviceTypeName)?($serviceType->serviceTypeName):"";
					 }

				  $id=DB::table('tblvenderservice')->insertGetId(
               ['venderServiceTypeId'=>$venderServiceTypeId,'name'=>$request->serviceName,'price'=>$request->servicePrice,'createdDate'=>date('Y-m-d H:i:s'),'isActive'=>1,'venderServiceTypeName'=>$serviceTypeName]);
				 
				 $sTypeId=0;
				 $vendorSubServices=$common->vendorSubServices($request->venderId,$sTypeId,$langId);
				 $myarray['result']=$vendorSubServices;					
				 $myarray['message']=$common->get_msg("sub_service",$langId)?$common->get_msg("sub_service",$langId):"Your Sub Service List.";
				 $myarray['status']=1;
				
				}
				
				
                }
			 
		   //}
		 }			 
	   return response()->json($myarray); 
	} 
	
	/* get Vendor Sub Services */
	public function getVendorSubServices(Request $request) {
	   $common=new CommanController;
       $langId=($request->header('langId'))?($request->header('langId')):1; 
	   
		if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select venderId";
		   $myarray['status']=0;
		 
		 } else {			
		     		 
			 $serviceTypeId=($request->serviceTypeId)?($request->serviceTypeId):0;
			 $vendorSubServices=$common->vendorSubServices($request->venderId,$serviceTypeId,$langId);			
		     $msg=$common->get_msg("no_sub_service",$langId)?$common->get_msg("no_sub_service",$langId):"No Sub Service List.";
			 if ($vendorSubServices) {
			 $msg=$common->get_msg("sub_service",$langId)?$common->get_msg("sub_service",$langId):"Your Sub Service List.";
			 }
			 $myarray['result']=$vendorSubServices;					
		     $myarray['message']=$msg;
		     $myarray['status']=1;
		 }
		 return response()->json($myarray); 
	}
	
	/* add vendor proof */
	
	 public function postLead(Request $request) {
	   
	   $common=new CommanController;
       $langId=($request->header('langId'))?($request->header('langId')):1; 
	   
		 if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select venderId";
		   $myarray['status']=0;
		 
		 } else if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0;
		 
		 } else {
		   $checkvendor = DB::table('tblvender')->where([['id', '=', $request->venderId],['isActive', '=', 1]])->count();
		   $checkcustomer = DB::table('tblcustomer')->where([['id', '=', $request->customerId],['isActive', '=', 1]])->count();
		     
			 
			if ($checkvendor==0) {
			 $myarray['result']=array();					
		     $myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):"Invalid VendorId";
		     $myarray['status']=0;
			} else if ($checkcustomer==0) {
			  $myarray['result']=array();					
		      $myarray['message']=$common->get_msg("invalid_customerId",$langId)?$common->get_msg("invalid_customerId",$langId):"Invalid CustomerId";
		      $myarray['status']=0;
			} else {
			
					 $venderId=($request->venderId)?($request->venderId):0;
					 $customerId=($request->customerId)?($request->customerId):0;
					 $locationId=($request->locationId)?($request->locationId):0;
					 $businessCategoryId=($request->businessCategoryId)?($request->businessCategoryId):0;
					 $serviceTypeId=($request->serviceTypeId)?($request->serviceTypeId):0;
					 $venderServiceTypeId=($request->venderServiceTypeId)?($request->venderServiceTypeId):0;
					 $leadDatetime=($request->leadDatetime)?(date("Y-m-d H:i:s",strtotime($request->leadDatetime))):"";
					 $timezone=($request->timezone)?($request->timezone):"";
					 $description=($request->description)?($request->description):"";
					 $location=($request->location)?($request->location):"";
					 $latitude=($request->latitude)?($request->latitude):"0.0";
					 $longitude=($request->longitude)?($request->longitude):"0.0";

					 if ($timezone!='' && $leadDatetime!='') {
					 	$leadDatetime=$common->ReverseConvertTimeZone($leadDatetime,$timezone);
					 }

					 $house_flatNo=($request->house_flatNo)?($request->house_flatNo):"";
					 $landmark=($request->landmark)?($request->landmark):"";
					 $title=($request->title)?($request->title):"";
			   
                if (!$request->leadId) {

					 if ($locationId==0) {
						 $checkcustomerlocation =(DB::select( DB::raw("select * from tblcustomerlocation 
		                 where customerId='{$customerId}' and longitude='{$longitude}' and latitude='{$latitude}' limit 1")));
					     $customercount=count($checkcustomerlocation);
						 if ($customercount==0) {
						$locationId=DB::table('tblcustomerlocation')->insertGetId(
		               ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
						 } else {
						   if ($checkcustomerlocation) {
						       foreach($checkcustomerlocation as $locationvals) {
							     $locationId=$locationvals->id;
								 $location=$locationvals->location;
								 $latitude=$locationvals->latitude;
								 $longitude=$locationvals->longitude;
								 $house_flatNo=$locationvals->house_flatNo;
								 $landmark=$locationvals->landmark;
								 $title=$locationvals->title;
							   }
						   }
						 }
					 } else {
                           $checkcustomerlocation =(DB::select( DB::raw("select * from tblcustomerlocation 
		                 where customerId='{$customerId}' and id='{$locationId}' limit 1")));
                           $customercount=count($checkcustomerlocation);
						 if ($customercount==0) {
						$locationId=DB::table('tblcustomerlocation')->insertGetId(
		               ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
						 } else {
						   if ($checkcustomerlocation) {
						       foreach($checkcustomerlocation as $locationvals) {
							     $locationId=$locationvals->id;
								 $location=$locationvals->location;
								 $latitude=$locationvals->latitude;
								 $longitude=$locationvals->longitude;
								 $house_flatNo=$locationvals->house_flatNo;
								 $landmark=$locationvals->landmark;
								 $title=$locationvals->title;
							   }
						   }
						 }
					 }
			  

		              $serviceTypeName="";
		              if ($serviceTypeId!=0) {
		                     $serviceType = DB::table('tblservicetype')->where([['id', '=',$serviceTypeId],['isActive','=',1]])->first();		
							 if ($serviceType) {
							 	$serviceTypeName=$common->getServiceTypeValue($serviceTypeId,$langId);
							 }
		              }

              
		              $venderServiceTypeName='';
		              if ($venderServiceTypeId!=0) {
		              	     $venderservice = DB::table('tblvenderservice')->where([['id', '=',$venderServiceTypeId],['isActive','=',1]])->first();		
		                     if ($venderservice) {
							 	$venderServiceTypeName=($venderservice->name)?($venderservice->name):"";
							 }
					   }

                      $leadNod=date("Ymd");
                      $leadNoH=date("His");
                      $leadNo=$leadNod.$customerId.$leadNoH;

					  $leadId=DB::table('tbllead')->insertGetId(
		               ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'locationId'=>$locationId,'businessCategoryId'=>$businessCategoryId,'serviceTypeId'=>$serviceTypeId,'venderServiceTypeId'=>$venderServiceTypeId,'leadDatetime'=>$leadDatetime,'timezone'=>$timezone,'description'=>$description,'status'=>1,'createdDate'=>date('Y-m-d H:i:s'),"serviceTypeName"=>$serviceTypeName,"venderServiceTypeName"=>$venderServiceTypeName,"leadNo"=>$leadNo,"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
					  

					  $leadVendor=DB::table('tblleadvender')->insertGetId(
		               ['leadId'=>$leadId,'venderId'=>$venderId,'isMain'=>1,'isApproved'=>0,'createdDate'=>date('Y-m-d H:i:s')]);
			  


					   $customer=CustomerLocation::where('customerId',$customerId)->orderBy('id', 'desc')->take(1)->first();
					   $customerlatitude=0;
					   $customerlongitude=0;
					   if ($customer) {
					   $customerlatitude=$customer->latitude;
					   $customerlongitude=$customer->longitude;
					   }
			  
			  
				   		$service=array();
				  //$service=$common->VendorDetails($venderId);
				        $currDate=date('Y-m-d');
				  
					   $mainvendordata=DB::select( DB::raw("Select tblvender.isVarified,tblvender.charity,tblvender.website,tblvender.id,tblvender.fname,tblvender.lname,tblvender.email,tblvender.aboutUs,tblvender.businessCategoryId,tblvenderbusiness.firmName,tblvenderbusiness.incorporationDate,tblvenderbusiness.location,tblvenderbusiness.latitude,tblvenderbusiness.longitude,tblvenderbusiness.pincode,tblvender.avgRate,tblvender.photo,111.045 * DEGREES(ACOS(COS(RADIANS($latitude))
		 * COS(RADIANS(tblvenderbusiness.latitude))
		 * COS(RADIANS(tblvenderbusiness.longitude) - RADIANS($longitude))
		 + SIN(RADIANS($latitude))
		 * SIN(RADIANS(tblvenderbusiness.latitude))))
		 AS distance_in_km,tblvender.deviceType,tblvender.deviceToken,tblvender.loginStatus,tblvender.isOnline from tblvender
		 INNER JOIN tblvenderbusiness ON tblvender.id=tblvenderbusiness.venderId
		 INNER JOIN tblvenderservicetype ON tblvender.id=tblvenderservicetype.venderId
		 INNER JOIN tblvendersubscription as subscription ON tblvender.id=subscription.venderId
		 INNER JOIN tblvenderpaymenttype as venderpaymenttype  ON tblvender.id=venderpaymenttype.venderId
         INNER JOIN tblvenderproof as venderproof ON tblvender.id=venderproof.venderId
         INNER JOIN tblvenderbusinesscategory as venderbusinesscategory ON tblvender.id=venderbusinesscategory.vendorId
		 Where tblvender.isActive=1 and subscription.status=1 and subscription.endDate >= '$currDate' and subscription.noOfRemainingLeads > 0 and tblvender.id='{$venderId}' and tblvenderbusiness.firmName!='' and tblvenderbusiness.location!='' and tblvender.isPhoneVerified=1 group by tblvender.id  LIMIT 1"));
				  
				  if ($mainvendordata) {
					  
				      foreach ($mainvendordata as $mainvalues) {
					     
						  $businessCategoryId=$mainvalues->businessCategoryId;
						 $BusinessCategory='';
						 if ($businessCategoryId!=0 && $businessCategoryId!='') {
						   $vendorBusinessCategory = DB::table('tblbusinesscategory')->where([['id', '=', $businessCategoryId]])->first();
						   $BusinessCategory=$common->getBusinessCategoryValue($businessCategoryId,$langId);
						 }
				 
				 
						 $newVendorId=$venderId;
						 $isFavorite=0;
						 if ($customerId!=0 && $newVendorId!=0) {
						  $vendorDataFavouriteCount = DB::table('tblcustomerfavourite')->where([['venderId', '=', $newVendorId],['customerId', '=', $customerId],['isFavourite','=',1]])->count();
							 if ($vendorDataFavouriteCount > 0) {
								$isFavorite=1; 
							 }
						 }
				 
						 $vendorFname=($mainvalues->fname)?($mainvalues->fname):"";
						 $vendorLname=($mainvalues->lname)?($mainvalues->lname):"";
						 $vendorEmail=($mainvalues->email)?($mainvalues->email):"";
						 $vendoraboutUs=($mainvalues->aboutUs)?($mainvalues->aboutUs):"";
						 $firmName=($mainvalues->firmName)?($mainvalues->firmName):"";
						 $incorporationDate=($mainvalues->incorporationDate)?($mainvalues->incorporationDate):"";
						 $location=($mainvalues->location)?($mainvalues->location):"";
						 $latitude=($mainvalues->latitude)?($mainvalues->latitude):"";
						 $longitude=($mainvalues->longitude)?($mainvalues->longitude):"";
						 $pincode=($mainvalues->pincode)?($mainvalues->pincode):"";
						 $distance_in_km=($mainvalues->distance_in_km)?(number_format(($mainvalues->distance_in_km),2)):"";
						 $avgRate=($mainvalues->avgRate)?($mainvalues->avgRate):0;
						 $vendorphoto=($mainvalues->photo!='')?($mainvalues->photo):'';
						 $deviceType=($mainvalues->deviceType)?($mainvalues->deviceType):0;
						 $deviceToken=($mainvalues->deviceToken)?($mainvalues->deviceToken):'';
						 $loginStatus=($mainvalues->loginStatus)?($mainvalues->loginStatus):0;
                         $isOnline=($mainvalues->isOnline)?($mainvalues->isOnline):0;
                         
                         
						 $url=url('/');
						 
						 if ($vendorphoto!='') {
							$vendorphoto=$url."/vendorphoto/".$vendorphoto;
						 }

						 $distanceUnit=$common->get_msg("distance_unit_km",$langId)?$common->get_msg("distance_unit_km",$langId):"Km";
						 
						 $service['mainserviceprovider'][]=array('vendorId'=>$newVendorId,"vendorFirstName"=>$vendorFname,"vendorLastName"=>$vendorLname,"vendorEmail"=>$vendorEmail,"vendorAboutUs"=>$vendoraboutUs,"vendorBusinessCategoryId"=>$businessCategoryId,"vendorBusinessCategory"=>$BusinessCategory,"vendorFirmName"=>$firmName,"vendorIncorporationDate"=>$incorporationDate,"vendorLocation"=>$location,"vendorLatitude"=>$latitude,"vendorLongitude"=>$longitude,"vendorPincode"=>$pincode,"distanceInKms"=>$distance_in_km,"ratting"=>$avgRate,'vendorPhoto'=>$vendorphoto,"distanceUnit"=>$distanceUnit,"isFavorite"=>$isFavorite,"isOnline"=>$isOnline);
                                  
                                    $cutomerName=$common->customerName($customerId);
						            $cutomerUrl=$common->customerProfilePic($customerId);
						            $vendorName=$common->vendorName($newVendorId);
						            $vendorUrl=$common->vendorProfilePic($newVendorId);

                                  $notificationmsg=$common->get_notification_msg("lead_request_vendor",$langId)?$common->get_notification_msg("lead_request_vendor",$langId):"";
                                    
                                    if ($notificationmsg!='') {
                                    	$Description=str_replace("#name",$cutomerName,$notificationmsg);
                                    } else {
                                    	$Description=$cutomerName." has requested to you for lead.";
                                    }
                                    
                                    $total_count = DB::table('tblnotification')->where([['notifiedUserId', '=', $newVendorId],['flag','=',0],['isCustomerNotification','=',0]])->count();
                  

                                           $NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>$customerId,'notifiedUserId'=>$newVendorId,'notificationType'=>3,'notification'=>$Description,'createdDate'=>date('Y-m-d H:i:s'),'SendBy'=>2,'leadId'=>$leadId,'isCustomerNotification'=>0]);
                                    
						                   if($deviceType==1) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>3,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);	
                                                }
						                    }

						                    if($deviceType==2) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>3,'message'=>$Description,'title'=>$Description,'NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);
                                                }
						                    }
					    
					  }

					  $latestPlan=DB::select( DB::raw("Select * from tblvendersubscription where venderId=".$newVendorId." and status=1 order by id desc LIMIT 1"));
                      
                       if (count($latestPlan) > 0)  {
                       	      $subId=isset($latestPlan[0]->id)?($latestPlan[0]->id):0;
                       	      $planStartDate=isset($latestPlan[0]->startDate)?($latestPlan[0]->startDate):"";
                       	      $planEndDate=isset($latestPlan[0]->endDate)?($latestPlan[0]->endDate):"";
                       	      $planNoOfRemainingLeads=isset($latestPlan[0]->noOfRemainingLeads)?($latestPlan[0]->noOfRemainingLeads):0;
                       	      $updateMainVendor=DB::select( DB::raw("update tblvendersubscription SET `noOfRemainingLeads`=`noOfRemainingLeads`-1 where id={$subId}"));
                       }
				  }
			  
					  $service['otherserviceprovider']=array();
					  $data=DB::select( DB::raw("Select tblvender.isVarified,tblvender.charity,tblvender.website,tblvender.id,tblvender.fname,tblvender.lname,tblvender.email,tblvender.aboutUs,tblvender.businessCategoryId,tblvenderbusiness.firmName,tblvenderbusiness.incorporationDate,tblvenderbusiness.location,tblvenderbusiness.latitude,tblvenderbusiness.longitude,tblvenderbusiness.pincode,tblvender.avgRate,tblvender.photo,111.045 * DEGREES(ACOS(COS(RADIANS($latitude))
		 * COS(RADIANS(tblvenderbusiness.latitude))
		 * COS(RADIANS(tblvenderbusiness.longitude) - RADIANS($longitude))
		 + SIN(RADIANS($latitude))
		 * SIN(RADIANS(tblvenderbusiness.latitude))))
		 AS distance_in_km,tblvender.deviceType,tblvender.deviceToken,tblvender.loginStatus,tblvender.isOnline from tblvender
		 INNER JOIN tblvenderbusiness ON tblvender.id=tblvenderbusiness.venderId
		 INNER JOIN tblvenderservicetype ON tblvender.id=tblvenderservicetype.venderId
		 INNER JOIN tblvendersubscription as subscription ON tblvender.id=subscription.venderId
		 INNER JOIN tblvenderpaymenttype as venderpaymenttype  ON tblvender.id=venderpaymenttype.venderId
         INNER JOIN tblvenderproof as venderproof ON tblvender.id=venderproof.venderId
         INNER JOIN tblvenderbusinesscategory as venderbusinesscategory ON tblvender.id=venderbusinesscategory.vendorId
		 Where tblvenderservicetype.serviceTypeId='{$serviceTypeId}' and tblvender.isActive=1 and subscription.status=1 and subscription.endDate >= '$currDate' and subscription.noOfRemainingLeads > 0 and tblvender.id!='{$venderId}' and tblvenderbusiness.firmName!='' and tblvenderbusiness.location!='' and tblvender.isPhoneVerified=1 group by tblvender.id ORDER BY distance_in_km ASC LIMIT 4"));
					  
					  $cont=0;
					  if ($data) {
					     foreach ($data as $values) {
							 $businessCategoryId=$values->businessCategoryId;
							 $BusinessCategory='';
							 if ($businessCategoryId!=0 && $businessCategoryId!='') {
							   $vendorBusinessCategory = DB::table('tblbusinesscategory')->where([['id', '=', $businessCategoryId]])->first();
							   $BusinessCategory=$common->getBusinessCategoryValue($businessCategoryId,$langId);
							 }
					 
					 
							 $newVendorId=($values->id)?($values->id):0;
							 $isFavorite=0;
							 if ($customerId!=0 && $newVendorId!=0) {
							  $vendorDataFavouriteCount = DB::table('tblcustomerfavourite')->where([['venderId', '=', $newVendorId],['customerId', '=', $customerId],['isFavourite','=',1]])->count();
								 if ($vendorDataFavouriteCount > 0) {
									$isFavorite=1; 
								 }
							 }
					 
							 $vendorFname=($values->fname)?($values->fname):"";
							 $vendorLname=($values->lname)?($values->lname):"";
							 $vendorEmail=($values->email)?($values->email):"";
							 $vendoraboutUs=($values->aboutUs)?($values->aboutUs):"";
							 $firmName=($values->firmName)?($values->firmName):"";
							 $incorporationDate=($values->incorporationDate)?($values->incorporationDate):"";
							 $location=($values->location)?($values->location):"";
							 $latitude=($values->latitude)?($values->latitude):"";
							 $longitude=($values->longitude)?($values->longitude):"";
							 $pincode=($values->pincode)?($values->pincode):"";
							 $distance_in_km=($values->distance_in_km)?(number_format(($values->distance_in_km),2)):"";
							 $avgRate=($values->avgRate)?($values->avgRate):0;
							 $vendorphoto=($values->photo!='')?($values->photo):'';

							 $deviceType=($values->deviceType)?($values->deviceType):0;
						     $deviceToken=($values->deviceToken)?($values->deviceToken):'';
						     $loginStatus=($values->loginStatus)?($values->loginStatus):0;
                             $isOnline=($values->isOnline)?($values->isOnline):0;
							 $url=url('/');
							 if ($vendorphoto!='') {
								$vendorphoto=$url."/vendorphoto/".$vendorphoto;
							 }
					 
							 
							 $leadVendor=DB::table('tblleadvender')->insertGetId(
		                     ['leadId'=>$leadId,'venderId'=>$newVendorId,'isMain'=>0,'isApproved'=>0,'createdDate'=>date('Y-m-d H:i:s')]);
					      $distanceUnit=$common->get_msg("distance_unit_km",$langId)?$common->get_msg("distance_unit_km",$langId):"Km";
						  $service['otherserviceprovider'][]=array('vendorId'=>$newVendorId,"vendorFirstName"=>$vendorFname,"vendorLastName"=>$vendorLname,"vendorEmail"=>$vendorEmail,"vendorAboutUs"=>$vendoraboutUs,"vendorBusinessCategoryId"=>$businessCategoryId,"vendorBusinessCategory"=>$BusinessCategory,"vendorFirmName"=>$firmName,"vendorIncorporationDate"=>$incorporationDate,"vendorLocation"=>$location,"vendorLatitude"=>$latitude,"vendorLongitude"=>$longitude,"vendorPincode"=>$pincode,"distanceInKms"=>$distance_in_km,"ratting"=>$avgRate,'vendorPhoto'=>$vendorphoto,"distanceUnit"=>$distanceUnit,"isFavorite"=>$isFavorite,"isOnline"=>$isOnline);

						            $cutomerName=$common->customerName($customerId);
						            $cutomerUrl=$common->customerProfilePic($customerId);
						            $vendorName=$common->vendorName($newVendorId);
						            $vendorUrl=$common->vendorProfilePic($newVendorId);

                                  $notificationmsg=$common->get_notification_msg("lead_request_vendor",$langId)?$common->get_notification_msg("lead_request_vendor",$langId):"";
                                    if ($notificationmsg!='') {
                                    	$Description=str_replace("#name",$cutomerName,$notificationmsg);
                                    } else {
                                    	$Description=$cutomerName." has requested to you for lead.";
                                    }
                                     
                                     $total_count = DB::table('tblnotification')->where([['notifiedUserId', '=', $newVendorId],['flag','=',0],['isCustomerNotification','=',0]])->count();
                  

                                           $NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>$customerId,'notifiedUserId'=>$newVendorId,'notificationType'=>3,'notification'=>$Description,'createdDate'=>date('Y-m-d H:i:s'),'SendBy'=>2,'leadId'=>$leadId,'isCustomerNotification'=>0]);
                                    
						                   if($deviceType==1) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>3,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);	
                                                }
						                    }

						                    if($deviceType==2) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>3,'message'=>$Description,'title'=>$Description,'NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);
                                                	/*$body['aps'] = array('alert' => $Description, 'sound' => 'default', 'badge' => $total_count, 'content-available' => 1,'NotificationType'=>3,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                	$common->iPhonePushBookVendor(array($deviceToken),$body);*/	
                                                }
						                    }

						                    $latestPlan=DB::select( DB::raw("Select * from tblvendersubscription where venderId=".$newVendorId." and status=1 order by id desc LIMIT 1"));
                      
					                       if (count($latestPlan) > 0)  {
					                       	      $subId=isset($latestPlan[0]->id)?($latestPlan[0]->id):0;
					                       	      $planStartDate=isset($latestPlan[0]->startDate)?($latestPlan[0]->startDate):"";
					                       	      $planEndDate=isset($latestPlan[0]->endDate)?($latestPlan[0]->endDate):"";
					                       	      $planNoOfRemainingLeads=isset($latestPlan[0]->noOfRemainingLeads)?($latestPlan[0]->noOfRemainingLeads):0;

					                       	      $updateOtherVendor=DB::select( DB::raw("update tblvendersubscription SET `noOfRemainingLeads`=`noOfRemainingLeads`-1 where id={$subId}"));

					                       }

						  $cont++;
						 }
					  }
			   
			        if($request->hasFile('files')) {
					   $allowedfileExtensionImage=['bmp','jpg','png','jpeg','gif','heic'];
					   $allowedfileExtensionDoc=['pdf','docx','doc','csv','xls','xlsx','ods','txt','ppt'];
					   $allowedfileExtensionVideo=['3gp','mp4','avi','ogg','wmv','flv','mov','m4v'];
					   
					   $files = $request->file('files');
					    foreach($files as $file){
						 $filename = rand(10,100000).time().$file->getClientOriginalName();
                         $extension = strtolower($file->getClientOriginalExtension());
						 $destinationPath = 'leadfiles';
                         $file->move($destinationPath,$filename); 
						 if (in_array($extension,$allowedfileExtensionImage)) {
						   
						   DB::table('tblleadfiles')->insert(
                          ['leadId'=>$leadId,'file'=>$filename,'type'=>1,'createdDate'=>date('Y-m-d H:i:s')]);
						 
						 } else if (in_array($extension,$allowedfileExtensionDoc)) {
						    DB::table('tblleadfiles')->insert(
                          ['leadId'=>$leadId,'file'=>$filename,'type'=>2,'createdDate'=>date('Y-m-d H:i:s')]);
						 } else if (in_array($extension,$allowedfileExtensionVideo)) {
						    DB::table('tblleadfiles')->insert(
                          ['leadId'=>$leadId,'file'=>$filename,'type'=>3,'createdDate'=>date('Y-m-d H:i:s')]);
						 } else {
						     DB::table('tblleadfiles')->insert(
                          ['leadId'=>$leadId,'file'=>$filename,'type'=>0,'createdDate'=>date('Y-m-d H:i:s')]);
						 }
					   }
                     }
			   
				    $msg=$common->get_msg("send_request_vendor",$langId)?$common->get_msg("send_request_vendor",$langId):"Your Request has been sent to vendor and service provider will contact you as soon as possible.";
				   if ($cont>0) {
				    $msg=$common->get_msg("send_request_vendor_other",$langId)?$common->get_msg("send_request_vendor_other",$langId):"Your Request has been sent to vendor and we have also recommended other vendors whichever provide this service and service provider will contact you as soon as possible.";
				   } 

			   $myarray['result']=$service;					
		       $myarray['message']=$msg;
		       $myarray['status']=1;

		       } else {
                     
                     $service=array();

	                 if ($locationId==0) {
					 $checkcustomerlocation =(DB::select( DB::raw("select * from tblcustomerlocation 
	                 where customerId='{$customerId}' and longitude='{$longitude}' and latitude='{$latitude}' limit 1")));
				     $customercount=count($checkcustomerlocation);
						 if ($customercount==0) {
						$locationId=DB::table('tblcustomerlocation')->insertGetId(
		               ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
						 } else {
						   if ($checkcustomerlocation) {
						       foreach($checkcustomerlocation as $locationvals) {
							     $locationId=$locationvals->id;
								 $location=$locationvals->location;
								 $latitude=$locationvals->latitude;
								 $longitude=$locationvals->longitude;
								 $house_flatNo=$locationvals->house_flatNo;
								 $landmark=$locationvals->landmark;
								 $title=$locationvals->title;
							   }
						   }
						 }
				    } else {
                           $checkcustomerlocation =(DB::select( DB::raw("select * from tblcustomerlocation 
		                 where customerId='{$customerId}' and id='{$locationId}' limit 1")));
                           $customercount=count($checkcustomerlocation);
						 if ($customercount==0) {
						$locationId=DB::table('tblcustomerlocation')->insertGetId(
		               ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
						 } else {
						   if ($checkcustomerlocation) {
						       foreach($checkcustomerlocation as $locationvals) {
							     $locationId=$locationvals->id;
								 $location=$locationvals->location;
								 $latitude=$locationvals->latitude;
								 $longitude=$locationvals->longitude;
								 $house_flatNo=$locationvals->house_flatNo;
								 $landmark=$locationvals->landmark;
								 $title=$locationvals->title;
							   }
						   }
						 }
					 }
			  

		              $serviceTypeName="";
		              if ($serviceTypeId!=0) {
		                     $serviceType = DB::table('tblservicetype')->where([['id', '=',$serviceTypeId],['isActive','=',1]])->first();		
							 if ($serviceType) {
							 	//$serviceTypeName=($serviceType->name)?($serviceType->name):"";
							 	$serviceTypeName=$common->getServiceTypeValue($serviceTypeId,$langId);
							 }
		              }

              
		              $venderServiceTypeName='';
		              if ($venderServiceTypeId!=0) {
		              	     $venderservice = DB::table('tblvenderservice')->where([['id', '=',$venderServiceTypeId],['isActive','=',1]])->first();		
		                     if ($venderservice) {
							 	$venderServiceTypeName=($serviceType->name)?($serviceType->name):"";
							 }
					   }

                         $leadId=($request->leadId)?($request->leadId):0;
					  $update=DB::table('tbllead')->where('id',$leadId)->update(
		               ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'locationId'=>$locationId,'businessCategoryId'=>$businessCategoryId,'serviceTypeId'=>$serviceTypeId,'venderServiceTypeId'=>$venderServiceTypeId,'leadDatetime'=>$leadDatetime,'timezone'=>$timezone,'description'=>$description,"serviceTypeName"=>$serviceTypeName,"venderServiceTypeName"=>$venderServiceTypeName,"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
			  

			  
					   $customer=CustomerLocation::where('customerId',$customerId)->orderBy('id', 'desc')->take(1)->first();
					   $customerlatitude=0;
					   $customerlongitude=0;
					   if ($customer) {
					   $customerlatitude=$customer->latitude;
					   $customerlongitude=$customer->longitude;
					   }
			  
			   
			        if($request->hasFile('files')) {
					   $allowedfileExtensionImage=['bmp','jpg','png','jpeg','gif','heic'];
					   $allowedfileExtensionDoc=['pdf','docx','doc','csv','xls','xlsx','ods','txt','ppt'];
					   $allowedfileExtensionVideo=['3gp','mp4','avi','ogg','wmv','flv'];
					   
					   $files = $request->file('files');
					    foreach($files as $file) {
						 $filename = rand(10,100000).time().$file->getClientOriginalName();
                         $extension = strtolower($file->getClientOriginalExtension());
						 $destinationPath = 'leadfiles';
                         $file->move($destinationPath,$filename); 
							 if (in_array($extension,$allowedfileExtensionImage)) {
							   
							   DB::table('tblleadfiles')->insert(
	                          ['leadId'=>$leadId,'file'=>$filename,'type'=>1,'createdDate'=>date('Y-m-d H:i:s')]);
							 
							 } else if (in_array($extension,$allowedfileExtensionDoc)) {
							    
							    DB::table('tblleadfiles')->insert(
	                          ['leadId'=>$leadId,'file'=>$filename,'type'=>2,'createdDate'=>date('Y-m-d H:i:s')]);
							 
							 } else if (in_array($extension,$allowedfileExtensionVideo)) {
							    DB::table('tblleadfiles')->insert(
	                          ['leadId'=>$leadId,'file'=>$filename,'type'=>3,'createdDate'=>date('Y-m-d H:i:s')]);
							 
							 } else {
							 
							     DB::table('tblleadfiles')->insert(
	                          ['leadId'=>$leadId,'file'=>$filename,'type'=>4,'createdDate'=>date('Y-m-d H:i:s')]);
							 
							 }
					    }
                     }

                     $msg=$common->get_msg("lead_update",$langId)?$common->get_msg("lead_update",$langId):"Your lead has been updated successfully.";
                     //$leadDetail=$common->getLeadDetail($request->leadId,$timezone);
                      $service['mainserviceprovider']=$common->mainVendorLead($leadId,$langId);
                      $service['otherserviceprovider']=$common->otherVendorLead($leadId,$langId);
                     $myarray['result']=$service;					
		             $myarray['message']=$msg;
		             $myarray['status']=1;

		       }
			  
			}				
		 }			 
	   return response()->json($myarray); 
	}


     

    public function addEditLocation(Request $request) {
	    $common=new CommanController;
	    $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0;
		 
		 } else {
			
			 $locationId=($request->locationId)?($request->locationId):0;
			 $customerId=($request->customerId)?($request->customerId):0;
			 $location=($request->location)?($request->location):"";
			 $latitude=($request->latitude)?($request->latitude):"0.0";
			 $longitude=($request->longitude)?($request->longitude):"0.0";
			 $house_flatNo=($request->house_flatNo)?($request->house_flatNo):"";
			 $landmark=($request->landmark)?($request->landmark):"";
			 $title=($request->title)?($request->title):"";
			 
			 $checkcustomer = DB::table('tblcustomer')->where([['id', '=', $customerId],['isActive', '=', 1]])->count();
			 
			 $newlongitude=$longitude;
			 $newlatitude=$latitude;
			 if ($longitude!='') {
			 	$longitudeex=explode(".",$longitude);
			 	$longitudeexln=strlen($longitudeex[1]);
			 	if ($longitudeexln==7) {
			 		$newlongitude=$longitudeex[1].'0';
			 		$newlongitude=$longitudeex[0].".".$newlongitude;
			 	}
			 }
			  if ($latitude!='') {
			 	$latitudeex=explode(".",$latitude);
			 	$latitudeexln=strlen($latitudeex[1]);
			 	if ($latitudeexln==7) {
			 		$newlatitude=$latitudeex[1].'0';
			 		$newlatitude=$latitudeex[0].".".$newlatitude;
			 	}
			 }
			 
            
            
			 $checkcustomerlocation =count(DB::select( DB::raw("select * from tblcustomerlocation 
where customerId='{$customerId}' and ((longitude='{$longitude}' and latitude='{$latitude}') OR (longitude='{$newlongitude}' and latitude='{$newlatitude}'))")));
			 
			 if($checkcustomer>0) {
				 
				 if ($checkcustomerlocation > 0) {
				   $location=$common->CustomerLocation($customerId);	 
				   $myarray['result']=$location;					
				   $myarray['message']=$common->get_msg("already_added_location",$langId)?$common->get_msg("already_added_location",$langId):"This location has been already added.";
				   $myarray['status']=0;
				 } elseif ($locationId==0) {
				   
				   DB::table('tblcustomerlocation')->insert(
							  ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
				   
				   $location=$common->CustomerLocation($customerId);
				   $myarray['result']=$location;					
				   $myarray['message']=$common->get_msg("added_location",$langId)?$common->get_msg("added_location",$langId):"Your location has been added successfully.";
				   $myarray['status']=1;
				 
				 } else {
				   
				   DB::table('tblcustomerlocation')->where('id',$locationId)->update(
				   ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]); 
				   
				   $location=$common->CustomerLocation($customerId);
				   $myarray['result']=$location;					
				   $myarray['message']=$common->get_msg("update_location",$langId)?$common->get_msg("update_location",$langId):"Your location has been updated successfully.";
				   $myarray['status']=1;
				 
				 }
			 } else {
			    
				$myarray['result']=array();					
		        $myarray['message']=$common->get_msg("invalid_customerId",$langId)?$common->get_msg("invalid_customerId",$langId):"Invalid CustomerId";
		        $myarray['status']=0;
				
			 }				 
		 
		 }
		 return response()->json($myarray); 
	}
    
     public function getCustomerLocation(Request $request) {
	     $common=new CommanController;
	     $langId=($request->header('langId'))?($request->header('langId')):1; 
		 
		 if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0;
		 
		 } else {
		   $customerId=($request->customerId)?($request->customerId):0; 
		   	 
		   $checkcustomer = DB::table('tblcustomer')->where([['id', '=', $customerId],['isActive', '=', 1]])->count();
		   if ($checkcustomer > 0) {
			   $location=$common->CustomerLocation($customerId);
			   $myarray['result']=$location;					
			   $myarray['message']=$common->get_msg("location_detail",$langId)?$common->get_msg("location_detail",$langId):"Your location details.";
			   $myarray['status']=1;
		   } else {
				$myarray['result']=array();					
				$myarray['message']=$common->get_msg("invalid_customerId",$langId)?$common->get_msg("invalid_customerId",$langId):"Invalid CustomerId";
				$myarray['status']=0;
		   }
		 }
		 return response()->json($myarray); 
	 }
     
     /* delete Achievement and Achievement Files */
     public function deleteAchievement(Request $request) {
     	$common=new CommanController;
	     $langId=($request->header('langId'))?($request->header('langId')):1; 
     	
        if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
		   $myarray['status']=0;
		 } elseif (!$request->achievementId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_achievementId",$langId)?$common->get_msg("blank_achievementId",$langId):"Please select achievement Id.";
		   $myarray['status']=0;
		 } else {
		 	
	      $checkach = DB::table('tblachievement')->where([['id', '=',$request->achievementId],['venderId', '=', $request->venderId]])->count();

            if ($checkach > 0) {
			 	
			 	if (!$request->fileId) {
	                $deleteachphoto=DB::delete("delete from tblachievementphoto where achievementId='{$request->achievementId}'");
	                $deleteach=DB::delete("delete from tblachievement where id='{$request->achievementId}' and venderId='{$request->venderId}'");
	                $myarray['result']=array();					
		            $myarray['message']=$common->get_msg("delete_achievement",$langId)?$common->get_msg("delete_achievement",$langId):"Your selected achievement deleted successfully.";
		            $myarray['status']=1;
			 	} else {
                    $deleteachphoto=DB::delete("delete from tblachievementphoto where id='{$request->fileId}'");
                    $myarray['result']=array();					
		            $myarray['message']=$common->get_msg("delete_achievement_file",$langId)?$common->get_msg("delete_achievement_file",$langId):"Your selected achievement file deleted successfully.";
		            $myarray['status']=1;
			 	}
			} else {
                  $myarray['result']=array();					
		         $myarray['message']=$common->get_msg("invalid_achievementId",$langId)?$common->get_msg("invalid_achievementId",$langId):"Invalid achievementId.";
		          $myarray['status']=0;
			} 	
		 }
         return response()->json($myarray);
     }

     /* delete PortFolio and PortFolio Files */
     public function deletePortFolio(Request $request) {
     	 $common=new CommanController;
	     $langId=($request->header('langId'))?($request->header('langId')):1; 
     	
        if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
		   $myarray['status']=0;
		 } elseif (!$request->portfolioId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_portfolioId",$langId)?$common->get_msg("blank_portfolioId",$langId):"Please select portfolio Id.";
		   $myarray['status']=0;
		 } else {
		 	
	      $checkport = DB::table('tblportfolio')->where([['id', '=',$request->portfolioId],['venderId', '=', $request->venderId]])->count();

            if ($checkport > 0) {
			 	
			 	if (!$request->fileId) {
	                 $deleteachphoto=DB::delete("delete from tblportfoliofiles where portfolioId='{$request->portfolioId}'");
	                 $deleteach=DB::delete("delete from tblportfolio where id='{$request->portfolioId}' and venderId='{$request->venderId}'");
	                $myarray['result']=array();					
		            $myarray['message']=$common->get_msg("delete_portfolio",$langId)?$common->get_msg("delete_portfolio",$langId):"Your selected portfolio deleted successfully.";
		            $myarray['status']=1;
                    
			 	} else {
                     $deleteachphoto=DB::delete("delete from tblportfoliofiles where id='{$request->fileId}'");
                    $myarray['result']=array();					
		            $myarray['message']=$common->get_msg("delete_portfolio_file",$langId)?$common->get_msg("delete_portfolio_file",$langId):"Your selected portfolio file deleted successfully.";
		            $myarray['status']=1;
			 	}
			} else {
                  $myarray['result']=array();					
		         $myarray['message']=$common->get_msg("invalid_portfolioId",$langId)?$common->get_msg("invalid_portfolioId",$langId):"Invalid portfolio Id.";
		          $myarray['status']=0;
			} 	
		 }
         return response()->json($myarray);
     }
	 
	 public function deleteVendorIdProof(Request $request) {
	 	  $common=new CommanController;
	 	  $langId=($request->header('langId'))?($request->header('langId')):1; 

	      if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
		   $myarray['status']=0;
		 } elseif (!$request->id) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_proof_id",$langId)?$common->get_msg("blank_proof_id",$langId):"Please enter proofId.";
		   $myarray['status']=0;
		 } elseif (!$request->proofTypeId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_proof_type_id",$langId)?$common->get_msg("blank_proof_type_id",$langId):"Please select proofTypeId.";
		   $myarray['status']=0;
		 } else {
		     
			 
			 $checkproof = DB::table('tblvenderproof')->where([['id', '=', $request->id],['venderId', '=', $request->venderId]])->count();
		    
			if ($checkproof==0) {
			 $myarray['result']=array();					
		     $myarray['message']=$common->get_msg("invalid_proof_id",$langId)?$common->get_msg("invalid_proof_id",$langId):"Invalid proofId.";
		     $myarray['status']=0;
			} else {
			  $url=url('/');
			  $delete=DB::delete('delete from tblvenderproof where id = ?',[$request->id]);
			  $checkproofcount = DB::table('tblvenderproof')->where([['proofId', '=', $request->proofTypeId],['venderId', '=', $request->venderId]])->count();
			  if ($checkproofcount==0) {
			  $deleteID=DB::delete("delete from tblvendorproofid where proofId='{$request->proofTypeId}' and vendorId='{$request->venderId}'");
			  }
			  
			  $VendorProof=array();
			  $VendorId=$request->venderId;
			  $dataproof=DB::select( DB::raw("select * from tblvendorproofid where vendorId=$VendorId group by proofId"));
			 if ($dataproof) {
			    foreach ($dataproof as $proofs) {
					$VendorProofs = DB::table('tblvenderproof')->where([['venderId', '=', $VendorId],['proofId','=',$proofs->proofId]])->get();
					$VendorProofPhoto=array();
					$vendorProofName = DB::table('tblidprooftype')->where([['id', '=',$proofs->proofId],['isActive','=',1]])->first();
					
					$proofName='';
					if ($vendorProofName) {
					  $proofName=$common->getIdProofValue($proofs->proofId,$langId);
					}

					if ($VendorProofs) {
					   foreach($VendorProofs as $proof) {
					      $photo=$proof->photo;
						  $proofId=$proof->id;
						  if ($photo!='') {
						   $photo=$url."/vendorproof/".$photo;
						  }
						  $VendorProofPhoto[]=array("id"=>(int)$proofId,"proof"=>$photo);
					   }
					}
				$VendorProof[]=array("id"=>(int)$proofs->id,"proofTypeId"=>$proofs->proofId,"proofName"=>$proofName,"proofPhoto"=>$VendorProofPhoto);
			    }
			 }
			 $myarray['result']=$VendorProof;					
		     $myarray['message']=$common->get_msg("delete_id_proof",$langId)?$common->get_msg("delete_id_proof",$langId):"Your Id Proof has been deleted successfully.";
		     $myarray['status']=1;
			}
		 }
		 return response()->json($myarray);
	 }
	 
	 public function deleteCustomerLocation(Request $request) {
	     $common=new CommanController;
         $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
		   $myarray['status']=0;
		 
		 } elseif (!$request->locationId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_location",$langId)?$common->get_msg("blank_location",$langId):"Please enter locationId.";
		   $myarray['status']=0;
		 
		 } else { 
           
		   $customerId=($request->customerId)?($request->customerId):0; 
           $locationId=($request->locationId)?($request->locationId):0; 	
		   $checkcustomer = DB::table('tblcustomer')->where([['id', '=', $customerId],['isActive', '=', 1]])->count();
		   $checklocation = DB::table('tblcustomerlocation')->where([['id', '=', $locationId],['customerId', '=', $customerId]])->count();
		   
			if ($checkcustomer==0) {
				$myarray['result']=array();					
				$myarray['message']=$common->get_msg("invalid_customerId",$langId)?$common->get_msg("invalid_customerId",$langId):"Invalid customerId.";
				$myarray['status']=0;
			} elseif ($checklocation==0) {
				$myarray['result']=array();					
				$myarray['message']=$common->get_msg("invalid_location",$langId)?$common->get_msg("invalid_location",$langId):"Invalid LocationId";
				$myarray['status']=0;
			} else {
			    $delete=DB::delete('delete from tblcustomerlocation where id = ?',[$locationId]);
			    $location=$common->CustomerLocation($customerId);
				$myarray['result']=$location;					
				$myarray['message']=$common->get_msg("delete_location",$langId)?$common->get_msg("delete_location",$langId):"Your selected location has deleted successfully.";
				$myarray['status']=1;
			}
		   
		 }
         return response()->json($myarray); 		 
	 }
	 
     /* Pending Booking */

     public function pendingBooking(Request $request) {
	      $common=new CommanController;	
		  $langId=($request->header('langId'))?($request->header('langId')):1; 

		  if (!$request->customerId) {
			    $myarray['result']=array();
                $myarray['leadCount']=0;
                $myarray['pageNo']=1;
                $myarray['totalRecordInPage']=0;				
			$myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
				$myarray['status']=0;
		  } else {
			
              $pageLimit = isset($request->count)?(int)($request->count):5;
	          $lastId = isset($request->lastId)?(int)($request->lastId):0;			
			  $pageNo = isset($request->page)?(int)($request->page):1;
			  $startDate = isset($request->startDate)?($request->startDate):'';
			  $endDate = isset($request->endDate)?($request->endDate):'';
              $timezone=($request->timezone)?($request->timezone):"";

			  if ($pageNo!=0) {
			  $start = ($pageNo - 1) * $pageLimit;
			  } else {
			  $start=1; 
			  }

			  if ($startDate!='' && $endDate!='') {
			  	$startDate=date("Y-m-d",strtotime($startDate));
			  	$endDate=date("Y-m-d",strtotime($endDate));
			  	$lead = DB::table('tbllead')->where('customerId', '=', $request->customerId)->where('status', '=', 1)->where(DB::raw("(DATE_FORMAT(leadDatetime,'%Y-%m-%d'))"),'>=',$startDate)->where(DB::raw("(DATE_FORMAT(leadDatetime,'%Y-%m-%d'))"),'<=',$endDate)->orderBy('id', 'desc')->skip($start)->take($pageLimit)->get();

			    $leadCount = DB::table('tbllead')->where('customerId', '=', $request->customerId)->where('status', '=', 1)->where(DB::raw("(DATE_FORMAT(leadDatetime,'%Y-%m-%d'))"),'>=',$startDate)->where(DB::raw("(DATE_FORMAT(leadDatetime,'%Y-%m-%d'))"),'<=',$endDate)->orderBy('id', 'desc')->orderBy('id', 'desc')->count();
			  } elseif ($startDate!='') {
			  	    $startDate=date("Y-m-d",strtotime($startDate));
                   $lead = DB::table('tbllead')->where('customerId', '=', $request->customerId)->where('status', '=', 1)->where(DB::raw("(DATE_FORMAT(leadDatetime,'%Y-%m-%d'))"),'>=',$startDate)->orderBy('id', 'desc')->skip($start)->take($pageLimit)->get();
                   $leadCount = DB::table('tbllead')->where('customerId', '=', $request->customerId)->where('status', '=', 1)->where(DB::raw("(DATE_FORMAT(leadDatetime,'%Y-%m-%d'))"),'>=',$startDate)->orderBy('id', 'desc')->count();
			  } elseif($endDate!='') {
			  	$endDate=date("Y-m-d",strtotime($endDate));
			  	 $lead = DB::table('tbllead')->where('customerId', '=', $request->customerId)->where('status', '=', 1)->where(DB::raw("(DATE_FORMAT(leadDatetime,'%Y-%m-%d'))"),'<=',$endDate)->orderBy('id', 'desc')->skip($start)->take($pageLimit)->get();
			    $leadCount = DB::table('tbllead')->where('customerId', '=', $request->customerId)->where('status', '=', 1)->where(DB::raw("(DATE_FORMAT(leadDatetime,'%Y-%m-%d'))"),'<=',$endDate)->orderBy('id', 'desc')->count();
			  } else {
			  	$lead = DB::table('tbllead')->where('customerId', '=', $request->customerId)->where('status', '=', 1)->orderBy('id', 'desc')->skip($start)->take($pageLimit)->get();
			  	$leadCount = DB::table('tbllead')->where([['customerId', '=', $request->customerId],['status','=','1']])->orderBy('id', 'desc')->count();
			  }
			  
			 
			 /*$lead = DB::table('tbllead')->where([['customerId', '=', $request->customerId],['status','=','1']])->orderBy('id', 'desc')->skip($start)->take($pageLimit)->get();*/
			 /* if ($lastId==0) { 
		     $lead = DB::table('tbllead')->where([['customerId', '=', $request->customerId],['status','=','1']])->orderBy('id', 'desc')->paginate($pageLimit);
		     } else {
			 $lead = DB::table('tbllead')->where([['customerId', '=', $request->customerId],['status','=','1'],['id','<',$lastId]])->orderBy('id', 'desc')->skip(5)->take(50)->get();
			 } */
			 
			 
			 $totalRecordInPage=$lead->count();
			 if ($lead->count() > 0) {
			    
				foreach($lead as $values) {
				  $leadId=($values->id)?($values->id):0;
				  $businessCategoryId=($values->businessCategoryId)?($values->businessCategoryId):0;
				  $serviceTypeId=($values->serviceTypeId)?($values->serviceTypeId):0;
				  $venderServiceTypeId=($values->venderServiceTypeId)?($values->venderServiceTypeId):0;
				  $leadDatetime=($values->leadDatetime)?($values->leadDatetime):"";
				  $description=($values->description)?($values->description):"";
				  $avgRate=($values->avgRate)?($values->avgRate):0;
                  $leadCreatedDatetime=($values->createdDate)?($values->createdDate):"";

				  $businessCategoryName='';
				  $serviceName='';
				  $subServiceName='';
				  
				  if ($timezone!='' && $leadDatetime!='') {
			 	  $leadDatetime=$common->NewConvertDate($leadDatetime,$timezone);
			      }

			      if ($timezone!='' && $leadCreatedDatetime!='') {
			 	  $leadCreatedDatetime=$common->NewConvertDate($leadCreatedDatetime,$timezone);
			      }

				  if ($businessCategoryId!=0) {
				  //$businessCategoryName=$common->businessCategoryName($businessCategoryId);
				  $businessCategoryName=$common->getBusinessCategoryValue($businessCategoryId,$langId);
				  }
				  if ($serviceTypeId!=0) {
				  //$serviceName=$common->serviceName($serviceTypeId);
				  $serviceName=$common->getServiceTypeValue($serviceTypeId,$langId);
				      if ($serviceName=='') {
				      	$serviceName=($values->serviceTypeName)?($values->serviceTypeName):'';
				      }
				  }
				  
				  if ($venderServiceTypeId!=0) {
						  $subServiceName=$common->subServiceName($venderServiceTypeId);
				  }
				  
				  $otherleadcount = DB::table('tblleadvender')->where([['leadId', '=', $leadId],['isMain','=',0]])->count();
				  $mainVendorLead=$common->mainVendorLead($leadId,$langId);
				  $otherVendorLead=$common->otherVendorLead($leadId,$langId);
				  $leads[]=array("leadId"=>$leadId,"businessCategoryId"=>$businessCategoryId,"businessCategoryName"=>$businessCategoryName,"serviceTypeId"=>$serviceTypeId,"serviceName"=>$serviceName,"leadDatetime"=>$leadDatetime,"description"=>$description,"avgRate"=>$avgRate,"leadCreatedDatetime"=>$leadCreatedDatetime,"otherLeadCount"=>$otherleadcount,"venderServiceTypeId"=>$venderServiceTypeId,"vendorsubServiceName"=>$subServiceName,"mainVendorLead"=>$mainVendorLead,"otherVendorLead"=>$otherVendorLead);
				}
				
				$myarray['result']=$leads;
                $myarray['leadCount']=$leadCount;
                $myarray['pageNo']=$pageNo;
                $myarray['totalRecordInPage']=$totalRecordInPage;				
				$myarray['message']=$common->get_msg("pending_booking",$langId)?$common->get_msg("pending_booking",$langId):"Your Pending Booking List.";
				$myarray['status']=1;
			 } else {
			    $myarray['result']=array();
                $myarray['leadCount']=0;
                $myarray['pageNo']=$pageNo;
                $myarray['totalRecordInPage']=$totalRecordInPage;				
				$myarray['message']=$common->get_msg("no_pending_booking",$langId)?$common->get_msg("no_pending_booking",$langId):"No Pending Booking Found in Your Account.";
				$myarray['status']=1;
			 }
		  }	
          return response()->json($myarray);		  
	 }		 
	 
	 /* awarded Booking */
	 
	 public function awardedBooking(Request $request) {
	      $common=new CommanController;
	      $langId=($request->header('langId'))?($request->header('langId')):1; 

		  if (!$request->customerId) {
			    $myarray['result']=array();
                $myarray['leadCount']=0;
                $myarray['pageNo']=1;
                $myarray['totalRecordInPage']=0;				
			    $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
				$myarray['status']=0;
		  } else {
			  
			  $pageLimit = isset($request->count)?(int)($request->count):5;
	          $lastId = isset($request->lastId)?(int)($request->lastId):0;
	          $startDate = isset($request->startDate)?($request->startDate):'';
			  $endDate = isset($request->endDate)?($request->endDate):'';
			  $timezone=($request->timezone)?($request->timezone):"";	
			  /* $cond="";
			  if ($lastId > 0) {
			    $cond=" and lead.id < $lastId ";
			  } */
			  
			  $pageNo = isset($request->page)?(int)($request->page):1;
              if ($pageNo!=0) {
			  $start = ($pageNo - 1) * $pageLimit;
			  } else {
			  $start=1; 
			  }
			  
			    $where="";
				if ($startDate!='' && $endDate!='') {
					$startDate=date("Y-m-d",strtotime($startDate));
					$endDate=date("Y-m-d",strtotime($endDate));
				    $where=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) >= '{$startDate}' and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) <= '{$endDate}'";
				} elseif ($startDate!='') {
					$startDate=date("Y-m-d",strtotime($startDate));
					$where=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) >= '{$startDate}'";
				} elseif($endDate!='') {
				    $endDate=date("Y-m-d",strtotime($endDate));
					$where=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) <= '{$endDate}'";
				} else {
				    $where="";
				}
              

			  $totalRecordInPage=0;
			  $leadCount =count(DB::select( DB::raw("select lead.*,leadvendor.venderId from tbllead as lead
inner join tblleadvender as leadvendor on lead.id=leadvendor.leadId
where leadvendor.isApproved=1 and lead.customerId='{$request->customerId}' and lead.status!=3 $where  order by lead.id desc")));
			  $lead=DB::select( DB::raw("select lead.*,leadvendor.venderId from tbllead as lead
inner join tblleadvender as leadvendor on lead.id=leadvendor.leadId
where leadvendor.isApproved=1 and lead.customerId='{$request->customerId}' and lead.status!=3 $where  order by lead.id desc LIMIT $start,$pageLimit"));
                 
				 if ($lead) {
				     
					 foreach($lead as $values) {
					      $leadId=($values->id)?($values->id):0;
						  $venderId=($values->venderId)?($values->venderId):0;
						  $businessCategoryId=($values->businessCategoryId)?($values->businessCategoryId):0;
						  $serviceTypeId=($values->serviceTypeId)?($values->serviceTypeId):0;
						  $venderServiceTypeId=($values->venderServiceTypeId)?($values->venderServiceTypeId):0;
						  $leadDatetime=($values->leadDatetime)?($values->leadDatetime):"";
						  $description=($values->description)?($values->description):"";
						  $leadstatus=($values->status)?($values->status):1;
						  $avgRate=($values->avgRate)?($values->avgRate):0;
						  $businessCategoryName='';
						  $serviceName='';
						  $subServiceName='';
						  if ($businessCategoryId!=0) {
						  //$businessCategoryName=$common->businessCategoryName($businessCategoryId);
						  $businessCategoryName=$common->getBusinessCategoryValue($businessCategoryId,$langId);
						  }
						  if ($serviceTypeId!=0) {
						  //$serviceName=$common->serviceName($serviceTypeId);
						  $serviceName=$common->getServiceTypeValue($serviceTypeId,$langId);
						     if ($serviceName=='') {
				      	      $serviceName=($values->serviceTypeName)?($values->serviceTypeName):'';
				              }
						  }
						  
						  if ($venderServiceTypeId!=0) {
						  $subServiceName=$common->subServiceName($venderServiceTypeId);
						  }

						  if ($timezone!='' && $leadDatetime!='') {
			 	          $leadDatetime=$common->NewConvertDate($leadDatetime,$timezone);
			                }
						  
						  if ($leadstatus==1) {
						   $leadStatusName=$common->get_msg("lead_status_pending",$langId)?$common->get_msg("lead_status_pending",$langId):'Pending';
						  } elseif ($leadstatus==2) {
						   $leadStatusName=$common->get_msg("lead_status_inprogress",$langId)?$common->get_msg("lead_status_inprogress",$langId):'In Progress';
						  } elseif ($leadstatus==3) {
						   $leadStatusName=$common->get_msg("lead_cancel",$langId)?$common->get_msg("lead_cancel",$langId):'Cancel';
						  } elseif ($leadstatus==4) {
						   $leadStatusName=$common->get_msg("lead_status_completed",$langId)?$common->get_msg("lead_status_completed",$langId):'Completed';
						  } else {
							  $leadStatusName='';
						  }
						  
						  $awardedVendor='';
						  if ($leadId!=0 && $venderId!=0) {
						     $awardedVendor=$common->awardedVendorLead($leadId,$venderId,$langId);
						  }
						  $leadComments='';
						  if ($leadId!=0) {
						  $leadComments=$common->leadComment($leadId);
						  }
						  $leads[]=array("leadId"=>$leadId,"businessCategoryId"=>$businessCategoryId,"businessCategoryName"=>$businessCategoryName,"serviceTypeId"=>$serviceTypeId,"serviceName"=>$serviceName,"leadDatetime"=>$leadDatetime,"description"=>$description,"leadStatus"=>$leadstatus,"leadStatusName"=>$leadStatusName,"awardedVendor"=>$awardedVendor,"venderServiceTypeId"=>$venderServiceTypeId,"vendorsubServiceName"=>$subServiceName,"leadComments"=>$leadComments,"avgRate"=>$avgRate); 
					   $totalRecordInPage++;
					 }
					 
					 $myarray['result']=$leads;
					 $myarray['pageNo']=$pageNo;
                     $myarray['totalRecordInPage']=$totalRecordInPage;
					 $myarray['leadCount']=$leadCount;				 
					 $myarray['message']=$common->get_msg("awarded_list",$langId)?$common->get_msg("awarded_list",$langId):"Your Awarded Booking List.";
					 $myarray['status']=1;
					 
				 } else {
				    $myarray['result']=array();
					$myarray['pageNo']=$pageNo;
                    $myarray['totalRecordInPage']=$totalRecordInPage;
					$myarray['leadCount']=$leadCount;					
					$myarray['message']=$common->get_msg("no_awarded",$langId)?$common->get_msg("no_awarded",$langId):"No Awarded Booking Found in Your Account.";
					$myarray['status']=1;
				 }

		  }
	    return response()->json($myarray);
	 }
	
	 /* Lead Detail */
	 
	 public function leadDetail(Request $request) {
		 $common=new CommanController;
		 $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if (!$request->leadId) {
			    $myarray['result']=array();
				$myarray['message']=$common->get_msg("blank_leadId",$langId)?$common->get_msg("blank_leadId",$langId):"Please select leadId.";
				$myarray['status']=0;
		  } else {
		 
	        $timezone=($request->timezone)?($request->timezone):"";

	        $leadDetail=$common->getLeadDetail($request->leadId,$timezone,$langId);
			if ($leadDetail) {
				     
					/* foreach($lead as $values) {
					      
					      $leadId=($values->id)?($values->id):0;
						  $venderId=($values->venderId)?($values->venderId):0;
						  $businessCategoryId=($values->businessCategoryId)?($values->businessCategoryId):0;
						  $serviceTypeId=($values->serviceTypeId)?($values->serviceTypeId):0;
						  $venderServiceTypeId=($values->venderServiceTypeId)?($values->venderServiceTypeId):0;
						  $leadDatetime=($values->leadDatetime)?($values->leadDatetime):"";
						  $description=($values->description)?($values->description):"";
						  $leadstatus=($values->status)?($values->status):1;
						  $isApproved=($values->isApproved)?($values->isApproved):0;
						  $location=($values->location)?($values->location):"";
						  $latitude=($values->latitude)?($values->latitude):"";
						  $longitude=($values->longitude)?($values->longitude):"";
						  
						  $businessCategoryName='';
						  $serviceName='';
						  $subServiceName='';
						  if ($businessCategoryId!=0) {
						  $businessCategoryName=$common->businessCategoryName($businessCategoryId);
						  }
						  if ($serviceTypeId!=0) {
						  $serviceName=$common->serviceName($serviceTypeId);
						  }
						  
						  if ($venderServiceTypeId!=0) {
						  $subServiceName=$common->subServiceName($venderServiceTypeId);
						  }
						  
						  if ($leadstatus==1) {
						   $leadStatusName='Pending';
						  } elseif ($leadstatus==2) {
						   $leadStatusName='In Progress';
						  } elseif ($leadstatus==3) {
						   $leadStatusName='Cancel';
						  } elseif ($leadstatus==4) {
						   $leadStatusName='Completed';
						  } else {
							  $leadStatusName='';
						  }
						  
						  $awardedVendor='';
						  if ($leadId!=0 && $venderId!=0 && $isApproved==1) {
						     $awardedVendor=$common->awardedVendorLead($leadId,$venderId);
						  }
						  $leadComments='';
						  if ($leadId!=0) {
						  $leadComments=$common->leadComment($leadId);
						  }
						  $leadFiles='';
						  if ($leadId!=0) {
						  $leadFiles=$common->leadFiles($leadId);
						  }
						  
						  $leadRate='';
						  if ($leadId!=0) {
						  $leadRate=$common->leadRate($leadId);
						  }
						  $avgRate=($values->avgRate)?($values->avgRate):0;

                          $otherVendorLead=$common->otherVendorLead($leadId);

						 $leads=array("leadId"=>$leadId,"businessCategoryId"=>$businessCategoryId,"businessCategoryName"=>$businessCategoryName,"serviceTypeId"=>$serviceTypeId,"serviceName"=>$serviceName,"leadDatetime"=>$leadDatetime,"description"=>$description,"leadStatus"=>$leadstatus,"leadStatusName"=>$leadStatusName,"awardedVendor"=>$awardedVendor,"venderServiceTypeId"=>$venderServiceTypeId,"vendorsubServiceName"=>$subServiceName,"leadComments"=>$leadComments,"leadFiles"=>$leadFiles,"leadRate"=>$leadRate,"avgRate"=>$avgRate,"location"=>$location,"latitude"=>$latitude,"longitude"=>$longitude,"otherVendorLead"=>$otherVendorLead);  
			         }*/
					 
					 $myarray['result']=$leadDetail;
				     $myarray['message']=$common->get_msg("lead_detail",$langId)?$common->get_msg("lead_detail",$langId):"Lead Detail.";
				     $myarray['status']=1;
				 
			} else {
				    $myarray['result']=(object)array();
					$myarray['message']=$common->get_msg("no_lead_detail",$langId)?$common->get_msg("no_lead_detail",$langId):"No Lead Detail Found for this leadId.";
					$myarray['status']=1;
			}				
		}
		return response()->json($myarray);
	 }

     public function assignLeadtoVendor(Request $request) {
	     
	     $common=new CommanController;
	     $langId=($request->header('langId'))?($request->header('langId')):1; 

		  if (!$request->leadId) {
			    $myarray['result']=(object)array();
				$myarray['message']=$common->get_msg("blank_leadId",$langId)?$common->get_msg("blank_leadId",$langId):"Please select leadId.";
				$myarray['status']=0;
		  } else if (!$request->customerId) {
			    $myarray['result']=(object)array();
			    $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
				$myarray['status']=0;
		  } else if (!$request->venderId) {
			    $myarray['result']=(object)array();
				$myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
				$myarray['status']=0;
		  } else {
		      $leadCount = DB::table('tbllead')->where([['customerId', '=', $request->customerId],['status','=','1'],['id', '=', $request->leadId]])->count();
		      $leadvendorCount = DB::table('tblleadvender')->where([['venderId', '=', $request->venderId],['leadId', '=', $request->leadId]])->count(); 
			  $vendorData = DB::table('tblvender')->where([['id', '=', $request->venderId]])->first();
			  $timezone=($request->timezone)?($request->timezone):"";

			   $isValidVendor=0;
			   if ($vendorData) {
			   	$isValidVendor=1;
			   } 
			   
			   if ($leadCount==0) {
			     $myarray['result']=(object)array();
				 $myarray['message']=$common->get_msg("invalid_lead_customer",$langId)?$common->get_msg("invalid_lead_customer",$langId):"Invalid leadId or customerId.";
				 $myarray['status']=0;
			   } else if ($leadvendorCount==0) {
			     $myarray['result']=(object)array();
				 $myarray['message']=$common->get_msg("invalid_lead_vendor",$langId)?$common->get_msg("invalid_lead_vendor",$langId):"Invalid leadId or venderId.";
				 $myarray['status']=0;
			   } else if ($isValidVendor==0) {
			     $myarray['result']=(object)array();
				 $myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):"Invalid venderId.";
				 $myarray['status']=0;	 
			   } else {
			     DB::table('tblleadvender')->where([['venderId', '=', $request->venderId],['leadId', '=', $request->leadId]])->update(
				   ['isApproved'=>1,'assignDate'=>date('Y-m-d H:i:s')]);
				  DB::table('tbllead')->where([['customerId', '=', $request->customerId],['id', '=', $request->leadId]])->update(
				   ['status'=>2]);
                  
                  $vendorfname=($vendorData->fname)?($vendorData->fname):'';
			      $vendorlname=($vendorData->lname!='')?($vendorData->lname):'';
			      $vnname=$vendorfname." ".$vendorlname;
                  
                  $deviceType=($vendorData->deviceType)?($vendorData->deviceType):0;
			      $deviceToken=($vendorData->deviceToken)?($vendorData->deviceToken):'';
			      $loginStatus=($vendorData->loginStatus)?($vendorData->loginStatus):0;

                  $leadDetail=$common->getLeadDetail($request->leadId,$timezone,$langId);
                  $customerId=$request->customerId;
                  $newVendorId=$request->venderId;
                  $leadId=$request->leadId;            

                                    $cutomerName=$common->customerName($customerId);
						            $cutomerUrl=$common->customerProfilePic($customerId);
						            $vendorName=$common->vendorName($newVendorId);
						            $vendorUrl=$common->vendorProfilePic($newVendorId);

                                  $notificationmsg=$common->get_notification_msg("award_lead",$langId)?$common->get_notification_msg("award_lead",$langId):"";
                                    if ($notificationmsg!='') {
                                    	$Description=str_replace("#name",$cutomerName,$notificationmsg);
                                    } else {
                                    	$Description=$cutomerName." has awarded lead to you.";
                                    }
                                    

                                    $total_count = DB::table('tblnotification')->where([['notifiedUserId', '=', $newVendorId],['flag','=',0],['isCustomerNotification','=',0]])->count();
                  

                                           $NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>$customerId,'notifiedUserId'=>$newVendorId,'notificationType'=>4,'notification'=>$Description,'createdDate'=>date('Y-m-d H:i:s'),'SendBy'=>2,'leadId'=>$leadId,'isCustomerNotification'=>0]);
                                    
						                   if($deviceType==1) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>4,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);	
                                                }
						                    }

						                    if($deviceType==2) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>4,'message'=>$Description,'title'=>$Description,'NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);
                                                	/*$body['aps'] = array('alert' => $Description, 'sound' => 'default', 'badge' => $total_count, 'content-available' => 1,'NotificationType'=>4,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                	$common->iPhonePushBookVendor(array($deviceToken),$body);*/	
                                                }
						                    }

                  $myarray['result']=$leadDetail;
				  $myarray['message']=$common->get_msg("lead_assign",$langId)?$common->get_msg("lead_assign",$langId):"Your lead has been assigned to $vnname successfully.";
				  $myarray['status']=1;				   
			   }
		  }
		 return response()->json($myarray);
	 }		 
	 
	 /* Lead Complete Api */
	  public function LeadComplete(Request $request) {
	  	  $common=new CommanController;
	  	  $langId=($request->header('langId'))?($request->header('langId')):1; 

          if (!$request->leadId) {
			    $myarray['result']=(object)array();
				$myarray['message']=$common->get_msg("blank_leadId",$langId)?$common->get_msg("blank_leadId",$langId):"Please select leadId.";
				$myarray['status']=0;
		  } else if (!$request->customerId) {
			    $myarray['result']=(object)array();
			    $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
				$myarray['status']=0;
		  }  else if (!$request->status) {
			    $myarray['result']=(object)array();
			    $myarray['message']=$common->get_msg("blank_lead_status",$langId)?$common->get_msg("blank_lead_status",$langId):"Please select lead status.";
				$myarray['status']=0;		
		  } else if (!$request->venderId) {
			    $myarray['result']=(object)array();
				$myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
				$myarray['status']=0;
		  } else {

		  	      $comment=($request->comment)?($request->comment):"";
                  $improvement=($request->improvement)?($request->improvement):"";
                  $rating = ($request->rating)?($request->rating):"";
                  $quality=($request->quality)?($request->quality):1;
                  $speed=($request->speed)?($request->speed):1;
                  $service=($request->service)?($request->service):1;
                  $recommendable=($request->recommendable)?($request->recommendable):1;
                  $reportComment=($request->reportComment)?($request->reportComment):"";
                  $status=($request->status)?($request->status):1;
                  $venderId=($request->venderId)?($request->venderId):0;
                  $timezone=($request->timezone)?($request->timezone):"";
                  $amount=($request->amount)?($request->amount):0;

				  $leadCount = DB::table('tbllead')->where([['customerId', '=', $request->customerId],['status','=',$status],['id', '=', $request->leadId]])->count();
				  
				  $leadCountCompleted = DB::table('tbllead')->where([['customerId', '=', $request->customerId],['status','=',4],['id', '=', $request->leadId]])->count();
		  	      
		  	      $leadDetail=$common->getLeadDetail($request->leadId,$timezone,$langId);
		  	
		  	if ($leadCountCompleted > 0 && $status!=4) {
			  		 $myarray['result']=$leadDetail;
					 $myarray['message']=$common->get_msg("completed_lead_status",$langId)?$common->get_msg("completed_lead_status",$langId):"Your lead has already completed so you can not change status.";
					 $myarray['status']=0;
		  	} else {


                  DB::table('tbllead')->where([['customerId', '=', $request->customerId],['id', '=', $request->leadId]])->update(
				   ['status'=>$status,'leadStatusDate'=>date("Y-m-d H:i:s"),"amount"=>$amount]);
                  
                  $rateId=0;
                    $leadCountdB = DB::table('tblleadrate')->where('leadId',$request->leadId)->first();
                  
                    if ($leadCountdB) {
                    $rateId=$leadCountdB->id;
	                 } elseif ($rating && $comment!='') {
	                 	$rateId=DB::table('tblleadrate')->insertGetId(
	               ['leadId'=>$request->leadId,'comment'=>$comment,'improvement'=>$improvement,'createdDate'=>date('Y-m-d H:i:s')]);
	                 } else {
	                 	$rateId=0;
	                 }


		  	     if ($rating && $status==4 && $rateId!=0) {
		  	     	 $delete=DB::delete('delete from tblleadratetype where leadRateId = ?',[$rateId]);

		  	     	$cn=0;
		  	     	$totalrate=0;
		  	     	$rats=json_decode($rating);
		  	     	foreach (json_decode($rating) as $ratingvalue) {
		  	     		 $typeId = ($ratingvalue->typeId)?($ratingvalue->typeId):1;
						 $rate=($ratingvalue->rate)?($ratingvalue->rate):0;

		  	     		DB::table('tblleadratetype')->insert(
                        ['leadRateId'=>$rateId,'typeId'=>$typeId,'rate'=>$rate,'createdDate'=>date('Y-m-d H:i:s')]);
		  	     		$totalrate=$totalrate+$rate;
		  	     		$cn++;
		  	     	}
		  	     	$avg=($totalrate/$cn);
		  	     	$avg=number_format($avg,2);
		  	     	 DB::table('tbllead')->where([['id', '=', $request->leadId]])->update(
				   ['avgRate'=>$avg]);

		  	     	 if ($venderId!=0) {
		  	     	 	 $avgRate=$common->getAverageRatingVendor($venderId);
                         DB::table('tblvender')->where([['id', '=', $venderId]])->update(['avgRate'=>$avgRate]);
		  	     	 }
		  	     }
                 
                 if ($reportComment!='') {
                 	DB::table('tblleadreport')->insert(
               ['leadId'=>$request->leadId,'comment'=>$reportComment,'createdDate'=>date('Y-m-d H:i:s')]);
                 }

                 $leadDetail=$common->getLeadDetail($request->leadId,$timezone,$langId);
		  	     $myarray['result']=$leadDetail;

		  	    
		  	     
		  	     $msg=$common->get_msg("change_lead_status",$langId)?$common->get_msg("change_lead_status",$langId):"Your lead status has been changed successfully.";
                  if ($status==3) {


                  	              $customerId=$request->customerId;
				                  $newVendorId=$venderId;
				                  $leadId=$request->leadId;            

                                    $cutomerName=$common->customerName($customerId);
						            $cutomerUrl=$common->customerProfilePic($customerId);
						            $vendorName=$common->vendorName($newVendorId);
						            $vendorUrl=$common->vendorProfilePic($newVendorId);

						            $device=$common->getVendorDeviceDetail($newVendorId);
						            $deviceType=($device->deviceType)?($device->deviceType):0;
						            $deviceToken=($device->deviceToken)?($device->deviceToken):"";

                                  $notificationmsg=$common->get_notification_msg("cancel_lead",$langId)?$common->get_notification_msg("cancel_lead",$langId):"";
                                    if ($notificationmsg!='') {
                                    	$Description=str_replace("#name",$cutomerName,$notificationmsg);
                                    } else {
                                    	$Description=$cutomerName." has cancelled their lead.";
                                    }

                                   
                                   $total_count = DB::table('tblnotification')->where([['notifiedUserId', '=', $newVendorId],['flag','=',0],['isCustomerNotification','=',0]])->count();
                  
                  
                                           $NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>$customerId,'notifiedUserId'=>$newVendorId,'notificationType'=>5,'notification'=>$Description,'createdDate'=>date('Y-m-d H:i:s'),'SendBy'=>2,'leadId'=>$leadId,'isCustomerNotification'=>0]);
                                    
						                   if($deviceType==1) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>5,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);	
                                                }
						                    }

						                    if($deviceType==2) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>5,'message'=>$Description,'title'=>$Description,'NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);
                                                	/*$body['aps'] = array('alert' => $Description, 'sound' => 'default', 'badge' => $total_count, 'content-available' => 1,'NotificationType'=>5,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                	$common->iPhonePushBookVendor(array($deviceToken),$body);*/	
                                                }
						                    }
                  	$msg=$common->get_msg("cancel_lead",$langId)?$common->get_msg("cancel_lead",$langId):"Your lead has been cancelled successfully.";
                  }

                  if ($status==4) {

                  	$customerId=$request->customerId;
				                  $newVendorId=$venderId;
				                  $leadId=$request->leadId;            

                                    $cutomerName=$common->customerName($customerId);
						            $cutomerUrl=$common->customerProfilePic($customerId);
						            $vendorName=$common->vendorName($newVendorId);
						            $vendorUrl=$common->vendorProfilePic($newVendorId);

						            $device=$common->getVendorDeviceDetail($newVendorId);
						            //$deviceType=$device->deviceType;
						            //$deviceToken=$device->deviceToken;
						            $deviceType=($device->deviceType)?($device->deviceType):0;
						            $deviceToken=($device->deviceToken)?($device->deviceToken):"";

                                  $notificationmsg=$common->get_notification_msg("complete_lead",$langId)?$common->get_notification_msg("complete_lead",$langId):"";
                                    if ($notificationmsg!='') {
                                    	$Description=str_replace("#name",$cutomerName,$notificationmsg);
                                    } else {
                                    	$Description=$cutomerName." has completed their lead status.";
                                    }

                                   
                                   $total_count = DB::table('tblnotification')->where([['notifiedUserId', '=', $newVendorId],['flag','=',0],['isCustomerNotification','=',0]])->count();
                  
                  
                                           $NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>$customerId,'notifiedUserId'=>$newVendorId,'notificationType'=>7,'notification'=>$Description,'createdDate'=>date('Y-m-d H:i:s'),'SendBy'=>2,'leadId'=>$leadId,'isCustomerNotification'=>0]);
                                    
						                   if($deviceType==1) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>7,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);	
                                                }
						                    }

						                    if($deviceType==2) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>7,'message'=>$Description,'title'=>$Description,'NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);
                                                	/*$body['aps'] = array('alert' => $Description, 'sound' => 'default', 'badge' => $total_count, 'content-available' => 1,'NotificationType'=>7,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                	$common->iPhonePushBookVendor(array($deviceToken),$body);*/	
                                                }
						                    }
                  	$msg=$common->get_msg("complete_lead",$langId)?$common->get_msg("complete_lead",$langId):"Your lead has been completed successfully.";
                  }

				 $myarray['message']=$msg;
				 $myarray['status']=1;
		  	}
		  }
		  return response()->json($myarray);
	  }

       /* LeadCancel */      
       public function LeadCancel(Request $request) {
	  	  $common=new CommanController;
	  	  $langId=($request->header('langId'))?($request->header('langId')):1; 
          
          if (!$request->leadId) {
			    $myarray['result']=(object)array();
				$myarray['message']=$common->get_msg("blank_leadId",$langId)?$common->get_msg("blank_leadId",$langId):"Please select leadId.";
				$myarray['status']=0;
		  } else if (!$request->customerId) {
			    $myarray['result']=(object)array();
			    $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
				$myarray['status']=0;
		  } else if (!$request->status) {
			    $myarray['result']=(object)array();
			    $myarray['message']=$common->get_msg("blank_lead_status",$langId)?$common->get_msg("blank_lead_status",$langId):"Please select lead status.";
				$myarray['status']=0;		
		  } else if (!$request->venderId) {
			    $myarray['result']=(object)array();
				$myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
				$myarray['status']=0;
		  } else {
               
               $timezone=($request->timezone)?($request->timezone):"";
               $status=($request->status)?($request->status):1;
               $venderId=($request->venderId)?($request->venderId):0;

               $leadCountCompleted = DB::table('tbllead')->where([['customerId', '=', $request->customerId],['status','=',4],['id', '=', $request->leadId]])->count();
               $leadDetail=$common->getLeadDetail($request->leadId,$timezone,$langId);
               
               if ($leadCountCompleted > 0) {
			  		 $myarray['result']=$leadDetail;
					 $myarray['message']=$common->get_msg("completed_lead_status",$langId)?$common->get_msg("completed_lead_status",$langId):"Your lead has already completed so you can not change status.";
					 $myarray['status']=0;
		  	   } else {
               DB::table('tbllead')->where([['customerId', '=', $request->customerId],['id', '=', $request->leadId]])->update(
				   ['status'=>$status,'leadStatusDate'=>date("Y-m-d H:i:s")]);
                $msg=$common->get_msg("cancel_lead",$langId)?$common->get_msg("cancel_lead",$langId):"Your lead has been cancelled successfully.";  
               
				                  $customerId=$request->customerId;
				                  $newVendorId=$venderId;
				                  $leadId=$request->leadId;            

                                    $cutomerName=$common->customerName($customerId);
						            $cutomerUrl=$common->customerProfilePic($customerId);
						            $vendorName=$common->vendorName($newVendorId);
						            $vendorUrl=$common->vendorProfilePic($newVendorId);

						            $device=$common->getVendorDeviceDetail($newVendorId);
						            $deviceType=$device->deviceType;
						            $deviceToken=$device->deviceToken;

                                  $notificationmsg=$common->get_notification_msg("cancel_lead",$langId)?$common->get_notification_msg("cancel_lead",$langId):"";
                                    if ($notificationmsg!='') {
                                    	$Description=str_replace("#name",$cutomerName,$notificationmsg);
                                    } else {
                                    	$Description=$cutomerName." has cancelled their lead.";
                                    }

                                   
                                   $total_count = DB::table('tblnotification')->where([['notifiedUserId', '=', $newVendorId],['flag','=',0],['isCustomerNotification','=',0]])->count();
                  

                                           $NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>$customerId,'notifiedUserId'=>$newVendorId,'notificationType'=>5,'notification'=>$Description,'createdDate'=>date('Y-m-d H:i:s'),'SendBy'=>2,'leadId'=>$leadId,'isCustomerNotification'=>0]);
                                    
						                   if($deviceType==1) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>5,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);	
                                                }
						                    }

						                    if($deviceType==2) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>5,'message'=>$Description,'title'=>$Description,'NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);
                                                	/*$body['aps'] = array('alert' => $Description, 'sound' => 'default', 'badge' => $total_count, 'content-available' => 1,'NotificationType'=>5,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$newVendorId,'leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                	$common->iPhonePushBookVendor(array($deviceToken),$body);*/	
                                                }
						                    }


		  	     $myarray['result']=$leadDetail;
		  	     $myarray['message']=$msg;
		  	     $myarray['status']=1;
		  	     }   
		  }
		  return response()->json($myarray);
	 }	  


	  /* Rate Type Listing */

	  public function RateType(Request $request) {
	         
	         $common=new CommanController;
             $langId=($request->header('langId'))?($request->header('langId')):1; 
             $rateType = DB::table('tblratetype')->where([['isActive', '=',1]])->get();
	          $arrays=array();
	          if ($rateType) {
	          	 foreach ($rateType as $value) {
	          	 	$name=$common->getRateTypeValue($value->id,$langId);
	          	 	$arrays[]=array("id"=>(int)$value->id,"name"=>$name,"createdDate"=>$value->createdDate,"isActive"=>$value->isActive);
	          	 }
	          	 $myarray['result']=$arrays;
				 $myarray['message']=$common->get_msg("rate_type",$langId)?$common->get_msg("rate_type",$langId):"Rate Type List.";
				 $myarray['status']=1;
	          } else {
	             $myarray['result']=$arrays;
				 $myarray['message']=$common->get_msg("no_rate_type",$langId)?$common->get_msg("no_rate_type",$langId):"No Rate Type Found.";
				 $myarray['status']=0;
				 
	          }

	          return response()->json($myarray);
	  }

	  /* Lead Complete Api */
	  public function leadRate(Request $request) {
	  	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1; 

          if (!$request->leadId) {
			    $myarray['result']=(object)array();
				$myarray['message']=$common->get_msg("blank_leadId",$langId)?$common->get_msg("blank_leadId",$langId):"Please select leadId.";
				$myarray['status']=0;
		  } else if (!$request->customerId) {
			    $myarray['result']=(object)array();
			    $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
				$myarray['status']=0;
		  }  else {


		  	      $comment=($request->comment)?($request->comment):"";
                  $improvement=($request->improvement)?($request->improvement):"";
                  $rating = ($request->rating)?($request->rating):"";
                  $quality=($request->quality)?($request->quality):1;
                  $speed=($request->speed)?($request->speed):1;
                  $service=($request->service)?($request->service):1;
                  $recommendable=($request->recommendable)?($request->recommendable):1;
                  $reportComment=($request->reportComment)?($request->reportComment):"";
                  $venderId=($request->venderId)?($request->venderId):0;
                  $timezone=($request->timezone)?($request->timezone):"";
                  $amount=($request->amount)?($request->amount):0;

                    $leadCountdB = DB::table('tblleadrate')->where('leadId',$request->leadId)->first();
                  
                    if ($leadCountdB) {
                    $rateId=$leadCountdB->id;
                    $update=DB::table('tblleadrate')->where([['leadId', '=', $request->leadId]])->update(
				   ['comment'=>$comment,'improvement'=>$improvement]);
	                } elseif ($rating) {
	                 	$rateId=DB::table('tblleadrate')->insertGetId(
	               ['leadId'=>$request->leadId,'comment'=>$comment,'improvement'=>$improvement,'createdDate'=>date('Y-m-d H:i:s')]);
	                } else {
	                 	$rateId=0;
	                 }
                 
                 if ($amount!=0) {
                 DB::table('tbllead')->where([['id', '=', $request->leadId]])->update(
				   ['amount'=>$amount]);
                   }


		  	     if ($rating &&  $rateId!=0) {
		  	     	 $delete=DB::delete('delete from tblleadratetype where leadRateId = ?',[$rateId]);
		  	     	$cn=0;
		  	     	$totalrate=0;
		  	     	$rats=json_decode($rating);
		  	     	foreach (json_decode($rating) as $ratingvalue) {
		  	     		 $typeId = ($ratingvalue->typeId)?($ratingvalue->typeId):1;
						 $rate=($ratingvalue->rate)?($ratingvalue->rate):0;

		  	     		DB::table('tblleadratetype')->insert(
               ['leadRateId'=>$rateId,'typeId'=>$typeId,'rate'=>$rate,'createdDate'=>date('Y-m-d H:i:s')]);
		  	     		$totalrate=$totalrate+$rate;
		  	     		$cn++;
		  	     	}
		  	     	$avg=($totalrate/$cn);
		  	     	$avg=number_format($avg,2);
		  	     	 DB::table('tbllead')->where([['id', '=', $request->leadId]])->update(
				   ['avgRate'=>$avg]);

		  	     	 if ($venderId!=0) {
		  	     	 	 $avgRate=$common->getAverageRatingVendor($venderId);
                         DB::table('tblvender')->where([['id', '=', $venderId]])->update(['avgRate'=>$avgRate]);
		  	     	 }
		  	     }
                 
                 if ($reportComment!='') {
                 	DB::table('tblleadreport')->insert(
               ['leadId'=>$request->leadId,'comment'=>$reportComment,'createdDate'=>date('Y-m-d H:i:s')]);
                 }

                $leadDetail=$common->getLeadDetail($request->leadId,$timezone,$langId);

		         $myarray['result']=$leadDetail;
		         if ($reportComment!='') {
                 $myarray['message']=$common->get_msg("report_comment",$langId)?$common->get_msg("report_comment",$langId):"Your Report has been successfully posted.";
		         } else {
		         	$myarray['message']=$common->get_msg("ratting",$langId)?$common->get_msg("ratting",$langId):"Your ratting has been successfully posted.";
		         }
			   
				$myarray['status']=1;
		  }
		  return response()->json($myarray);
           
		}
       
       /* HowDidYouKnow Listing */

	  public function HowDidYouKnow(Request $request) {
	         
	         $common=new CommanController;
             $langId=($request->header('langId'))?($request->header('langId')):1; 
             $arrays=array();
	          $howdidyouknow = DB::table('tblhowdidyouknow')->where([['isActive', '=',1]])->get();
	             if ($howdidyouknow->count() > 0) {
	             	foreach($howdidyouknow as  $value) {
                     $id=$value->id;
                     $title=$common->getHdykValue($id,$langId);
                    $arrays[]=array("id"=>(int)$id,"name"=>$title,"isActive"=>$value->isActive,"createdDate"=>$value->createdDate);
	             	}
	             }

	             
				 
				 $msg=$common->get_msg("no_record_found",$langId)?$common->get_msg("no_record_found",$langId):"No Record Found.";
				 if (count($howdidyouknow)>0) {
				 $msg=$common->get_msg("howdidyouknow_listing",$langId)?$common->get_msg("howdidyouknow_listing",$langId):"How Did You Know Listing.";
				 }
				 $myarray['result']=$arrays;
				 $myarray['message']=$msg;
				 $myarray['status']=1;
			return response()->json($myarray);	 
	  }

	  /* Pending Lead Request */

	  public function pendingLeadRequest(Request $request) {
             $common=new CommanController;
             $langId=($request->header('langId'))?($request->header('langId')):1; 

       if (!$request->venderId) {
			    $myarray['result']=(object)array();
				$myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
				$myarray['status']=0;
		  
		} else {

              $pageLimit = isset($request->count)?(int)($request->count):5;
	          $lastId = isset($request->lastId)?(int)($request->lastId):0;
	          $startDate = isset($request->startDate)?($request->startDate):'';
			  $endDate = isset($request->endDate)?($request->endDate):'';
			  $timezone=($request->timezone)?($request->timezone):"";
			  $serviceType=($request->serviceType)?($request->serviceType):"";

			  $leads=array();
			  $totalRecordInPage=0;
			  $pageNo = isset($request->page)?(int)($request->page):1;
              
              if ($pageNo!=0) {
			  
			  $start = ($pageNo - 1) * $pageLimit;
			  
			  } else {
			  
			  $start=1; 
			  
			  }			
			  
			    $where="";
				if ($startDate!='' && $endDate!='') {
					$startDate=date("Y-m-d",strtotime($startDate));
					$endDate=date("Y-m-d",strtotime($endDate));
				    $where=" and (DATE_FORMAT(lead.createdDate,'%Y-%m-%d')) >= '{$startDate}' and (DATE_FORMAT(lead.createdDate,'%Y-%m-%d')) <= '{$endDate}'";
				} elseif ($startDate!='') {
					$startDate=date("Y-m-d",strtotime($startDate));
					$where=" and (DATE_FORMAT(lead.createdDate,'%Y-%m-%d')) >= '{$startDate}'";
				} elseif($endDate!='') {
				    $endDate=date("Y-m-d",strtotime($endDate));
					$where=" and (DATE_FORMAT(lead.createdDate,'%Y-%m-%d')) <= '{$endDate}'";
				} else {
				    $where="";
				}

				if ($serviceType!='') {

					$where .=" and lead.serviceTypeId IN (".$serviceType.")";
				}
              

             $leadCount =count(DB::select( DB::raw("select lead.*,leadvendor.venderId from tbllead as lead
inner join tblleadvender as leadvendor on lead.id=leadvendor.leadId
where leadvendor.isApproved=0 and leadvendor.venderId='{$request->venderId}' and lead.status=1 $where  order by lead.createdDate desc")));
			  $lead=DB::select( DB::raw("select lead.*,leadvendor.venderId from tbllead as lead
inner join tblleadvender as leadvendor on lead.id=leadvendor.leadId
where leadvendor.isApproved=0 and leadvendor.venderId='{$request->venderId}' and lead.status=1 $where  order by lead.createdDate desc LIMIT $start,$pageLimit"));
                 
				 if ($lead) {
				     
					 foreach($lead as $values) {
					      $leadId=($values->id)?($values->id):0;
						  $venderId=($values->venderId)?($values->venderId):0;
						  $businessCategoryId=($values->businessCategoryId)?($values->businessCategoryId):0;
						  $serviceTypeId=($values->serviceTypeId)?($values->serviceTypeId):0;
						  $venderServiceTypeId=($values->venderServiceTypeId)?($values->venderServiceTypeId):0;
						  $leadDatetime=($values->leadDatetime)?($values->leadDatetime):"";
						  $description=($values->description)?($values->description):"";
						  $leadstatus=($values->status)?($values->status):1;
						  $avgRate=($values->avgRate)?($values->avgRate):0;
						  $leadcreatedDate=($values->createdDate)?($values->createdDate):"";



						  if ($timezone!='' && $leadDatetime!='') {
			 	           $leadDatetime=$common->NewConvertDate($leadDatetime,$timezone);
			              }

			              if ($timezone!='' && $leadcreatedDate!='') {
			 	           $leadcreatedDate=$common->NewConvertDate($leadcreatedDate,$timezone);
			              }



						  $customerId=($values->customerId)?($values->customerId):0;
						  $customerName='';
						  if ($customerId!=0) {
						  $customerData = DB::table('tblcustomer')->where([['id', '=', $customerId]])->first();
                            if ($customerData) {
                            	$customerName=$customerData->fname." ".$customerData->lname;
                            }
                          }
                          
                          $serviceName=($values->serviceTypeName)?($values->serviceTypeName):'';
                          $subServiceName=($values->venderServiceTypeName)?($values->venderServiceTypeName):'';
						  $businessCategoryName='';
						  //$serviceName='';
						  //$subServiceName='';
						  if ($businessCategoryId!=0) {
						  //$businessCategoryName=$common->businessCategoryName($businessCategoryId);
						  	$businessCategoryName=$common->getBusinessCategoryValue($businessCategoryId,$langId);
						  }
                          /*if ($serviceTypeId!=0) {
						   $serviceName=$common->getServiceTypeValue($serviceTypeId,$langId);

						  }*/

						  if ($serviceTypeId!=0) {
						  //$serviceName=$common->serviceName($serviceTypeId);
						  $serviceName=$common->getServiceTypeValue($serviceTypeId,$langId);
						     if ($serviceName=='') {
				      	      $serviceName=($values->serviceTypeName)?($values->serviceTypeName):'';
				              }
						  }
						  
						  /*if ($serviceTypeId!=0) {
						  $serviceName=$common->serviceName($serviceTypeId);
						  }
						  
						  if ($venderServiceTypeId!=0) {
						  $subServiceName=$common->subServiceName($venderServiceTypeId);
						  }*/
						  
						  if ($leadstatus==1) {
						   $leadStatusName=$common->get_msg("lead_status_pending",$langId)?$common->get_msg("lead_status_pending",$langId):'Pending';
						  } elseif ($leadstatus==2) {
						   $leadStatusName=$common->get_msg("lead_status_inprogress",$langId)?$common->get_msg("lead_status_inprogress",$langId):'In Progress';
						  } elseif ($leadstatus==3) {
						   $leadStatusName=$common->get_msg("lead_cancel",$langId)?$common->get_msg("lead_cancel",$langId):'Cancel';
						  } elseif ($leadstatus==4) {
						   $leadStatusName=$common->get_msg("lead_status_completed",$langId)?$common->get_msg("lead_status_completed",$langId):'Completed';
						  } else {
							  $leadStatusName='';
						  }
						  
						  $awardedVendor='';
						  if ($leadId!=0 && $venderId!=0) {
						     $awardedVendor=$common->awardedVendorLead($leadId,$venderId,$langId);
						  }
						  $leadComments='';
						  if ($leadId!=0) {
						  $leadComments=$common->leadComment($leadId);
						  }
						  $leads[]=array("leadId"=>$leadId,"businessCategoryId"=>$businessCategoryId,"businessCategoryName"=>$businessCategoryName,"serviceTypeId"=>$serviceTypeId,"serviceName"=>$serviceName,"leadDatetime"=>$leadDatetime,"description"=>$description,"leadStatus"=>$leadstatus,"leadStatusName"=>$leadStatusName,"awardedVendor"=>$awardedVendor,"venderServiceTypeId"=>$venderServiceTypeId,"vendorsubServiceName"=>$subServiceName,"leadComments"=>$leadComments,"avgRate"=>$avgRate,"customerId"=>$customerId,"customerName"=>$customerName,"leadCreatedDate"=>$leadcreatedDate); 
					   $totalRecordInPage++;
					 }
					 
					 $myarray['result']=$leads;
					 $myarray['pageNo']=$pageNo;
                     $myarray['totalRecordInPage']=$totalRecordInPage;
					 $myarray['leadCount']=$leadCount;				 
					 $myarray['message']=$common->get_msg("pending_lead_request",$langId)?$common->get_msg("pending_lead_request",$langId):"Your pending lead request list.";
					 $myarray['status']=1;
					 
				 } else {
				 	 $myarray['result']=$leads;
					 $myarray['pageNo']=$pageNo;
                     $myarray['totalRecordInPage']=$totalRecordInPage;
					 $myarray['leadCount']=$leadCount;				 
					 $myarray['message']=$common->get_msg("no_pending_lead_request",$langId)?$common->get_msg("no_pending_lead_request",$langId):"No pending lead request found in your account.";
					 $myarray['status']=1;
				 }

	    }
	    return response()->json($myarray);
	}

	/* Ongoing Lead Request */

	  public function ongoingLeads(Request $request) {
             $common=new CommanController;
             $langId=($request->header('langId'))?($request->header('langId')):1; 

       if (!$request->venderId) {
			    $myarray['result']=(object)array();
				$myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
				$myarray['status']=0;
		  
		} else {

              $pageLimit = isset($request->count)?(int)($request->count):5;
	          $lastId = isset($request->lastId)?(int)($request->lastId):0;
	          $startDate = isset($request->startDate)?(date("Y-m-d",strtotime($request->startDate))):'';
			  $endDate = isset($request->endDate)?(date("Y-m-d",strtotime($request->endDate))):'';
			  $timezone=($request->timezone)?($request->timezone):"";
              $businessCategoryId = isset($request->businessCategoryId)?(int)($request->businessCategoryId):0; 
              $serviceType=($request->serviceType)?($request->serviceType):"";   

			  $leads=array();
			  $totalRecordInPage=0;
			  $pageNo = isset($request->page)?(int)($request->page):1;
              
              if ($pageNo!=0) {
			  
			  $start = ($pageNo - 1) * $pageLimit;
			  
			  } else {
			  
			  $start=1; 
			  
			  }			
			  
			    $where="";
				if ($startDate!='' && $endDate!='') {
					$startDate=date("Y-m-d",strtotime($startDate));
					$endDate=date("Y-m-d",strtotime($endDate));
				    $where=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) >= '{$startDate}' and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) <= '{$endDate}'";
				} elseif ($startDate!='') {
					$startDate=date("Y-m-d",strtotime($startDate));
					$where=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) >= '{$startDate}'";
				} elseif($endDate!='') {
				    $endDate=date("Y-m-d",strtotime($endDate));
					$where=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) <= '{$endDate}'";
				} else {
				    $where="";
				}
              
              if ($businessCategoryId!=0) {
              	 $where .=" and lead.businessCategoryId='{$businessCategoryId}'";
              }

              if ($serviceType!='') {

					$where .=" and lead.serviceTypeId IN (".$serviceType.")";
				}

             $leadCount =count(DB::select( DB::raw("select lead.*,leadvendor.venderId from tbllead as lead
inner join tblleadvender as leadvendor on lead.id=leadvendor.leadId
where leadvendor.isApproved=1 and leadvendor.venderId='{$request->venderId}' and lead.status=2 $where  order by lead.id desc")));
			  $lead=DB::select( DB::raw("select lead.*,leadvendor.venderId from tbllead as lead
inner join tblleadvender as leadvendor on lead.id=leadvendor.leadId
where leadvendor.isApproved=1 and leadvendor.venderId='{$request->venderId}' and lead.status=2 $where  order by lead.id desc LIMIT $start,$pageLimit"));
                 
				 if ($lead) {
				     
					 foreach($lead as $values) {
					      $leadId=($values->id)?($values->id):0;
						  $venderId=($values->venderId)?($values->venderId):0;
						  $businessCategoryId=($values->businessCategoryId)?($values->businessCategoryId):0;
						  $serviceTypeId=($values->serviceTypeId)?($values->serviceTypeId):0;
						  $venderServiceTypeId=($values->venderServiceTypeId)?($values->venderServiceTypeId):0;
						  $leadDatetime=($values->leadDatetime)?($values->leadDatetime):"";
						  $description=($values->description)?($values->description):"";
						  $leadstatus=($values->status)?($values->status):1;
						  $avgRate=($values->avgRate)?($values->avgRate):0;

						  if ($timezone!='' && $leadDatetime!='') {
			 	           $leadDatetime=$common->NewConvertDate($leadDatetime,$timezone);
			              }

						  $customerId=($values->customerId)?($values->customerId):0;
						  $customerName='';
						  if ($customerId!=0) {
						  $customerData = DB::table('tblcustomer')->where([['id', '=', $customerId]])->first();
                            if ($customerData) {
                            	$customerName=$customerData->fname." ".$customerData->lname;
                            }
                          }
                          
                          $serviceName=($values->serviceTypeName)?($values->serviceTypeName):'';
                          $subServiceName=($values->venderServiceTypeName)?($values->venderServiceTypeName):'';
						  $businessCategoryName='';
						  //$serviceName='';
						  //$subServiceName='';
						  if ($businessCategoryId!=0) {
						  //$businessCategoryName=$common->businessCategoryName($businessCategoryId);
						  	$businessCategoryName=$common->getBusinessCategoryValue($businessCategoryId,$langId);
						  }
						  
						  if ($serviceTypeId!=0) {
						  //$serviceName=$common->serviceName($serviceTypeId);
						  $serviceName=$common->getServiceTypeValue($serviceTypeId,$langId);
						     if ($serviceName=='') {
				      	      $serviceName=($values->serviceTypeName)?($values->serviceTypeName):'';
				              }
						  }

						  /*if ($serviceTypeId!=0) {
						  $serviceName=$common->serviceName($serviceTypeId);
						  }
						  
						  if ($venderServiceTypeId!=0) {
						  $subServiceName=$common->subServiceName($venderServiceTypeId);
						  }*/
						  
						  if ($leadstatus==1) {
						   $leadStatusName=$common->get_msg("lead_status_pending",$langId)?$common->get_msg("lead_status_pending",$langId):'Pending';
						  } elseif ($leadstatus==2) {
						   $leadStatusName=$common->get_msg("lead_status_inprogress",$langId)?$common->get_msg("lead_status_inprogress",$langId):'In Progress';
						  } elseif ($leadstatus==3) {
						   $leadStatusName=$common->get_msg("lead_cancel",$langId)?$common->get_msg("lead_cancel",$langId):'Cancel';
						  } elseif ($leadstatus==4) {
						   $leadStatusName=$common->get_msg("lead_status_completed",$langId)?$common->get_msg("lead_status_completed",$langId):'Completed';
						  } else {
							  $leadStatusName='';
						  }
						  
						  $awardedVendor='';
						  if ($leadId!=0 && $venderId!=0) {
						     $awardedVendor=$common->awardedVendorLead($leadId,$venderId,$langId);
						  }
						  $leadComments='';
						  if ($leadId!=0) {
						  $leadComments=$common->leadComment($leadId);
						  }
						  $leads[]=array("leadId"=>$leadId,"businessCategoryId"=>$businessCategoryId,"businessCategoryName"=>$businessCategoryName,"serviceTypeId"=>$serviceTypeId,"serviceName"=>$serviceName,"leadDatetime"=>$leadDatetime,"description"=>$description,"leadStatus"=>$leadstatus,"leadStatusName"=>$leadStatusName,"awardedVendor"=>$awardedVendor,"venderServiceTypeId"=>$venderServiceTypeId,"vendorsubServiceName"=>$subServiceName,"leadComments"=>$leadComments,"avgRate"=>$avgRate,"customerId"=>$customerId,"customerName"=>$customerName); 
					   $totalRecordInPage++;
					 }
					 
					 $myarray['result']=$leads;
					 $myarray['pageNo']=$pageNo;
                     $myarray['totalRecordInPage']=$totalRecordInPage;
					 $myarray['leadCount']=$leadCount;				 
					 $myarray['message']=$common->get_msg("ongoing_lead",$langId)?$common->get_msg("ongoing_lead",$langId):"Your ongoing lead list.";
					 $myarray['status']=1;
					 
				 } else {
				 	 $myarray['result']=$leads;
					 $myarray['pageNo']=$pageNo;
                     $myarray['totalRecordInPage']=$totalRecordInPage;
					 $myarray['leadCount']=$leadCount;				 
					 $myarray['message']=$common->get_msg("no_ongoing_lead",$langId)?$common->get_msg("no_ongoing_lead",$langId):"No ongoing lead found in your account.";
					 $myarray['status']=1;
				 }

	    }
	    return response()->json($myarray);
	}

	/* Completed Lead Request */

	  public function completedLeads(Request $request) {
             $common=new CommanController;
             $langId=($request->header('langId'))?($request->header('langId')):1; 

       if (!$request->venderId) {
			    $myarray['result']=(object)array();
				$myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
				$myarray['status']=0;
		  
		} else {

              $pageLimit = isset($request->count)?(int)($request->count):5;
	          $lastId = isset($request->lastId)?(int)($request->lastId):0;
	          $startDate = isset($request->startDate)?($request->startDate):'';
			  $endDate = isset($request->endDate)?($request->endDate):'';
			  $timezone=($request->timezone)?($request->timezone):"";
              $businessCategoryId = isset($request->businessCategoryId)?(int)($request->businessCategoryId):0;    
              $serviceType=($request->serviceType)?($request->serviceType):"";

			  $leads=array();
			  $totalRecordInPage=0;
			  $pageNo = isset($request->page)?(int)($request->page):1;
              
              if ($pageNo!=0) {
			  
			  $start = ($pageNo - 1) * $pageLimit;
			  
			  } else {
			  
			  $start=1; 
			  
			  }			
			  
			    $where="";
				if ($startDate!='' && $endDate!='') {
					$startDate=date("Y-m-d",strtotime($startDate));
					$endDate=date("Y-m-d",strtotime($endDate));
				    $where=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) >= '{$startDate}' and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) <= '{$endDate}'";
				} elseif ($startDate!='') {
					$startDate=date("Y-m-d",strtotime($startDate));
					$where=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) >= '{$startDate}'";
				} elseif($endDate!='') {
				    $endDate=date("Y-m-d",strtotime($endDate));
					$where=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) <= '{$endDate}'";
				} else {
				    $where="";
				}
              
              if ($businessCategoryId!=0) {
              	 $where .=" and lead.businessCategoryId='{$businessCategoryId}'";
              }

              if ($serviceType!='') {
                 $where .=" and lead.serviceTypeId IN (".$serviceType.")";
				}

             $leadCount =count(DB::select( DB::raw("select lead.*,leadvendor.venderId from tbllead as lead
inner join tblleadvender as leadvendor on lead.id=leadvendor.leadId
where leadvendor.isApproved=1 and leadvendor.venderId='{$request->venderId}' and lead.status=4 $where  order by lead.id desc")));
			  $lead=DB::select( DB::raw("select lead.*,leadvendor.venderId from tbllead as lead
inner join tblleadvender as leadvendor on lead.id=leadvendor.leadId
where leadvendor.isApproved=1 and leadvendor.venderId='{$request->venderId}' and lead.status=4 $where  order by lead.id desc LIMIT $start,$pageLimit"));
                 
				 if ($lead) {
				     
					 foreach($lead as $values) {
					      $leadId=($values->id)?($values->id):0;
						  $venderId=($values->venderId)?($values->venderId):0;
						  $businessCategoryId=($values->businessCategoryId)?($values->businessCategoryId):0;
						  $serviceTypeId=($values->serviceTypeId)?($values->serviceTypeId):0;
						  $venderServiceTypeId=($values->venderServiceTypeId)?($values->venderServiceTypeId):0;
						  $leadDatetime=($values->leadDatetime)?($values->leadDatetime):"";
						  $description=($values->description)?($values->description):"";
						  $leadstatus=($values->status)?($values->status):1;
						  $avgRate=($values->avgRate)?($values->avgRate):0;

						  if ($timezone!='' && $leadDatetime!='') {
			 	           $leadDatetime=$common->NewConvertDate($leadDatetime,$timezone);
			              }

						  $customerId=($values->customerId)?($values->customerId):0;
						  $customerName='';
						  if ($customerId!=0) {
						  $customerData = DB::table('tblcustomer')->where([['id', '=', $customerId]])->first();
                            if ($customerData) {
                            	$customerName=$customerData->fname." ".$customerData->lname;
                            }
                          }
                          
                          $serviceName=($values->serviceTypeName)?($values->serviceTypeName):'';
                          $subServiceName=($values->venderServiceTypeName)?($values->venderServiceTypeName):'';
						  $businessCategoryName='';
						  //$serviceName='';
						  //$subServiceName='';
						  if ($businessCategoryId!=0) {
						 // $businessCategoryName=$common->businessCategoryName($businessCategoryId);
						  $businessCategoryName=$common->getBusinessCategoryValue($businessCategoryId,$langId);
						  }
						  
						  /*if ($serviceTypeId!=0) {
						  $serviceName=$common->serviceName($serviceTypeId);
						  }
						  
						  if ($venderServiceTypeId!=0) {
						  $subServiceName=$common->subServiceName($venderServiceTypeId);
						  }*/
						  
						  if ($serviceTypeId!=0) {
						  //$serviceName=$common->serviceName($serviceTypeId);
						  $serviceName=$common->getServiceTypeValue($serviceTypeId,$langId);
						     if ($serviceName=='') {
				      	      $serviceName=($values->serviceTypeName)?($values->serviceTypeName):'';
				              }
						  }

						  if ($leadstatus==1) {
						   $leadStatusName=$common->get_msg("lead_status_pending",$langId)?$common->get_msg("lead_status_pending",$langId):'Pending';
						  } elseif ($leadstatus==2) {
						   $leadStatusName=$common->get_msg("lead_status_inprogress",$langId)?$common->get_msg("lead_status_inprogress",$langId):'In Progress';
						  } elseif ($leadstatus==3) {
						   $leadStatusName=$common->get_msg("lead_cancel",$langId)?$common->get_msg("lead_cancel",$langId):'Cancel';
						  } elseif ($leadstatus==4) {
						   $leadStatusName=$common->get_msg("lead_status_completed",$langId)?$common->get_msg("lead_status_completed",$langId):'Completed';
						  } else {
							  $leadStatusName='';
						  }
						  
						  $awardedVendor='';
						  if ($leadId!=0 && $venderId!=0) {
						     $awardedVendor=$common->awardedVendorLead($leadId,$venderId,$langId);
						  }
						  $leadComments='';
						  if ($leadId!=0) {
						  $leadComments=$common->leadComment($leadId);
						  }
						  $leads[]=array("leadId"=>$leadId,"businessCategoryId"=>$businessCategoryId,"businessCategoryName"=>$businessCategoryName,"serviceTypeId"=>$serviceTypeId,"serviceName"=>$serviceName,"leadDatetime"=>$leadDatetime,"description"=>$description,"leadStatus"=>$leadstatus,"leadStatusName"=>$leadStatusName,"awardedVendor"=>$awardedVendor,"venderServiceTypeId"=>$venderServiceTypeId,"vendorsubServiceName"=>$subServiceName,"leadComments"=>$leadComments,"avgRate"=>$avgRate,"customerId"=>$customerId,"customerName"=>$customerName); 
					   $totalRecordInPage++;
					 }
					 
					 $myarray['result']=$leads;
					 $myarray['pageNo']=$pageNo;
                     $myarray['totalRecordInPage']=$totalRecordInPage;
					 $myarray['leadCount']=$leadCount;				 
					 $myarray['message']=$common->get_msg("completed_lead",$langId)?$common->get_msg("completed_lead",$langId):"Your completed lead list.";
					 $myarray['status']=1;
					 
				 } else {
				 	 $myarray['result']=$leads;
					 $myarray['pageNo']=$pageNo;
                     $myarray['totalRecordInPage']=$totalRecordInPage;
					 $myarray['leadCount']=$leadCount;				 
					 $myarray['message']=$common->get_msg("no_completed_lead",$langId)?$common->get_msg("no_completed_lead",$langId):"No completed lead found in your account.";
					 $myarray['status']=1;
				 }

	    }
	    return response()->json($myarray);
	}

     /* delete vendor Services */
	public function deleteVendorService(Request $request) {
	 	  $common=new CommanController;
	 	  $langId=($request->header('langId'))?($request->header('langId')):1; 

	      if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
		   $myarray['status']=0;
		 } elseif (!$request->vendorServiceTypeId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_serviceTypeId",$langId)?$common->get_msg("blank_serviceTypeId",$langId):"Please select serviceTypeId.";
		   $myarray['status']=0;
		 } else {
		 	$vendorServiceTypeId=($request->vendorServiceTypeId)?($request->vendorServiceTypeId):0;

		 	$servicecount=DB::select( DB::raw("select * from tblvenderservicetype where id IN (".$vendorServiceTypeId.")"));
		 	$checkvendor = DB::table('tblvender')->where([['id', '=',$request->venderId]])->count();

            if ($checkvendor==0) {
            	$myarray['result']=array();					
			    $myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):"Invalid VendorId.";
			    $myarray['status']=0;
		 	
		 	} elseif ($servicecount) {
		 	//foreach($vendorServiceTypeId as $TypeId){
                  $delete=DB::delete('delete from tblvenderservicetype where id IN ('.$vendorServiceTypeId.')');
		 	//}
		 	$vendorServices=$common->vendorServices($request->venderId,$langId);
		 	$myarray['result']=$vendorServices;					
		    $myarray['message']=$common->get_msg("delete_services",$langId)?$common->get_msg("delete_services",$langId):"Your selected services has been deleted successfully.";
		    $myarray['status']=1;
		       
		       } else {
                
			 	$myarray['result']=array();					
			    $myarray['message']=$common->get_msg("invalid_service_type",$langId)?$common->get_msg("invalid_service_type",$langId):"Invalid Service Type.";
			    $myarray['status']=0;
		       }

		 }
		 return response()->json($myarray);
	}

	/* delete vendor Sub Services */
	public function deleteVendorSubServices(Request $request) {
	 	  $common=new CommanController;
	 	  $langId=($request->header('langId'))?($request->header('langId')):1; 

	      if (!$request->venderId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId.";
		   $myarray['status']=0;
		 } elseif (!$request->vendorSubServiceId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_sub_service",$langId)?$common->get_msg("blank_sub_service",$langId):"Please select sub service Id.";
		   $myarray['status']=0;
		 } else {
		 	$vendorSubServiceId=($request->vendorSubServiceId)?($request->vendorSubServiceId):0;

		 	$servicecount=DB::select( DB::raw("select * from tblvenderservice where id IN (".$vendorSubServiceId.")"));
		 	$checkvendor = DB::table('tblvender')->where([['id', '=',$request->venderId]])->count();

            if ($checkvendor==0) {
            	$myarray['result']=array();					
			    $myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):"Invalid VendorId.";
			    $myarray['status']=0;
		 	
		 	} elseif ($servicecount) {
		 	//foreach($vendorServiceTypeId as $TypeId){
                  $delete=DB::delete('delete from tblvenderservice where id IN ('.$vendorSubServiceId.')');
		 	//}
            $sTypeId=0;      
		 	$vendorServices=$common->vendorSubServices($request->venderId,$sTypeId,$langId);
		 	$myarray['result']=$vendorServices;					
		    $myarray['message']=$common->get_msg("delete_sub_services",$langId)?$common->get_msg("delete_sub_services",$langId):"Your selected sub services has been deleted successfully.";
		    $myarray['status']=1;
		       
		       } else {
                
			 	$myarray['result']=array();					
			    $myarray['message']=$common->get_msg("invalid_sub_service_id",$langId)?$common->get_msg("invalid_sub_service_id",$langId):"Invalid vendor sub service Id.";
			    $myarray['status']=0;
		       }

		 }
		 return response()->json($myarray);
	}

	/* deleteLeadFile */

	public function deleteLeadFile(Request $request) {
	 	  $common=new CommanController;
	 	  $langId=($request->header('langId'))?($request->header('langId')):1;

	      if (!$request->leadId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_leadId",$langId)?$common->get_msg("blank_leadId",$langId):"Please select leadId.";
		   $myarray['status']=0;
		 } elseif (!$request->fileId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_fileId",$langId)?$common->get_msg("blank_fileId",$langId):"Please select File Id.";
		   $myarray['status']=0;
		 } elseif (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
		   $myarray['status']=0;
		 } else {

		       $checklead = DB::table('tbllead')->where([['id', '=',$request->leadId],['customerId','=',$request->customerId]])->count();
				  if ($checklead==0) {
		              $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("invalid_lead",$langId)?$common->get_msg("invalid_lead",$langId):"Invalid lead Id.";
				      $myarray['status']=0;
				  }  else {
                     
                     $delete=DB::delete('delete from tblleadfiles where id IN ('.$request->fileId.')');
		 	        $timezone='';
				 	$leadDetail=$common->getLeadDetail($request->leadId,$timezone,$langId);
				 	$myarray['result']=$leadDetail;					
				    $myarray['message']=$common->get_msg("delete_lead_file",$langId)?$common->get_msg("delete_lead_file",$langId):"Your selected lead files has been deleted successfully.";
				    $myarray['status']=1;
				  
				  }

			}

			return response()->json($myarray);
		
		}

		/* Language Listing */

	  public function Languages(Request $request) {
	         $langId=($request->header('langId'))?($request->header('langId')):1; 
	         $common=new CommanController;

	          $language = DB::table('language')->where([['status', '=','Active']])->get();
	             $myarray['result']=$language;
				 $myarray['message']=$common->get_msg("language_list",$langId)?$common->get_msg("language_list",$langId):"Language List.";
				 $myarray['status']=1;
			return response()->json($myarray);	 
	  }

	  /* chat Api */

	  public function chat(Request $request) {
        
        $langId=($request->header('langId'))?($request->header('langId')):1; 
	    $common=new CommanController;
        $chatType=($request->chatType)?($request->chatType):0;
        if ($chatType==0 || $chatType==1) {
			     if (!$request->leadId) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_leadId",$langId)?$common->get_msg("blank_leadId",$langId):"Please select leadId.";
				      $myarray['status']=0;
				 } elseif (!$request->customerId) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customer Id.";
				      $myarray['status']=0;
				 } elseif (!$request->vendorId) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendor Id.";
				      $myarray['status']=0;
				  } else {

				  	  $leadId=($request->leadId)?($request->leadId):0;
				  	  $customerId=($request->customerId)?($request->customerId):0;
				  	  $vendorId=($request->vendorId)?($request->vendorId):0;
				  	  $chatSessionId=($request->chatSessionId)?($request->chatSessionId):0;
				  	  $content=($request->content)?($request->content):"";
				  	  $timeZone=($request->timezone)?($request->timezone):"+5:30";
				  	  $type=($request->type)?($request->type):0;
				  	  
				  	  $lastAddedDate=($request->lastAddedDate)?($request->lastAddedDate):"";
				  	  $imageName=($request->imageName)?($request->imageName):"";
				  	  $videoName=($request->videoName)?($request->videoName):"";
				  	  $audioName=($request->audioName)?($request->audioName):"";
		              $sentBy=($request->sentBy)?($request->sentBy):1;
				  	  
				  	  $Username="";

				  	  if ($sentBy==1) {
		              $Username=$common->vendorName($vendorId);
		              }
		              if ($sentBy==2) {
		              $Username=$common->customerName($customerId);
		              }

		              $chatList=array();
			          $msgstatus="fail";

			          $ids=array();
			          $ids1=array();
			          $ids2=array();

			          if (strlen($timeZone)==5) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,1)." hour "; 
						$m=$strsign.substr($timeZone,3,2)." minutes";
						$final=$h.$m;	
					   }

					   if (strlen($timeZone)==6) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,2)." hour "; 
						$m=$strsign.substr($timeZone,4,2)." minutes";
						$final=$h.$m;	
						}
						
						if (strlen($timeZone)==5) {
						$strsign=substr($timeZone,0,1);
						
							if ($strsign=='+') {
							   $strsign='-';
							} else {
							   $strsign='+';
							}
						$h=$strsign.substr($timeZone,1,1)." hour "; 
						$m=$strsign.substr($timeZone,3,2)." minutes";
						$final1=$h.$m;	
						}

			
						if (strlen($timeZone)==6) {
							
							$strsign=substr($timeZone,0,1);
							if ($strsign=='+') {
							   $strsign='-';
							} else {
							   $strsign='+';
							}
						  $h=$strsign.substr($timeZone,1,2)." hour "; 
						  $m=$strsign.substr($timeZone,4,2)." minutes";
						  $final1=$h.$m;	
						}

			          $qry=DB::select( DB::raw("SELECT main.chatSessionId FROM `tblchatsession` as main LEFT JOIN tblchatcontent as sub ON main.chatSessionId = sub.chatSessionId WHERE (sub.customerId = '$customerId' AND sub.vendorId = '$vendorId') AND main.leadId='$leadId' AND main.chatType = '1'"));
			          if($qry) {
		                    foreach ($qry as $values) {
		                    	$chatSessionId=$values->chatSessionId;
		                    }
			          } else {

			          	session_start();
						$new_sessionid = session_id();
			          	$chatSessionId=DB::table('tblchatsession')->insertGetId(
		               ['sessionId'=>$new_sessionid,'chatType'=>1,'createdate'=>date('Y-m-d H:i:s'),'leadId'=>$leadId]);

			          }
		              $createdate=date('Y-m-d H:i:s');
		              $insertType = '1';
		              $uniqueId = uniqid();
		              
			          if($content!='' && $type==0) {
			          	 
			          	 $chatContent=DB::table('tblchatcontent')->insert(
		               ['chatSessionId'=>$chatSessionId,'customerId'=>$customerId,'createdate'=>date('Y-m-d H:i:s'),'vendorId'=>$vendorId,'content'=>$content,"senderdate"=>$createdate,"receiverdate"=>$createdate,"chatType"=>$insertType,"type"=>$type,"uniqueId"=>$uniqueId,"sentBy"=>$sentBy,'chatType'=>1]);
			          	 $flag=true;
						$message = $common->get_msg("sent_you_message")?$common->get_msg("sent_you_message"):"";	
						$message=str_replace('#username#',$Username,$message);

			          }

		                   if ($sentBy==1) {
									$sender=$vendorId;
								} else {
									$sender=$customerId;
								}

			             if($request->hasFile('Photo') && $type==1) {
							    
							    $path = public_path().'\chat\chatimages/';
			                    //echo $path.$sender;
			                    //exit;
			                    if (!is_dir($path.$sender)) 
								{
									mkdir($path.$sender,0755, true);
									//chmod(CHAT_IMG_PATH.$sender, 777);  
								}

								
		                    
		                         $ImagePath="chat/chatimages/".$sender;

								 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
							     $file = $request->file('Photo');
								 $filename = rand(1,1000000).time().$file->getClientOriginalName();
								 
								 $newFilePath = public_path()."/chat/chatimages/".$sender."/" . $filename;
					             $newFileURL = url('/')."/chat/chatimages/".$sender."/" . $filename;

		                         $extension = strtolower($file->getClientOriginalExtension());
								 $check=in_array($extension,$allowedfileExtension);
								 if($check) {
								   $destinationPath = $ImagePath;
		                           $file->move($destinationPath,$filename);
		                           
		                           $chatContent=DB::table('tblchatcontent')->insert(
		               ['chatSessionId'=>$chatSessionId,'customerId'=>$customerId,'createdate'=>date('Y-m-d H:i:s'),'vendorId'=>$vendorId,'content'=>$content,"senderdate"=>$createdate,"receiverdate"=>$createdate,"chatType"=>$insertType,"type"=>$type,"uniqueId"=>$uniqueId,"sentBy"=>$sentBy,"sd_image_name"=>$imageName,"photo"=>$sender."/".$filename,"photo_path"=>$newFilePath,"photo_url"=>$newFileURL,'chatType'=>1]);
		                            
		                            $flag=true;
						            $message = $common->get_msg("sent_you_message")?$common->get_msg("sent_you_message"):"";	
						            $message=str_replace('#username#',$Username,$message);
								 }
							 }

							 if($request->hasFile('Video') && $type==2) {
							    
							    $path = public_path().'/chat/chatvideos/';
			                    
			                    if (!is_dir($path.$sender)) 
								{
									mkdir($path.$sender,0755, true);
									//chmod(CHAT_IMG_PATH.$sender, 777);  
								}

								if ($sentBy==1) {
									$sender=$vendorId;
								} else {
									$sender=$customerId;
								}
		                    
		                         $VideoPath="chat/chatvideos/".$sender;

								 //$allowedfileExtension=['avi','mp4','png','docx','jpeg','doc','gif'];
							     $file = $request->file('Video');
								 $filename = time().$file->getClientOriginalName();
								 
								 $newFilePath = public_path()."/chat/chatvideos/".$sender."/" . $filename;
					             $newFileURL = url('/')."/chat/chatvideos/".$sender."/" . $filename;

		                         $extension = strtolower($file->getClientOriginalExtension());
								// $check=in_array($extension,$allowedfileExtension);
								// if($check) {
								   $destinationPath = $VideoPath;
		                           $file->move($destinationPath,$filename);
		                           
		                           $chatContent=DB::table('tblchatcontent')->insert(
		               ['chatSessionId'=>$chatSessionId,'customerId'=>$customerId,'createdate'=>date('Y-m-d H:i:s'),'vendorId'=>$vendorId,'content'=>$content,"senderdate"=>$createdate,"receiverdate"=>$createdate,"chatType"=>$insertType,"type"=>$type,"uniqueId"=>$uniqueId,"sentBy"=>$sentBy,"sd_image_name"=>$videoName,"photo"=>$sender."/".$filename,"photo_path"=>$newFilePath,"photo_url"=>$newFileURL,'chatType'=>1]);
		                            
		                            $flag=true;
						            $message = $common->get_msg("sent_you_message")?$common->get_msg("sent_you_message"):"";	
						            $message=str_replace('#username#',$Username,$message);
								// }
							 }

							 if($request->hasFile('Audio') && $type==3) {
							    
							    $path = public_path().'/chat/chataudios/';
			                    
			                    if (!is_dir($path.$sender)) 
								{
									mkdir($path.$sender,0755, true);
									//chmod(CHAT_IMG_PATH.$sender, 777);  
								}

								if ($sentBy==1) {
									$sender=$vendorId;
								} else {
									$sender=$customerId;
								}
		                    
		                         $VideoPath="chat/chataudios/".$sender;

								 //$allowedfileExtension=['avi','mp4','png','docx','jpeg','doc','gif'];
							     $file = $request->file('Audio');
								 $filename = time().$file->getClientOriginalName();
								 
								 $newFilePath = public_path()."/chat/chataudios/".$sender."/" . $filename;
					             $newFileURL = url('/')."/chat/chataudios/".$sender."/" . $filename;

		                         $extension = strtolower($file->getClientOriginalExtension());
								// $check=in_array($extension,$allowedfileExtension);
								// if($check) {
								   $destinationPath = $VideoPath;
		                           $file->move($destinationPath,$filename);
		                           
		                           $chatContent=DB::table('tblchatcontent')->insert(
		               ['chatSessionId'=>$chatSessionId,'customerId'=>$customerId,'createdate'=>date('Y-m-d H:i:s'),'vendorId'=>$vendorId,'content'=>$content,"senderdate"=>$createdate,"receiverdate"=>$createdate,"chatType"=>$insertType,"type"=>$type,"uniqueId"=>$uniqueId,"sentBy"=>$sentBy,"sd_image_name"=>$audioName,"photo"=>$sender."/".$filename,"photo_path"=>$newFilePath,"photo_url"=>$newFileURL,'chatType'=>1]);
		                            
		                            $flag=true;
						            $message = $common->get_msg("sent_you_message")?$common->get_msg("sent_you_message"):"";
						            $message=str_replace('#username#',$Username,$message);
								// }
							 }

							 $lastAdd="";
							if ($lastAddedDate!='') {
								//$lastAdd=" AND createdate > '$lastAddedDate'";
								$temp1= strtotime("$lastAddedDate $final1");
								$lastAddedDate= date("Y-m-d H:i:s", $temp1);
								$lastAdd=" AND createdate > '$lastAddedDate'";
							} else{
								$lastAdd="";
							}

							 $res_qry=DB::select( DB::raw("SELECT id FROM tblchatcontent WHERE chatSessionId='$chatSessionId' AND ((customerId=$customerId AND issenderdelete='0') OR (vendorId=$vendorId AND isreceiverdelete='0')) group by uniqueId order by id  desc"));
							 $res_count=count($res_qry);		
								$isPaging=0; 
								if ($res_count>500) {  
								$isPaging=1; 
								}
						     $qry_content=DB::select( DB::raw("SELECT *  FROM (SELECT * FROM tblchatcontent WHERE chatSessionId='$chatSessionId' AND ((customerId=$customerId AND issenderdelete='0') OR (vendorId=$vendorId AND isreceiverdelete='0')) $lastAdd group by uniqueId order by id  desc limit 0, 500) temp ORDER BY id desc LIMIT 1"));
						     $chatList=array();	

						     foreach ($qry_content as  $value) {
		                                
		                                $createdate=$value->createdate;
									$temp= strtotime("$createdate $final");
									
									$temputc= strtotime("$createdate");
									
									$createDate = date('Y-m-d',$temp);
									/* $createTime= date("H:i:s", $temp); */
									$createTime= date("H:i:s", $temp);
									
									$createDateUtc = date('Y-m-d',$temputc);
								    $createTimeUtc= date("H:i:s", $temputc);
									
									$receiverdate=$value->receiverdate;
									if(isset($receiverdate)){
										$temp1= strtotime("$receiverdate $final");
										$receiverdate= date("Y-m-d H:i:s", $temp1);
									}
									if($receiverdate==null){ $receiverdate=''; }
									
									$msgstatus="Success";
									$status_array="Success";
									$cutomerName=$common->customerName($value->customerId);
									$cutomerUrl=$common->customerProfilePic($value->customerId);
									$vendorName=$common->vendorName($value->vendorId);
									$vendorUrl=$common->vendorProfilePic($value->vendorId);
									//echo  '<pre>'; print_r($value);
									//exit;
						     	     $chatList[]=array("chatContentId"=>intval($value->id),"content"=>utf8_encode($value->content),"imageName"=>utf8_encode($value->sd_image_name),"url"=>utf8_encode($value->photo_url),"createDate"=>$createDate,"createTime"=>$createTime,"vendorId"=>intval($value->vendorId),"vendorName"=>utf8_encode($vendorName),"vendorUrl"=>utf8_encode($vendorUrl),"customerId"=>intval($value->customerId),"customerName"=>utf8_encode($cutomerName),"customerUrl"=>utf8_encode($cutomerUrl),"isRead"=>intval($value->IsRead),"receiverDate"=>$receiverdate,"type"=>(int)$value->type,"chatType"=>(int)$value->chatType,"createDateUtc"=>$createDateUtc,"createTimeUtc"=>$createTimeUtc,"sentBy"=>$value->sentBy);
						     			# code...
						     		}	

						     		$qry_getlast=DB::select( DB::raw("SELECT createdate FROM tblchatcontent WHERE chatsessionid = '$chatSessionId' ORDER BY id DESC LIMIT 0 , 1"));	
						     		if ($qry_getlast) {
						     			foreach ($qry_getlast as  $value) {
						     				$row_createdate=$value->createdate;
						     			}
						     		}

						     		$lastAddedDate=$row_createdate;
									if(isset($lastAddedDate)){
										$temp1= strtotime("$lastAddedDate $final");
										$lastAddedDate= date("Y-m-d H:i:s", $temp1);
									}
								    if($lastAddedDate==null){ $lastAddedDate=''; }
									
									$newDate='';
									if ($lastAddedDate!='') {
									  $newDate=$row_createdate;
									}

									if($content!='') {

                                         $nomsg=$common->get_msg("chat_message",$langId)? ($common->get_msg("chat_message",$langId)):'#name has been sent message to you.';
                                         if ($sentBy==1) {
                                           $receiverName=$vendorName;	
                                         }
                                         if ($sentBy==2) {
                                           $receiverName=$cutomerName;	
                                         }
                                         $nomsgreplace=str_replace("#name",$receiverName,$nomsg);
				                         $Description=$nomsgreplace;
                                          
                                          $device='';
                                         if ($sentBy==1) {
                                            /*$NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>$vendorId,'notifiedUserId'=>$customerId,'notificationType'=>1,'notification'=>$Description,'createdDate'=>date('Y-m-d H:i:s'),'SendBy'=>$sentBy,'leadId'=>$leadId]);*/
                                             $NotificationID=0;
                                            $total_count=$common->lead_count_chat_notification($vendorId);
                                            $device=$common->getCustomerDeviceDetail($customerId);
                                         }

                                         if ($sentBy==2) {
                                         	 /*$NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>$customerId,'notifiedUserId'=>$vendorId,'notificationType'=>1,'notification'=>$Description,'createdDate'=>date('Y-m-d H:i:s'),'SendBy'=>$sentBy,'leadId'=>$leadId]);*/
                                             $NotificationID=0;
                                         	 $total_count=$common->lead_count_chat_notification($customerId);
                                         	 $device=$common->getVendorDeviceDetail($vendorId);

                                         }

                                         if ($device) {
                                         	//echo '<pre>'; print_r($device);
                                         	//exit;
                                         	 $deviceType=$device->deviceType;
						                    $deviceToken=$device->deviceToken;
						                    
						                    $cutomerName=$common->customerName($customerId);
									$cutomerUrl=$common->customerProfilePic($customerId);
									$vendorName=$common->vendorName($vendorId);
									$vendorUrl=$common->vendorProfilePic($vendorId);

						                    if($deviceType==1) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>1,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$vendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                	if ($sentBy==1) {
                                                	$common->firebasepushCustomer($ExtraInfo,array($deviceToken));
                                                    } else {
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);	
                                                    }
                                                }
						                    }

						                    if($deviceType==2) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>1,'message'=>$Description,'title'=>$Description,'NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$vendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                	
                                                	$body['aps'] = array('alert' => $Description, 'sound' => 'default', 'badge' => $total_count, 'content-available' => 1,'NotificationType'=>1,'notificationId'=>$NotificationID,'customerId'=>$customerId,'vendorId'=>$vendorId,'leadId'=>$leadId,'productId'=>0,'cutomerName'=>$cutomerName,'cutomerUrl'=>$cutomerUrl,'vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                	if ($sentBy==1) {
                                                	$common->iPhonePushBookCustomer(array($deviceToken),$body);
                                                    } else {
                                                     $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);	
                                                    }
                                                }
						                    }

                                         }

									}

									$my_array=array("chatList"=>$chatList, "chatSessionId"=>intval($chatSessionId), "isPaging" =>1, 'lastAddedDate'=>$lastAddedDate,"newDate"=>$newDate);
		                           $status_array=1;
		                        $myarray['result']=$my_array;					
					    $myarray['message']="Chat List";
					    $myarray['status']=1;

				  }

		  } else {

		  	   if (!$request->productId) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_product_id",$langId)?$common->get_msg("blank_product_id",$langId):"Please select product Id.";
				      $myarray['status']=0;
				 } elseif (!$request->sender) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customer Id.";
				      $myarray['status']=0;
				 } elseif (!$request->receiver) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customer Id.";
				      $myarray['status']=0;
				  } else {

				  	  $productId=($request->productId)?($request->productId):0;
				  	  $sender=($request->sender)?($request->sender):0;
				  	  $receiver=($request->receiver)?($request->receiver):0;
				  	  $chatSessionId=($request->chatSessionId)?($request->chatSessionId):0;
				  	  $content=($request->content)?($request->content):"";
				  	  $timeZone=($request->timezone)?($request->timezone):"+5:30";
				  	  $type=($request->type)?($request->type):0;
				  	  
				  	  $lastAddedDate=($request->lastAddedDate)?($request->lastAddedDate):"";
				  	  $imageName=($request->imageName)?($request->imageName):"";
				  	  $videoName=($request->videoName)?($request->videoName):"";
				  	  $audioName=($request->audioName)?($request->audioName):"";
		              $sentBy=($request->sentBy)?($request->sentBy):1;
				  	  
				  	  $Username="";

				  	 
		              $Username=$common->customerName($sender);
		             
		              $chatList=array();
			          $msgstatus="fail";

			          $ids=array();
			          $ids1=array();
			          $ids2=array();

			          if (strlen($timeZone)==5) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,1)." hour "; 
						$m=$strsign.substr($timeZone,3,2)." minutes";
						$final=$h.$m;	
					   }

					   if (strlen($timeZone)==6) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,2)." hour "; 
						$m=$strsign.substr($timeZone,4,2)." minutes";
						$final=$h.$m;	
						}
						
						if (strlen($timeZone)==5) {
						$strsign=substr($timeZone,0,1);
						
							if ($strsign=='+') {
							   $strsign='-';
							} else {
							   $strsign='+';
							}
						$h=$strsign.substr($timeZone,1,1)." hour "; 
						$m=$strsign.substr($timeZone,3,2)." minutes";
						$final1=$h.$m;	
						}

			
						if (strlen($timeZone)==6) {
							
							$strsign=substr($timeZone,0,1);
							if ($strsign=='+') {
							   $strsign='-';
							} else {
							   $strsign='+';
							}
						  $h=$strsign.substr($timeZone,1,2)." hour "; 
						  $m=$strsign.substr($timeZone,4,2)." minutes";
						  $final1=$h.$m;	
						}

			          $qry=DB::select( DB::raw("SELECT main.chatSessionId FROM `tblchatsession` as main LEFT JOIN tblchatcontent as sub ON main.chatSessionId = sub.chatSessionId WHERE (sub.sender = '$sender' OR sub.receiver = '$sender') AND (sub.sender = '$receiver' OR sub.receiver = '$receiver') AND main.productId='$productId' AND main.chatType = '2'"));
			          if($qry) {
		                    foreach ($qry as $values) {
		                    	$chatSessionId=$values->chatSessionId;
		                    }
			          } else {

			          	session_start();
						$new_sessionid = session_id();
			          	$chatSessionId=DB::table('tblchatsession')->insertGetId(
		               ['sessionId'=>$new_sessionid,'chatType'=>2,'createdate'=>date('Y-m-d H:i:s'),'productId'=>$productId]);

			          }
		              $createdate=date('Y-m-d H:i:s');
		              $insertType = '1';
		              $uniqueId = uniqid();
		              
			          if($content!='' && $type==0) {
			          	 
			          	 $chatContent=DB::table('tblchatcontent')->insert(
		               ['chatSessionId'=>$chatSessionId,'sender'=>$sender,'createdate'=>date('Y-m-d H:i:s'),'receiver'=>$receiver,'content'=>$content,"senderdate"=>$createdate,"receiverdate"=>$createdate,"chatType"=>$insertType,"type"=>$type,"uniqueId"=>$uniqueId,"sentBy"=>$sentBy,'chatType'=>2]);
			          	 $flag=true;
						$message = $common->get_msg("sent_you_message")?$common->get_msg("sent_you_message"):"";	
						$message=str_replace('#username#',$Username,$message);

			          }

		                   

			             if($request->hasFile('Photo') && $type==1) {
							    
							    $path = public_path().'\chat\chatimages/';
			                    //echo $path.$sender;
			                    //exit;
			                    if (!is_dir($path.$sender)) 
								{
									mkdir($path.$sender,0755, true);
									//chmod(CHAT_IMG_PATH.$sender, 777);  
								}

								
		                    
		                         $ImagePath="chat/chatimages/".$sender;

								 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
							     $file = $request->file('Photo');
								 $filename = rand(10,100000).time().$file->getClientOriginalName();
								 
								 $newFilePath = public_path()."/chat/chatimages/".$sender."/" . $filename;
					             $newFileURL = url('/')."/chat/chatimages/".$sender."/" . $filename;

		                         $extension = strtolower($file->getClientOriginalExtension());
								 $check=in_array($extension,$allowedfileExtension);
								 if($check) {
								   $destinationPath = $ImagePath;
		                           $file->move($destinationPath,$filename);
		                           
		                           $chatContent=DB::table('tblchatcontent')->insert(
		               ['chatSessionId'=>$chatSessionId,'sender'=>$sender,'createdate'=>date('Y-m-d H:i:s'),'receiver'=>$receiver,'content'=>$content,"senderdate"=>$createdate,"receiverdate"=>$createdate,"chatType"=>$insertType,"type"=>$type,"uniqueId"=>$uniqueId,"sentBy"=>$sentBy,"sd_image_name"=>$imageName,"photo"=>$sender."/".$filename,"photo_path"=>$newFilePath,"photo_url"=>$newFileURL,'chatType'=>2]);
		                            
		                            $flag=true;
						            $message = $common->get_msg("sent_you_message")?$common->get_msg("sent_you_message"):"";	
						            $message=str_replace('#username#',$Username,$message);
								 }
							 }

							 if($request->hasFile('Video') && $type==2) {
							    
							    $path = public_path().'/chat/chatvideos/';
			                    
			                    if (!is_dir($path.$sender)) 
								{
									mkdir($path.$sender,0755, true);
									//chmod(CHAT_IMG_PATH.$sender, 777);  
								}

								/*if ($sentBy==1) {
									$sender=$vendorId;
								} else {
									$sender=$customerId;
								}*/
		                    
		                         $VideoPath="chat/chatvideos/".$sender;

								 //$allowedfileExtension=['avi','mp4','png','docx','jpeg','doc','gif'];
							     $file = $request->file('Video');
								 $filename = time().$file->getClientOriginalName();
								 
								 $newFilePath = public_path()."/chat/chatvideos/".$sender."/" . $filename;
					             $newFileURL = url('/')."/chat/chatvideos/".$sender."/" . $filename;

		                         $extension = strtolower($file->getClientOriginalExtension());
								// $check=in_array($extension,$allowedfileExtension);
								// if($check) {
								   $destinationPath = $VideoPath;
		                           $file->move($destinationPath,$filename);
		                           
		                           $chatContent=DB::table('tblchatcontent')->insert(
		               ['chatSessionId'=>$chatSessionId,'sender'=>$sender,'createdate'=>date('Y-m-d H:i:s'),'receiver'=>$receiver,'content'=>$content,"senderdate"=>$createdate,"receiverdate"=>$createdate,"chatType"=>$insertType,"type"=>$type,"uniqueId"=>$uniqueId,"sentBy"=>$sentBy,"sd_image_name"=>$videoName,"photo"=>$sender."/".$filename,"photo_path"=>$newFilePath,"photo_url"=>$newFileURL,'chatType'=>2]);
		                            
		                            $flag=true;
						            $message = $common->get_msg("sent_you_message")?$common->get_msg("sent_you_message"):"";	
						            $message=str_replace('#username#',$Username,$message);
								// }
							 }

							 if($request->hasFile('Audio') && $type==3) {
							    
							    $path = public_path().'/chat/chataudios/';
			                    
			                    if (!is_dir($path.$sender)) 
								{
									mkdir($path.$sender,0755, true);
									//chmod(CHAT_IMG_PATH.$sender, 777);  
								}

								
		                    
		                         $VideoPath="chat/chataudios/".$sender;

								 //$allowedfileExtension=['avi','mp4','png','docx','jpeg','doc','gif'];
							     $file = $request->file('Audio');
								 $filename = time().$file->getClientOriginalName();
								 
								 $newFilePath = public_path()."/chat/chataudios/".$sender."/" . $filename;
					             $newFileURL = url('/')."/chat/chataudios/".$sender."/" . $filename;

		                         $extension = strtolower($file->getClientOriginalExtension());
								 //$check=in_array($extension,$allowedfileExtension);
								// if($check) {
								   $destinationPath = $VideoPath;
		                           $file->move($destinationPath,$filename);
		                           
		                           $chatContent=DB::table('tblchatcontent')->insert(
		               ['chatSessionId'=>$chatSessionId,'sender'=>$sender,'createdate'=>date('Y-m-d H:i:s'),'receiver'=>$receiver,'content'=>$content,"senderdate"=>$createdate,"receiverdate"=>$createdate,"chatType"=>$insertType,"type"=>$type,"uniqueId"=>$uniqueId,"sentBy"=>$sentBy,"sd_image_name"=>$audioName,"photo"=>$sender."/".$filename,"photo_path"=>$newFilePath,"photo_url"=>$newFileURL,'chatType'=>2]);
		                            
		                            $flag=true;
						            $message = $common->get_msg("sent_you_message")?$common->get_msg("sent_you_message"):"";
						            $message=str_replace('#username#',$Username,$message);
								// }
							 }

							 $lastAdd="";
							if ($lastAddedDate!='') {
								//$lastAdd=" AND createdate > '$lastAddedDate'";
								$temp1= strtotime("$lastAddedDate $final1");
								$lastAddedDate= date("Y-m-d H:i:s", $temp1);
								$lastAdd=" AND createdate > '$lastAddedDate'";
							} else{
								$lastAdd="";
							}

							 $res_qry=DB::select( DB::raw("SELECT id FROM tblchatcontent WHERE chatSessionId='$chatSessionId' AND ((sender=$sender AND issenderdelete='0') OR (receiver=$sender AND isreceiverdelete='0')) group by uniqueId order by id  desc"));
							 $res_count=count($res_qry);		
								$isPaging=0; 
								if ($res_count>500) {  
								$isPaging=1; 
								}
						     $qry_content=DB::select( DB::raw("SELECT *  FROM (SELECT * FROM tblchatcontent WHERE chatSessionId='$chatSessionId' AND ((sender=$sender AND issenderdelete='0') OR (receiver=$sender AND isreceiverdelete='0')) $lastAdd group by uniqueId order by id  desc limit 0, 500) temp ORDER BY id desc LIMIT 1"));
						     $chatList=array();	

						     foreach ($qry_content as  $value) {
		                                
		                                $createdate=$value->createdate;
									$temp= strtotime("$createdate $final");
									
									$temputc= strtotime("$createdate");
									
									$createDate = date('Y-m-d',$temp);
									/* $createTime= date("H:i:s", $temp); */
									$createTime= date("H:i:s", $temp);
									
									$createDateUtc = date('Y-m-d',$temputc);
								    $createTimeUtc= date("H:i:s", $temputc);
									
									$receiverdate=$value->receiverdate;
									if(isset($receiverdate)){
										$temp1= strtotime("$receiverdate $final");
										$receiverdate= date("Y-m-d H:i:s", $temp1);
									}
									if($receiverdate==null){ $receiverdate=''; }
									
									$msgstatus="Success";
									$status_array="Success";
									$senderName=$common->customerName($value->sender);
									$senderUrl=$common->customerProfilePic($value->sender);
									$receiverName=$common->customerName($value->receiver);
									$receiverUrl=$common->customerProfilePic($value->receiver);
									//echo  '<pre>'; print_r($value);
									//exit;
						     	     $chatList[]=array("chatContentId"=>intval($value->id),"content"=>utf8_encode($value->content),"imageName"=>utf8_encode($value->sd_image_name),"url"=>utf8_encode($value->photo_url),"createDate"=>$createDate,"createTime"=>$createTime,"sender"=>intval($value->sender),"senderName"=>utf8_encode($senderName),"senderUrl"=>utf8_encode($senderUrl),"receiver"=>intval($value->receiver),"receiverName"=>utf8_encode($receiverName),"receiverUrl"=>utf8_encode($receiverUrl),"isRead"=>intval($value->IsRead),"receiverDate"=>$receiverdate,"type"=>(int)$value->type,"chatType"=>(int)$value->chatType,"createDateUtc"=>$createDateUtc,"createTimeUtc"=>$createTimeUtc,"sentBy"=>$value->sentBy);
						     			# code...
						     		}	

						     		$qry_getlast=DB::select( DB::raw("SELECT createdate FROM tblchatcontent WHERE chatsessionid = '$chatSessionId' ORDER BY id DESC LIMIT 0 , 1"));	
						     		if ($qry_getlast) {
						     			foreach ($qry_getlast as  $value) {
						     				$row_createdate=$value->createdate;
						     			}
						     		}

						     		$lastAddedDate=$row_createdate;
									if(isset($lastAddedDate)){
										$temp1= strtotime("$lastAddedDate $final");
										$lastAddedDate= date("Y-m-d H:i:s", $temp1);
									}
								    if($lastAddedDate==null){ $lastAddedDate=''; }
									
									$newDate='';
									if ($lastAddedDate!='') {
									  $newDate=$row_createdate;
									}

									if($content!='') {

                                         $nomsg=$common->get_msg("chat_message",$langId)? ($common->get_msg("chat_message",$langId)):'#name has been sent message to you.';
                                         $receiverName=$common->customerName($sender);
                                         $nomsgreplace=str_replace("#name",$receiverName,$nomsg);
				                         $Description=$nomsgreplace;

                                         /*$NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>$sender,'notifiedUserId'=>$receiver,'notificationType'=>2,'notification'=>$Description,'createdDate'=>date('Y-m-d H:i:s'),'SendBy'=>0,'productId'=>$productId]);*/
                                          $NotificationID=0;

                                         $total_count=$common->product_count_chat_notification($sender);
                                         $device=$common->getCustomerDeviceDetail($receiver);

                                         if ($device) {
                                         	 $deviceType=$device->deviceType;
						                     $deviceToken=$device->deviceToken;

						                     	$senderName=$common->customerName($sender);
												$senderUrl=$common->customerProfilePic($sender);
												$receiverName=$common->customerName($receiver);
												$receiverUrl=$common->customerProfilePic($receiver);
								
						                    if($deviceType==1) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>2,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>1,'notificationId'=>$NotificationID,'sender'=>$sender,'receiver'=>$receiver,'icon'=>'myicon','sound'=>'mySound','leadId'=>0,'productId'=>$productId,'senderName'=>$senderName,'senderUrl'=>$senderUrl,'receiverName'=>$receiverName,'receiverUrl'=>$receiverUrl);
                                                	
                                                	$common->firebasepushCustomer($ExtraInfo,array($deviceToken));
                                                }
						                    }

						                    if($deviceType==2) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$body['aps'] = array('alert' => $Description, 'sound' => 'default', 'badge' => $total_count, 'content-available' => 1,'NotificationType'=>2,'notificationId'=>$NotificationID,'sender'=>$sender,'receiver'=>$receiver,'leadId'=>0,'productId'=>$productId,'senderName'=>$senderName,'senderUrl'=>$senderUrl,'receiverName'=>$receiverName,'receiverUrl'=>$receiverUrl);
                                                	
                                                	$common->iPhonePushBookCustomer(array($deviceToken),$body);
                                                    
                                                }
						                    }

                                         }
                                         
									}

									$my_array=array("chatList"=>$chatList, "chatSessionId"=>intval($chatSessionId), "isPaging" =>1, 'lastAddedDate'=>$lastAddedDate,"newDate"=>$newDate);
		                           $status_array=1;
		                        $myarray['result']=$my_array;					
					    $myarray['message']="Chat List";
					    $myarray['status']=1;

				  }
		  }
		  return response()->json($myarray);

	  }

	   /* chat Api */

	  public function chatList(Request $request) {
        
        $langId=($request->header('langId'))?($request->header('langId')):1; 
	    $common=new CommanController;
        $chatType=($request->chatType)?($request->chatType):0;
        if ($chatType==0 || $chatType==1) {
			     if (!$request->leadId) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_leadId",$langId)?$common->get_msg("blank_leadId",$langId):"Please select leadId.";
				      $myarray['status']=0;
				 } elseif (!$request->customerId) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customer Id.";
				      $myarray['status']=0;
				 } elseif (!$request->vendorId) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendor Id.";
				      $myarray['status']=0;
				  } else {

				  	  $leadId=($request->leadId)?($request->leadId):0;
				  	  $customerId=($request->customerId)?($request->customerId):0;
				  	  $vendorId=($request->vendorId)?($request->vendorId):0;
				  	  $chatSessionId=($request->chatSessionId)?($request->chatSessionId):0;
				  	  $content=($request->content)?($request->content):"";
				  	  $timeZone=($request->timezone)?($request->timezone):"+5:30";
				  	  $type=($request->type)?($request->type):0;
				  	  
				  	  $lastAddedDate=($request->lastAddedDate)?($request->lastAddedDate):"";
				  	 
		              $sentBy=($request->sentBy)?($request->sentBy):1;
				  	  
				  	  $Username="";

				  	  if ($sentBy==1) {
		              $Username=$common->vendorName($vendorId);
		              }
		              if ($sentBy==2) {
		              $Username=$common->customerName($customerId);
		              }

		              $chatList=array();
			          $msgstatus="fail";

			          $ids=array();
			          $ids1=array();
			          $ids2=array();

			          if (strlen($timeZone)==5) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,1)." hour "; 
						$m=$strsign.substr($timeZone,3,2)." minutes";
						$final=$h.$m;	
					   }

					   if (strlen($timeZone)==6) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,2)." hour "; 
						$m=$strsign.substr($timeZone,4,2)." minutes";
						$final=$h.$m;	
						}
						
						if (strlen($timeZone)==5) {
						$strsign=substr($timeZone,0,1);
						
							if ($strsign=='+') {
							   $strsign='-';
							} else {
							   $strsign='+';
							}
						$h=$strsign.substr($timeZone,1,1)." hour "; 
						$m=$strsign.substr($timeZone,3,2)." minutes";
						$final1=$h.$m;	
						}

			
						if (strlen($timeZone)==6) {
							
							$strsign=substr($timeZone,0,1);
							if ($strsign=='+') {
							   $strsign='-';
							} else {
							   $strsign='+';
							}
						  $h=$strsign.substr($timeZone,1,2)." hour "; 
						  $m=$strsign.substr($timeZone,4,2)." minutes";
						  $final1=$h.$m;	
						}

			          $qry=DB::select( DB::raw("SELECT main.chatSessionId FROM `tblchatsession` as main LEFT JOIN tblchatcontent as sub ON main.chatSessionId = sub.chatSessionId WHERE (sub.customerId = '$customerId' OR sub.vendorId = '$vendorId') AND main.leadId='$leadId' AND main.chatType = '1'"));
			          if($qry) {
		                    foreach ($qry as $values) {
		                    	$chatSessionId=$values->chatSessionId;
		                    }
			          }

		              $createdate=date('Y-m-d H:i:s');
		              $res_qry=DB::select( DB::raw("SELECT id FROM tblchatcontent WHERE chatSessionId='$chatSessionId' AND ((customerId=$customerId AND issenderdelete='0') OR (vendorId=$vendorId AND isreceiverdelete='0')) group by uniqueId order by id  desc"));
			          $res_count=count($res_qry);
			          $isPaging=0; 
			          
			          if ($res_count>500) {  
			          	$isPaging=1; 
			          }
		               
		               if ($sentBy==1) {
		               	   $sentByflag=2;
		               }

		               if ($sentBy==2) {
		               	   $sentByflag=1;
		               }

			          $qry2=DB::select( DB::raw("UPDATE tblchatcontent
		       JOIN tblchatsession
		       ON tblchatcontent.chatSessionId = tblchatsession.chatSessionId
		       SET  IsRead='1' where (tblchatcontent.customerId=$customerId) and (tblchatcontent.vendorId=$vendorId) and  tblchatcontent.chatType=1 and tblchatcontent.sentBy='$sentByflag' and tblchatsession.leadId='$leadId'"));
			          
		                   if ($sentBy==1) {
									$sender=$vendorId;
								} else {
									$sender=$customerId;
								}

			             

							 $lastAdd="";
							if ($lastAddedDate!='') {
								//$lastAdd=" AND createdate > '$lastAddedDate'";
								$temp1= strtotime("$lastAddedDate $final1");
								$lastAddedDate= date("Y-m-d H:i:s", $temp1);
								$lastAdd=" AND createdate > '$lastAddedDate'";
							} else{
								$lastAdd="";
							}
		                    

							 
						     $qry_content=DB::select( DB::raw("SELECT *  FROM (SELECT * FROM tblchatcontent WHERE chatSessionId='$chatSessionId' AND ((customerId=$customerId AND issenderdelete='0') AND (vendorId=$vendorId AND isreceiverdelete='0')) $lastAdd group by uniqueId order by id  desc limit 0, 500) temp ORDER BY id ASC"));
						     $chatList=array();	

						     foreach ($qry_content as  $value) {
		                                
		                                $createdate=$value->createdate;
									$temp= strtotime("$createdate $final");
									
									$temputc= strtotime("$createdate");
									
									$createDate = date('Y-m-d',$temp);
									/* $createTime= date("H:i:s", $temp); */
									$createTime= date("H:i:s", $temp);
									
									$createDateUtc = date('Y-m-d',$temputc);
								    $createTimeUtc= date("H:i:s", $temputc);
									
									$receiverdate=$value->receiverdate;
									if(isset($receiverdate)){
										$temp1= strtotime("$receiverdate $final");
										$receiverdate= date("Y-m-d H:i:s", $temp1);
									}
									if($receiverdate==null){ $receiverdate=''; }
									
									$msgstatus="Success";
									$status_array="Success";
									$cutomerName=$common->customerName($value->customerId);
									$cutomerUrl=$common->customerProfilePic($value->customerId);
									$vendorName=$common->vendorName($value->vendorId);
									$vendorUrl=$common->vendorProfilePic($value->vendorId);
									//echo  '<pre>'; print_r($value);
									//exit;
						     	     $chatList[]=array("chatContentId"=>intval($value->id),"content"=>utf8_encode($value->content),"imageName"=>utf8_encode($value->sd_image_name),"url"=>utf8_encode($value->photo_url),"createDate"=>$createDate,"createTime"=>$createTime,"vendorId"=>intval($value->vendorId),"vendorName"=>utf8_encode($vendorName),"vendorUrl"=>utf8_encode($vendorUrl),"customerId"=>intval($value->customerId),"customerName"=>utf8_encode($cutomerName),"customerUrl"=>utf8_encode($cutomerUrl),"isRead"=>intval($value->IsRead),"receiverDate"=>$receiverdate,"type"=>(int)$value->type,"chatType"=>(int)$value->chatType,"createDateUtc"=>$createDateUtc,"createTimeUtc"=>$createTimeUtc,"sentBy"=>$value->sentBy);
						     			# code...
						     		}	

						     		$qry_getlast=DB::select( DB::raw("SELECT createdate FROM tblchatcontent WHERE chatsessionid = '$chatSessionId' ORDER BY id DESC LIMIT 0 , 1"));	
						     		$row_createdate='';
						     		if ($qry_getlast) {
						     			foreach ($qry_getlast as  $value) {
						     				$row_createdate=$value->createdate;
						     			}
						     		}

						     		$lastAddedDate=$row_createdate;
									if(isset($lastAddedDate)){
										$temp1= strtotime("$lastAddedDate $final");
										$lastAddedDate= date("Y-m-d H:i:s", $temp1);
									}
								    if($lastAddedDate==null){ $lastAddedDate=''; }
									
									$newDate='';
									if ($lastAddedDate!='') {
									  $newDate=$row_createdate;
									}

									$my_array=array("chatList"=>$chatList, "chatSessionId"=>intval($chatSessionId), "isPaging" =>$isPaging, 'lastAddedDate'=>$lastAddedDate,"newDate"=>$newDate);
		                           $status_array=1;
		                        $myarray['result']=$my_array;					
					    $myarray['message']="Chat List";
					    $myarray['status']=1;

				  }
				  
			} else {

				 if (!$request->productId) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_product_id",$langId)?$common->get_msg("blank_product_id",$langId):"Please select product Id.";
				      $myarray['status']=0;
				 } elseif (!$request->sender) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customer Id.";
				      $myarray['status']=0;
				 } elseif (!$request->receiver) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select leadId.";
				      $myarray['status']=0;
				  } else {

				  	  $productId=($request->productId)?($request->productId):0;
				  	  $sender=($request->sender)?($request->sender):0;
				  	  $receiver=($request->receiver)?($request->receiver):0;
				  	  $chatSessionId=($request->chatSessionId)?($request->chatSessionId):0;
				  	  $content=($request->content)?($request->content):"";
				  	  $timeZone=($request->timezone)?($request->timezone):"+5:30";
				  	  $type=($request->type)?($request->type):0;
				  	  
				  	  $lastAddedDate=($request->lastAddedDate)?($request->lastAddedDate):"";
				  	 
		              $sentBy=($request->sentBy)?($request->sentBy):1;
				  	  
				  	  $Username="";

				  	  
		              $Username=$common->customerName($sender);
		              

		              $chatList=array();
			          $msgstatus="fail";

			          $ids=array();
			          $ids1=array();
			          $ids2=array();

			          if (strlen($timeZone)==5) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,1)." hour "; 
						$m=$strsign.substr($timeZone,3,2)." minutes";
						$final=$h.$m;	
					   }

					   if (strlen($timeZone)==6) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,2)." hour "; 
						$m=$strsign.substr($timeZone,4,2)." minutes";
						$final=$h.$m;	
						}
						
						if (strlen($timeZone)==5) {
						$strsign=substr($timeZone,0,1);
						
							if ($strsign=='+') {
							   $strsign='-';
							} else {
							   $strsign='+';
							}
						$h=$strsign.substr($timeZone,1,1)." hour "; 
						$m=$strsign.substr($timeZone,3,2)." minutes";
						$final1=$h.$m;	
						}

			
						if (strlen($timeZone)==6) {
							
							$strsign=substr($timeZone,0,1);
							if ($strsign=='+') {
							   $strsign='-';
							} else {
							   $strsign='+';
							}
						  $h=$strsign.substr($timeZone,1,2)." hour "; 
						  $m=$strsign.substr($timeZone,4,2)." minutes";
						  $final1=$h.$m;	
						}
                     

			          $qry=DB::select( DB::raw("SELECT main.chatSessionId FROM `tblchatsession` as main LEFT JOIN tblchatcontent as sub ON main.chatSessionId = sub.chatSessionId WHERE (sub.sender = '$sender' OR sub.receiver = '$sender') AND (sub.sender = '$receiver' OR sub.receiver = '$receiver') AND main.productId='$productId' AND main.chatType = '2'"));
			          if($qry) {
		                    foreach ($qry as $values) {
		                    	$chatSessionId=$values->chatSessionId;
		                    }
			          }


		              $createdate=date('Y-m-d H:i:s');
		              $res_qry=DB::select( DB::raw("SELECT id FROM tblchatcontent WHERE chatSessionId='$chatSessionId' AND ((sender=$sender AND issenderdelete='0') OR (receiver=$receiver AND isreceiverdelete='0')) group by uniqueId order by id  desc"));
			          $res_count=count($res_qry);
			          $isPaging=0; 
			          
			          if ($res_count>500) {  
			          	$isPaging=1; 
			          }
		               
		               

			          $qry2=DB::select( DB::raw("UPDATE tblchatcontent
		       JOIN tblchatsession
		       ON tblchatcontent.chatSessionId = tblchatsession.chatSessionId
		       SET  IsRead='1' where (tblchatcontent.sender=$receiver) and (tblchatcontent.receiver=$sender) and  tblchatcontent.chatType=2 and tblchatsession.productId='$productId'"));
			          
		                   
			             

							 $lastAdd="";
							if ($lastAddedDate!='') {
								//$lastAdd=" AND createdate > '$lastAddedDate'";
								$temp1= strtotime("$lastAddedDate $final1");
								$lastAddedDate= date("Y-m-d H:i:s", $temp1);
								$lastAdd=" AND createdate > '$lastAddedDate'";
							} else{
								$lastAdd="";
							}
		                    


						     $qry_content=DB::select( DB::raw("SELECT *  FROM (SELECT * FROM tblchatcontent WHERE chatSessionId='$chatSessionId' AND ((sender=$sender AND issenderdelete='0') OR (receiver=$sender AND isreceiverdelete='0')) $lastAdd group by uniqueId order by id  desc limit 0, 500) temp ORDER BY id ASC"));
						     $chatList=array();	

						     foreach ($qry_content as  $value) {
		                                
		                                $createdate=$value->createdate;
									$temp= strtotime("$createdate $final");
									
									$temputc= strtotime("$createdate");
									
									$createDate = date('Y-m-d',$temp);
									/* $createTime= date("H:i:s", $temp); */
									$createTime= date("H:i:s", $temp);
									
									$createDateUtc = date('Y-m-d',$temputc);
								    $createTimeUtc= date("H:i:s", $temputc);
									
									$receiverdate=$value->receiverdate;
									if(isset($receiverdate)){
										$temp1= strtotime("$receiverdate $final");
										$receiverdate= date("Y-m-d H:i:s", $temp1);
									}
									if($receiverdate==null){ $receiverdate=''; }
									
									$msgstatus="Success";
									$status_array="Success";
									$senderName=$common->customerName($value->sender);
									$senderUrl=$common->customerProfilePic($value->sender);
									$receiverName=$common->customerName($value->receiver);
									$receiverUrl=$common->customerProfilePic($value->receiver);
									//echo  '<pre>'; print_r($value);
									//exit;
						     	     $chatList[]=array("chatContentId"=>intval($value->id),"content"=>utf8_encode($value->content),"imageName"=>utf8_encode($value->sd_image_name),"url"=>utf8_encode($value->photo_url),"createDate"=>$createDate,"createTime"=>$createTime,"sender"=>intval($value->sender),"senderName"=>utf8_encode($senderName),"senderUrl"=>utf8_encode($senderUrl),"receiver"=>intval($value->receiver),"receiverName"=>utf8_encode($receiverName),"receiverUrl"=>utf8_encode($receiverUrl),"isRead"=>intval($value->IsRead),"receiverDate"=>$receiverdate,"type"=>(int)$value->type,"chatType"=>(int)$value->chatType,"createDateUtc"=>$createDateUtc,"createTimeUtc"=>$createTimeUtc,"sentBy"=>$value->sentBy);
						     			# code...
						     		}	

						     		$qry_getlast=DB::select( DB::raw("SELECT createdate FROM tblchatcontent WHERE chatsessionid = '$chatSessionId' ORDER BY id DESC LIMIT 0 , 1"));	
						     		$row_createdate='';
						     		if ($qry_getlast) {
						     			foreach ($qry_getlast as  $value) {
						     				$row_createdate=$value->createdate;
						     			}
						     		}

						     		$lastAddedDate=$row_createdate;
									if(isset($lastAddedDate)){
										$temp1= strtotime("$lastAddedDate $final");
										$lastAddedDate= date("Y-m-d H:i:s", $temp1);
									}
								    if($lastAddedDate==null){ $lastAddedDate=''; }
									
									$newDate='';
									if ($lastAddedDate!='') {
									  $newDate=$row_createdate;
									}

									$my_array=array("chatList"=>$chatList, "chatSessionId"=>intval($chatSessionId), "isPaging" =>$isPaging, 'lastAddedDate'=>$lastAddedDate,"newDate"=>$newDate);
		                           $status_array=1;
		                        $myarray['result']=$my_array;					
					    $myarray['message']="Chat List";
					    $myarray['status']=1;

				  }
			}	  
		  return response()->json($myarray);

	  }

	  /* chat user list */

	  public function chatUserList (Request $request) {
	  	 $langId=($request->header('langId'))?($request->header('langId')):1; 
	     $common=new CommanController;
         $usersList=array();
         $chatType=($request->chatType)?($request->chatType):0;

         if($chatType==0 || $chatType==1) {

		         if (!$request->userId) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_leadId",$langId)?$common->get_msg("blank_leadId",$langId):"Please select leadId.";
				      $myarray['status']=0;
				 } else {

					     $timeZone=($request->timezone)?($request->timezone):"+5:30";
			             $userType=($request->userType)?($request->userType):1;
			             $userId=($request->userId)?($request->userId):0;

			             $final='';
						if (strlen($timeZone)==5) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,1)." hour "; 
						$m=$strsign.substr($timeZone,3,2)." minutes";
						$final=$h.$m;	
						}
						if (strlen($timeZone)==6) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,2)." hour "; 
						$m=$strsign.substr($timeZone,4,2)." minutes";
						$final=$h.$m;	
						}

			             if ($userType==1) {
			             $qry=DB::select( DB::raw("Select chatcontent.customerId,chatcontent.vendorId,chatcontent.receiverdate,chatcontent.chatType,
			chatcontent.chatSessionId,cs.leadId,lead.serviceTypeId,lead.venderServiceTypeId,Concat(customer.fname,' ',customer.lname) as name,lead.serviceTypeName,lead.venderServiceTypeName,chatcontent.sentBy,lead.status from tblchatcontent as chatcontent 
			INNER JOIN  `tblchatsession` as cs ON chatcontent.chatSessionId = cs.chatSessionId 
			INNER JOIN  `tblcustomer` as customer ON chatcontent.customerId = customer.id  
			INNER JOIN  `tbllead` as lead ON cs.leadId = lead.id
			where ((chatcontent.vendorId=$userId AND isreceiverdelete='0')) 
			 and chatcontent.customerId!=0  and customer.isActive=1
			group by chatcontent.chatSessionId order by chatcontent.receiverdate  desc"));	
					       } else {
					       	
			             $qry=DB::select( DB::raw("Select chatcontent.customerId,chatcontent.vendorId,chatcontent.receiverdate,chatcontent.chatType,
			chatcontent.chatSessionId,cs.leadId,lead.serviceTypeId,lead.venderServiceTypeId,Concat(vender.fname,' ',vender.lname) as name,lead.serviceTypeName,lead.venderServiceTypeName,chatcontent.sentBy,lead.status from tblchatcontent as chatcontent 
			INNER JOIN  `tblchatsession` as cs ON chatcontent.chatSessionId = cs.chatSessionId 
			INNER JOIN  `tblvender` as vender ON chatcontent.vendorId = vender.id 
			INNER JOIN  `tbllead` as lead ON cs.leadId = lead.id 
			where ((chatcontent.customerId=$userId AND isreceiverdelete='0')) 
			 and chatcontent.vendorId!=0  and vender.isActive=1 
			group by chatcontent.chatSessionId order by chatcontent.receiverdate  desc"));	
					       }

				       $countRows=count($qry);

				       if ($countRows > 0) {

				       	  foreach ($qry as $rowvalue) {
				       	  	 
				       	  	 $customerId=($rowvalue->customerId)?($rowvalue->customerId):0;
				       	  	 $vendorId=($rowvalue->vendorId)?($rowvalue->vendorId):0;
				       	  	 $receiverdate=($rowvalue->receiverdate)?($rowvalue->receiverdate):"";
				       	  	 $leadId=($rowvalue->leadId)?($rowvalue->leadId):0;
		                     $chatSessionId=($rowvalue->chatSessionId)?($rowvalue->chatSessionId):0;
		                     $serviceTypeId=($rowvalue->serviceTypeId)?($rowvalue->serviceTypeId):0;
		                     $venderServiceTypeId=($rowvalue->venderServiceTypeId)?($rowvalue->venderServiceTypeId):0;
		                     $name=($rowvalue->name)?($rowvalue->name):0;
		                     $serviceTypeName=($rowvalue->serviceTypeName)?($rowvalue->serviceTypeName):"";
		                     $venderServiceTypeName=($rowvalue->venderServiceTypeName)?($rowvalue->venderServiceTypeName):"";
		                     $leadstatus=($rowvalue->status)?($rowvalue->status):1;
		                     $vendorFirmName=$common->vendorFirmName($vendorId);
                             $isOnline=$common->isOnline($vendorId);
		                     $isLeadAssigned=$common->isLeadAssigned($leadId);

		                     if ($serviceTypeId!=0) {
						  //$serviceName=$common->serviceName($serviceTypeId);
						  $serviceTypeName=$common->getServiceTypeValue($serviceTypeId,$langId);
						     if ($serviceTypeName=='') {
				      	      $serviceTypeName=($rowvalue->serviceTypeName)?($rowvalue->serviceTypeName):'';
				              }
						  }

		                     if ($userType==1) {
		                     $photo=$common->customerProfilePic($customerId);
		                     }

		                     if ($userType==2) {
		                     $photo=$common->vendorProfilePic($vendorId);
		                     }

		                     if ($leadstatus==1) {
						   $leadStatusName=$common->get_msg("lead_status_pending",$langId)?$common->get_msg("lead_status_pending",$langId):'Pending';
						  } elseif ($leadstatus==2) {
						   $leadStatusName=$common->get_msg("lead_status_inprogress",$langId)?$common->get_msg("lead_status_inprogress",$langId):'In Progress';
						  } elseif ($leadstatus==3) {
						   $leadStatusName=$common->get_msg("lead_cancel",$langId)?$this->common("lead_cancel",$langId):'Cancel';
						  } elseif ($leadstatus==4) {
						   $leadStatusName=$common->get_msg("lead_status_completed",$langId)?$common->get_msg("lead_status_completed",$langId):'Completed';
						  } else {
							  $leadStatusName='';
						  }

				       	  	 $qry_getlast=DB::select( DB::raw("SELECT content,createdate FROM tblchatcontent WHERE chatsessionid = '$chatSessionId' ORDER BY id DESC LIMIT 0 , 1"));	
						     		$chatLastContent='';
					                $chatLastcreatedate='';
						     		if ($qry_getlast) {
						     			foreach ($qry_getlast as  $value) {
						     				
						     				$chatLastContent=$value->content;
						                    $chatLastcreatedate=$value->createdate;

						                    if ($final!='') {
											   $temp= strtotime("$chatLastcreatedate $final");
									           $chatLastcreatedate = date('Y-m-d H:i:s',$temp);
											  }
						     			}
						     		}

                                    
                                    $sendType=1;
						     		if($userType==1) {
						     			$sendType=2;
						     		}

						     		$UnreadMsgCount=$common->leadUnreadMsgList($leadId,$customerId,$vendorId,$sendType);

						     		$usersList[]=array('customerId'=>$customerId,'vendorId'=>$vendorId,'leadId'=>$leadId,'lastMessage'=>$chatLastContent,'lastMessageDate'=>$chatLastcreatedate,"chatSessionId"=>$chatSessionId,"serviceTypeId"=>$serviceTypeId,"venderServiceTypeId"=>$venderServiceTypeId,"name"=>$name,"serviceTypeName"=>$serviceTypeName,"venderServiceTypeName"=>$venderServiceTypeName,"photo"=>$photo,"vendorFirmName"=>$vendorFirmName,"isLeadAssigned"=>$isLeadAssigned,"leadStatus"=>$leadstatus,"leadStatusName"=>$leadStatusName,"isOnline"=>$isOnline,"unReadMsgCount"=>$UnreadMsgCount);
				       	     }

				       	     $msg=$common->get_msg("chat_user_list",$langId)?$common->get_msg("chat_user_list",$langId):"Chat User List.";
				       	     $myarray['result']=$usersList;					
					         $myarray['message']=$msg;
					         $myarray['status']=1;
				       } else {
				       	     $msg=$common->get_msg("no_record_found",$langId)?$common->get_msg("no_record_found",$langId):'No Records Found';
		                     $myarray['result']=$usersList;					
					         $myarray['message']=$msg;
					         $myarray['status']=1;
				       }

				 }
			} else {

				if (!$request->userId) {
				      $myarray['result']=array();					
				      $myarray['message']=$common->get_msg("blank_leadId",$langId)?$common->get_msg("blank_leadId",$langId):"Please select leadId.";
				      $myarray['status']=0;
				 } else {

                         //echo $common->firebasepush("test","dddd");

					     $timeZone=($request->timezone)?($request->timezone):"+5:30";
			             $userType=($request->userType)?($request->userType):1;
			             $userId=($request->userId)?($request->userId):0;

			             $final='';
						if (strlen($timeZone)==5) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,1)." hour "; 
						$m=$strsign.substr($timeZone,3,2)." minutes";
						$final=$h.$m;	
						}
						if (strlen($timeZone)==6) {
						$strsign=substr($timeZone,0,1);
						$h=$strsign.substr($timeZone,1,2)." hour "; 
						$m=$strsign.substr($timeZone,4,2)." minutes";
						$final=$h.$m;	
						}

			            
			             $qry=DB::select( DB::raw("Select chatcontent.sender,chatcontent.receiver,chatcontent.receiverdate,chatcontent.chatType,
			chatcontent.chatSessionId,cs.productId,product.name,product.description from tblchatcontent as chatcontent 
			INNER JOIN  `tblchatsession` as cs ON chatcontent.chatSessionId = cs.chatSessionId 
			INNER JOIN  `tblcustomer` as customer ON chatcontent.receiver = customer.id  
			INNER JOIN  `tblcustomerproduct` as product ON cs.productId = product.id
			where ((chatcontent.receiver=$userId AND isreceiverdelete='0') OR (chatcontent.sender=$userId AND issenderdelete='0')) 
			   and customer.isActive=1
			group by chatcontent.chatSessionId order by chatcontent.receiverdate  desc"));	
					       

				       $countRows=count($qry);

				       if ($countRows > 0) {

				       	  foreach ($qry as $rowvalue) {
				       	  	 
				       	  	 $sender=($rowvalue->sender)?($rowvalue->sender):0;
				       	  	 $receiver=($rowvalue->receiver)?($rowvalue->receiver):0;
				       	  	 $receiverdate=($rowvalue->receiverdate)?($rowvalue->receiverdate):"";
				       	  	 $productId=($rowvalue->productId)?($rowvalue->productId):0;
		                     $chatSessionId=($rowvalue->chatSessionId)?($rowvalue->chatSessionId):0;
		                     $productName=($rowvalue->name)?($rowvalue->name):0;
		                     $productDescription=($rowvalue->description)?($rowvalue->description):0;
		                     

                              
                             if ($userId!=$sender) {
                                $custId=$sender;
                                $senderName=$common->customerName($custId);
                                $senderPicture=$common->customerProfilePic($custId);
                             }

                             if ($userId!=$receiver) {
                                 $custId=$receiver;
                                 $senderName=$common->customerName($custId);
                                 $senderPicture=$common->customerProfilePic($custId);
                             } 

		                     

				       	  	 $qry_getlast=DB::select( DB::raw("SELECT content,createdate FROM tblchatcontent WHERE chatsessionid = '$chatSessionId' ORDER BY id DESC LIMIT 0 , 1"));	
						     		$chatLastContent='';
					                $chatLastcreatedate='';
						     		if ($qry_getlast) {
						     			foreach ($qry_getlast as  $value) {
						     				
						     				$chatLastContent=$value->content;
						                    $chatLastcreatedate=$value->createdate;

						                    if ($final!='') {
											   $temp= strtotime("$chatLastcreatedate $final");
									           $chatLastcreatedate = date('Y-m-d H:i:s',$temp);
											  }
						     			}
						     		}
                                    
                                    $UnreadMsgCount=$common->productUnreadMsgList($productId,$userId);

						     		$usersList[]=array('userId'=>$custId,"senderName"=>$senderName,"senderImage"=>$senderPicture,'productId'=>$productId,'lastMessage'=>$chatLastContent,'lastMessageDate'=>$chatLastcreatedate,"chatSessionId"=>$chatSessionId,"productName"=>$productName,"productDescription"=>$productDescription,"unReadMsgCount"=>$UnreadMsgCount);
				       	     }

				       	     $msg=$common->get_msg("chat_user_list",$langId)?$common->get_msg("chat_user_list",$langId):"Chat User List.";
				       	     $myarray['result']=$usersList;					
					         $myarray['message']=$msg;
					         $myarray['status']=1;
				       } else {
				       	     $msg=$common->get_msg("no_record_found",$langId)?$common->get_msg("no_record_found",$langId):'No Records Found';
		                     $myarray['result']=$usersList;					
					         $myarray['message']=$msg;
					         $myarray['status']=1;
				       }

				 }
			}	 
         return response()->json($myarray);
	     
	  }

	  /* addEdit Product */

	  public function addEditProduct(Request $request) {
	    $common=new CommanController;
	    $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0;
		 
		 } else {
			
			 $locationId=($request->locationId)?($request->locationId):0;
			 $customerId=($request->customerId)?($request->customerId):0;
			 $categoryId=($request->categoryId)?($request->categoryId):0;
			 $location=($request->location)?($request->location):"";
			 $latitude=($request->latitude)?($request->latitude):"0.0";
			 $longitude=($request->longitude)?($request->longitude):"0.0";
			 $price=($request->price)?($request->price):"0.0";
			 $name=($request->name)?($request->name):"";
			 $description=($request->description)?($request->description):"";
			 $prodcondtype=($request->prodcondtype)?($request->prodcondtype):"";
			 $fileId=($request->fileId)?($request->fileId):"";
			 $house_flatNo=($request->house_flatNo)?($request->house_flatNo):"";
			 $landmark=($request->landmark)?($request->landmark):"";
			 $title=($request->title)?($request->title):"";
			 //$locationId=($request->locationId)?($request->locationId):0;

			 $checkcustomer = DB::table('tblcustomer')->where([['id', '=', $customerId],['isActive', '=', 1]])->count();

		     $checkproduct = DB::table('tblcustomerproduct')->where([['customerId', '=', $customerId],['status', '=', 1],['name','=',$name]])->count();
			 
			 $newlongitude=$longitude;
			 $newlatitude=$latitude;
			 if ($longitude!='') {
			 	$longitudeex=explode(".",$longitude);
			 	$longitudeexln=strlen($longitudeex[1]);
			 	if ($longitudeexln==7) {
			 		$newlongitude=$longitudeex[1].'0';
			 		$newlongitude=$longitudeex[0].".".$newlongitude;
			 	}
			 }
			  if ($latitude!='') {
			 	$latitudeex=explode(".",$latitude);
			 	$latitudeexln=strlen($latitudeex[1]);
			 	if ($latitudeexln==7) {
			 		$newlatitude=$latitudeex[1].'0';
			 		$newlatitude=$latitudeex[0].".".$newlatitude;
			 	}
			 }
			 
            
            
			 
			 if($checkcustomer>0) {
				 
				 if (!$request->productId) {
				   
				   if ($checkproduct ==0) {
                          
                           if ($locationId==0) {
						 $checkcustomerlocation =(DB::select( DB::raw("select * from tblcustomerlocation 
		                 where customerId='{$customerId}' and longitude='{$longitude}' and latitude='{$latitude}' limit 1")));
					     $customercount=count($checkcustomerlocation);
						 if ($customercount==0) {
						$locationId=DB::table('tblcustomerlocation')->insertGetId(
		               ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
						 } else {
						   if ($checkcustomerlocation) {
						       foreach($checkcustomerlocation as $locationvals) {
							     $locationId=$locationvals->id;
								 $location=$locationvals->location;
								 $latitude=$locationvals->latitude;
								 $longitude=$locationvals->longitude;
								 $house_flatNo=$locationvals->house_flatNo;
								 $landmark=$locationvals->landmark;
								 $title=$locationvals->title;
							   }
						   }
						 }
					 } else {
                           $checkcustomerlocation =(DB::select( DB::raw("select * from tblcustomerlocation 
		                 where customerId='{$customerId}' and id='{$locationId}' limit 1")));
                           $customercount=count($checkcustomerlocation);
						 if ($customercount==0) {
						$locationId=DB::table('tblcustomerlocation')->insertGetId(
		               ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
						 } else {
						   if ($checkcustomerlocation) {
						       foreach($checkcustomerlocation as $locationvals) {
							     $locationId=$locationvals->id;
								 $location=$locationvals->location;
								 $latitude=$locationvals->latitude;
								 $longitude=$locationvals->longitude;
								 $house_flatNo=$locationvals->house_flatNo;
								 $landmark=$locationvals->landmark;
								 $title=$locationvals->title;
							   }
						   }
						 }
					 }

				          $productId=DB::table('tblcustomerproduct')->insertGetId(
							  ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"name"=>$name,"description"=>$description,"price"=>$price,"categoryId"=>$categoryId,"status"=>1,"prodcondtype"=>$prodcondtype,"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title,'locationId'=>$locationId]);
				   
				        if($request->hasFile('photo')) {
							   $allowedfileExtension=['pdf','jpg','png','jpeg','doc','gif','heic'];
							   $files = $request->file('photo');
							    foreach($files as $file){
								  $extension = strtolower($file->getClientOriginalExtension());
								 $filename = rand(1,1000000).time().'_'.$productId.".".$extension;
		                        
		                         $check=in_array($extension,$allowedfileExtension);
								 if($check) {
								   $destinationPath = 'productphoto';
		                           $file->move($destinationPath,$filename); 
								   $productphoto=new ProductPhoto;
								   $productphoto->customerProductId=$productId;
								   $productphoto->photo=$filename;
								   $productphoto->createdDate=date('Y-m-d H:i:s');
								   $productphoto->save();
								 }
							   }
		                     }
						   $product=$common->ProductDetails($productId,$customerId,$langId);
						   $myarray['result']=$product;					
						   $myarray['message']=$common->get_msg("added_product",$langId)?$common->get_msg("added_product",$langId):"Your product has been added successfully.";
						   $myarray['status']=1;

					   } else {
                             
                             $myarray['result']=array();					
						   $myarray['message']=$common->get_msg("already_product_name",$langId)?$common->get_msg("already_product_name",$langId):"This product name has been already taken please try another.";
						   $myarray['status']=0;
					   }	   
				 
				 } else {
				   
				   if ($checkproduct <=1) {
				   
				         if ($locationId==0) {
						 $checkcustomerlocation =(DB::select( DB::raw("select * from tblcustomerlocation 
		                 where customerId='{$customerId}' and longitude='{$longitude}' and latitude='{$latitude}' limit 1")));
					     $customercount=count($checkcustomerlocation);
						 if ($customercount==0) {
						$locationId=DB::table('tblcustomerlocation')->insertGetId(
		               ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
						 } else {
						   if ($checkcustomerlocation) {
						       foreach($checkcustomerlocation as $locationvals) {
							     $locationId=$locationvals->id;
								 $location=$locationvals->location;
								 $latitude=$locationvals->latitude;
								 $longitude=$locationvals->longitude;
								 $house_flatNo=$locationvals->house_flatNo;
								 $landmark=$locationvals->landmark;
								 $title=$locationvals->title;
							   }
						   }
						 }
					 } else {
                           $checkcustomerlocation =(DB::select( DB::raw("select * from tblcustomerlocation 
		                 where customerId='{$customerId}' and id='{$locationId}' limit 1")));
                           $customercount=count($checkcustomerlocation);
						 if ($customercount==0) {
						$locationId=DB::table('tblcustomerlocation')->insertGetId(
		               ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title]);
						 } else {
						   if ($checkcustomerlocation) {
						       foreach($checkcustomerlocation as $locationvals) {
							     $locationId=$locationvals->id;
								 $location=$locationvals->location;
								 $latitude=$locationvals->latitude;
								 $longitude=$locationvals->longitude;
								 $house_flatNo=$locationvals->house_flatNo;
								 $landmark=$locationvals->landmark;
								 $title=$locationvals->title;
							   }
						   }
						 }
					 }

					     DB::table('tblcustomerproduct')->where('id',$request->productId)->update(
					     ['customerId'=>$customerId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s'),"name"=>$name,"description"=>$description,"price"=>$price,"categoryId"=>$categoryId,"prodcondtype"=>$prodcondtype,"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title,'locationId'=>$locationId]); 
				   
					       if($request->hasFile('photo')) {
								   $allowedfileExtension=['pdf','jpg','png','jpeg','doc','gif'];
								   $files = $request->file('photo');
								    foreach($files as $file){
									 $extension = strtolower($file->getClientOriginalExtension());
									 $filename = rand(1,1000000).time().'_'.$request->productId.".".$extension;
			                         
			                         $check=in_array($extension,$allowedfileExtension);
									 if($check) {
									   $destinationPath = 'productphoto';
			                           $file->move($destinationPath,$filename); 
									   $productphoto=new ProductPhoto;
									   $productphoto->customerProductId=$request->productId;
									   $productphoto->photo=$filename;
									   $productphoto->createdDate=date('Y-m-d H:i:s');
									   $productphoto->save();
									 }
								   }
			                     }
                                
                                 if ($fileId!='') {
			                     $delete=DB::delete('delete from tblcustomerproductphoto where id IN ('.$fileId.') and customerProductId='.$request->productId.'');
                                  }
							   $product=$common->ProductDetails($request->productId,$customerId,$langId);
							   $myarray['result']=$product;					
							   $myarray['message']=$common->get_msg("update_product",$langId)?$common->get_msg("update_product",$langId):"Your product has been updated successfully.";
							   $myarray['status']=1;
				   
				   } else {
                    
                           $myarray['result']=array();					
						   $myarray['message']=$common->get_msg("already_product_name",$langId)?$common->get_msg("already_product_name",$langId):"This product name has been already taken please try another.";
						   $myarray['status']=0;
				   
				   }
				 
				 }
			 } else {
			    
				$myarray['result']=array();					
		        $myarray['message']=$common->get_msg("invalid_customerId",$langId)?$common->get_msg("invalid_customerId",$langId):"Invalid CustomerId";
		        $myarray['status']=0;
				
			 }				 
		 
		 }
		 return response()->json($myarray); 
	}

	 /* awarded Booking */
	 
	 public function productList(Request $request) {
	      $common=new CommanController;
	      $langId=($request->header('langId'))?($request->header('langId')):1; 

		  if (!$request->customerId) {
		    $myarray['result']=array();
            $myarray['productCount']=0;
            $myarray['pageNo']=1;
            $myarray['totalRecordInPage']=0;				
		    $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
			$myarray['status']=0;
		 } else if (!$request->lat) {
		   $myarray['result']=array();
		   $myarray['productCount']=0;
           $myarray['pageNo']=1;
           $myarray['totalRecordInPage']=0;					
		   $myarray['message']=$common->get_msg("blank_lat",$langId)?$common->get_msg("blank_lat",$langId):"Please enter latitude.";
		   $myarray['status']=0;
		} else if (!$request->long) {
		   $myarray['result']=array();
		   $myarray['productCount']=0;
           $myarray['pageNo']=1;
           $myarray['totalRecordInPage']=0;					
		   $myarray['message']=$common->get_msg("blank_long",$langId)?$common->get_msg("blank_long",$langId):"Please select longitude.";
		   $myarray['status']=0;   
				
		} else {
			  
			  $pageLimit = isset($request->count)?(int)($request->count):5;
	          
	          $startDate = isset($request->startDate)?($request->startDate):'';
			  $endDate = isset($request->endDate)?($request->endDate):'';
			  $timezone=($request->timezone)?($request->timezone):"";
			  $categoryId = isset($request->categoryId)?($request->categoryId):0;
			  $search=($request->search)?($request->search):"";
			  $orderBy= isset($request->orderBy)?(int)($request->orderBy):0;
			  $isSold= isset($request->isSold)?(int)($request->isSold):0;
			  $isMyProduct= isset($request->isMyProduct)?(int)($request->isMyProduct):0;

			  $url=url('/');

			  /* $cond="";
			  if ($lastId > 0) {
			    $cond=" and lead.id < $lastId ";
			  } */
			  
			  $pageNo = isset($request->page)?(int)($request->page):1;
              if ($pageNo!=0) {
			  $start = ($pageNo - 1) * $pageLimit;
			  } else {
			  $start=1; 
			  }
			  
			    $where="and product.status!=2";
                
			    if ($isSold==1) {
                 $where="and product.status=2";
				}

				if ($startDate!='' && $endDate!='') {
					$startDate=date("Y-m-d",strtotime($startDate));
					$endDate=date("Y-m-d",strtotime($endDate));
				    $where .=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) >= '{$startDate}' and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) <= '{$endDate}'";
				} elseif ($startDate!='') {
					$startDate=date("Y-m-d",strtotime($startDate));
					$where .=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) >= '{$startDate}'";
				} elseif($endDate!='') {
				    $endDate=date("Y-m-d",strtotime($endDate));
					$where .=" and (DATE_FORMAT(leadDatetime,'%Y-%m-%d')) <= '{$endDate}'";
				} else {
				   // $where="";
				}

				if ($categoryId!=0) {
				   $where .=" and product.categoryId IN ($categoryId)";	
				}

				if ($search!='') {
					$where .=" and product.name like '%".$search."%'";
				}
                
                $mycond="and product.customerId!='{$request->customerId}'";

                if ($isMyProduct==1) {
                	$mycond="and product.customerId='$request->customerId'";
                }

				$orderByt='ORDER BY distance_in_km ASC,product.id DESC';
				if ($orderBy!=0) {
                    if ($orderBy==1) {
					 $orderByt='ORDER BY product.price DESC,distance_in_km ASC';
				    }
				    if ($orderBy==2) {
					 $orderByt='ORDER BY product.price ASC,distance_in_km ASC';
				    }
				}



				
                
              
			  $totalRecordInPage=0;
			  if ($isMyProduct==0) {
			  $productCount =count(DB::select( DB::raw("select product.*,111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(product.latitude))
 * COS(RADIANS(product.longitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(product.latitude))))
 AS distance_in_km from tblcustomerproduct as product
inner join tblcustomer as customer on product.customerId=customer.id
where customer.isActive=1 $mycond  $where  HAVING distance_in_km < 100 $orderByt")));
			  $product=DB::select( DB::raw("select product.*,111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(product.latitude))
 * COS(RADIANS(product.longitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(product.latitude))))
 AS distance_in_km from tblcustomerproduct as product
inner join tblcustomer as customer on product.customerId=customer.id
where customer.isActive=1 $mycond  $where  HAVING distance_in_km < 100 $orderByt LIMIT $start,$pageLimit"));
			} else {

			$productCount =count(DB::select( DB::raw("select product.*,111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(product.latitude))
 * COS(RADIANS(product.longitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(product.latitude))))
 AS distance_in_km from tblcustomerproduct as product
inner join tblcustomer as customer on product.customerId=customer.id
where customer.isActive=1 $mycond  $where  $orderByt")));
			  $product=DB::select( DB::raw("select product.*,111.045 * DEGREES(ACOS(COS(RADIANS($request->lat))
 * COS(RADIANS(product.latitude))
 * COS(RADIANS(product.longitude) - RADIANS($request->long))
 + SIN(RADIANS($request->lat))
 * SIN(RADIANS(product.latitude))))
 AS distance_in_km from tblcustomerproduct as product
inner join tblcustomer as customer on product.customerId=customer.id
where customer.isActive=1 $mycond  $where $orderByt LIMIT $start,$pageLimit"));	

			}
                 
				 if ($product) {
				     
					 foreach($product as $productData) {

					 	    $productcustomerId=($productData->customerId)?($productData->customerId):0;
						    $productId=($productData->id)?($productData->id):'';
							$productname=($productData->name!='')?($productData->name):'';
							$productdescription=($productData->description!='')?($productData->description):'';
							$productprice=($productData->price!='')?($productData->price):'0.0';
							$productlocation=($productData->location!='')?($productData->location):'';
							$productcreatedDate=($productData->createdDate)?($productData->createdDate):'';
							$productlatitude=($productData->latitude)?($productData->latitude):0;
							$productlongitude=($productData->longitude)?($productData->longitude):0;
							$productcategoryId=($productData->categoryId)?($productData->categoryId):0;
							$productstatus=($productData->status)?($productData->status):1;
							$prodcondtype=($productData->prodcondtype)?($productData->prodcondtype):0;
				            $customerName=$common->customerName($productcustomerId);
				            $house_flatNo=($productData->house_flatNo)?($productData->house_flatNo):"";
							$landmark=($productData->landmark)?($productData->landmark):"";
							$title=($productData->title)?($productData->title):"";
							$locationId=($productData->locationId)?($productData->locationId):0; 
				            //$totalLike=($productData->total_like)?($productData->total_like):0;
							
							//$totalViews=($productData->total_views)?($productData->total_views):0;
							$totalLike=$common->favouriteViewCount($productId);
							$totalViews=$common->productViewCount($productId);
                            $totalComment=$common->productUnreadMsg($productId);
							//$totalComment=($productData->total_comment)?($productData->total_comment):0;
			                 
			                 $prodcondtypetitle='';
			                 if (is_numeric($prodcondtype) && $prodcondtype!=0) {
			                $prodcondtypetitle=$common->get_msg_title($prodcondtype,$langId)?$common->get_msg_title($prodcondtype,$langId):"";
			                 }
							$productphoto = DB::table('tblcustomerproductphoto')->where([['customerProductId', '=', $productId]])->get();
							$photo=array();
							if ($productphoto) {
				                foreach ($productphoto as  $value) {
				                	$prophoto=($value->photo)?($value->photo):"";
				                	if ($prophoto!='') {
							        $prophoto=$url."/productphoto/".$prophoto;
							        }
				                	$photo[]=array('id'=>(int)$value->id,'productphoto'=>$prophoto);
				                }
								
							}
                            
                            $categoryName='';
                            if ($productcategoryId!=0) {
							$categoryName=$common->getProductCategoryValue($productcategoryId,$langId);
					          }
                              
                              $customerProfilePic='';
                              if ($productcustomerId!=0) {
					          $customerProfilePic=$common->customerProfilePic($productcustomerId);
					            }
                              //$currency=$common->getSettingValue('product_currency');
						        //$currency=$common->getCurrency();

						        $customercurrency=$common->customerCurrency($productcustomerId);
					            if ($customercurrency=='') {
					              $currency=$common->getCurrency();
					            } else {
					               $currency= $customercurrency;	
					            }

						          $isFavorite=$common->isFavorite($request->customerId,$productId);
						  $products[]=array('customerId'=>$productcustomerId,'customerName'=>$customerName,'customerProfilePic'=>$customerProfilePic,'productId'=>$productId,'productName'=>$productname,'productDescription'=>$productdescription,'productCreatedDate'=>$productcreatedDate,'productPrice'=>$productprice,'productLocation'=>$productlocation,'productStatus'=>$productstatus,'productCategoryId'=>$productcategoryId,"productLatitude"=>$productlatitude,"productLongitude"=>$productlongitude,"productPhoto"=>$photo,"productCategoryName"=>$categoryName,"isFavorite"=>(int)$isFavorite,"currency"=>$currency,"prodCondTypeId"=>$prodcondtype,"totalLike"=>(int)$totalLike,"totalViews"=>(int)$totalViews,"totalComment"=>$totalComment,"prodCondTypeTitle"=>$prodcondtypetitle,"house_flatNo"=>$house_flatNo,"landmark"=>$landmark,"title"=>$title,"locationId"=>$locationId); 
					   $totalRecordInPage++;
					 }
					 
					 $myarray['result']=$products;
					 $myarray['pageNo']=$pageNo;
                     $myarray['totalRecordInPage']=$totalRecordInPage;
					 $myarray['productCount']=$productCount;				 
					 $myarray['message']=$common->get_msg("product_list",$langId)?$common->get_msg("product_list",$langId):"Product list.";
					 $myarray['status']=1;
					 
				 } else {
				    $myarray['result']=array();
					$myarray['pageNo']=$pageNo;
                    $myarray['totalRecordInPage']=$totalRecordInPage;
					$myarray['productCount']=$productCount;					
					$myarray['message']=$common->get_msg("no_product",$langId)?$common->get_msg("no_product",$langId):"No product found.";
					$myarray['status']=1;
				 }

		  }
	    return response()->json($myarray);
	 }

    
    public function productDetails(Request $request) {
    	$common=new CommanController;
    	$langId=($request->header('langId'))?($request->header('langId')):1;
    	$productId=($request->productId)?($request->productId):0;
    	$customerId=($request->customerId)?($request->customerId):0;
    	if (!$request->productId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_product_id",$langId)?$common->get_msg("blank_product_id",$langId):"Please select product Id.";
		   $myarray['status']=0;
		 
		 } else {
		 	$product=$common->ProductDetails($productId,$customerId,$langId);
		 	$myarray['result']=$product;					
		   $myarray['message']=$common->get_msg("product_detail",$langId)?$common->get_msg("product_detail",$langId):"Product details.";
		   $myarray['status']=1;
		 }
		 return response()->json($myarray);
    }
    /* favouriteProduct */

	 public function favouriteProduct(Request $request) {
	   
	     $common=new CommanController;
         $langId=($request->header('langId'))?($request->header('langId')):1; 
	  
		 if (!$request->productId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_product_id",$langId)?$common->get_msg("blank_product_id",$langId):"Please select product Id.";
		   $myarray['status']=0;
		 
		 } else if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0;
		 
		 } else {
		   $count=DB::table('tblcustomerproductfavourite')->where([['productId', '=', $request->productId],['customerId', '=', $request->customerId]])->count();
		   if ($count > 0) {

		      if ($request->isFavourite==0) {
		      $delete=DB::delete("delete from tblcustomerproductfavourite where productId='{$request->productId}' and customerId=$request->customerId");
		      }

		      $myarray['result']=array();					
		      $myarray['message']=$common->get_msg("favourite_updated",$langId)?$common->get_msg("favourite_updated",$langId):"You have favourite updated successfully.";
		     $myarray['status']=1;
		   } else {
		   	 if ($request->isFavourite==1) {
		     DB::table('tblcustomerproductfavourite')->insert(
               ['customerId'=>$request->customerId,'productId'=>$request->productId,'createdDate'=>date('Y-m-d H:i:s')]);
		     }
		     $myarray['result']=array();					
		     $myarray['message']=$common->get_msg("favourite_added",$langId)?$common->get_msg("favourite_added",$langId):"You have favourite added successfully.";
		     $myarray['status']=1;
		   }
		 }			 
	   return response()->json($myarray); 
	}
    
    /* favourite product list */

    public function favouriteProductList(Request $request) {
	    $common=new CommanController;
		$langId=($request->header('langId'))?($request->header('langId')):1; 
		$vendor=array();			
		if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0;
		 
		 } else {
		   
		   $customerId=($request->customerId)?($request->customerId):0;
		   $productarr=array();
		   $i=0;
		   $data=DB::select( DB::raw("select custfav.productId from tblcustomerproductfavourite as custfav INNER JOIN tblcustomerproduct as product ON custfav.productId=product.id 
              inner join tblcustomer as customer on product.customerId=customer.id
		   	where customer.isActive=1 and custfav.customerId='{$request->customerId}'"));
		    $service=array();
		    $vendorList=array();
			if ($data) {
			  foreach ($data as $valuesvendor) {
			       $productId=$valuesvendor->productId;
				   $product=$common->ProductDetails($productId,$customerId,$langId);
			         $productarr[]=$product;
		           $i++;
		         } 
			     $myarray['result']=$productarr;
                 $myarray['productCount']=$i;			  
		         $myarray['message']=$common->get_msg("favourite_product",$langId)?$common->get_msg("favourite_product",$langId):"Your favourite product list.";
		         $myarray['status']=1;
			} else {
			$myarray['result']=array();	
            $myarray['productCount']=$i;			
		    $myarray['message']=$common->get_msg("no_favourite_product",$langId)?$common->get_msg("no_favourite_product",$langId):"No favourite products found.";
		    $myarray['status']=1;
			}
		 }
       return response()->json($myarray);		 
	}

	/* product condition type */

	public function productConditionType(Request $request) {

		 $common=new CommanController;
		 $langId=($request->header('langId'))?($request->header('langId')):1; 
         $cond1=$common->get_msg("prod_cond1",$langId)?$common->get_msg("prod_cond1",$langId):"Good";
         $cond2=$common->get_msg("prod_cond2",$langId)?$common->get_msg("prod_cond2",$langId):"Average";
         $cond3=$common->get_msg("prod_cond3",$langId)?$common->get_msg("prod_cond3",$langId):"Bad";
         $cond4=$common->get_msg("prod_cond4",$langId)?$common->get_msg("prod_cond4",$langId):"Excellent";

         
         $cond1Id=$common->get_msg_id("prod_cond1",$langId)?$common->get_msg_id("prod_cond1",$langId):1;
         $cond2Id=$common->get_msg_id("prod_cond2",$langId)?$common->get_msg_id("prod_cond2",$langId):2;
         $cond3Id=$common->get_msg_id("prod_cond3",$langId)?$common->get_msg_id("prod_cond3",$langId):3;
         $cond4Id=$common->get_msg_id("prod_cond4",$langId)?$common->get_msg_id("prod_cond4",$langId):4;
         //$cond=array(,$cond1,$cond2,$cond3);
         $cond=array(array('id'=>$cond1Id,"title"=>$cond1),array('id'=>$cond2Id,"title"=>$cond2),array('id'=>$cond3Id,"title"=>$cond3),array('id'=>$cond4Id,"title"=>$cond4));   
            $myarray['result']=$cond;	
            $myarray['message']=$common->get_msg("pro_cond_type",$langId)?$common->get_msg("pro_cond_type",$langId):"Product condition type.";
		    $myarray['status']=1;
		     return response()->json($myarray);	

	}

	/* product mark as sold */

	public function productMarkAsSold(Request $request) {

		$common=new CommanController;
		 $langId=($request->header('langId'))?($request->header('langId')):1;

		 if (!$request->productId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_product_id",$langId)?$common->get_msg("blank_product_id",$langId):"Please select product Id.";
		   $myarray['status']=0;
		 
		 } else if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0;
		 
		 } else {

		 	$checkproduct = DB::table('tblcustomerproduct')->where([['customerId', '=', $request->customerId],['status', '=', 1],['id','=',$request->productId]])->count();

		 	 if ($checkproduct==0) {
              $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("invalid_product",$langId)?$common->get_msg("invalid_product",$langId):"Invalid product details.";
		   $myarray['status']=0;
		 	 } else {

		 	 	DB::table('tblcustomerproduct')->where('id',$request->productId)->update(
					     ['status'=>2]); 
		 	 	 $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("mark_as_sold",$langId)?$common->get_msg("mark_as_sold",$langId):"Product has been marked as sold successfully.";
		   $myarray['status']=1;
		 	 }
		 } 
         return response()->json($myarray);
	}

	/* product delete */

	public function productDelete(Request $request) {

		$common=new CommanController;
		 $langId=($request->header('langId'))?($request->header('langId')):1;

		 if (!$request->productId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_product_id",$langId)?$common->get_msg("blank_product_id",$langId):"Please select product Id.";
		   $myarray['status']=0;
		 
		 } else if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0;
		 
		 } else {

		 	$checkproduct = DB::table('tblcustomerproduct')->where([['customerId', '=', $request->customerId],['id','=',$request->productId]])->count();

		 	 if ($checkproduct==0) {
              $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("invalid_product",$langId)?$common->get_msg("invalid_product",$langId):"Invalid product details.";
		   $myarray['status']=0;
		 	 } else {
                
                $deleteviewprod=DB::delete("delete from tblproductview where  productId='$request->productId'");
                $deletefavprod=DB::delete("delete from tblcustomerproductfavourite where  productId='$request->productId'");
                $deleteprodphoto=DB::delete("delete from tblcustomerproductphoto where  customerProductId='$request->productId'");
		 	 	$delete=DB::delete("delete from tblcustomerproduct where customerId='$request->customerId' and id='$request->productId'");

		 	 	 $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("delete_product",$langId)?$common->get_msg("delete_product",$langId):"Your product has been deleted successfully.";
		   $myarray['status']=1;
		 	 }
		 } 
         return response()->json($myarray);
	}

	/* notification list */

	public function notificationList(Request $request) {
		$common=new CommanController;
		 $langId=($request->header('langId'))?($request->header('langId')):1;

		      $pageLimit = isset($request->count)?(int)($request->count):5;
	          $lastId = isset($request->lastId)?(int)($request->lastId):0;			
			  $pageNo = isset($request->page)?(int)($request->page):1;
			  $startDate = isset($request->startDate)?($request->startDate):'';
			  $endDate = isset($request->endDate)?($request->endDate):'';
              $timezone=($request->timezone)?($request->timezone):"";
              $userId=isset($request->userId)?(int)($request->userId):0;
              $isCustomer=isset($request->isCustomer)?(int)($request->isCustomer):1;

              if ($pageNo!=0) {
			  $start = ($pageNo - 1) * $pageLimit;
			  } else {
			  $start=1; 
			  }
              
              $final='';
				if (strlen($timezone)==5) {
				$strsign=substr($timezone,0,1);
				$h=$strsign.substr($timezone,1,1)." hour "; 
				$m=$strsign.substr($timezone,3,2)." minutes";
				$final=$h.$m;	
				}
				if (strlen($timezone)==6) {
				$strsign=substr($timezone,0,1);
				$h=$strsign.substr($timezone,1,2)." hour "; 
				$m=$strsign.substr($timezone,4,2)." minutes";
				$final=$h.$m;	
				}
              
              $notifications=array();
              $notification = DB::table('tblnotification')->where('notifiedUserId', '=', $userId)->where('isCustomerNotification', '=', $isCustomer)->orderBy('id', 'desc')->skip($start)->take($pageLimit)->get();
              $notificationcn = DB::table('tblnotification')->where('notifiedUserId', '=', $userId)->where('isCustomerNotification', '=', $isCustomer)->orderBy('id', 'desc')->get();
              $totalRecordInPage=$notificationcn->count();

              $updateview=DB::select( DB::raw("update tblnotification SET `flag`=1 where notifiedUserId='{$userId}'"));


			 if ($notification->count() > 0) {
			    foreach ($notification as  $values) {
			    	$id=($values->id)?($values->id):0;
			    	$notifiedByUserId=($values->notifiedByUserId)?($values->notifiedByUserId):0;
			    	$notifiedUserId=($values->notifiedUserId)?($values->notifiedUserId):0;
			    	$flag=($values->flag)?($values->flag):0;
			    	$notificationType=($values->notificationType)?($values->notificationType):0;
			    	$createdDate=($values->createdDate)?($values->createdDate):0;
			    	$notification=($values->notification)?($values->notification):0;
			    	$SendBy=($values->SendBy)?($values->SendBy):0;
                    $leadId=($values->leadId)?($values->leadId):0;
                    $productId=($values->productId)?($values->productId):0;

                    $isCustomerNotification=($values->isCustomerNotification)?($values->isCustomerNotification):0;

			                    $lastAddedDate=$createdDate;  
			                    if($timezone!='') {
								$temp1= strtotime("$lastAddedDate $final");
					            $lastAddedDate= date("Y-m-d H:i:s", $temp1);
								}
					  
			    	          

				    	

				    	

				    	if ($notificationType==0) {
				    		$notifiedByUserName='Admin';
				    		$notifiedByUserPicture='';
				    		if ($isCustomerNotification==1) {
				    		$notifiedUserName=$common->customerName($notifiedUserId);
				    		$notifiedUserPicture=$common->customerProfilePic($notifiedUserId);
				    	    } else {
				    	    	$notifiedUserName=$common->vendorName($notifiedUserId);
	                       $notifiedUserPicture=$common->vendorProfilePic($notifiedUserId);
				    	    }
				    	} elseif ($notificationType==1) {
	                       if ($SendBy==1) {
	                       $notifiedByUserName=$common->vendorName($notifiedByUserId);
	                       $notifiedByUserPicture=$common->vendorProfilePic($notifiedByUserId);
	                       $notifiedUserName=$common->customerName($notifiedUserId);
	                       $notifiedUserPicture=$common->customerProfilePic($notifiedUserId);
				    	   }
				    	   if ($SendBy==2) {
	                       $notifiedByUserName=$common->customerName($notifiedByUserId);
	                       $notifiedByUserPicture=$common->customerProfilePic($notifiedByUserId);
	                       $notifiedUserName=$common->vendorName($notifiedUserId);
	                       $notifiedUserPicture=$common->vendorProfilePic($notifiedUserId);
				    	   }
				    	} elseif ($notificationType==2) {
				    		$notifiedByUserName=$common->customerName($notifiedByUserId);
				    		$notifiedByUserPicture=$common->customerProfilePic($notifiedByUserId);
				    		$notifiedUserName=$common->customerName($notifiedUserId);
				    		$notifiedUserPicture=$common->customerProfilePic($notifiedUserId);
				    	} else {

				    		if ($isCustomerNotification==1) {
				    		$notifiedUserName=$common->customerName($notifiedUserId);
				    		$notifiedUserPicture=$common->customerProfilePic($notifiedUserId);
				    		$notifiedByUserName=$common->vendorName($notifiedByUserId);
	                       $notifiedByUserPicture=$common->vendorProfilePic($notifiedByUserId);
				    	    } else {
				    	    	$notifiedByUserName=$common->customerName($notifiedByUserId);
				    		$notifiedByUserPicture=$common->customerProfilePic($notifiedByUserId);
				    	    	$notifiedUserName=$common->vendorName($notifiedUserId);
	                           $notifiedUserPicture=$common->vendorProfilePic($notifiedUserId);
				    	    }
				    	}

			    	$notifications[]=array("id"=>(int)$id,"notifiedByUserId"=>(int)$notifiedByUserId,"notifiedByUserName"=>$notifiedByUserName,"notifiedByUserPicture"=>$notifiedByUserPicture,"notifiedUserId"=>(int)$notifiedUserId,"notifiedUserName"=>$notifiedUserName,"notifiedUserPicture"=>$notifiedUserPicture,"flag"=>(int)$flag,"createdDate"=>$lastAddedDate,"notification"=>$notification,"notificationType"=>(int)$notificationType,"sentBy"=>(int)$SendBy,"productId"=>(int)$productId,"leadId"=>(int)$leadId);

			      

			    }
			    $msg ='Notification List.';
			       $myarray['result']=$notifications;
                   $myarray['page'] = $pageNo;                				
				   $myarray['totalRecord'] = (int)$totalRecordInPage;					
				   $myarray['message']=$msg;
				   $myarray['status']=1;
			 } else {
                   $msg='No Notification Found.';
                   $myarray['result']=array();
                   $myarray['page'] = $pageNo;                				
				   $myarray['totalRecord'] = (int)$totalRecordInPage;					
				   $myarray['message']=$msg;
				   $myarray['status']=1;
			 }

			return response()->json($myarray);    
	}
    
    public function Report(Request $request) {
           
           

        
    	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1; 
    	$startDate = isset($request->startDate)?(date("Y-m-d",strtotime($request->startDate))):date("Y-m-d",strtotime('-1 month'));
		$endDate = isset($request->endDate)?(date("Y-m-d",strtotime($request->endDate))):date("Y-m-d");
		$isVendorReport=($request->isVendorReport)?($request->isVendorReport):0;
		$customerId=($request->customerId)?($request->customerId):0;
		$vendorId=($request->vendorId)?($request->vendorId):0;

          if ($isVendorReport==0) {
              
              if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0; 
		     }  else {
		     	//echo $startDate;
		     	//echo '<br>';
		     	//echo $endDate;
		     	//exit;
		     	$TotalLeadRequest=$common->customerLeadRequest($customerId,$startDate,$endDate);
		     	$TotalConfirmedLeadRequest=$common->customerLeadConfirmedRequest($customerId,$startDate,$endDate);
		     	$TotalCompletedLead=$common->customerLeadCompleteRequest($customerId,$startDate,$endDate);
		     	$TotalCompleLead=$common->customerLeadTotalCompleteRequest($customerId);
               
               $chart=array();
               for ($i = 1; $i <= 6; $i++) {
                   $dateyearmonth=date('Y-m', strtotime("-$i month"));
                   $chartmy=date("M'y", strtotime("-$i month"));
                   $dateyear=date('Y', strtotime("-$i month"));
                   $datemonth=date('m', strtotime("-$i month"));
                   $capMonth=date("M", strtotime("-$i month"));
                   $capYear=date("y", strtotime("-$i month"));
                   if ($capMonth=='Jan') {
                   	$chartMonth=$common->get_msg("jan_month",$langId)?$common->get_msg("jan_month",$langId):"Jan";
                   }
                   if ($capMonth=='Feb') {
                   	$chartMonth=$common->get_msg("feb_month",$langId)?$common->get_msg("feb_month",$langId):"Feb";
                   }
                   if ($capMonth=='Mar') {
                   	$chartMonth=$common->get_msg("march_month",$langId)?$common->get_msg("march_month",$langId):"Mar";
                   }
                   if ($capMonth=='Apr') {
                   	$chartMonth=$common->get_msg("apr_month",$langId)?$common->get_msg("apr_month",$langId):"Apr";
                   }
                   if ($capMonth=='May') {
                   	$chartMonth=$common->get_msg("may_month",$langId)?$common->get_msg("may_month",$langId):"May";
                   }
                   if ($capMonth=='Jun') {
                   	$chartMonth=$common->get_msg("jun_month",$langId)?$common->get_msg("jun_month",$langId):"Jun";
                   }
                   if ($capMonth=='Jul') {
                   	$chartMonth=$common->get_msg("jul_month",$langId)?$common->get_msg("jul_month",$langId):"Jul";
                   }
                   if ($capMonth=='Aug') {
                   	$chartMonth=$common->get_msg("aug_month",$langId)?$common->get_msg("aug_month",$langId):"Aug";
                   }
                   if ($capMonth=='Sep') {
                   	$chartMonth=$common->get_msg("sep_month",$langId)?$common->get_msg("sep_month",$langId):"Sep";
                   }
                   if ($capMonth=='Oct') {
                   	$chartMonth=$common->get_msg("oct_month",$langId)?$common->get_msg("oct_month",$langId):"Oct";
                   }
                   if ($capMonth=='Nov') {
                   	$chartMonth=$common->get_msg("nov_month",$langId)?$common->get_msg("nov_month",$langId):"Nov";
                   }
                   if ($capMonth=='Dec') {
                   	$chartMonth=$common->get_msg("dec_month",$langId)?$common->get_msg("dec_month",$langId):"Dec";
                   }
                     
                   $chartmyYear=$chartMonth."'".$capYear;  
                   $totalRequestPlaced=$common->customerLeadRequestChart($customerId,$dateyear,$datemonth);
                   $totalConfirmedRequest=$common->customerLeadConfirmedRequestChart($customerId,$dateyear,$datemonth);
                    $chart[]=array('monthYear'=>$chartmyYear,'totalRequestPlaced'=>(int)$totalRequestPlaced,"totalConfirmedRequest"=>(int)$totalConfirmedRequest);
                  }

		        $reportcount=array("totalRequestsPlaced"=>(int)$TotalLeadRequest,"totalConfirmedRequests"=>$TotalConfirmedLeadRequest,"totalCompletedRequests"=>$TotalCompletedLead,"allCompletedRequests"=>$TotalCompleLead,"chart"=>$chart);
		     
                 $myarray['result']=$reportcount;					
		         $myarray['message']="Report Counts";
		         $myarray['status']=1; 
		     }
           
          } else {

          	 if (!$request->vendorId) {
		   $myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
		   $myarray['status']=0;
				 
		      } else {

		      	$TotalLeadRequest=$common->vendorLeadRequest($vendorId,$startDate,$endDate);
		     	$TotalConvertedLead=$common->vendorConvertedLeadRequest($vendorId,$startDate,$endDate);
		     	$TotalCancelledLead=$common->vendorCancelledLeadRequest($vendorId,$startDate,$endDate);
		     	$avgRate=$common->getAverageRatingVendor($vendorId);
		     	$vendorTotalCustomer=$common->vendorTotalCustomer($vendorId);
		        $newCustomer=$common->vendorCustomer($vendorId,$startDate,$endDate);
		        
                 $chart=array();
               for ($i = 1; $i <= 6; $i++) {
                   $dateyearmonth=date('Y-m', strtotime("-$i month"));
                   $chartmy=date("M'y", strtotime("-$i month"));
                   $dateyear=date('Y', strtotime("-$i month"));
                   $datemonth=date('m', strtotime("-$i month"));

                   $capMonth=date("M", strtotime("-$i month"));
                   $capYear=date("y", strtotime("-$i month"));
                   if ($capMonth=='Jan') {
                   	$chartMonth=$common->get_msg("jan_month",$langId)?$common->get_msg("jan_month",$langId):"Jan";
                   }
                   if ($capMonth=='Feb') {
                   	$chartMonth=$common->get_msg("feb_month",$langId)?$common->get_msg("feb_month",$langId):"Feb";
                   }
                   if ($capMonth=='Mar') {
                   	$chartMonth=$common->get_msg("march_month",$langId)?$common->get_msg("march_month",$langId):"Mar";
                   }
                   if ($capMonth=='Apr') {
                   	$chartMonth=$common->get_msg("apr_month",$langId)?$common->get_msg("apr_month",$langId):"Apr";
                   }
                   if ($capMonth=='May') {
                   	$chartMonth=$common->get_msg("may_month",$langId)?$common->get_msg("may_month",$langId):"May";
                   }
                   if ($capMonth=='Jun') {
                   	$chartMonth=$common->get_msg("jun_month",$langId)?$common->get_msg("jun_month",$langId):"Jun";
                   }
                   if ($capMonth=='Jul') {
                   	$chartMonth=$common->get_msg("jul_month",$langId)?$common->get_msg("jul_month",$langId):"Jul";
                   }
                   if ($capMonth=='Aug') {
                   	$chartMonth=$common->get_msg("aug_month",$langId)?$common->get_msg("aug_month",$langId):"Aug";
                   }
                   if ($capMonth=='Sep') {
                   	$chartMonth=$common->get_msg("sep_month",$langId)?$common->get_msg("sep_month",$langId):"Sep";
                   }
                   if ($capMonth=='Oct') {
                   	$chartMonth=$common->get_msg("oct_month",$langId)?$common->get_msg("oct_month",$langId):"Oct";
                   }
                   if ($capMonth=='Nov') {
                   	$chartMonth=$common->get_msg("nov_month",$langId)?$common->get_msg("nov_month",$langId):"Nov";
                   }
                   if ($capMonth=='Dec') {
                   	$chartMonth=$common->get_msg("dec_month",$langId)?$common->get_msg("dec_month",$langId):"Dec";
                   }
                     
                   $chartmyYear=$chartMonth."'".$capYear; 
                   
                   $totalRequestPlaced=$common->vendorLeadRequestChart($vendorId,$dateyear,$datemonth);
                   $totalConvertedRequest=$common->vendorConvertedLeadRequestChart($vendorId,$dateyear,$datemonth);
                    $chart[]=array('monthYear'=>$chartmyYear,'totalLeadRequest'=>(int)$totalRequestPlaced,"totalConvertedLead"=>(int)$totalConvertedRequest);
                  }

		        $reportcount=array("totalLeadRecieved"=>(int)$TotalLeadRequest,"totalConveretedLead"=>(int)$TotalConvertedLead,"TotalCancelledLead"=>(int)$TotalCancelledLead,"newCustomer"=>(int)$newCustomer,"avgRate"=>$avgRate,"vendorTotalCustomer"=>$vendorTotalCustomer,"chart"=>$chart);
		     
                 $myarray['result']=$reportcount;					
		         $myarray['message']="Report Counts";
		         $myarray['status']=1; 

		      }

          }
        
        return response()->json($myarray);
    }

    public function locationList(Request $request) {

    	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $country = DB::table('tblcountries')->where([['status','=','1']])->get(['id','name']);
        $state = DB::table('tblstates')->get(['id','name','country_id']);
        //$coutry = DB::table('tblcountries')->where([['status','=','1']])->get(['id','name']);
        $city = DB::table('tblcities')->get(['id','name','state_id']);
        $countryarr=array();
        $statearr=array();
        $cityarr=array();
        if ($country) {
            foreach ($country as  $value) {
            	$countryId=$value->id;
            	$countryName=$common->getCountryValue($countryId,$langId);
               $countryarr[]=array('countryId'=>(int)$value->id,'countryName'=>$countryName);
            }        	
        }
        if ($state) {
            foreach ($state as  $value) {
               $stateId=$value->id;
               $stateName=$common->getStateValue($stateId,$langId);
               $statearr[]=array('stateId'=>(int)$value->id,'stateName'=>$stateName,'countryId'=>(int)$value->country_id);
            }        	
        }
        if ($city) {
            foreach ($city as $value) {
            	$cityId=$value->id;
            	$cityNameval=$common->getCityValue($cityId,$langId);
               $cityarr[]=array('cityId'=>(int)$value->id,'cityName'=>$cityNameval,'stateId'=>(int)$value->state_id);
            }        	
        }

        $array=array('country'=>$countryarr,'state'=>$statearr,'city'=>$cityarr);

                $myarray['result']=$array;					
		         $myarray['message']="Location List";
		         $myarray['status']=1; 
         return response()->json($myarray);
    }

    public function viewProduct(Request $request) {
         $common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;

    	if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0; 
		} elseif (!$request->productId) {
	      $myarray['result']=array();					
	      $myarray['message']=$common->get_msg("blank_product_id",$langId)?$common->get_msg("blank_product_id",$langId):"Please select product Id.";
	      $myarray['status']=0;
		} else {
    	
			$checkview = DB::table('tblproductview')->where([['productId', '=', $request->productId],['userId', '=', $request->customerId]])->count();
			$checkproduct = DB::table('tblcustomerproduct')->where([['id', '=', $request->productId]])->count();
			$checkcust = DB::table('tblcustomer')->where([['id', '=', $request->customerId]])->count();
			if ($checkcust >0 && $checkproduct > 0) {
				if ($checkview==0) {
					DB::table('tblproductview')->insert(
	               ['userId'=>$request->customerId,'productId'=>$request->productId,'createdDate'=>date('Y-m-d H:i:s')]);
	               $msg=$common->get_msg("view_product",$langId)?$common->get_msg("view_product",$langId):"You have viewed this product.";
				   $productview=DB::select( DB::raw("update tblcustomerproduct SET `total_views`=`total_views`+1 where id={$request->productId}"));
				   $myarray['result']=array();					
			       $myarray['message']=$msg;
			       $myarray['status']=1;
				} else {
					$msg=$common->get_msg("already_view_product",$langId)?$common->get_msg("already_view_product",$langId):"You have already seen this product.";
	                $myarray['result']=array();					
					$myarray['message']=$msg;
					$myarray['status']=1;
				}
			} else {

				 $msg=$common->get_msg("invalid_product",$langId)?$common->get_msg("invalid_product",$langId):"Invalid product details.";
	                $myarray['result']=array();					
					$myarray['message']=$msg;
					$myarray['status']=0;
			}
		}
		 return response()->json($myarray);
    }

    public function countryList(Request $request) {
        $common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $country = DB::table('tblcountries')->where([['status','=','1']])->get(['id','name']);
        
        //$coutry = DB::table('tblcountries')->where([['status','=','1']])->get(['id','name']);
        
        $countryarr=array();
        
        
        if ($country) {
            foreach ($country as  $value) {
            	$countryId=$value->id;
            	$countryName=$common->getCountryValue($countryId,$langId);
               $countryarr[]=array('countryId'=>(int)$value->id,'countryName'=>$countryName);
            }        	
        }

        $myarray['result']=$countryarr;					
		         $myarray['message']="Country List";
		         $myarray['status']=1; 
         return response()->json($myarray);
    }
    public function stateList(Request $request) {
    	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $statearr=array();
        $countryId =($request->countryId)?($request->countryId):0;
        if ($countryId!=0) {
        $state = DB::table('tblstates')->where([['country_id','=',$countryId]])->get(['id','name','country_id']);
        } else {
        $state = DB::table('tblstates')->get(['id','name','country_id']);
        }

        if ($state) {
            foreach ($state as  $value) {
               $stateId=$value->id;
               $stateName=$common->getStateValue($stateId,$langId);
               $statearr[]=array('stateId'=>(int)$value->id,'stateName'=>$stateName,'countryId'=>(int)$value->country_id);
            }        	
        }
    	 $myarray['result']=$statearr;					
		         $myarray['message']="State List";
		         $myarray['status']=1; 
         return response()->json($myarray);
    }
    public function cityList(Request $request) {
    	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $cityarr=array();
        $stateId =($request->stateId)?($request->stateId):0;
        if ($stateId!=0) {
        $city = DB::table('tblcities')->where([['state_id','=',$stateId]])->get(['id','name','state_id']);
        } else {
        $city = DB::table('tblcities')->get(['id','name','state_id']);	
        }

        if ($city) {
            foreach ($city as $value) {
            	$cityId=$value->id;
            	$cityNameval=$common->getCityValue($cityId,$langId);
               $cityarr[]=array('cityId'=>(int)$value->id,'cityName'=>$cityNameval,'stateId'=>(int)$value->state_id);
            }        	
        }
         $myarray['result']=$cityarr;					
         $myarray['message']="City List";
         $myarray['status']=1; 
         return response()->json($myarray);
    }

    public function readNotification(Request $request) {
    	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $notificationId=($request->notificationId)?($request->notificationId):0;
        
        if (!$request->notificationId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_notification_id",$langId)?$common->get_msg("blank_notification_id",$langId):"Please Enter Notification Id.";
		   $myarray['status']=0; 
		} else {
	        $checkview = DB::table('tblnotification')->where([['id', '=', $notificationId]])->count();

	        if ($checkview > 0) {
	        $productview=DB::select( DB::raw("update tblnotification SET `flag`=1 where id={$notificationId}"));
	          $myarray['result']=array();					
			$myarray['message']=$common->get_msg("update_notification_status",$langId)?$common->get_msg("update_notification_status",$langId):"Your notification status has been updated.";
			$myarray['status']=0;
	          } else {
	           
	         $myarray['result']=array();					
			$myarray['message']=$common->get_msg("invalid_notification",$langId)?$common->get_msg("invalid_notification",$langId):"Invalid Notification Id.";
			$myarray['status']=0;
	          }
	     }     

          return response()->json($myarray);

    }

    public function reportProduct(Request $request) {
    	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;

        if (!$request->customerId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId";
		   $myarray['status']=0; 
		} elseif (!$request->productId) {
	      $myarray['result']=array();					
	      $myarray['message']=$common->get_msg("blank_product_id",$langId)?$common->get_msg("blank_product_id",$langId):"Please select product Id.";
	      $myarray['status']=0;
		} else {
			DB::table('tblreportproduct')->insert(
               ['productId'=>$request->productId,'customerId'=>$request->customerId,'comment'=>$request->comment,'createdDate'=>date('Y-m-d H:i:s')]);
			 $myarray['result']=array();					
	      $myarray['message']=$common->get_msg("report_product",$langId)?$common->get_msg("report_product",$langId):"Your report has been submitted for this product.";
	      $myarray['status']=1;  
		}
		return response()->json($myarray);
    }

    public function userUnReadCount(Request $request) {
    	       $common=new CommanController;
               $langId=($request->header('langId'))?($request->header('langId')):1;
               $isCustomer=($request->isCustomer)?($request->isCustomer):0;
             
             if (!$request->userId) {
			   $myarray['result']=array();
			   $myarray['badgeCount']=0;					
			   $myarray['message']=$common->get_msg("blank_user",$langId)?$common->get_msg("blank_user",$langId):"Please select user Id.";
			   $myarray['status']=0; 
		     } else {

               if ($isCustomer==1) {
              $count = DB::table('tblnotification')->where([['notifiedUserId', '=', $request->userId],['flag','=',0],['isCustomerNotification','=',1]])->count();
               } else {
               	$count = DB::table('tblnotification')->where([['notifiedUserId', '=', $request->userId],['flag','=',0],['isCustomerNotification','=',0]])->count();
               }
               $msg="Badge Count.";
               $myarray['result']=array();
			   $myarray['badgeCount']=$count;					
			   $myarray['message']=$msg;
			   $myarray['status']=1;
           }
           return response()->json($myarray);
    }

    public function addVendorLocation(Request $request) {
	    $common=new CommanController;
	    $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if (!$request->vendorId) {
		   $myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
		   $myarray['status']=0;
				 
		} else {
			
			 
			 $vendorId=($request->vendorId)?($request->vendorId):0;
			 //$showCurrentLocation=($request->showCurrentLocation)?($request->showCurrentLocation):0;
			 $location=($request->location)?($request->location):"";
			 $latitude=($request->latitude)?($request->latitude):"0.0";
			 $longitude=($request->longitude)?($request->longitude):"0.0";
			 
			 $checkvendor = DB::table('tblvender')->where([['id', '=', $vendorId],['isActive', '=', 1]])->count();
			 
			 $newlongitude=$longitude;
			 $newlatitude=$latitude;
			 if ($longitude!='') {
			 	$longitudeex=explode(".",$longitude);
			 	$longitudeexln=strlen($longitudeex[1]);
			 	if ($longitudeexln==7) {
			 		$newlongitude=$longitudeex[1].'0';
			 		$newlongitude=$longitudeex[0].".".$newlongitude;
			 	}
			 }
			  if ($latitude!='') {
			 	$latitudeex=explode(".",$latitude);
			 	$latitudeexln=strlen($latitudeex[1]);
			 	if ($latitudeexln==7) {
			 		$newlatitude=$latitudeex[1].'0';
			 		$newlatitude=$latitudeex[0].".".$newlatitude;
			 	}
			 }
			 
            
            
			 $checkvendorlocation =count(DB::select( DB::raw("select * from tblvendorlocation 
where vendorId='{$vendorId}'")));
			 
			 if($checkvendor>0) {
				 
				 if ($checkvendorlocation==0) {
				   
				   DB::table('tblvendorlocation')->insert(
							  ['vendorId'=>$vendorId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s')]);

				   DB::table('tblvender')->where('id',$vendorId)->update(
				   ['currentAddress'=>$location,'currentLatitude'=>$latitude,'currentLongitude'=>$longitude]); 
				   /*DB::table('tblvender')->where('id',$vendorId)->update(
				   ['showCurrentLocation'=>$showCurrentLocation,'currentAddress'=>$location,'currentLatitude'=>$latitude,'currentLongitude'=>$longitude]); */
				   
				   $location=$common->VendorLocation($vendorId);
				   $myarray['result']=$location;					
				   $myarray['message']=$common->get_msg("added_location",$langId)?$common->get_msg("added_location",$langId):"Your location has been added successfully.";
				   $myarray['status']=1;
				 
				 } else {
				   
				   DB::table('tblvendorlocation')->where('vendorId',$vendorId)->update(
				   ['vendorId'=>$vendorId,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'createdDate'=>date('Y-m-d H:i:s')]); 

				   DB::table('tblvender')->where('id',$vendorId)->update(
				   ['currentAddress'=>$location,'currentLatitude'=>$latitude,'currentLongitude'=>$longitude]); 
				   
				   $location=$common->VendorLocation($vendorId);
				   $myarray['result']=$location;					
				   $myarray['message']=$common->get_msg("update_location",$langId)?$common->get_msg("update_location",$langId):"Your location has been updated successfully.";
				   $myarray['status']=1;
				 
				 }
			 } else {
			    
				$myarray['result']=array();					
		       $myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):'Invalid VendorId.';
		        $myarray['status']=0;
				
			 }				 
		 
		 }
		 return response()->json($myarray); 
	}

	public function UpdateVendorStatus(Request $request) {
	    $common=new CommanController;
	    $langId=($request->header('langId'))?($request->header('langId')):1; 
        $showCurrentLocation=($request->showCurrentLocation)?($request->showCurrentLocation):0;

		 if (!$request->vendorId) {
		   $myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
		   $myarray['status']=0;
				 
		} else {
			
			 $vendorId=($request->vendorId)?($request->vendorId):0;
			 $isOnline=($request->isOnline)?($request->isOnline):0;
			 $checkvendor = DB::table('tblvender')->where([['id', '=', $vendorId],['isActive', '=', 1]])->count();
			 
			
			 
			 if($checkvendor>0) {
				   DB::table('tblvender')->where('id',$vendorId)->update(
				   ['isOnline'=>$isOnline,'showCurrentLocation'=>$showCurrentLocation]); 
				   
				   $location=$common->VendorLocation($vendorId);
				   $myarray['result']=array('isOnline'=>(int)$isOnline,'showCurrentLocation'=>(int)$showCurrentLocation);					
				   $myarray['message']="";
				   $myarray['status']=1;
				 
			 } else {
			    
				$myarray['result']=array();					
		       $myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):'Invalid VendorId.';
		        $myarray['status']=0;
				
			 }				 
		 
		 }
		 return response()->json($myarray); 
	}

	public function deleteChat(Request $request) {
	     $common=new CommanController;
         $langId=($request->header('langId'))?($request->header('langId')):1; 

		 if (!$request->chatSessionId) {
		   $myarray['result']=array();					
		   $myarray['message']=$common->get_msg("blank_chat_session_Id",$langId)?$common->get_msg("blank_chat_session_Id",$langId):"Please select chat Session Id.";
		   $myarray['status']=0;
		 
		 } else { 
                
                $chatSessionId=($request->chatSessionId)?($request->chatSessionId):"";

			    $deletechatSession=DB::delete('delete from tblchatsession where chatSessionId = ?',[$chatSessionId]);
			    $deletechatContent=DB::delete('delete from tblchatcontent where chatSessionId = ?',[$chatSessionId]);
			    //$location=$common->CustomerLocation($customerId);
				$myarray['result']=array();					
				$myarray['message']=$common->get_msg("delete_chat",$langId)?$common->get_msg("delete_chat",$langId):"Your chat has been deleted successfully.";
				$myarray['status']=1;
			
		   
		 }
         return response()->json($myarray); 		 
	 }

	 public function UnreadTotalCount(Request $request) {
               $common=new CommanController;
             $userId=($request->userId)?($request->userId):0;
			 $isCustomer=($request->isCustomer)?($request->isCustomer):0;

			 if ($isCustomer==1) {

			 	$leadCount=$common->leadUnreadMsgCustomerListCount($userId);
				$productCount=$common->productUnreadMsgListCount($userId);
				$notificationCount=$common->NotificationCountCustomer($userId);
				$totalUnreadCount=$leadCount+$productCount+$notificationCount;

			 } else {

			 	$leadCount=$common->leadUnreadMsgVendorListCount($userId);
				$productCount=$common->productUnreadMsgListCount($userId);
				$notificationCount=$common->NotificationCountVendor($userId);
				$totalUnreadCount=$leadCount+$productCount+$notificationCount;

			 }

                $res=array('leadCount'=>$leadCount,'productCount'=>$productCount,'notificationCount'=>$notificationCount,'totalUnreadCount'=>$totalUnreadCount);
			    $myarray['result']=$res;					
				$myarray['message']="unReadMsgCount";
				$myarray['status']=1;
				return response()->json($myarray); 

	 }

	 public function bankList(Request $request) {
        $common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $url=url('/');
        $bank = DB::table('tblbanks')->where([['isActive','=','1']])->get(['id','name','icon']);
        
        $bankarr=array();
        
        
        if ($bank) {
            foreach ($bank as  $value) {
            	$bankId=$value->id;
            	$bankName=$common->getBankValue($bankId,$langId);
            	$bankIcon=$value->icon;
					if ($bankIcon!='') {
					  $bankIcon=$url."/bankicon/thumbnail_images/".$bankIcon;
					}
               $bankarr[]=array('bankId'=>(int)$value->id,'bankName'=>$bankName,"bankIcon"=>$bankIcon);
            }        	
        }

        $myarray['result']=$bankarr;					
		         $myarray['message']="Bank List";
		         $myarray['status']=1; 
         return response()->json($myarray);
    }

    public function addEditVendorPaymentType(Request $request) {

    	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $url=url('/');
        $type=($request->type)?($request->type):1;
        $vendorId=($request->vendorId)?($request->vendorId):0;
        $bankId=($request->bankId)?($request->bankId):0;
        $businessEmail=($request->businessEmail)?($request->businessEmail):"";
        $accountNumber=($request->accountNumber)?($request->accountNumber):"";
        $IBAN=($request->IBAN)?($request->IBAN):"";
        $branchName=($request->branchName)?($request->branchName):"";
        $accHolderName=($request->accHolderName)?($request->accHolderName):"";
        $swiftcode=($request->swiftcode)?($request->swiftcode):"";

         if (!$request->vendorId) {
		   $myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
		   $myarray['status']=0;
				 
		} else if(!$request->type) {
			$myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("blank_payment_type",$langId)?$common->get_msg("blank_payment_type",$langId):"Please select payment type.";
		   $myarray['status']=0;
		} else {
             

            $checkvendors = DB::table('tblvender')->where([['id', '=',$vendorId],['isActive','=',1]])->count();
            
         if ($checkvendors > 0) {

			if (!$request->id) {

				if ($type==1) {
                    
                 $checkvendor = DB::table('tblvenderpaymenttype')->where([['venderId', '=', $vendorId],['type', '=',1]])->count();
                     if ($checkvendor==0) {
                    $vendorpaymentType=DB::table('tblvenderpaymenttype')->insert(
               ['venderId'=>$vendorId,'type'=>1,'createdDate'=>date('Y-m-d H:i:s')]);

                           $myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_cash_payment_type_add",$langId)?$common->get_msg("vendor_cash_payment_type_add",$langId):"You have successfully added this payment type.";
					   $myarray['status']=1;

                      } else {
                      	$myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_cash_payment_type",$langId)?$common->get_msg("vendor_cash_payment_type",$langId):"You have already added this payment type.";
					   $myarray['status']=0;
                      } 

				}

				if ($type==2) {
					 
					 $checkvendor = DB::table('tblvenderpaymenttype')->where([['venderId', '=', $vendorId],['emailId', '=',$businessEmail]])->count();
                     if ($checkvendor==0) {
                    $vendorpaymentType=DB::table('tblvenderpaymenttype')->insert(
               ['venderId'=>$vendorId,'type'=>2,'createdDate'=>date('Y-m-d H:i:s'),'emailId'=>$businessEmail]);

                           $myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_paypal_payment_type_add",$langId)?$common->get_msg("vendor_paypal_payment_type_add",$langId):"You have successfully added this payment type.";
					   $myarray['status']=1;

                      } else {
                      	$myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_paypal_payment_type",$langId)?$common->get_msg("vendor_paypal_payment_type",$langId):"You have already added this payment type.";
					   $myarray['status']=0;
                      } 
				}

				if ($type==3) {
					
					$checkvendor = DB::table('tblvenderpaymenttype')->where([['venderId', '=', $vendorId],['bankId', '=',$bankId],['accountNumber', '=',$accountNumber]])->count();
                     
                     if ($checkvendor==0) {
                    $vendorpaymentType=DB::table('tblvenderpaymenttype')->insert(
               ['venderId'=>$vendorId,'type'=>3,'createdDate'=>date('Y-m-d H:i:s'),'bankId'=>$bankId,'accountNumber'=>$accountNumber,'IBAN'=>$IBAN,'branchName'=>$branchName,'accHolderName'=>$accHolderName,'swiftcode'=>$swiftcode]);

                           $myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_bank_payment_type_add",$langId)?$common->get_msg("vendor_bank_payment_type_add",$langId):"You have successfully added this payment type.";
					   $myarray['status']=1;

                      } else {
                      	$myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_bank_payment_type",$langId)?$common->get_msg("vendor_bank_payment_type",$langId):"You have already added this payment type.";
					   $myarray['status']=0;
                      } 
				}
			
			} else {

				if ($type==1) {
                    
                 $checkvendor = DB::table('tblvenderpaymenttype')->where([['venderId', '=', $vendorId],['type', '=',1],['id','=',$request->id]])->count();
                     if ($checkvendor==1) {
                    $vendorpaymentType=DB::table('tblvenderpaymenttype')->where('id',$request->id)->update(
               ['venderId'=>$vendorId,'type'=>1,'createdDate'=>date('Y-m-d H:i:s')]);

                           $myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_cash_payment_type_add",$langId)?$common->get_msg("vendor_cash_payment_type_add",$langId):"You have successfully updated this payment type.";
					   $myarray['status']=1;

                      } else {
                      	$myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_cash_payment_type",$langId)?$common->get_msg("vendor_cash_payment_type",$langId):"Invalid payment type.";
					   $myarray['status']=0;
                      } 

				}

				if ($type==2) {
					 
					 $checkvendor = DB::table('tblvenderpaymenttype')->where([['venderId', '=', $vendorId],['id','=',$request->id]])->count();
                     if ($checkvendor==1) {
                    $vendorpaymentType=DB::table('tblvenderpaymenttype')->where('id',$request->id)->update(
               ['venderId'=>$vendorId,'type'=>2,'createdDate'=>date('Y-m-d H:i:s'),'emailId'=>$businessEmail]);

                           $myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_paypal_payment_type_add",$langId)?$common->get_msg("vendor_paypal_payment_type_add",$langId):"You have successfully updated this payment type.";
					   $myarray['status']=1;

                      } else {
                      	$myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_paypal_payment_type",$langId)?$common->get_msg("vendor_paypal_payment_type",$langId):"Invalid payment type";
					   $myarray['status']=0;
                      } 
				}

				if ($type==3) {
					
					$checkvendor = DB::table('tblvenderpaymenttype')->where([['venderId', '=', $vendorId],['id','=',$request->id]])->count();
                     
                     if ($checkvendor==1) {
                    $vendorpaymentType=DB::table('tblvenderpaymenttype')->where('id',$request->id)->update(
               ['venderId'=>$vendorId,'type'=>3,'createdDate'=>date('Y-m-d H:i:s'),'bankId'=>$bankId,'accountNumber'=>$accountNumber,'IBAN'=>$IBAN,'branchName'=>$branchName,'accHolderName'=>$accHolderName,'swiftcode'=>$swiftcode]);

                           $myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_bank_payment_type_add",$langId)?$common->get_msg("vendor_bank_payment_type_add",$langId):"You have successfully updated this payment type.";
					   $myarray['status']=1;

                      } else {
                      	$myarray['result']=(object)array();					
					   $myarray['message']=$common->get_msg("vendor_bank_payment_type",$langId)?$common->get_msg("vendor_bank_payment_type",$langId):"Invalid payment type";
					   $myarray['status']=0;
                      } 
				}

			}

		  } else {

		  	$myarray['result']=(object)array();	
					$myarray['status']=1;
					$myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):'Invalid VendorId.';

		  }	

		}

        return response()->json($myarray);
    }


    public function deleteVendorPaymentType(Request $request) {

    	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $url=url('/');
        $paymentTypeId=($request->paymentTypeId)?($request->paymentTypeId):1;
        $vendorId=($request->vendorId)?($request->vendorId):0;
        
         if (!$request->vendorId) {
		   $myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
		   $myarray['status']=0;
				 
		} else if(!$request->paymentTypeId) {
			$myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("blank_payment_type_id",$langId)?$common->get_msg("blank_payment_type_id",$langId):"Please select payment type id.";
		   $myarray['status']=0;
		} else {
             

            $checkvendors = DB::table('tblvenderpaymenttype')->where([['id', '=',$paymentTypeId],['venderId','=',$vendorId]])->count();
            
         if ($checkvendors > 0) {

			   $deleteachvenderpaymenttype=DB::delete("delete from tblvenderpaymenttype where id='{$paymentTypeId}'");
			   $myarray['result']=(object)array();	
			   $myarray['status']=1;
			   $myarray['message']=$common->get_msg("deleted_vendor_payment_type",$langId)?$common->get_msg("deleted_vendor_payment_type",$langId):'Your payment type has been deleted successfully.';

			} else {

		  	$myarray['result']=(object)array();	
			$myarray['status']=0;
			$myarray['message']=$common->get_msg("invalid_vendorId_paymentId",$langId)?$common->get_msg("invalid_vendorId_paymentId",$langId):'Invalid VendorId or Payment Id.';

		  }	

		}

        return response()->json($myarray);
    }
     
     public function verifyUserCode(Request $request) {
         
         $common=new CommanController;
	     $langId=($request->header('langId'))?($request->header('langId')):1;
         
         //if ($authenticate==$serverauthenticate) { 
	    	if (!$request->userId) { 
				    $myarray['result']=(object)array();
					$myarray['message']=$common->get_msg("userId_blank",$langId)?$common->get_msg("userId_blank",$langId):"Please enter userId.";
					$myarray['status']=0;
			 } elseif (!$request->phoneVerificationCode) { 
				    $myarray['result']=(object)array();
					$myarray['message']=$common->get_msg("verifycode_blank",$langId)?$common->get_msg("verifycode_blank",$langId):"Please enter Verification Code.";
					$myarray['status']=0;
			 } else {
	              
	              $checkcode = DB::table('tblvender')->where('id', '=',$request->userId)->where('phoneVerificationCode', '=',$request->phoneVerificationCode)->first();
	              if (!$checkcode) {
	              	$myarray['result']=(object)array();
					$myarray['message']=$common->get_msg("verifycode_invalid",$langId)?$common->get_msg("verifycode_invalid",$langId):"Invalid Verification Code.";
					$myarray['status']=0;
	              } else {
	              	$verificationcodelimit=($common->getSettingValue('verification_code_limit_in_minute'))?($common->getSettingValue('verification_code_limit_in_minute')):30;
	              	 $vendor=Vendor::find($request->userId);
	              	 $phoneVerificationSentRequestTime=$vendor->phoneVerificationSentRequestTime;
					 $currentdatetime=date('Y-m-d H:i:s');
					 
                      //echo $verificationcodelimit;
                      //exit();
					 $start_date = new \DateTime($phoneVerificationSentRequestTime);
                     $since_start = $start_date->diff(new \DateTime($currentdatetime));
                     $diffInMinutes=$since_start->i;
                     $diffInSeconds=$since_start->s;
                     
                     //echo $diffInMinutes;
                    // echo $verificationcodelimit;
                     //exit();
                     if ($diffInMinutes <= $verificationcodelimit) {
                          $vendor->isPhoneVerified=1;
                          $vendor->save(); 
                           $user_info=$common->VendorDetailsNew($request->userId,$langId);

					 $myarray['result']=$user_info;
					$myarray['message']=$common->get_msg("verifycode_success",$langId)?$common->get_msg("verifycode_success",$langId):"Your phone has been successfully verified.";
					$myarray['status']=1;

                     } else {
                          $myarray['result']=(object)array();
					$myarray['message']=$common->get_msg("expired_verification_code",$langId)?$common->get_msg("expired_verification_code",$langId):"Your verification code has been expired.";
					$myarray['status']=0;                     	
                     }

                     //echo $diffInMinutes;
                      //exit();

                     //echo $diffInMinutes;
                     //echo $diffInSeconds;
                     //exit;

					 //$vendor->isVerify=1;
					 //$vendor->save();
	                   
	                   
	              }

			 }
		/*} else {
	    	$myarray['result']=array();					
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
	    } */ 	 

		  return response()->json($myarray);
    }

    public function reSendVerificationCode(Request $request) {
         
         $common=new CommanController;
	     $langId=($request->header('langId'))?($request->header('langId')):1;
         //$serverauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
         //$authenticate=$this->authenticate();
         //if ($authenticate==$serverauthenticate) { 
	    	if (!$request->userId) { 
				    $myarray['result']=(object)array();
					$myarray['message']=$common->get_msg("userId_blank",$langId)?$common->get_msg("userId_blank",$langId):"Please enter userId.";
					$myarray['status']=0;
			 } elseif (!$request->phone) {
				   $myarray['result']=(object)array();					
				   $myarray['message']=$common->get_msg("phone_blank",$langId)?$common->get_msg("phone_blank",$langId):"Please Enter Phone.";
				   $myarray['status']=0;
				 
			 } else {
	              
	              $checkcode = DB::table('tblvender')->where('id', '=',$request->userId)->count();
	              if ($checkcode==0) {
	              	$myarray['result']=(object)array();
					$myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):'Invalid VendorId.';
					$myarray['status']=0;
	              } else {
	              	$digits = 4;
                    $verificationCode=rand(pow(10, $digits-1), pow(10, $digits)-1);
        	
	              	 $vendor= Vendor::find($request->userId);
					 $vendor->phoneVerificationCode=$verificationCode;
					 $vendor->phoneVerificationSentRequestTime=date('Y-m-d H:i:s');
					 $vendor->save();
	                   
	                 $user_info=array('phoneVerificationCode'=>$verificationCode);
                          
                      $isInvalid=0;    
	                  if ($request->phone && $request->phone!='') {

                       	$sid    = env('TWILIO_ACCOUNT_SID');
				       $token  = env('TWILIO_AUTH_TOKEN');
				       $from   = env('TWILIO_PHONE_NUMBER');
				        $text="".$verificationCode." is your Quickserve verification code.";
				       $client = new Client( $sid, $token );

					               try
					               {
						            // Use the client to do fun stuff like send text messages!
						            $client->messages->create(
						            // the number you'd like to send the message to
						                $request->phone,
						           array(
						                 // A Twilio phone number you purchased at twilio.com/console
						                 'from' => env('TWILIO_PHONE_NUMBER'),
						                 // the body of the text message you'd like to send
						                 'body' => $text
						             )
						         );
						            //echo "sucess";
						            //exit;
						            $messages ="success";
						           }
							        catch (\Exception $e)
							        {
							        	$isInvalid=1;
							        	if($e->getCode() == 21614)
													{
														
												   	$messages = $e->getMessage();
												    // echo $messages;
												     //exit();
												   } else {
							                          $messages="Invalid Mobile No.";
												   }
							            
							        }

                       }

                       /* sms code end */
                     if ($isInvalid==0) {
					 $myarray['result']=$user_info;
					$myarray['message']=$common->get_msg("verifycode_sent",$langId)?$common->get_msg("verifycode_sent",$langId):"Your verification code has been sent you.";
					$myarray['status']=1;
                       } else {
                       	$myarray['result']=(object)array();
					$myarray['message']=$common->get_msg("invalid_phone_number",$langId)?$common->get_msg("invalid_phone_number",$langId):"Please enter valid phone number.";
					$myarray['status']=0;
                       }

	              
	              }

			 }
		/*} else {
	    	$myarray['result']=array();					
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
	    } */ 	 

		  return response()->json($myarray);
    }

    public function planList(Request $request) {

        $common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $url=url('/');
        $plan = DB::table('tblsubscriptionplans')->where([['isActive','=','1'],['price','>','0']])->get();
        
        $planarr=array();
        
        
        if ($plan) {
            foreach ($plan as  $value) {
            	$planId=$value->id;
            	$noOfLeadsPerDuration=$value->noOfLeadsPerDuration;
            	$price=$value->price;

            	$planName=($common->getSubscriptionPlanNameValue($planId,$langId))?($common->getSubscriptionPlanNameValue($planId,$langId)):"";
            	$planDesc=($common->getSubscriptionPlanDescValue($planId,$langId))?($common->getSubscriptionPlanDescValue($planId,$langId)):"";

            	
               $planarr[]=array('planId'=>(int)$planId,'planName'=>$planName,"planDesc"=>$planDesc,"noOfLeadsPerMonth"=>$noOfLeadsPerDuration,"planPrice"=>$price);
            }

             $myarray['result']=$planarr;					
		     $myarray['message']=$common->get_msg("plan_list",$langId)?$common->get_msg("plan_list",$langId):"Plan list.";
		     $myarray['status']=1;         	
        } else {
             $myarray['result']=(object)$planarr;					
		     $myarray['message']=$common->get_msg("no_plan_found",$langId)?$common->get_msg("no_plan_found",$langId):"No plan found.";
		     $myarray['status']=0; 
        }

       
         return response()->json($myarray);
    }

    public function vendorCharity(Request $request) {
               
        $common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $url=url('/');
        
             if (!$request->vendorId) {
		   $myarray['result']=(object)array();					
		   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
		   $myarray['status']=0;
				 
		   } elseif (!$request->day) {
				   $myarray['result']=(object)array();					
				   $myarray['message']=$common->get_msg("day_blank",$langId)?$common->get_msg("day_blank",$langId):"Please Enter Day.";
				   $myarray['status']=0;
				 
		    } else {
                    
                    $vendorId=($request->vendorId)?($request->vendorId):0;
                    $day=($request->day)?($request->day):"";
                    //$fromTime=($request->fromTime)?($request->fromTime):"";
                    //$toTime=($request->toTime)?($request->toTime):"";

                  $checkvendors = DB::table('tblvender')->where([['id', '=',$vendorId],['isActive','=',1]])->count();
                    if ($checkvendors > 0) {
                    	//print_r($day);
                    	//exit();

			            if ($day) {
			            	$days=json_decode($day,true);
			            	//print_r($days);
			            	//exit();
			            	 $deletebusinessCat=DB::select( DB::raw("delete from tblvendorcharity where `vendorId`='$vendorId'"));
			            	 
			            	foreach ($days as $key => $value) {
			            		//echo $key;
			            		//echo $value['dayIndex'];
			            		//echo $value['fromTime'];
			            		//exit();
			            		//print($value);
			            		//exit();
			            	     $dayvalue=($value['dayIndex'])?($value['dayIndex']):0;
			            		 
			            		 $fromTimeVal=($value['fromTime'])?($value['fromTime']):"";
			            	     $toTimeVal=($value['toTime'])?($value['toTime']):"";
			            		
			            		 
			            		 /*echo $dayvalue;
			            		 exit;
			                     $fromTimeVal="";
			                     $toTimeVal="";
			                     if (isset($fromTime[$key])) {
			                     $fromTimeVal=$fromTime[$key];
			                      }
			                     if (isset($toTime[$key])) { 
			                     $toTimeVal=$toTime[$key];
			                     }*/
			                     //echo $toTimeVal;
			                     //exit(); 
			                    /*$checkCharity= DB::table('tblvendorcharity')->where([['vendorId', '=',$vendorId],['day','=',$value]])->count();*/

			                   

			                      /*if ($checkCharity > 0) {
	                                $deletebusinessCat=DB::select( DB::raw("delete from tblvendorcharity where `vendorId`='$vendorId' and day='$dayvalue'"));
			                      }*/ 
	                                 $vendorpaymentType=DB::table('tblvendorcharity')->insert(
	                                 ['vendorId'=>$vendorId,'day'=>$dayvalue,'fromTime'=>$fromTimeVal,'toTime'=>$toTimeVal,'createdDate'=>date('Y-m-d H:i:s')]);
			                      
			            	}
			                  
			                $res=$common->getVendorCharityDetail($vendorId);    
			                $myarray['result']=$res;					
					   $myarray['message']=$common->get_msg("charity_details",$langId)?$common->get_msg("charity_details",$langId):"Your charity details.";
					   $myarray['status']=1;    

			            	}
			         } else {
			         	  $myarray['result']=(object)array();	
					      $myarray['status']=1;
					      $myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):'Invalid VendorId.';
			         }   	
		             

		    }
		    return response()->json($myarray);

    }

    public function vendorSubscriptionPlan(Request $request) {

	    	$common=new CommanController;
	        $langId=($request->header('langId'))?($request->header('langId')):1;
	        $url=url('/');
            
            if (!$request->vendorId) {
			   $myarray['result']=(object)array();					
			   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
			   $myarray['status']=0;
				 
		   } else {

                $currDate=date('Y-m-d');

		   	    $vendorId=($request->vendorId)?($request->vendorId):0;
                
                $checkvendors = DB::table('tblvender')->where([['id', '=',$vendorId],['isActive','=',1]])->count();

		   	    if ($checkvendors > 0) {

		   	    	  $vendorsubscription = DB::table('tblvendersubscription')->where([['venderId', '=', $vendorId],['status', '=',1],['endDate','>=',$currDate]])->orderBy('id', 'desc')->take(1)->get();

		   	    	  $subscription=array();

						if (count($vendorsubscription) > 0) {
							 foreach ($vendorsubscription as  $value) {
							 	$subscription=array("vendorSubscriptionPlanId"=>$value->id,"subscriptionPlanId"=>$value->subscriptionPlanId,"subscriptionName"=>$value->subscriptionName,"subscriptionPrice"=>$value->price,"subscriptionstartDate"=>$value->startDate,"subscriptionendDate"=>$value->endDate,"noOfLeads"=>$value->noOfLeadsPerDuration,"noOfRemainingLeads"=>$value->noOfRemainingLeads,"status"=>$value->status);
							 }

					 $myarray['result']=$subscription;	
			         $myarray['status']=1;
			         $myarray['message']=$common->get_msg("plan_list",$langId)?$common->get_msg("plan_list",$langId):'Plan List.';	
						} else {
						$myarray['result']=(object)array();	
			         $myarray['status']=1;
			         $myarray['message']=$common->get_msg("no_plan_found",$langId)?$common->get_msg("no_plan_found",$langId):'No plan found.';	
						}


					  

		   	    } else {

		   	    	$myarray['result']=(object)array();	
			        $myarray['status']=0;
			        $myarray['message']=$common->get_msg("invalid_vendorId",$langId)?$common->get_msg("invalid_vendorId",$langId):'Invalid VendorId.';
		   	    }

		   } 

		   return response()->json($myarray);

    }

    public function addVendorBusinessCategory(Request $request) {
           
            $common=new CommanController;
	        $langId=($request->header('langId'))?($request->header('langId')):1;
	        $url=url('/');
              
              if (!$request->vendorId) {
			   $myarray['result']=(object)array();					
			   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
			   $myarray['status']=0;
				 
		     } elseif (!$request->businessCategoryId) {
				   $myarray['result']=(object)array();					
				   $myarray['message']=$common->get_msg("businesscategoryId_blank",$langId)?$common->get_msg("businesscategoryId_blank",$langId):"Please enter business categoryId.";
				   $myarray['status']=0;
				 
		     } else {
                   
                   $delete=DB::select( DB::raw("delete from tblvenderbusinesscategory where `vendorId`='$request->vendorId'"));  

                   $buscatexplode=explode(",",$request->businessCategoryId);
                       foreach ($buscatexplode as  $value) {
                       	  $checkcat = DB::table('tblvenderbusinesscategory')->where([['vendorId', '=',$request->vendorId],['businessCategoryId', '=',$value]])->count();
                       	  
                          if ($checkcat==0) {
                          	$vendorCategory=DB::select( DB::raw("Insert tblvenderbusinesscategory SET `vendorId`='$request->vendorId',businessCategoryId={$value}"));      
                          }
                          
                          /*if ($checkcat>0) {
                              $delete=DB::select( DB::raw("delete from tblvenderbusinesscategory where `vendorId`='$request->vendorId' and businessCategoryId={$value}"));      
                          } else {
                          	   $vendorCategory=DB::select( DB::raw("Insert tblvenderbusinesscategory SET `vendorId`='$request->vendorId',businessCategoryId={$value}")); 
                          } */


                       }
                     $suserId=0;
                       
                    $VendorDetails=$common->vendorBusinessCategoryListData($request->vendorId,$langId);
					$myarray['result']=$VendorDetails;
					$myarray['message']=$common->get_msg("updated_business_cat",$langId)?$common->get_msg("updated_business_cat",$langId):"You have successfully updated business category.";
					$myarray['status']=1;

		     }

		     return response()->json($myarray);

    	  
    }

    public function deleteVendorBusinessCategory(Request $request) { 

    	    $common=new CommanController;
	        $langId=($request->header('langId'))?($request->header('langId')):1;
	        $url=url('/');
              
              if (!$request->vendorId) {
			   $myarray['result']=(object)array();					
			   $myarray['message']=$common->get_msg("blank_vendorId",$langId)?$common->get_msg("blank_vendorId",$langId):"Please select vendorId";
			   $myarray['status']=0;
				 
		     } elseif (!$request->businessCategoryId) {
				   $myarray['result']=(object)array();					
				   $myarray['message']=$common->get_msg("businesscategoryId_blank",$langId)?$common->get_msg("businesscategoryId_blank",$langId):"Please enter business categoryId.";
				   $myarray['status']=0;
				 
		     } else {
                   
                   $isInvalid=0;
                   $buscatexplode=explode(",",$request->businessCategoryId);

                   foreach ($buscatexplode as  $value) {
                       	  $checkcat = DB::table('tblvenderbusinesscategory')->where([['vendorId', '=',$request->vendorId],['businessCategoryId', '=',$value]])->count();
                          if ($checkcat>0) {
                              $delete=DB::select( DB::raw("delete from tblvenderbusinesscategory where `vendorId`='$request->vendorId' and businessCategoryId={$value}"));      
                          } else {
                          	$isInvalid=1;
                          }

                       }

                    if ($isInvalid==1) {
                      $VendorDetails=$common->vendorBusinessCategoryListData($request->vendorId,$langId);
					$myarray['result']=(object)array();
					$myarray['message']=$common->get_msg("invalid_cat_vendor",$langId)?$common->get_msg("invalid_cat_vendor",$langId):"Invalid Business Category or VendorId";
					$myarray['status']=1;
                    } else {
                    $VendorDetails=$common->vendorBusinessCategoryListData($request->vendorId,$langId);
					$myarray['result']=$VendorDetails;
					$myarray['message']=$common->get_msg("delete_vendor_business_cat",$langId)?$common->get_msg("delete_vendor_business_cat",$langId):"You have successfully deleted business category.";
					$myarray['status']=1;
                    }   
                     
                       
                    

		     }

             return response()->json($myarray);


    }
 
    
}