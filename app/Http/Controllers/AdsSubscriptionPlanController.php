<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\AdsSubscriptionPlanRequest;
use Illuminate\Http\Request;
use Laraspace\AdsSubscriptionPlan;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class AdsSubscriptionPlanController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
    	
	   //$language = Language::where('status','Active')->get();	
	   $plan= AdsSubscriptionPlan::all();
       return view('admin.adssubscriptionplan.index',compact('plan'));
	}
	
	public function add() {
		
	   return view('admin.adssubscriptionplan.addedit');
	}

	
	
	
	 public function postCreate(AdsSubscriptionPlanRequest $request) {
		 
		 $checkduplicate = DB::table('tblmissionsubscriptionplan')->where([['planName', '=',$request->planName]])->count();
		 
		 $validatyMonths=isset($request->validatyMonths)?($request->validatyMonths):0;
		 $noOfAdsAllowed=isset($request->noOfAdsAllowed)?($request->noOfAdsAllowed):0;
		 $price=isset($request->price)?($request->price):0;

		 if ($checkduplicate==0) {
			 $levelmanagement = new AdsSubscriptionPlan();
			 if ($request->planName) {
			 $levelmanagement->planName = $request->planName;
			 }
			 $levelmanagement->isActive=$request->status;
			 $levelmanagement->validatyMonths=$validatyMonths;
			 $levelmanagement->noOfAdsAllowed=$noOfAdsAllowed;
			 $levelmanagement->price=$price;
			 $levelmanagement->createdDate=date('Y-m-d H:i:s');
			 $levelmanagement->save();

			 

			 flash()->success('Plan has  added successfully.');
			 return redirect()->to('/admin/adssubscriptionplan');
		 } else {
		     flash()->error('This Plan name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/adssubscriptionplan/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      
		  $levelmanagement = AdsSubscriptionPlan::find($id);
		  $levelmanagement->isActive=$status;
		  $levelmanagement->save();
		  flash()->success('Plan status has updated successfully.');
		 return redirect()->to('/admin/adssubscriptionplan');
		  
	   }
	   
	   public function Delete($id) {
		   
		      $user = AdsSubscriptionPlan::find($id);
              $user->delete();
			  echo 2;
		  
		   exit();
	   }
	   
	   /*public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  $categoryname1='';
		  foreach ($request->del as $val) {
		    $checkvendor = DB::table('tblvender')->where([['proofTypeId', '=',$val]])->count();
		    if ($checkvendor > 0) {
			$vendor = DB::table('tblvender')->where([['proofTypeId', '=',$val]])->first();
			$vendorName=$vendor->fname." ".$vendor->lname;
			$section .=$vendorName.",";
			//$error='Some IdProofType can not delete because vendors are using this category.';
			$err=1;
			} else {
			  $category1 = DB::table('tblidprooftype')->where([['id', '=',$val]])->first();
			  $categoryname1=$category1->name;
              $section2 .=$categoryname1.",";	
			  $business = IdProofType::find($val);
              $business->delete();
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
		    $msg='Selected Id Proof Type has been deleted successfully.';
		  }
		  
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/areaofinterest');
		  //exit;
	   }*/
	   
	   public function getEdit($id)
       {
       	 
         $plan = AdsSubscriptionPlan::find($id);
         return view('admin.adssubscriptionplan.addedit',compact('plan'));
       }
	   
	   public function postEdit(AdsSubscriptionPlanRequest $request,$id) {
            
            $validatyMonths=isset($request->validatyMonths)?($request->validatyMonths):0;
		 $noOfAdsAllowed=isset($request->noOfAdsAllowed)?($request->noOfAdsAllowed):0;
		 $price=isset($request->price)?($request->price):0;

	   	  $checkduplicate = DB::table('tblmissionsubscriptionplan')->where([['planName', '=',$request->name],['id', '!=',$id]])->count();
	   	  
          if ($checkduplicate==0) {

		      $levelmanagement = AdsSubscriptionPlan::find($id);
			  if ($request->planName) {
			 $levelmanagement->planName = $request->planName;
			 }
			 $levelmanagement->isActive=$request->status;
			 $levelmanagement->validatyMonths=$validatyMonths;
			 $levelmanagement->noOfAdsAllowed=$noOfAdsAllowed;
			 $levelmanagement->price=$price;
			  $levelmanagement->updatedDate=date('Y-m-d H:i:s');
			  $levelmanagement->save();
	 
			  flash()->success('Plan has updated successfully.');
			  return redirect()->to('/admin/adssubscriptionplan');
			} else {
				 flash()->error('This Plan name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/adssubscriptionplan/edit/'.$id);
			} 
	   }
}
