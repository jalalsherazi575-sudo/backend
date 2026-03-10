<?php
namespace Laraspace\Http\Controllers\API;
use Illuminate\Support\Facades\Session;
use Laraspace\Http\Controllers\Controller; 
use Laraspace\Http\Controllers\CommanController;
use Laraspace\Mail\VendorForgotPassword;
use Laraspace\Mail\CustomerForgotPassword;
use Laraspace\Mail\GeneralMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Carbon;
use Illuminate\Http\Request; 
use Laraspace\QuestionCommnent;
use Laraspace\UnnecessaryWords;
use Laraspace\LevelManagement;
use Laraspace\TransactionDetails;
use Laraspace\Questions;
use Laraspace\Banner;
use Laraspace\Subject;
use Laraspace\Topics;
use Laraspace\LearningAnswer;
use Validator;
use Config;
use Hash;
use Mail;
use File;
use Laraspace\CustomerRegister;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;



use Laraspace\Helpers\Helper;

class ApiController extends Controller 
{
    public $successStatus = 200;
	/** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
    */ 
	 
	public function __construct(Config $authenticate){
	    $this->_authenticate = config('constant.authenticate');
	}
	/*Get Messages*/
	public function getMessages(Request $request) 
	{
	    $common=new CommanController;
	    $lang_id=($request->lang_id)?($request->lang_id):1;
	    //$userId=($request->userId)?($request->userId):0;
	    //$isVendor=($request->isVendor)?($request->isVendor):0;
        $langId=($request->header('langId'))?($request->header('langId')):1; 
        $apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
        $authenticate=$this->_authenticate;
       
        if (in_array($apiauthenticate,$authenticate)) {
			// Fixed SQL Injection: Use query builder with parameter binding
			$data = DB::table('tblgeneralmessagetranslation')
				->join('tblgeneralmessage', 'tblgeneralmessage.id', '=', 'tblgeneralmessagetranslation.general_message_id')
				->select('tblgeneralmessage.title_key', 'tblgeneralmessagetranslation.title_value')
				->where('tblgeneralmessagetranslation.lang_id', '=', $lang_id)
				->where('tblgeneralmessage.is_app_msg', '=', '1')
				->get(); 
			$vals='';
			$messageList = []; // Fixed: Initialize to prevent undefined variable error
			if ($data && count($data) > 0) {
		      	foreach ($data as $values) {
				   	$title_value=$values->title_value;
				   	$title_key=$values->title_key;
				   	$messageList[]=array("msgKey"=>$title_key,"msgValue"=>$title_value);
				}
				$myarray['result']=$messageList;					
				$myarray['message']=$common->get_msg("message_list",$langId)?$common->get_msg("message_list",$langId):"Message List.";
				$myarray['status']=1;
			}else {
		       	$myarray['result']=array();					
				$myarray['message']=$common->get_msg("no_message_list",$langId)?$common->get_msg("no_message_list",$langId):"No App Messages Found.";
				$myarray['status']=1;
		   	}
		} else {
			$myarray['result']=(object)array();					
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
        }	   

	   return response()->json($myarray);
	}
	public function customerLogout(Request $request) 
	{
		$deviceType=($request->deviceType)?($request->deviceType):0;
	   	$deviceToken=($request->deviceToken)?($request->deviceToken):"";
	   	$userId=($request->userId)?($request->userId):0;
	   	$common=new CommanController;
       	$langId=($request->header('langId'))?($request->header('langId')):1; 

	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
       	$authenticate=$this->_authenticate;

	   	if (in_array($apiauthenticate,$authenticate)) {
            if (!$request->userId) {
			   	$myarray['result']=array();					
			   	$myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
			   	$myarray['status']=0;
			 
			} elseif (!$request->deviceType) {
			   	$myarray['result']=array();					
			   	$myarray['message']=$common->get_msg("blank_deviceType",$langId)?$common->get_msg("blank_deviceType",$langId):"Please enter deviceType.";
			   	$myarray['status']=0;
			 
			} elseif (!$request->deviceToken) {
			   	$myarray['result']=array();					
			   	$myarray['message']=$common->get_msg("blank_deviceToken",$langId)?$common->get_msg("blank_deviceToken",$langId):"Please enter deviceToken.";
			   	$myarray['status']=0;
			} else {
				$checkDeviceTokenCount = DB::table('tbldevicetoken')->where([['customerId', '=',$userId],['deviceType', '=',$deviceType],['deviceToken', '=',$deviceToken]])->count();
                 
                if ($checkDeviceTokenCount > 0) {
                	// Fixed SQL Injection: Use query builder with parameter binding
                	$deletecustomerpaymenttype=DB::table('tbldevicetoken')
                		->where('customerId', '=', $userId)
                		->where('deviceType', '=', $deviceType)
                		->where('deviceToken', '=', $deviceToken)
                		->delete();
                 	  
                 	$customerDeviceTokenUpdate=DB::table('tblcustomer')->where([['id', '=',$userId]])->update(['loginStatus'=>0,'lastLogoutDate'=>date('Y-m-d H:i:s')]);

                 	$myarray['result']=array();					
			        $myarray['message']=$common->get_msg("logout_success",$langId)?$common->get_msg("logout_success",$langId):"You have successfully logout from this device.";
			        $myarray['status']=1;  
				} else {
             		$myarray['result']=array();					
			        $myarray['message']=$common->get_msg("invalid_login_credintial",$langId)?$common->get_msg("invalid_login_credintial",$langId):"Invalid login credintial.";
			        $myarray['status']=0;
                }
			}
		} else {
           
           	$myarray['result']=array();					
		   	$myarray['message']="Invalid Authentication.";
		   	$myarray['status']=0;
	   } 
	    return response()->json($myarray);
	}

