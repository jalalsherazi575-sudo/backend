<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\CustomerRequest;
use Illuminate\Http\Request;
use Laraspace\Customer;
use Image;
use Laraspace\BusinessCategory;
use Laraspace\Country;
use Laraspace\IdProofType;
use Laraspace\AreaOfInterest;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
		
	   $customer = Customer::pluck('id');
	   $myarray=array();
	   $common=new CommanController;
	   if ($customer) {
	      foreach($customer as $Ids) {
		  $CustomerDetails=$common->CustomerDetails($Ids);
		  $myarray[]=$CustomerDetails;
		  }
	   }
	   //echo '<pre>'; print_r($myarray);
	  // exit;
       return view('admin.customer.index')->with('customer', $myarray);
	}
	
	public function add() {
		$country = Country::where([['status', '=','1'],['id', '=','221']])->get();
		$idprooftype=IdProofType::where([['isActive', '=','1']])->get();
		$areaofinterest=AreaOfInterest::where([['isActive', '=','1']])->get();
	   return view('admin.customer.addedit',compact('country','idprooftype','areaofinterest'));
	}


	
	
	 public function postCreate(CustomerRequest $request) {
		 
		// $checkduplicate = DB::table('tblcustomer')->where([['fname', '=',$request->fname],['lname', '=',$request->lname]])->count();
		 $countemail = DB::table('tblcustomer')->where('email', '=', $request->email)->count();
		 $countphone = DB::table('tblcustomer')->where('phone', '=', $request->phone)->count();
		 if ($countemail==0 && $countphone==0) {
             
             $proofTypeId=isset($request->proofTypeId)?(($request->proofTypeId)):0;
             $interestId=isset($request->interestId)?(($request->interestId)):0;
             $isVerify=isset($request->isVerify)?(($request->isVerify)):0;


			 $customer = new Customer();
			 $customer->fname = isset($request->fname)?(ltrim($request->fname)):"";
			 $customer->lname = isset($request->lname)?(ltrim($request->lname)):"";
			 $customer->email = isset($request->email)?(ltrim($request->email)):"";
			 if (isset($request->password) && $request->password!='') {
			 $customer->password =(ltrim(bcrypt($request->password)));
			 }
			 
			 $customer->phone = isset($request->phone)?(ltrim($request->phone)):"";
			 $customer->gender = isset($request->gender)?(ltrim($request->gender)):0;
			 $customer->birthdate = isset($request->birthdate)?(ltrim(date("Y-m-d",strtotime($request->birthdate)))):"";
			 $customer->countryId = isset($request->countryId)?(ltrim($request->countryId)):0;
			 $customer->proofTypeId =$proofTypeId;

             
             
             
             
			
			 $customer->createdDate=date('Y-m-d H:i:s');
			 if($request->hasFile('photo')) {
				 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
				 $file = $request->file('photo');
				 $filename = time().$file->getClientOriginalName();
				 $extension = $file->getClientOriginalExtension();
				 $check=in_array($extension,$allowedfileExtension);
				 if($check) {
				   $destinationPath = 'customerphoto';
				   $file->move($destinationPath,$filename);
				   $customer->photo=$filename;						   
				 }
			 }

             $customer->isVerify=$isVerify;
			 $customer->isActive=$request->isActive;
			 $customer->save();
			 $customerId=$customer->id;

			 if ($interestId!=0) {
			   foreach ($interestId as  $value) {
				 DB::table('tblcustomerareaofinterest')->insert(
                  ['customerId'=>$customerId,'interestId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

						 
                if ($proofTypeId!=0) {

						 if($request->hasFile('proofType')) {
							
								   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
								   $files = $request->file('proofType');
								    
								    foreach($files as $file) {
									 $rand=rand(10,1000);
			                         $extension = strtolower($file->getClientOriginalExtension());
									 $filename = $rand.time().".".$extension;
			                         $check=in_array($extension,$allowedfileExtension);
										 if($check) {
										   $destinationPath = 'customerproof';
				                           $file->move($destinationPath,$filename); 
										   $isUploaded=1;
			                                
			                                 DB::table('tblcustomeridproofdoc')->insert(
			                  ['customerId'=>$customerId,'proofTypeId'=>$proofTypeId,'createdDate'=>date('Y-m-d H:i:s'),'idProof'=>$filename]);

										 }
								   }
						}
				}				   
			 
			 
			session()->flash('success','Customer has  added successfully.');
			return redirect()->to('/admin/customer');
		 } else {
		 	session()->flash('error','This Customer name has been already taken. Please try with another name.');
		     return redirect()->to('/admin/customer/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      //$status;
		  $customer = Customer::find($id);
		  $customer->isActive=$status;
		  $customer->save();
		  session()->flash('success','Status has been updated successfully.');
		 return redirect()->to('/admin/customer');
		  
	   }

	   public function Verify($status,$id) {
	      $common=new CommanController;
		    $customer = Customer::find($id);
		    //$email=$vendor->email;
            $customer->verifiedDate=date("Y-m-d H:i:s");
		    $customer->isVerify=$status;
		    $customer->save();
          
          if ($status==1) {

	          /*$emailtype=2;
			      $getEmailContent=$common->getEmailContent($emailtype);
		      
		        $content='';
		      
		        $url=url('/');

  			    if ($getEmailContent!='') {
  		         $name=$vendor->fname." ".$vendor->lname;
  		         $content=str_replace("#name",$name, $getEmailContent);
  		         $content=str_replace("#url",$url, $content);
  			     }

		      $template_data = ['content' => $content];

          try {

             Mail::send(['html' => 'emails.general-email'], $template_data,
                    function ($message) use ( $email) {
                       $message->to( $email)
                       ->from('contact@quickserv.com') 
                       ->subject('Your account has been verified by Quick Serve');
          });

          } catch(Exception $e)
               {

                      $msg="Getting Error";
                      echo $msg;
                       
               }  */
		      
	          
             

                       /* $newVendorId=$id;
						            $vendorName=$common->vendorName($newVendorId);
						            $vendorUrl=$common->vendorProfilePic($newVendorId);

						            $deviceType=($vendor->deviceType)?($vendor->deviceType):0;
			                        $deviceToken=($vendor->deviceToken)?($vendor->deviceToken):'';
			                        $loginStatus=($vendor->loginStatus)?($vendor->loginStatus):0;
			                        $langId=($vendor->langId)?($vendor->langId):0;

                                  $notificationmsg=$common->get_msg("verified_vendor",$langId)?$common->get_msg("verified_vendor",$langId):"";
                                    if ($notificationmsg!='') {
                                    	$Description=$notificationmsg;
                                    } else {
                                    	$Description="Congratulation Quickserv has verified to you.";
                                    }*/


                  /*$total_count = DB::table('tblnotification')->where([['notifiedUserId', '=', $newVendorId],['flag','=',0],['isCustomerNotification','=',0]])->count();

                                           $NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>1,'notifiedUserId'=>$newVendorId,'notificationType'=>6,'notification'=>$Description,'createdDate'=>date('Y-m-d H:i:s'),'SendBy'=>0,'leadId'=>0,'isCustomerNotification'=>0]);*/
                                    
						                   /*if($deviceType==1) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                	$ExtraInfo = array('notificationType'=>6,'message'=>$Description,'title'=>'Quick serv','NotificationType'=>0,'notificationId'=>$NotificationID,'customerId'=>0,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>0,'productId'=>0,'cutomerName'=>'','cutomerUrl'=>'','vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);	
                                                }
						                    }*/

						                    /*if($deviceType==2) {
                                                if ($deviceToken!='' && strlen($deviceToken) > 40) {
                                                  $ExtraInfo = array('notificationType'=>6,'message'=>$Description,'title'=>$Description,'NotificationType'=>0,'notificationId'=>$NotificationID,'customerId'=>0,'vendorId'=>$newVendorId,'icon'=>'myicon','sound'=>'mySound','leadId'=>0,'productId'=>0,'cutomerName'=>'','cutomerUrl'=>'','vendorName'=>$vendorName,'vendorUrl'=>$vendorUrl);
                                                    $common->firebasepushVendor($ExtraInfo,array($deviceToken),$deviceType);
                                                	
                                                }
						                    }*/
	          /*if (count(Mail::failures()) > 0 ) {
	         flash()->error('sorry system could not be sent mail on this email address.');
			 return redirect()->to('/admin/vendor');
	          }*/
        }  

		  session()->flash('success','Customer verify status has been updated successfully.');
		 return redirect()->to('/admin/customer');
		  
	   }
	   
	   public function Delete($id) {
		         $delete1=DB::delete("delete from tblcustomeridproofdoc where id=$id");
		         $delete2=DB::delete("delete from tblcustomerareaofinterest where customerId=$id");
		      $user = Customer::find($id);
              $user->delete();
			  echo 2;
		   
		   exit();
	   }
	   
	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  foreach ($request->del as $val) {
		    $checkcustomer=0;
			if ($checkcustomer > 0) {
			$customer = DB::table('tblcustomer')->where([['id', '=',$val]])->first();
			$customerName=$customer->fname." ".$customer->lname;
			$section .=$customerName.",";
			$err=1;
			} else {
			  $category1 = DB::table('tblcustomer')->where([['id', '=',$val]])->first();
			  $customerName=$category1->fname." ".$category1->lname;
              $section2 .=$customerName.",";			  
			  $customer = Customer::find($val);
              $customer->delete();
			}
		  }
		  
		  if ($err==1 && $section!='') {
			  $section=substr($section,0,-1);
			  $section2=substr($section2,0,-1);
		    $msg='Warning! Selected record is related to following Customer('.$section.') therefore deleting operation can’t be performed. For deleting the record needs to remove related records from listed vendor. ';
		    if ($section2!='') {
			$msg .="But $section2 has been deleted successfully.";
			}
		  } else {
		    $msg='Selected Customer has been deleted successfully.';
		  }
		  
		  	if ($err==1) {
		  		session()->flash('success',$msg);
	  		} else {
		  		session()->flash('error',$msg);
		  	}
		  return redirect()->to('/admin/customer');
	   }
	   
	   public function getEdit($id)
       {
		
		 $country = Country::where([['status', '=','1'],['id', '=','221']])->get(); 
         $common=new CommanController;
         $customer=$common->CustomerDetails($id);
         $idprooftype=IdProofType::where([['isActive', '=','1']])->get();
		 $areaofinterest=AreaOfInterest::where([['isActive', '=','1']])->get();
		 $customerAreaofInterestList=$common->customerAreaofInterestList($id);
		 $customerProofIdList=$common->customerProofIdList($id);
         return view('admin.customer.addedit',compact('customer','country','idprooftype','areaofinterest','customerAreaofInterestList','customerProofIdList'));
       }
	   
	   public function deleteIdProof($id) {
	   	$delete=DB::delete("delete from tblcustomeridproofdoc where id=$id");
	   	echo 1;
	   	exit();
	   }

	   public function postEdit(CustomerRequest $request, $id) {
		     $customer = Customer::find($id);
		     $proofTypeId=isset($request->proofTypeId)?(($request->proofTypeId)):0;
             $interestId=isset($request->interestId)?(($request->interestId)):0;
             $isVerify=isset($request->isVerify)?(($request->isVerify)):0;

			 
			 $customer->fname = isset($request->fname)?(ltrim($request->fname)):"";
			 $customer->lname = isset($request->lname)?(ltrim($request->lname)):"";
			 $customer->email = isset($request->email)?(ltrim($request->email)):"";
			 
			 if (isset($request->password) && $request->password!='') {
			 $customer->password =(ltrim(bcrypt($request->password)));
			 }
			 
			 $customer->phone = isset($request->phone)?(ltrim($request->phone)):"";
			 $customer->gender = isset($request->gender)?(ltrim($request->gender)):0;
			 $customer->birthdate = isset($request->birthdate)?(ltrim(date("Y-m-d",strtotime($request->birthdate)))):"";
			 $customer->countryId = isset($request->countryId)?(ltrim($request->countryId)):0;
			 $customer->proofTypeId =$proofTypeId;
		
		
		     $customer->updatedDate=date('Y-m-d H:i:s');

			 if($request->hasFile('photo')) {
				 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
				 $file = $request->file('photo');
				 $filename = time().$file->getClientOriginalName();
				 $extension = $file->getClientOriginalExtension();
				 $check=in_array($extension,$allowedfileExtension);
				 if($check) {
				   $destinationPath = 'customerphoto';
				   $file->move($destinationPath,$filename);
				   $customer->photo=$filename;						   
				 }
			 }

			 if ($interestId!=0) {
		 	    $delete=DB::delete("delete from tblcustomerareaofinterest where customerId=$id");
				   foreach ($interestId as  $value) {
					 DB::table('tblcustomerareaofinterest')->insert(
	                  ['customerId'=>$id,'interestId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
				   }
				 }

						 
                if ($proofTypeId!=0) {
                 
						 if($request->hasFile('proofType')) {
							        $delete=DB::delete("delete from tblcustomeridproofdoc where customerId=$id");

								   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
								   $files = $request->file('proofType');
								    
								    foreach($files as $file) {
									 $rand=rand(10,1000);
			                         $extension = strtolower($file->getClientOriginalExtension());
									 $filename = $rand.time().".".$extension;
			                         $check=in_array($extension,$allowedfileExtension);
										 if($check) {
										   $destinationPath = 'customerproof';
				                           $file->move($destinationPath,$filename); 
										   $isUploaded=1;
			                                
			                                 DB::table('tblcustomeridproofdoc')->insert(
			                  ['customerId'=>$id,'proofTypeId'=>$proofTypeId,'createdDate'=>date('Y-m-d H:i:s'),'idProof'=>$filename]);
										 }
								   }
						}
				}

         $customer->isVerify=$isVerify;
		 $customer->isActive=$request->isActive;
		 $customer->save();
		 session()->flash('success','Subject has updated successfully.');
		 return redirect()->to('/admin/customers');
	   }

   
}
