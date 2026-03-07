<?php
namespace Laraspace\Http\Controllers;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Laraspace\Customer;
use Laraspace\LevelManagement;
use Laraspace\UserSubscriptionPlan;
use Laraspace\Questions;
use Auth;
use File;
use Mail;

class CommanController extends Controller 
{
	
	/*Question id Wise option get */
	public function getQuestion($questionId)
	{
		$question = Questions::with('topics','options')->whereIn('questionId',$questionId)->get();
		return !empty($question) ? $question : null;

		
	}	
    public function getQuestionMaxRank() 
    {

		$maxRank=1;
		$data=DB::select( DB::raw("select max(sortOrder) as rank_num from tblquestion"));
		if ($data) {
			foreach ($data as  $value) {
				$maxRank=$value->rank_num;
				$maxRank=$maxRank+1;
			}
		}
		return $maxRank;
	}
    
    public function getLevelMaxRank() 
    {

		$maxRank=1;
		$data=DB::select( DB::raw("select max(sortOrder) as rank_num from tbllevelmanagement"));
		if ($data) {
			foreach ($data as  $value) {
				$maxRank=$value->rank_num;
				$maxRank=$maxRank+1;
			}
		}
		return $maxRank;
	}

	public function getLessionMaxRank() 
	{

		$maxRank=1;
		$data=DB::select( DB::raw("select max(sortOrder) as rank_num from tbllessionmanagement"));
		if ($data) {
			foreach ($data as  $value) {
				$maxRank=$value->rank_num;
				$maxRank=$maxRank+1;
			}
		}
		return $maxRank;
	}

	public function getNoticeMaxRank() 
	{

		$maxRank=1;
		$data=DB::select( DB::raw("select max(sortOrder) as rank_num from tblappnoticeboard"));
		if ($data) {
			foreach ($data as  $value) {
				$maxRank=$value->rank_num;
				$maxRank=$maxRank+1;
			}
		}
		return $maxRank;
	}
    
	
    public function getUserSubscriptionPlanPackage($userId) 
    {

       $userSubscriptionData = UserSubscriptionPlan::where('userId',$userId)->where('isActive',1)->orderby('id','desc')->first();
       $Display_Level_Id=$this->get_msg('Display_Level_Id') ? $this->get_msg('Display_Level_Id') :1;
       $curDate=date("Y-m-d");
       
       if ($userSubscriptionData) {

       	   $usersubscriptionId=isset($userSubscriptionData->id)?($userSubscriptionData->id):0;
	       $userpackageId=isset($userSubscriptionData->packageId)?($userSubscriptionData->packageId):0;
	       $packageName=isset($userSubscriptionData->packageName)?($userSubscriptionData->packageName):"";
	       $packagePrice=isset($userSubscriptionData->packagePrice)?($userSubscriptionData->packagePrice):"0.00";
	       $createdDate=isset($userSubscriptionData->createdDate)?($userSubscriptionData->createdDate):"";
	       $packagePeriodInMonth=isset($userSubscriptionData->packagePeriodInMonth)?($userSubscriptionData->packagePeriodInMonth):0;
	       $expiryDate=isset($userSubscriptionData->expiryDate)?($userSubscriptionData->expiryDate):'';
	       $androidPlanKey=isset($userSubscriptionData->androidPlanKey)?($userSubscriptionData->androidPlanKey):'';
	       $iosPlanKey=isset($userSubscriptionData->iosPlanKey)?($userSubscriptionData->iosPlanKey):'';
	       $fromDate=isset($userSubscriptionData->fromDate)?($userSubscriptionData->fromDate):"";
           
           $isExpired=0;
	       if ($expiryDate < $curDate) {
	       	  $isExpired=1;
	       }

	       $userSubscriptionDataPurchaseCount = UserSubscriptionPlan::where('userId',$userId)->where('expiryDate','>',$expiryDate)->count();

           $isFuturePlanPurchased=0;
	       if ($userSubscriptionDataPurchaseCount > 0) {
	       	   $isFuturePlanPurchased=1;
	       }
	                              
           $Subscriptionarr['userSubscription']=array("usersubscriptionId"=>(int)$usersubscriptionId,"packageId"=>(int)$userpackageId,"packageName"=>$packageName,"packagePrice"=>$packagePrice,"packagePeriodInMonth"=>(int)$packagePeriodInMonth,"expiryDate"=>$expiryDate,"androidPlanKey"=>$androidPlanKey,"iosPlanKey"=>$iosPlanKey,"createdDate"=>$createdDate,"DisplayLevelId"=>(int)$Display_Level_Id,"isExpired"=>$isExpired,"isFuturePlanPurchased"=>$isFuturePlanPurchased,"fromDate"=>$fromDate);
       
       } else {
          
          $Subscriptionarr['userSubscription']=null;
          //$Subscriptionarr['userSubscription']=(object)array();

       }  

	       

    	return $Subscriptionarr;

    }