	/* Insert DeviceToken */

    public function InsertDeviceToken(Request $request) 
    {

    	$deviceType=($request->deviceType)?($request->deviceType):0;
		$deviceToken=($request->deviceToken)?($request->deviceToken):"";
		$deviceDetails=($request->deviceDetails)?($request->deviceDetails):"";
		$my_array=array();
		$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1; 

        $apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
        $authenticate=$this->_authenticate;

        if (in_array($apiauthenticate,$authenticate)) { 
			if (!$request->deviceType) {
			   	$myarray['result']=(object)array();					
			   	$myarray['message']=$common->get_msg("blank_device_type",$langId)?$common->get_msg("blank_device_type",$langId):"Please Enter DeviceType.";
			   	$myarray['status']=0;
				 
		 	}  elseif (!$request->deviceToken) {
			   	$myarray['result']=(object)array();					
			   	$myarray['message']=$common->get_msg("blank_device_Token",$langId)?$common->get_msg("blank_device_Token",$langId):"Please Enter Device Token.";
			   	$myarray['status']=0;
		 	} else {
				if ($deviceType!=0 && $deviceToken!='') {
					if ($deviceToken!='' && strlen($deviceToken) > 40) {
						$countToken= DB::table('tbldevicetoken')->where('deviceType', '=',$deviceType)->where('deviceToken', '=',$deviceToken)->count();     
	               
		                if ($countToken==0) {
		                	// Fixed SQL Injection: Use query builder with parameter binding
		                	$deleteDeviceToken=DB::table('tbldevicetoken')
		                		->where('deviceType', '=', $deviceType)
		                		->where('deviceToken', '=', $deviceToken)
		                		->delete();
		                    $insert=DB::table('tbldevicetoken')->insert(['deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'deviceDetails'=>$deviceDetails,'tokenDate'=>date('Y-m-d H:i:s')]);
						}
					}   
                }

		     	$myarray['result']=(object)array();
			 	$myarray['status']=1;
			 	$myarray['message']=$common->get_msg("devicedetail_updated",$langId)?$common->get_msg("devicedetail_updated",$langId):'Your device detail updated successfully.'; 
			}	
	    } else {
	    	$myarray['result']=(object)array();					
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
	    } 
        return response()->json($myarray); 
    }
	
	/* Check Version */
	
	public function checkVersion(Request $request) 
	{
		$issubscription = 0;
		$isUpdateAvailable = 0;
		$Url = '';
		$my_array=array();
		$msg='';
		$updateMessage='';
		$culture_code='';
	    $user_info=array();
	    $common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1; 

        $apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
        $authenticate=$this->_authenticate;

        $deviceType=($request->deviceType)?($request->deviceType):0;
		$deviceToken=($request->deviceToken)?($request->deviceToken):"";
		$deviceDetails=($request->deviceDetails)?($request->deviceDetails):"";
		$userId=isset($request->userId)?($request->userId):0;	
        
        if (in_array($apiauthenticate,$authenticate)) { 

			if (!$request->deviceType) {
				$myarray['result']=(object)array();					
				$myarray['message']=$common->get_msg("blank_device_type",$langId)?$common->get_msg("blank_device_type",$langId):"Please Enter DeviceType.";
				$myarray['status']=0;
		 	}  elseif (!$request->version) {
				$myarray['result']=(object)array();					
				$myarray['message']=$common->get_msg("blank_app_version",$langId)?$common->get_msg("blank_app_version",$langId):"Please Enter App Version.";
				$myarray['status']=0;
			} else {
				$url=url('/');
				$aboutusLink=$url."/about_us";
				$termsLink=$url."/terms";
				$privacyPolicyLink=$url."/privacypolicy";

			    $Version = DB::table('tblversion')->where([['app_type', '=', $request->deviceType],['app_version','>',$request->version]])->first();
				$contactusemail=$common->getSettingValue('contact_us_email');
				
				$is_approved = 0;

			    if ($Version) {
				 	$culture_code = $Version->culture_code;
			        $isUpdateAvailable = intval($Version->is_update_available);
					$is_approved = intval($Version->is_approved);
					$app_version=intval($Version->app_version);
				}

	            $GraterVersion= DB::table('tblversion')->where([['app_type', '=', $request->deviceType],['app_version','>',$request->version]])->first();

	            
	            
	            

	            $checkSameVersion= DB::table('tblversion')->where([['app_type', '=', $request->deviceType],['app_version','=',$request->version]])->first();

		        $AppLink='';
				if ($request->deviceType && $request->deviceType==1) {
				  $AppLink=$common->getAndroidAppLink();
				}
				if ($request->deviceType && $request->deviceType==2) {
				  $AppLink=$common->getIosAppLink();
				}
					
                $updateMessage="No update available";
	            

	            
                
                if (!empty($GraterVersion)) {

                	

					$Url=$GraterVersion->app_url;
					$isUpdateAvailable = intval($GraterVersion->is_update_available);
					$issubscription = 1;
					if ($isUpdateAvailable==0) {
						$updateMessage="Mandatory update available";
					}

					if ($isUpdateAvailable==1) {
						$updateMessage="No mandatory update available";
					}

					if ($isUpdateAvailable==2) {
						$updateMessage="Mandatory update available";
					}
						
				} else if(!empty($checkSameVersion)) {

					

					$isUpdateAvailable=0;
					$updateMessage="No update available";
					$issubscription = 1;

				} else {

					$issubscription = 0;
					if ($isUpdateAvailable==0) {
						$updateMessage="No update available";
					}

					if ($isUpdateAvailable==1) {
						$updateMessage="No mandatory update available";
					}

					if ($isUpdateAvailable==2) {
						$updateMessage="Mandatory update available";
					}
						
				}
                    
              	$contactusLink=$url."/contact_us_customer";
				$feedbackLink=$url."/feedback_customer";
					
				if ($deviceType!=0 && $deviceToken!='') {
					if ($deviceToken!='' && strlen($deviceToken) > 40) {
						$countToken= DB::table('tbldevicetoken')->where('deviceType', '=',$deviceType)->where('deviceToken', '=',$deviceToken)->count();     
	               		
	               		if ($countToken==0) {
							// Fixed SQL Injection: Use query builder with parameter binding
						$deleteDeviceToken=DB::table('tbldevicetoken')
							->where('deviceType', '=', $deviceType)
							->where('deviceToken', '=', $deviceToken)
							->delete();
		                    $insert=DB::table('tbldevicetoken')->insert(['deviceType'=>$deviceType,'deviceToken'=>$deviceToken,'deviceDetails'=>$deviceDetails,'tokenDate'=>date('Y-m-d H:i:s')]);
						}
					}   
				}
                 
              	if ($userId!=0) {
                    $loggedInData =  CustomerRegister::select('*',DB::raw("CONCAT('".url('/')."/customerregisterphoto/thumbnail_images/', photo) as photo"))->where('id',$userId)->first(); 

                 	$custData=$loggedInData->toArray();
                 	$packageData=$common->getUserSubscriptionPlanPackage($userId);
                 	$CustomerSubscriptionArr=array_merge($custData,$packageData);

              	} else {
                    $CustomerSubscriptionArr=(object)array();
              	} 
					    

			  	$UnderConstContent = $common->get_msg("errorMsgShortMaintananceBreak",$langId) ? $common->get_msg("errorMsgShortMaintananceBreak",$langId) : '';
				$my_array = array("isUpdateAvailable"=>$isUpdateAvailable,"issubscription"=>$issubscription,"updateMessage"=>$updateMessage,"isApprovedApp"=>$is_approved,'maintenanceMsg'=>$UnderConstContent,"Url"=>$Url,"aboutusLink"=>$aboutusLink,"contactusLink"=>$contactusLink,"feedbackLink"=>$feedbackLink,"privacypolicyLink"=>$privacyPolicyLink,"termsLink"=>$termsLink,"userInfo"=>$CustomerSubscriptionArr);
		    	$final_array = array("result"=>$my_array,"message"=>"",'status'=>1);					
	      	}

		} else {
			$myarray['result']=(object)array();					
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
			$final_array = $myarray; // Fixed: use $myarray instead of undefined $my_array
        }      
		return response()->json($final_array);			
	}

	public function bannerList(Request $request)
	{
		$common=new CommanController;
       	$langId=($request->header('langId'))?($request->header('langId')):1; 
	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
       	$authenticate=$this->_authenticate;
	   	$now = Carbon::now();
	    if (in_array($apiauthenticate,$authenticate)) {
	    	$banners = Banner::whereDate('startDate', '<=', $now)
			    ->whereDate('endDate', '>=', $now)
			    ->get();


			if(count($banners)>0){
				$myarray['result']=$banners;
			  	$myarray['message']="Banner data is available.";
			  	$myarray['status']=1;	
			}else{
				$myarray['result']=$banners;
			  	$myarray['message']="Banner data is not available.";
			  	$myarray['status']=0;
			}
	    	
		} else {
		  	$myarray['result']=(object)array();					
		  	$myarray['message']="Invalid Authentication.";
		  	$myarray['status']=0;
        }	
		return response()->json($myarray); 
	}


	public function trainingSubjectsList(Request $request)
	{
		$common=new CommanController;
       	$langId=($request->header('langId'))?($request->header('langId')):1; 
	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
       	$authenticate=$this->_authenticate;
       	if (in_array($apiauthenticate,$authenticate)) {

       		$perPage=($request->perPage)?($request->perPage):10;
	       	$currentPage=($request->currentPage)?($request->currentPage):1;
	       	$userId=($request->userId)?($request->userId):0;
	       	$offset = ($currentPage - 1) * $perPage;
	       	$userId=($request->userId)?($request->userId):0;
	       	$categoryId=($request->categoryId)?($request->categoryId):0;
	       	// $currentDateTime = Carbon::now()->endOfDay();
	       	$currentDateTime = now()->format('Y-m-d H:i:s');
	       	$results = DB::table('subject as subjects')
			    ->select(
			        'subjects.id as id',
			        'subjects.subjectName as subjectName',
			        DB::raw("CONCAT('" . url('images/subject') . "/', subjects.subImage) as subImage"),
			        'subjects.categoryId as categoryId'
			    )
			    ->join('transaction_details as td', 'td.subject_id', '=', 'subjects.id')
			    ->where('td.customer_id', $userId)
			    ->where('td.start_date', '<', $currentDateTime)
			    ->where('td.end_date', '>=', $currentDateTime)
			    ->where('td.category_id', $categoryId)
			    ->where('td.status', '1')
			    ->skip($offset)
			    ->limit($perPage)
			    ->get();




	       	if(count($results)>0){
				$myarray['result']=$results;
			  	$myarray['message']="Subject data is available.";
			  	$myarray['status']=1;	
			}else{
				$myarray['result']=(object)array();
			  	$myarray['message']="Subject data is not available.";
			  	$myarray['status']=0;
			}

       	}else{
	    	$myarray['result']=(object)array();
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
	    }	
		
		return response()->json($myarray);

	}


	public function dashboardSubject(Request $request)
	{
		$common=new CommanController;
       	$langId=($request->header('langId'))?($request->header('langId')):1; 
	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
       	$authenticate=$this->_authenticate;


       	

       	if (in_array($apiauthenticate,$authenticate)) {
       		$perPage=($request->perPage)?($request->perPage):10;
	       	$currentPage=($request->currentPage)?($request->currentPage):1;
	       	$userId=($request->userId)?($request->userId):0;
	       	$offset = ($currentPage - 1) * $perPage;
	       	$userId=($request->userId)?($request->userId):0;
	       	$categoryId=($request->categoryId)?($request->categoryId):0;
	   		$subjects = Subject::where('categoryId',$categoryId)->offset($offset)->limit($perPage)->get();
	   		$i=0;
	   		$subjectData =$subjects->toArray();
	   		// $currentDateTime = Carbon::now()->endOfDay(); // Set the time to the end of today (23:59:59)
	   		$currentDateTime = now()->format('Y-m-d H:i:s');
	   		//print_r($subjectData);
	   		foreach($subjectData as $sub){
	   			$checkSubscribed = TransactionDetails::where("subject_id",$sub['id'])->where('customer_id', $userId)
							    	->where('start_date', '<', $currentDateTime)
								    ->where('end_date', '>=', $currentDateTime)
								    ->where('status', '1')
								    ->orderBy('end_date', 'DESC')->first();

								   /*if ($sub['id'] == 34) {
       	  							echo "<pre>";print_r($userId);
       	  							echo "<pre>";print_r($currentDateTime);
       	  							echo "<pre>";print_r($currentDateTime);
       	  							exit();
								   }*/


			       /*$q1 = $checkSubscribed->toSql();
			        $bindings1 = $checkSubscribed->getBindings();
			        $fullQuery1 = vsprintf(str_replace('?', "'%s'", $q1), $bindings1);
							echo "<pre>";
							print_r($fullQuery1);
							exit;*/
				$subjectData[$i]['isSubscribed'] = $checkSubscribed ? '1' : '0';
	   			if($sub['subImage'] != ''){
	   				$subjectData[$i]['subImage'] = url('images/subject/'.$sub['subImage']);
	   			}
	   			$i++;
	   		}
	   		//exit;
	   		//echo "<pre>";print_r($subjectData);exit;
	    	if(count($subjectData)>0){
				$myarray['result']=$subjectData;
			  	$myarray['message']="Subject data is available.";
			  	$myarray['status']=1;	
			}else{
				$myarray['result']=(object)array();
			  	$myarray['message']="Subject data is not available.";
			  	$myarray['status']=0;
			}
	    }else{
	    	$myarray['result']=(object)array();
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
	    }	
		
		return response()->json($myarray);
   	}

   	public function trainingCategoryList(Request $request)
   	{
   		$isPurchased = 0;
   		$common=new CommanController;
       	$langId=($request->header('langId'))?($request->header('langId')):1; 
	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
       	$authenticate=$this->_authenticate;
       	if (in_array($apiauthenticate,$authenticate)) {
	       	$perPage=($request->perPage)?($request->perPage):10;
	       	$currentPage=($request->currentPage)?($request->currentPage):1;
	       	$userId=($request->userId)?($request->userId):0;
	       	$offset = ($currentPage - 1) * $perPage;
	       	$currentDateTime = now()->format('Y-m-d H:i:s');






	       	$results = DB::table('tbllevelmanagement as l')
    ->select(
        DB::raw('DISTINCT l.levelId as levelId'),
        'l.levelName as levelName',
        DB::raw("CONCAT('" . url('images/category/') . "/', l.catImage) as catImage")
    )
    ->join('transaction_details as td', 'td.category_id', '=', 'l.levelId')
    ->where('td.customer_id', $userId)
    ->where('td.start_date', '<', $currentDateTime)
    ->where('td.end_date', '>=', $currentDateTime)
    ->where('td.status', '1')
    ->skip($offset)
    ->limit($perPage)
    ->get();


			if(count($results)>0){
	    		$mainArray = ["currentPage"=>$currentPage,"data"=>$results];
				$myarray['result']=$mainArray;
			  	$myarray['message']="Category data is available.";
			  	$myarray['status']=1;	
			}else{
				$myarray['result']=(object)array();
			  	$myarray['message']="Category data is not available.";
			  	$myarray['status']=0;
			}


	       }else{
	    	$myarray['result']=(object)array();
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
	    }		
		return response()->json($myarray);
   	}

   	public function dashboardCategory(Request $request)
   	{
   		$isPurchased = 0;
   		$common=new CommanController;
       	$langId=($request->header('langId'))?($request->header('langId')):1; 
	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
       	$authenticate=$this->_authenticate;
       	if (in_array($apiauthenticate,$authenticate)) {
	       	$perPage=($request->perPage)?($request->perPage):10;
	       	$currentPage=($request->currentPage)?($request->currentPage):1;
	       	$userId=($request->userId)?($request->userId):0;
	       	$offset = ($currentPage - 1) * $perPage;
	   		$category = LevelManagement::where('isActive',1)->offset($offset)->limit($perPage)->get();
	   		$categoryData =$category->toArray();
	   		$i=0;
	   		$currentDateTime = now()->format('Y-m-d H:i:s');
	   		foreach($categoryData as $cat){
	   			$checkSubscribed = TransactionDetails::where("category_id",$cat['levelId'])
	   								->where("customer_id",$userId)
	   								->where('start_date', '<', $currentDateTime)
								    ->where('end_date', '>=', $currentDateTime)
								    ->where('status', '1')
								    ->orderByDesc('end_date')
								    ->first();
	   			$categoryData[$i]['isSubscribed'] = $checkSubscribed ? '1' : '0';
	   			if($cat['catImage'] != ''){	
	   				$categoryData[$i]['catImage'] = url('images/category/'.$cat['catImage']);
	   			}
	   			$i++;
	   		}

	    	if(count($categoryData)>0){
	    		$mainArray = ["currentPage"=>$currentPage,"data"=>$categoryData];
				$myarray['result']=$mainArray;
			  	$myarray['message']="Category data is available.";
			  	$myarray['status']=1;	
			}else{
				$myarray['result']=(object)array();
			  	$myarray['message']="Category data is not available.";
			  	$myarray['status']=0;
			}
	    }else{
	    	$myarray['result']=(object)array();
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
	    }
		
		return response()->json($myarray);
   	}
   	/*New API*/
   	public function dashboardCategoryV1(Request $request)
   	{
   		$isPurchased = 0;
   		$common=new CommanController;
       	$langId=($request->header('langId'))?($request->header('langId')):1; 
	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
       	$authenticate=$this->_authenticate;
       	if (in_array($apiauthenticate,$authenticate)) {
	       	$perPage=($request->perPage)?($request->perPage):10;
	       	$currentPage=($request->currentPage)?($request->currentPage):1;
	       	$userId=($request->userId)?($request->userId):0;
	       	$offset = ($currentPage - 1) * $perPage;
	   		$category = LevelManagement::offset($offset)->limit($perPage)->get();
	   		$categoryData =$category->toArray();
	   		$i=0;
	   		$currentDateTime = now()->format('Y-m-d H:i:s');
	   		if(count($categoryData)>0){
	    		$mainArray = ["currentPage"=>$currentPage,"data"=>$categoryData];
				$myarray['result']=$mainArray;
			  	$myarray['message']="Category data is available.";
			  	$myarray['status']=1;	
			}else{
				$myarray['result']=(object)array();
			  	$myarray['message']="Category data is not available.";
			  	$myarray['status']=0;
			}
	    }else{
	    	$myarray['result']=(object)array();
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
	    }
		
		return response()->json($myarray);
   	}
   	public function topics(Request $request){
   		
   		$common=new CommanController;
       	$langId=($request->header('langId'))?($request->header('langId')):1; 
	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
       	$authenticate=$this->_authenticate;
       	if (in_array($apiauthenticate,$authenticate)) {
       		$perPage=($request->perPage)?($request->perPage):10;
	       	$currentPage=($request->currentPage)?($request->currentPage):1;
	       	$subjectId=($request->subjectId)?($request->subjectId):0;
	       	$offset = ($currentPage - 1) * $perPage;
	   		$topicData = Topics::where('subjectId',$subjectId)->orderBy('short_order_id', 'DESC')->offset($offset)->limit($perPage)->get();
	   		//$topicData = Topics::where('subjectId',$subjectId)->orderBy('createdDate', 'DESC')->offset($offset)->limit($perPage)->get();
	   		$subjectData = Subject::where('id',$subjectId)->first();
	   		
	   		
	   		if(count($topicData)>0){
	   			$data = ['topics'=>$topicData,'subject'=>$subjectData];
	    		$mainArray = ["currentPage"=>$currentPage,"data"=>$data];
				$myarray['result']=$mainArray;
			  	$myarray['message']="Topics data is available.";
			  	$myarray['status']=1;	
			}else{
				$myarray['result']=(object)array();
			  	$myarray['message']="Topics data is not available.";
			  	$myarray['status']=0;
			}
       	}else{
	    	$myarray['result']=(object)array();
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
	    }
		
		return response()->json($myarray);	
   	}

   	public function questions(Request $request){
   		$common=new CommanController;
       	$langId=($request->header('langId'))?($request->header('langId')):1; 
	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
	   	$newArray = array();
       	$authenticate=$this->_authenticate;
       	if (in_array($apiauthenticate,$authenticate)) {
       		$topicId = ($request->topicId)?($request->topicId):0;
       		$topicData = Topics::where('id',$topicId)->first();
       		$cust_id = ($request->cust_id)?($request->cust_id):0;
       		
			$newArray = Questions::whereHas('topicQueRel', function ($query) use ($topicId) {
					    $query->where('topicId', '=', $topicId);
					})
					->with('topics')
					->with('options')->get()
					->map(function ($item) {
				        $item->description = html_entity_decode(strip_tags($item->description));
				        return $item;
				    });


			//echo "<pre>";print_r($newArray);exit;
			
			
			if(!empty($newArray)){
				$myarray['result']=$newArray;
				$myarray['status']=1;
				$myarray['message']=$common->get_msg("level_list",$langId)?$common->get_msg("level_list",$langId):'Question List.';
			} else {
				$myarray['result']=(object)array();
				$myarray['status']= 0;
				$myarray['message']=$common->get_msg("level_list_not_found",$langId)?$common->get_msg("level_list",$langId):'Question Not Found';
			}
       	}else{
	    	$myarray['result']=(object)array();
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
	    }
	    return response()->json($myarray);		
   	}
   	/*Get Question wise Comment*/
   	public function getComments(Request $request)
   	{
   		$allComments = QuestionCommnent::query()->with(['customer' => function ($query) {
		    $query->select('id','name', 'photo');
		}]);

		if (!empty($request->questionId)) {
		    $allComments->where('questionId', $request->questionId);
		}

		$allComments = $allComments->select('id', 'userId', 'questionId', 'comment', 'is_parent', 'parentId','created_at')->get();
		$myarray['result']=$allComments;
		$myarray['message']="All Comment Show successfully.";
		$myarray['status']= 1;
		return response()->json($myarray);		
   	}
   	/*Create Comment*/
   	public function createComments(Request $request)
   	{
   		$wordcheck = UnnecessaryWords::all();
   		$comment = $request->comment;
		$unnecessaryWords = $wordcheck->pluck('word')->toArray();
		$newcomment = str_ireplace($unnecessaryWords, $comment[0].'***', $comment);
		
   		$create = new QuestionCommnent;
   		$create->userId 	= $request->userId;
   		$create->questionId = $request->questionId;
   		$create->comment 	= $newcomment;
   		if(!empty($request->parentId)){
   			$create->is_parent = 1;
   		} else {
   			$create->is_parent = 0;
   		}
   		$create->parentId 	= $request->parentId;
   		$create->save();

   		/*23-9-2024 Mail send*/
	    $mail = Helper::getEmailContent(5);
	    if (!empty($mail)) {
	    	$udata = CustomerRegister::find($request->userId);
	   		$qdata = Questions::find($request->questionId);
	   		$cname = $udata->name;
	   		$question = $qdata->question;
	   		$commentdata = $newcomment;
	        $Data = [
	            'customername' => $cname,
	            'question' => $question,
	            'comment' => $commentdata,
	            'logourl' => url('/assets/admin/img/logo.svg'),
	        ];
	        $mailDescription = str_replace(
	            ['#logourl', '##CUSTOMERNAME##', '##QUESTION##', '##COMMENT##'],
	            [$Data['logourl'],$Data['customername'],$Data['question'],$Data['comment']],
	            $mail->description
	        );

	        try {
                $mail->mail_cc .= ', kalpesh@abbacus.com';
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


   		$myarray['result']=$create;
		$myarray['message']="Create Comment successfully.";
		$myarray['status']= 1;
		return response()->json($myarray);		

   	}
   	/*Comment Delete*/
   	public function deleteComment(Request $request)
    {
    	$parentComment = QuestionCommnent::find($request->commentId);
		if ($parentComment !== null) {
	    	

		    if ($parentComment->delete()) {
		        $myarray['result'] = array();
		        $myarray['message'] = "Comment deleted successfully";
		        $myarray['status'] = 1;
		    } else {
		        $myarray['result'] = array();
		        $myarray['message'] = "Comment could not be deleted.";
		        $myarray['status'] = 0;
		    }
		} else {
		    $myarray['result'] = array();
		    $myarray['message'] = "Comment not found.";
		    $myarray['status'] = 0;
		}
		return response()->json($myarray);
    }

    /*Get  All  Question wise Comment*/
   	public function getAllComments(Request $request)
   	{
   		$allComments = QuestionCommnent::query()
	     ->with([
	        'customer' => function ($query) {
	            $query->select('id', 'name', 'photo');
	        },
	        'childComments.customer' => function ($query) {
	            $query->select('id', 'name', 'photo');
	        },
	        'childComments.childComments.customer' => function ($query) {
	            $query->select('id', 'name', 'photo');
	        }
	    ])
	   ->where('parentId',0);
   		if (!empty($request->questionId)) {
		    $allComments->where('questionId', $request->questionId);
		}

		$allComments = $allComments->select('id', 'userId', 'questionId', 'comment', 'is_parent', 'parentId','created_at')->get();
		$myarray['result']=$allComments;
		$myarray['message']="All Comment Show successfully.";
		$myarray['status']= 1;
		return response()->json($myarray);		
   	}
   	/*Get  All  Question wise Comment*/
   	public function getAllCommentsCount(Request $request){
   		$allComments = QuestionCommnent::query();
   		if (!empty($request->questionId)) {
		    $allComments->where('questionId', $request->questionId);
		}
		$allComments = $allComments->count();
		$myarray['result']=$allComments;
		$myarray['message']="All Comment Count.";
		$myarray['status']= 1;
		return response()->json($myarray);	
   	}
   	/*Learning Exam submit*/
   	public function learningExam(Request $request)
   	{
   		$common=new CommanController;
       	$langId=($request->header('langId'))?($request->header('langId')):1; 
	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
       	$authenticate=$this->_authenticate;
       	if (in_array($apiauthenticate,$authenticate)) {
       		$validator = Validator::make($request->all() , 
	        [
	            'topics_id' => 'required',
	            'cust_id' => 'required',
	            'learning_result' => 'required',
	        ]);
	        
	        if ($validator->fails()){
	        	$errors = collect($validator->errors());
	            $error = $errors->first();
	            $myarray['result'] = (object)array();
	            $myarray['message'] = implode('', $error);
	            $myarray['status'] = 0;
	        } else {

	        	$update = LearningAnswer::where('topics_id',$request->topics_id)->where('cust_id',$request->cust_id)->first();
	        	if($update){
	        		$update->learning_result = $request->learning_result;
	        		$update->save();
	        		$myarray['result'] = $update;
		            $myarray['message'] = $common->get_msg("update_learning_exam", $langId) 
		                            ? $common->get_msg("update_learning_exam", $langId) 
		                            : 'Update Exam successfully.';
		            $myarray['status'] = 1;
	        	} else {
	        		
	        		$create = new LearningAnswer;
	        		$create->topics_id 	= $request->topics_id;
	        		$create->cust_id = $request->cust_id;
	        		$create->learning_result = $request->learning_result;
	        		$create->save();
	        		$myarray['result'] = $create;
		            $myarray['message'] = $common->get_msg("add_learning_exam", $langId) 
		                            ? $common->get_msg("add_learning_exam", $langId) 
		                            : 'Add Exam successfully.';
		            $myarray['status'] = 1;
	        	}
	        }
	      }
	      return response()->json($myarray);
    }
    /*Question testing*/
    /*public function listQuestion(Request $request){
    	$common = new CommanController;
    	$langId=($request->header('langId'))?($request->header('langId')):1; 
	   	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
    	$randomQuestionIDs = DB::table('tblquestion')
									    ->join('topicQueRel', 'tblquestion.questionId', '=', 'topicQueRel.questionId')
									    ->where('topicQueRel.topicId', $request->topicId)
									    ->pluck('tblquestion.questionId')->toArray();
		if(!empty($randomQuestionIDs)){
			$allGetQuestion = $common->getQuestion($randomQuestionIDs);
			if(count($allGetQuestion) != 0){
				$myarray['result'] = $allGetQuestion;
		        $myarray['message'] = $common->get_msg("all_question", $langId) 
		                        ? $common->get_msg("all_question", $langId) 
		                        : 'Question Listing.';
		        $myarray['status'] = 1;

			} else {
				$myarray['result'] = (object)array();
		        $myarray['message'] = $common->get_msg("all_question_not_found", $langId) 
		                        ? $common->get_msg("all_question_not_found", $langId) 
		                        : 'Question not found.';
		        $myarray['status'] = 0;
			}
		} else {
			$myarray['result'] = (object)array();
	        $myarray['message'] = $common->get_msg("all_question_not_found", $langId) 
	                        ? $common->get_msg("all_question_not_found", $langId) 
	                        : 'Question not found.';
	        $myarray['status'] = 0;
		}
		return response()->json($myarray);
    }*/

    public function listQuestion(Request $request) {
	    $common = new CommanController;
	    $langId = ($request->header('langId')) ? ($request->header('langId')) : 1; 
	    $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
	    $randomQuestionIDs = DB::table('tblquestion')
	                            ->join('topicQueRel', 'tblquestion.questionId', '=', 'topicQueRel.questionId')
	                            ->where('topicQueRel.topicId', $request->topicId)
	                            ->where('tblquestion.isActive', 1)
	                            ->pluck('tblquestion.questionId')->toArray();

	    if (!empty($randomQuestionIDs)) {
	        $allGetQuestion = $common->getQuestion($randomQuestionIDs);
	        if (count($allGetQuestion) != 0) {
	            foreach ($allGetQuestion as &$question) {
	                if ($question->video) {
	                    $question->videoUrl = URL::to('/') . '/topicImages/' . $question->video;
	                }else{
	                	$question->videoUrl = null;
	                }

	                foreach ($question->options as &$option) {
	                    if ($option->questionImage) {
	                        $option->questionImageUrl = URL::to('/') . '/optionImages/' . $option->questionImage;
	                    }else{
	                    	$option->questionImageUrl = null;
	                    }
	                }
	            }

	            $myarray['result'] = $allGetQuestion;
	            $myarray['message'] = $common->get_msg("all_question", $langId) 
	                                ? $common->get_msg("all_question", $langId) 
	                                : 'Question Listing.';
	            $myarray['status'] = 1;
	        } else {
	            $myarray['result'] = (object)[];
	            $myarray['message'] = $common->get_msg("all_question_not_found", $langId) 
	                                ? $common->get_msg("all_question_not_found", $langId) 
	                                : 'Question not found.';
	            $myarray['status'] = 0;
	        }
	    } else {
	        $myarray['result'] = (object)[];
	        $myarray['message'] = $common->get_msg("all_question_not_found", $langId) 
	                            ? $common->get_msg("all_question_not_found", $langId) 
	                            : 'Question not found.';
	        $myarray['status'] = 0;
	    }
	    return response()->json($myarray);
	}
}      

