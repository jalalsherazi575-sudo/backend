<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\GeneralNotificationRequest;
use Illuminate\Http\Request;
use Laraspace\Language;
use Laraspace\GeneralNotification;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class GeneralNotificationController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
       return view('admin.generalnotification.addedit');
	}
	
	public function add() {
		
	   return view('admin.generalmessage.addedit');
	}
	
	 public function postCreate(GeneralNotificationRequest $request) {
		 $common=new CommanController;

			 $select_device=($request->select_device)?($request->select_device):0;
			 $message=($request->message)?($request->message):"";
			 //$app_type=($request->app_type)?($request->app_type):0;

	           $customerdata=array();
	           
	           
	           
	                  
	                  if ($select_device==0) {
	                  $customerdata=DB::select( DB::raw("select * from tbldevicetoken as devicetokenl
                        inner join tblcustomer as customer on devicetokenl.customerId=customer.id
	                  	where devicetokenl.loginStatus=1 and customer.isActive=1 and devicetokenl.deviceToken!=''"));
	                  } else {
	                  $customerdata=DB::select( DB::raw("select * from tbldevicetoken as devicetokenl
                        inner join tblcustomer as customer on devicetokenl.customerId=customer.id where devicetokenl.loginStatus=1 and customer.isActive=1 and devicetokenl.deviceToken!='' and devicetokenl.deviceType=$select_device"));	
	                  }

	         

	           $customerAndToken='';
	           $customerIpToken=array();

	           $customerAndArray=array();
	           
	           if ($customerdata) {
	           	   foreach ($customerdata as $custdata) {
	           	   	   $userId=$custdata->customerId;
	           	   	   $deviceType=$custdata->deviceType;
	           	   	   $deviceToken=$custdata->deviceToken;
	           	   	   if ($deviceType==1) {
	           	   	   	 if (strlen($deviceToken) > 40) {
	           	   	   	
	           	   	   	$customerAndArray[]=$deviceToken;
	           	   	     }
	           	   	   }
	           	   	   if ($deviceType==2) {
	           	   	   	 if (strlen($deviceToken) > 40) {
	           	   	   	$customerIpToken[]=$deviceToken;
	           	   	   	  }
	           	   	   }

	           	   	    DB::table('tblnotification')->insert(
			   ['notifiedByUserId'=>2,'notifiedUserId'=>$userId,'createdDate'=>date('Y-m-d H:i:s'),"notification"=>$message,"notificationType"=>2,"isCustomerNotification"=>1]);
	           	   }
	           }

              // echo "<pre>"; print_r($vendordata);
               //exit;
	           

	           if (!empty($customerAndArray)) {
	                 
	                 
	                 $ExtraInfo = array('message'=>$message,'title'=>"Mission App Notification",'userId'=>0,'icon'=>'myicon','sound' => 'mySound','notificationType'=>2,'notificationId'=>0);
	                     
	                     $common->firebasepushCustomer($ExtraInfo,$customerAndArray);
	           }

	          

	           if ((!empty($customerIpToken)) && (count($customerIpToken) > 0)) {
	                 //$body = array();
	                 $body['aps'] =array('alert' => $message, 'sound' => 'default', 'badge' => 0, 'content-available' => 1,'userId'=>0,'notificationType'=>2,'notificationId'=>0);
	                 $common->iPhonePushBookCustomer($customerIpToken,$body);
	           }

	           

             flash()->success('General Notification has been send successfully.');
		     return redirect()->to('/admin/generalnotification');

       }
	   
	   
	   
	   
	  
}