	public function getQuestionOptionMaxRank($questionId=0) 
	{

		$maxRank=1;
		$data=DB::select( DB::raw("select max(sortOrder) as rank_num from tblsurveyquestionoption where questionId=$questionId"));
		if ($data) {
			foreach ($data as  $value) {
				$maxRank=$value->rank_num;
				$maxRank=$maxRank+1;
			}
		}
		return $maxRank;
	}

	

	public static function LevelName($Id) 
	{
		$vendorData = DB::table('tbllevelmanagement')->where([['id', '=', $Id]])->first();
		$companyName='';
		if ($vendorData) {
		$companyName=($vendorData->name)?($vendorData->name):'';
		
        }
        return $companyName;
	}

	
    public function NotificationCountVendor($notified_userid) 
    {
	$NCount = DB::table('tblnotification')->where([['flag', '=', 0],['notifiedUserId','=',$notified_userid],['isCustomerNotification','=',0]])->count();
          return $NCount; 
    }

    public function NotificationCountCustomer($notified_userid) 
    {
		$NCount = DB::table('tblnotification')->where([['flag', '=', 0],['notifiedUserId','=',$notified_userid],['isCustomerNotification','=',1]])->count();
          return $NCount; 
    }
	
    
	function firebasepushCustomer($msg,$registrationIds) 
	{
   	 
      require base_path().'/android-notification/Notification.php';
    }

    function firebasepushVendor($msg,$registrationIds,$deviceType=1) 
    {
   	 
      require base_path().'/android-notification/Notification_Vendor.php';
    }

    function iPhonePushBookCustomer($DeviceId,$body) {
   	  $body['aps']['content-available'] = 1;
      require base_path().'/ios-notification/send-multiple-device-notification.php';
    }

    function iPhonePushBookVendor($DeviceId,$body) {
   	  $body['aps']['content-available'] = 1;
      require base_path().'/ios-notification/send-multiple-device-notification.php';
    }

    public function customerCurrency($CustomerId) {

    	$currency=DB::table('tblcountries')->join('tblcustomer', 'tblcountries.id', '=', 'tblcustomer.countryId')->where('tblcustomer.id', '=',$CustomerId)->first();
    	$curr='';
    	if ($currency) {
    		$curr=$currency->currency;
    	}
    	return $curr;
    }

	public function customerName($CustomerId) {
		$customerData = DB::table('tblcustomer')->where([['id', '=', $CustomerId]])->first();
		$customerName='';
		if ($customerData) {
			$customerfname=($customerData->fname)?($customerData->fname):'';
			$customerlname=($customerData->lname!='')?($customerData->lname):'';
	        $customerName=$customerfname." ".$customerlname;
	    }
        return $customerName;
	}

	public function customerProfilePic($CustomerId) {
		$url=url('/');
		$customerData = DB::table('tblcustomer')->where([['id', '=', $CustomerId]])->first();
		$customerphoto='';
		if ($customerData) {
			$customerphoto=($customerData->photo!='')?($customerData->photo):'';
			if ($customerphoto!='') {
				    $customerphoto=$url."/customerphoto/".$customerphoto;
				}
			}
        return $customerphoto;
	}


    public function getDayName($number=0) {
    	 if ($number==0) {
    	 	$dayName='Monday';
    	 } elseif($number==1) {
    	 	$dayName='Tuesday';
    	 } elseif($number==2) {
    	 	$dayName='Wednesday';
    	 } elseif($number==3) {
    	 	$dayName='Thursday';
    	 } elseif($number==4) {
    	 	$dayName='Friday';
    	 } elseif($number==5) {
    	 	$dayName='Saturday';
    	 } elseif($number==6) {
    	 	$dayName='Sunday';
    	 } else {
    	 	$dayName="";
    	 }
    	 return $dayName; 
    }
    
