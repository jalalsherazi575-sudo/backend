<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\SubscriptionPlanRequest;
use Illuminate\Http\Request;
use Laraspace\SubscriptionPlan;
//use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class SubscriptionPlanController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
	   $language = Language::where('status','Active')->get();	
	   $subscriptionplan = SubscriptionPlan::all();
       return view('admin.subscriptionplan.index',compact('subscriptionplan','language'));
	}
	
	public function add() {
		$language = Language::where('status','Active')->get();
	   return view('admin.subscriptionplan.addedit',compact('language'));
	}
	
	 public function postCreate(SubscriptionPlanRequest $request) {
		 
		 $checkduplicate = DB::table('tblsubscriptionplans')->where([['name', '=',$request->name[1]]])->count();
		 
		 if ($checkduplicate==0) {
			 $subscriptionplan = new SubscriptionPlan();
			 if ($request->name) {
			 $subscriptionplan->name = $request->name[1];
			 }
			 if ($request->description) {
			 $subscriptionplan->description = $request->description[1];
			 }
			 $subscriptionplan->isActive=$request->status;
			 $subscriptionplan->price=$request->price;
			 $subscriptionplan->noOfLeadsPerDuration=$request->noOfLeadsPerDuration;
			 $subscriptionplan->createdDate=date('Y-m-d H:i:s');
			 $subscriptionplan->save();

			 if ($request->name) {
				 
			   foreach ($request->name as $key => $value) {
			   	   $desc=($request->description[$key])?($request->description[$key]):"";

				 DB::table('tblsubscriptionplanstranslation')->insert(
               ['subscriptionPlanId'=>$subscriptionplan->id,'name'=>$value,'description'=>$desc,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 flash()->success('Subscription Plan has  added successfully.');
			 return redirect()->to('/admin/subscriptionplan');
		 } else {
		     flash()->error('This Subscription Plan name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/subscriptionplan/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      $status;
		  $subscriptionplan = SubscriptionPlan::find($id);
		  $subscriptionplan->isActive=$status;
		  $subscriptionplan->save();
		  flash()->success('Subscription Plan status has updated successfully.');
		 return redirect()->to('/admin/subscriptionplan');
		  
	   }
	   
	   public function Delete($id) {
	   	$common=new CommanController;
           $Vname="";
		   $checkvendor = DB::table('tblvendersubscription')->where([['subscriptionPlanId', '=',$id]])->get();
	       $checkvendorcount=count($checkvendor);
	       if ($checkvendorcount > 0) {
	       	  foreach ($checkvendor as  $value) {
	       	  	  $vendorId=$value->venderId;
	       	  	  $vendorName=$common->vendorName($vendorId);
	       	  	  $Vname.=$vendorName.",";
	       	  }
	       	  $Vname=substr($Vname,0,-1);
		     echo "You can not delete this plan because this plan using this vendor (".$Vname.")";
		   } else {
		   	  $deletesub=DB::select( DB::raw("delete from tblsubscriptionplanstranslation where `subscriptionPlanId`='$id'"));

		      $user = SubscriptionPlan::find($id);
              $user->delete();
			  echo 2;
		   }
		   exit();
	   }
	   
	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  $categoryname1='';
		  foreach ($request->del as $val) {
		    $checkvendor = DB::table('tblvendersubscription')->where([['subscriptionPlanId', '=',$val]])->get();
		    $checkvendorcount=count($checkvendor);
		    if ($checkvendorcount > 0) {
			  
			  foreach ($checkvendor as  $value) {
	       	  	  $vendorId=$value->venderId;
	       	  	  $vendorName=$common->vendorName($vendorId);
	       	  	  $Vname.=$vendorName.",";
	       	  }
			$plans= DB::table('tblsubscriptionplans')->where([['id', '=',$val]])->first();
			$planName=$plans->name;
			//$vendorName=$vendor->fname." ".$vendor->lname;
			$section .=$planName.",";
			//$error='Some IdProofType can not delete because vendors are using this category.';
			$err=1;
			} else {
				$deletesub=DB::select( DB::raw("delete from tblsubscriptionplanstranslation where `subscriptionPlanId`='$val'"));
			  $plans= DB::table('tblsubscriptionplans')->where([['id', '=',$val]])->first();
			  $planName=$plans->name;
              $section2 .=$planName.",";	
			  $subscriptionplan = SubscriptionPlan::find($val);
              $subscriptionplan->delete();
			  //$error='Selected Id Proof Type has been deleted successfully.';
			}
		  }
		  
		  if ($err==1 && $section!='') {
			  $section=substr($section,0,-1);
			  $section2=substr($section2,0,-1);
		    $msg='Warning! Selected record is related to following vendor('.$section.') therefore deleting operation can’t be performed. For deleting the record needs to remove related records from listed vendor. ';
		    if ($section2!='') {
			$msg .="But $categoryname1 has been deleted successfully.";
			}
		  } else {
		    $msg='Selected Plans has been deleted successfully.';
		  }
		  
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/subscriptionplan');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
       	 $language = Language::where('status','Active')->get();
         $subscriptionplan = SubscriptionPlan::find($id);
         return view('admin.subscriptionplan.addedit',compact('subscriptionplan','language'));
       }
	   
	   public function postEdit(SubscriptionPlanRequest $request, $id) {
	      $subscriptionplan = SubscriptionPlan::find($id);
		   if ($request->name) {
			 $subscriptionplan->name = $request->name[1];
			 }
			 if ($request->description) {
			 $subscriptionplan->description = $request->description[1];
			 }
		  $subscriptionplan->isActive=$request->status;
		  $subscriptionplan->price=$request->price;
		  $subscriptionplan->noOfLeadsPerDuration=$request->noOfLeadsPerDuration;
		  $subscriptionplan->createdDate=date('Y-m-d H:i:s');
		  $subscriptionplan->save();

		  if ($request->name) {
				 $delete=DB::delete('delete from tblsubscriptionplanstranslation where subscriptionPlanId = ?',[$id]);
			    foreach ($request->name as $key => $value) {
			   	   $desc=($request->description[$key])?($request->description[$key]):"";

				 DB::table('tblsubscriptionplanstranslation')->insert(
               ['subscriptionPlanId'=>$subscriptionplan->id,'name'=>$value,'description'=>$desc,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }
			 
		  flash()->success('Subscription Plan has updated successfully.');
		  return redirect()->to('/admin/subscriptionplan');
	   }
}