	public function ReverseConvertTimeZone($Date,$timeZone) {
        $convert='';
	    if (strlen($timeZone)==5) {
		    
		    $strsign=substr($timeZone,0,1);
		    if ($strsign=='+') {
		       $strsign='-';
		    } else {
		       $strsign='+';
		    }
		
		   $h=$strsign.substr($timeZone,1,1)." hour "; 
		   $m=$strsign.substr($timeZone,3,2)." minutes";
		   $final=$h.$m;
		   //echo $h;
		   //exit;
		   $temp= strtotime("$Date $final");
	       $convert = date('Y-m-d H:i:s',$temp); 	
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
		    $final=$h.$m;
		    $temp= strtotime("$Date $final");
	        $convert = date('Y-m-d H:i:s',$temp);	
		}

	return $convert; 
    
    } 

    public function NewConvertDate($Date,$Timezone) {
    	$convert='';

    	if (strlen($Timezone)==5) {
			$strsign=substr($Timezone,0,1);
			$h=$strsign.substr($Timezone,1,1)." hour "; 
			$m=$strsign.substr($Timezone,3,2)." minutes";
			$final=$h.$m;
			
			$temp= strtotime("$Date $final");
			$convert = date('Y-m-d H:i:s',$temp); 
	    }

		if (strlen($Timezone)==6) {
		$strsign=substr($Timezone,0,1);
		$h=$strsign.substr($Timezone,1,2)." hour "; 
		$m=$strsign.substr($Timezone,4,2)." minutes";
		$final=$h.$m;
		
		$temp= strtotime("$Date $final");
		$convert = date('Y-m-d H:i:s',$temp); 
		}
		return $convert;
    }

    

    public function getCustomerDeviceDetail($customerId) {
        $customer=DB::table('tblcustomer')->where('id', $customerId)->first(['deviceType','deviceToken']);
        return $customer;
    }

    

	

	public function CustomerDetails($CustomerId,$langId=1) {
		    $url=url('/');
	        $myarray=array();
			$customerData = DB::table('tblcustomer')->where([['id', '=', $CustomerId]])->first();
		    $customerDataCount = DB::table('tblcustomer')->where([['id', '=', $CustomerId]])->count();	
		if ($customerDataCount > 0) {	

			$customerPassword=$customerData->password;
			$customerId=$customerData->id;
			$customerStatus=$customerData->isActive;
			$customerfname=($customerData->fname)?($customerData->fname):'';
			$customerlname=($customerData->lname!='')?($customerData->lname):'';
			$customeremail=($customerData->email!='')?($customerData->email):'';
			$customerphoto=($customerData->photo!='')?($customerData->photo):'';
			$customerphone=($customerData->phone!='')?($customerData->phone):'';
			$createdDate=($customerData->createdDate)?($customerData->createdDate):'';
			$isActive=($customerData->isActive)?($customerData->isActive):0;
			$loginStatus=($customerData->loginStatus)?($customerData->loginStatus):0;
			$lastLoginDate=($customerData->lastLoginDate)?($customerData->lastLoginDate):'';
			$socialMediaId=($customerData->socialMediaId)?($customerData->socialMediaId):'';
			$loginType=($customerData->loginType)?($customerData->loginType):0;
			$deviceType=($customerData->deviceType)?($customerData->deviceType):0;
			$gender=($customerData->gender)?($customerData->gender):0;
			$proofTypeId=($customerData->proofTypeId)?($customerData->proofTypeId):0;

			$isVerify=($customerData->isVerify)?($customerData->isVerify):0;


			$deviceToken=($customerData->deviceToken)?($customerData->deviceToken):'';
			$birthDate=($customerData->birthDate)?($customerData->birthDate):'';
                $age='';

	           if ($birthDate!='' && $birthDate!='0000-00-00' && $birthDate!='1970-01-01') {
	           	    $from = new \DateTime($birthDate);
                    $to   = new \DateTime('today');
                    $age=$from->diff($to)->y;
	           }
			
			
			
			$langId=($customerData->langId)?($customerData->langId):1;
			$deviceDetails=($customerData->deviceDetails)?($customerData->deviceDetails):'';
			$countryId=($customerData->countryId)?($customerData->countryId):0;
	        $stateId=($customerData->stateId)?($customerData->stateId):0;
	        $cityId=($customerData->cityId)?($customerData->cityId):0;
	        
	        $countryName=($countryId!=0)?$this->getCountryValue($countryId,$langId):"";
	        $stateName=($stateId!=0)?$this->getStateValue($stateId,$langId):"";
	        $cityName=($cityId!=0)?$this->getCityValue($cityId,$langId):"";

	        $cashReward=($customerData->cashReward)?($customerData->cashReward):0;
	        $rewardPoint=($customerData->rewardPoint)?($customerData->rewardPoint):0;
			
			
			if ($customerphoto!='') {
			    $customerphoto=$url."/customerphoto/".$customerphoto;
			}

			
            $Country = DB::table('tblcountries')->where([['id', '=', $countryId]])->first();

            $iso=isset($Country->iso2)?($Country->iso2):0;
            $currency=isset($Country->currency)?($Country->currency):0;
            $symbol=isset($Country->symbol)?($Country->symbol):0;

            //$cashReward=0;
		 	//$rewardPoint=0;

         
		
			
			$myarray=array('id'=>$customerId,'fname'=>$customerfname,'lname'=>$customerlname,'email'=>$customeremail,'phone'=>$customerphone,'photo'=>$customerphoto,'createdDate'=>$createdDate,'isActive'=>$isActive,'loginStatus'=>$loginStatus,'lastLoginDate'=>$lastLoginDate,'socialMediaId'=>$socialMediaId,"loginType"=>$loginType,"deviceType"=>$deviceType,"deviceToken"=>$deviceToken,'langId'=>$langId,'deviceDetails'=>$deviceDetails,"countryId"=>$countryId,"stateId"=>$stateId,"cityId"=>$cityId,"countryName"=>$countryName,"stateName"=>$stateName,"cityName"=>$cityName,"gender"=>$gender,"birthDate"=>$birthDate,'age'=>$age,"isVerify"=>$isVerify,"cashReward"=>floatval($cashReward),"rewardPoint"=>floatval($rewardPoint),"iso"=>$iso,"currency"=>$currency,"symbol"=>$symbol);
        }  
		  return $myarray;  
	}

	

	
    public function getCustomerlevelId($rewardpoint) {
       $level=DB::select( DB::raw("Select id from tbllevelmanagement where ".$rewardpoint." between fromPoints And toPoints"));
       $levelId=isset($level[0]->id)?($level[0]->id):0;
       return $levelId;
    } 

	
	

    
    public function getEmailContent($type) {
	  $SettingValue = DB::table('tblemailtemplate')->where([['type', '=', $type]])->first();
	  $templateDescription="";
	  if ($SettingValue) {
	  $templateDescription=$SettingValue->templateDescription;
	  }
	  return $templateDescription;
	}

	

	public function getImageSizeValue($key) {
	  $SettingValue = DB::table('settings')->where([['option', '=', $key]])->first();
	  $value=100;
	  if ($SettingValue) {
	  $value=$SettingValue->value;
	  }
	  
	  return $value;
	}

	public function getSettingValue($key) {
	  $SettingValue = DB::table('settings')->where([['option', '=', $key]])->first();
	  $value='';
	  if ($SettingValue) {
	  $value=$SettingValue->value;
	  }
	  
	  return $value;
	}

	public function getCurrency() {
	  $SettingValue = DB::table('settings')->where([['option', '=', 'currency']])->first();
	  $width='';
	  if ($SettingValue) {
	  $width=$SettingValue->value;
	  }
	  return $width;
	}

	public function getIosAppLink() {
	  $SettingValue = DB::table('settings')->where([['option', '=', 'ios_app_link']])->first();
	  $link='';
	  if ($SettingValue) {
	  $link=$SettingValue->value;
	  }
	  return $link;
	}

	public function getAndroidAppLink() {
	  $SettingValue = DB::table('settings')->where([['option', '=', 'android_app_link']])->first();
	  $link='';
	  if ($SettingValue) {
	  $link=$SettingValue->value;
	  }
	  return $link;
	}

	
	
	public static function getMessageValue($Id,$landId) {
		//echo $Id; exit;
	  $SettingValue = DB::table('tblgeneralmessagetranslation')->where([['general_message_id', '=', $Id],['lang_id','=',$landId]])->first();
	  $title_value='';
	  if ($SettingValue) {
	  $title_value=$SettingValue->title_value;
	  }
	  return $title_value;
	}

	public static function getNotificationMessageValue($Id,$landId) {
	  $SettingValue = DB::table('tblnotificationmessagetranslation')->where([['notification_message_id', '=', $Id],['lang_id','=',$landId]])->first();
	  $title_value='';
	  if ($SettingValue) {
	  $title_value=$SettingValue->title_value;
	  }
	  return $title_value;
	}

	
    
        public function getStateList(Request $request)
        { 
        	
            $states = DB::table("tblstates")
                        ->where("country_id",'=',$request->country_id)
                        ->pluck("name","id")->all();
            return response()->json($states);
        }

        public function getCityList(Request $request)
        {
            $cities = DB::table("tblcities")
                        ->where("state_id",$request->state_id)
                        ->pluck("name","id")->all();
            return response()->json($cities);
        }

        


   
	

	public static function getCountryValue($Id,$landId) {
	  $SettingValue = DB::table('tblcountrytranslation')->where([['countryId', '=', $Id],['langId','=',$landId]])->first();
	  $title_value='';
	  if ($SettingValue) {
	  $title_value=$SettingValue->name;
	  }
	  return $title_value;
	}

	public static function getCountryValues($Id) {
	  $SettingValue = DB::table('tblcountries')->where([['id', '=', $Id]])->first();
	  $title_value='';
	  if ($SettingValue) {
	  $title_value=$SettingValue->name;
	  }
	  return $title_value;
	}

	public static function getSubjectValue($Id,$landId) {
	  $SettingValue = DB::table('subject')->where([['id', '=', $Id],['langId','=',$landId]])->first();
	  $title_value='';
	  if ($SettingValue) {
	  $title_value=$SettingValue->subjectName;
	  }
	  return $title_value;
	}

	public static function getStateValue($Id,$landId) {
	  $SettingValue = DB::table('tblstatetranslation')->where([['stateId', '=', $Id],['langId','=',$landId]])->first();
	  $title_value='';
	  if ($SettingValue) {
	  $title_value=$SettingValue->name;
	  }
	  return $title_value;
	}

	public static function getCityValue($Id,$landId) {
	  $SettingValue = DB::table('tblcitytranslation')->where([['cityId', '=', $Id],['langId','=',$landId]])->first();
	  $title_value='';
	  if ($SettingValue) {
	  $title_value=$SettingValue->name;
	  }
	  return $title_value;
	}

	public static function getIdProofValue($Id,$landId) {
	  $SettingValue = DB::table('tblidprooftypetranslation')->where([['proofIdType', '=', $Id],['langId','=',$landId]])->first();
	  $title_value='';
	  if ($SettingValue) {
	  $title_value=$SettingValue->name;
	  }
	  return $title_value;
	}

	

	public static function getCmsPageNameValue($Id,$landId) {
	  $SettingValue = DB::table('tblcmstranslation')->where([['cmsId', '=', $Id],['langId','=',$landId]])->first();
	  $title_value='';
	  if ($SettingValue) {
	  $title_value=$SettingValue->name;
	  }
	  return $title_value;
	}

	public static function getCmsPageDescValue($Id,$landId) {
	  $SettingValue = DB::table('tblcmstranslation')->where([['cmsId', '=', $Id],['langId','=',$landId]])->first();
	  $title_value='';
	  if ($SettingValue) {
	  $title_value=$SettingValue->description;
	  }
	  return $title_value;
	}

	
	
	
    public function distance($lat1, $lon1, $lat2, $lon2, $unit) {

		  $theta = $lon1 - $lon2;
		  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		  $dist = acos($dist);
		  $dist = rad2deg($dist);
		  $miles = $dist * 60 * 1.1515;
		  $unit = strtoupper($unit);

		  if ($unit == "K") {
		  	$miles=number_format(($miles * 1.609344),2);
		    return ($miles);
		  } else if ($unit == "N") {
		  	   $miles=number_format(($miles * 0.8684),2);
		      return ($miles);
		  } else {
		    	 $miles=number_format(($miles),2);
		        return $miles;
		   }
    } 

	
	
	
	
	
	
	public function get_msg($key,$lang_id=1) {
	   $data=DB::select( DB::raw("SELECT tblgeneralmessagetranslation.title_value from tblgeneralmessagetranslation
	inner join tblgeneralmessage on tblgeneralmessage.id=tblgeneralmessagetranslation.general_message_id where tblgeneralmessagetranslation.lang_id=$lang_id and tblgeneralmessage.title_key='$key'") ); 
	   $vals='';
	   if ($data) {
	      foreach ($data as $values) {
		   $vals=$values->title_value;
		  }
	   }
	   return $vals;
	}

	public function get_notification_msg($key,$lang_id=1) {
	   $data=DB::select( DB::raw("SELECT tblnotificationmessagetranslation.title_value from tblnotificationmessagetranslation
	inner join tblnotificationmessage on tblnotificationmessage.id=tblnotificationmessagetranslation.notification_message_id where tblnotificationmessagetranslation.lang_id=$lang_id and tblnotificationmessage.title_key='$key'") ); 
	   $vals='';
	   if ($data) {
	      foreach ($data as $values) {
		   $vals=$values->title_value;
		  }
	   }
	   return $vals;
	}
    
    public function get_msg_title($id,$lang_id=1) {
	   $data=DB::select( DB::raw("SELECT tblgeneralmessagetranslation.title_value from tblgeneralmessagetranslation
	inner join tblgeneralmessage on tblgeneralmessage.id=tblgeneralmessagetranslation.general_message_id where tblgeneralmessagetranslation.lang_id=$lang_id and tblgeneralmessagetranslation.general_message_id='$id'") ); 
	   $vals='';
	   if ($data) {
	      foreach ($data as $values) {
		   $vals=$values->title_value;
		  }
	   }
	   return $vals;
	}

	public function get_msg_id($key,$lang_id=1) {
	   $data=DB::select( DB::raw("SELECT tblgeneralmessagetranslation.general_message_id from tblgeneralmessagetranslation
	inner join tblgeneralmessage on tblgeneralmessage.id=tblgeneralmessagetranslation.general_message_id where tblgeneralmessagetranslation.lang_id=$lang_id and tblgeneralmessage.title_key='$key'") ); 
	   $vals='';
	   if ($data) {
	      foreach ($data as $values) {
		   $vals=$values->general_message_id;
		  }
	   }
	   return $vals;
	}
	

    
	public static function proposnallyimage($height,$width,$setheight,$setwidth){
        $finalsize =[];

        if($width <= $setwidth && $height <=$setheight)
        {
            
            $finalsize['NewWidth']   = $width;
            $finalsize['Newheight'] = $height;

        } 
        elseif($width > $height) {
            if($width > $setwidth){
                $Newheightpr                = $setwidth *100/$width;
                $finalsize['Newheight']     = $height *$Newheightpr/100;
                $finalsize['NewWidth']      = $setwidth;
            } else{
                $NewWidthpr               = $setheight *100/$height;
                $finalsize['NewWidth']      = $width *$NewWidthpr/100;
                $finalsize['Newheight']     = $setheight;
            }
           
            
        } 
        elseif($height > $width) {
          
            if($height > $setheight){
                $NewWidthpr             = $setheight *100/$height;
                $finalsize['NewWidth']      = $width *$NewWidthpr/100;
                $finalsize['Newheight']    = $setheight;
            } else{
                $Newheightpr                = $setwidth *100/$width;
                $finalsize['Newheight']     = $height *$Newheightpr/100;
                $finalsize['NewWidth']      = $setwidth;
            }
            
        }else {
            $finalsize['NewWidth']   = $width;
            $finalsize['Newheight'] = $height;
        }
        return $finalsize;

    }
	/*Mail */
	public static function sendemail($subject,$email,$content)
    {
        /*Php Code*/
        $mail_from_email = 'info@medfellowsadminpanel.com';
        $mail_from_name = "Medfellows"; // Set your desired From name

        $boundary =md5(date('r', time()));  
        $headers = "MIME-Version: 1.0" . "\r\n"; 
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
        $headers .= "From: ".$mail_from_email."\r\n";
        //$headers .= "From: " . $mail_from_name . " <" . $mail_from_email . ">\r\n";

        $headers .= "Reply-To: ".$mail_from_email."\r\n";
        $message = $content;
        mail($email, $subject, $message, $headers);
    }	
}
  
?>