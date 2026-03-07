<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\BusinessUsersRequest;
use Illuminate\Http\Request;
use Laraspace\BusinessUsers;
use Laraspace\BusinessUsersBeacon;
use Laraspace\IndustryCategory;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Mail;
use Laraspace\Country;
use Laraspace\State;
use Laraspace\City;
use Laraspace\Mail\GeneralMail;

class BusinessUsersController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function login()
    {
        return view('admin.sessions.login');
    }

    public function adsPlan($id) {
        $common=new CommanController;
    	$businessusers=$common->BusinessUsersDetails($id);
    	$adsplan = DB::table('tbladstranscation')->where([['businessId', '=',$id]])->orderby('id','desc')->get();
    	return view('admin.businessusers.adsplan',compact('businessusers','adsplan'));
    }

    public function missionPlan($id) {
        $common=new CommanController;
    	$businessusers=$common->BusinessUsersDetails($id);
    	$missionplan = DB::table('tblmissiontranscation')->where([['businessId', '=',$id]])->orderby('id','desc')->get();
    	return view('admin.businessusers.missionplan',compact('businessusers','missionplan'));
    }

    public function index() {
		
	   $businessusers = BusinessUsers::orderby('id','desc')->pluck('id');
	   $myarray=array();
	   $common=new CommanController;
	   if ($businessusers) {
	      foreach($businessusers as $Ids) {
		  $BusinessUsersDetails=$common->BusinessUsersDetails($Ids);
		  $myarray[]=$BusinessUsersDetails;
		  }
	   }
       return view('admin.businessusers.index')->with('businessusers', $myarray);
	}
	
	public function add() {
		$common=new CommanController;
		//$country = Country::where([['status', '=','1'],['id', '=','221']])->get();
		$country = Country::where([['status', '=','1']])->get();
		$industrycategory=IndustryCategory::where([['isActive', '=','1']])->get();
		$businesswidth=$common->getImageSizeValue('business_image_width');
		$businessheight=$common->getImageSizeValue('business_image_height');
	   return view('admin.businessusers.addedit',compact('country','industrycategory','businesswidth','businessheight'));
	}
	
	 public function postCreate(BusinessUsersRequest $request) {
		 
		 $checkduplicate = DB::table('tblbusinessusers')->where([['companyName', '=',$request->companyName]])->count();
		 $countemail = DB::table('tblbusinessusers')->where('emailAddress', '=', $request->emailAddress)->count();
		 
		
		 if ($checkduplicate==0 && $countemail==0) {
			 $businessusers = new BusinessUsers();
			 $businessusers->companyName = isset($request->companyName)?(ltrim($request->companyName)):"";
			 $businessusers->emailAddress = isset($request->emailAddress)?(ltrim($request->emailAddress)):"";
			 if (isset($request->password) && $request->password!='') {
			 $businessusers->password =(ltrim(bcrypt($request->password)));
			 }
			 $businessusers->contactPersonName = isset($request->contactPersonName)?(ltrim($request->contactPersonName)):"";

			 $businessusers->contactNo = isset($request->contactNo)?(ltrim($request->contactNo)):"";
			 
			 $businessusers->address1 = isset($request->address1)?(ltrim($request->address1)):"";
			 $businessusers->address2 = isset($request->address2)?(ltrim($request->address2)):"";
			 
			 $businessusers->createdDate=date('Y-m-d H:i:s');

			 $businessusers->countryId = isset($request->countryId)?(ltrim($request->countryId)):0;
			 $businessusers->stateId = isset($request->stateId)?(ltrim($request->stateId)):0;
			 $businessusers->cityId = isset($request->cityId)?(ltrim($request->cityId)):0;
			 $businessusers->industryCategoryId = isset($request->industryCategoryId)?(ltrim($request->industryCategoryId)):0;
			 


			 /*if($request->hasFile('profilePicture')) {
				 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
				 $file = $request->file('profilePicture');
				 $filename = $file->getClientOriginalName();
				 $extension = $file->getClientOriginalExtension();
				 $check=in_array($extension,$allowedfileExtension);
				 if($check) {
				   $destinationPath = 'businessusers';
				   $file->move($destinationPath,$filename);
				   $businessusers->profilePicture=$filename;						   
				 }
			 }*/

			 if ($request->file('profilePicture')) {
				 $file = $request->file('profilePicture');
				 $size = getimagesize($file);
				 $ratio = $size[0]/$size[1];
				 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

				 $common=new CommanController;
				 $businesswidth=$common->getImageSizeValue('business_image_width');
				 $businessheight=$common->getImageSizeValue('business_image_height');
				 if( $ratio > 1) {
					$width = $businesswidth;
					$height = $businessheight/$ratio;
				 }
				 else {
					$width = $businesswidth;
					$height = $businessheight;
				 }
				 
				 $extension = strtolower($file->getClientOriginalExtension());

				 $category_image=time().$file->getClientOriginalName();
				 $category_destinationPath = public_path('/businessusers/thumbnail_images');
				//$thumb_img = Image::make($file->getRealPath())->resize(150, 150);
				 $check=in_array($extension,$allowedfileExtension);
				 if($check) {
				 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
				 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
				 $filename=$file->getClientOriginalName();
				 $destinationPath = 'businessusers';
				 $file->move($destinationPath,$file->getClientOriginalName());
				 $businessusers->profilePicture=$category_image;
				}
			 }

			 $businessusers->isActive=$request->status;
			 $businessusers->save();
			 
			 
			 
			 
			 flash()->success('Business User has  added successfully.');
			 return redirect()->to('/admin/businessusers');
		 } else {
		     flash()->error('duplicate records please check another.');
			 return redirect()->to('/admin/businessusers/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      //$status;
		  $businessusers = BusinessUsers::find($id);
		  $businessusers->isActive=$status;
		  $businessusers->save();


		  flash()->success('Business user status has updated successfully.');
		 return redirect()->to('/admin/businessusers');
		  
	   }
	   
	   
	   
	   public function Delete($id) {

	   	     $businessusers = BusinessUsers::find($id);
              $businessusers->delete();
              
                $msg='';
               echo $msg; 
	           exit();

		   /*$checkvendor = DB::table('tblleadvender')->where([['venderId', '=',$id]])->count();
		   $checkportfolio = DB::table('tblportfolio')->where([['venderId', '=',$id]])->count();
		   $checkac = DB::table('tblachievement')->where([['venderId', '=',$id]])->count();
           $msg='';
           if ($checkvendor > 0) {
           	$msg='Lead';
           } elseif ($checkvendor > 0 && $checkportfolio > 0) {
           	$msg='Lead,PortFolio';
           } elseif ($checkvendor > 0 && $checkportfolio > 0 && $checkac > 0) {
           	$msg='Lead,PortFolio,Achievment';
           } elseif ($checkportfolio > 0 && $checkac > 0) {
           	$msg='PortFolio,Achievment';
           } elseif ($checkportfolio > 0) {
           	$msg='PortFolio';
           } elseif ($checkac > 0) {
           	$msg='Achievment';
           } else {
           	$msg='';
           	 $vendor = Vendor::find($id);
              $vendor->delete();
           }
           
           if ($msg!='') {
           	$msg="you can not delete this vendor because first you have to delete this vendor of ".$msg."";
           }
           echo $msg; 
	       exit();*/
	       /*if ($checkvendor > 0) {
		     echo 1;
		   } else {
		      $user = Vendor::find($id);
              $user->delete();
			  echo 2;
		   }*/
		   
	   }

	   /*public function getShow($id) {

	   	    $common=new CommanController;
         $businessusers=$common->BusinessUsersDetails($id);
         $country = Country::where([['status', '=','1'],['id', '=','221']])->get();
         return view('admin.businessusers.show',compact('businessusers','country'));

	   }*/

	   public function getShow($id) {

	   	 $common=new CommanController;
         $businessusers=$common->BusinessUsersDetails($id);
         $beacon=BusinessUsersBeacon::where('businessId',$id)->get();
         $country = Country::where([['status', '=','1'],['id', '=','221']])->get();
         //print_r($businessusers);
         //exit;
         return view('admin.businessusers.show',compact('businessusers','country','beacon'));

	   }
	   
	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  foreach ($request->del as $val) {
		    $checkvendor = DB::table('tblmission')->where([['businessId', '=',$val]])->count();
		    
			if ($checkvendor > 0) {
			
			$mis = DB::table('tblmission')->where([['businessId', '=',$val]])->first();
			$misName=$mis->missionName;
			$section .=$misName.",";
			//$error='Some Category can not delete because vendors are using this category.';
			$err=1;
			} else {
			  $category1 = DB::table('tblmission')->where([['businessId', '=',$val]])->first();
			  $misNames=$category1->missionName;
              $section2 .=$misNames.",";			  
			  $bus = BusinessUsers::find($val);
              $bus->delete();
			  //$error='Selected Categories has been deleted successfully.';
			}
		  }
		  
		  if ($err==1 && $section!='') {
			  $section=substr($section,0,-1);
			  $section2=substr($section2,0,-1);
		    $msg='Warning! Selected record is related to following mission('.$section.') therefore deleting operation can’t be performed. For deleting the record needs to remove related records from listed mission. ';
		    if ($section2!='') {
			$msg .="But $section2 has been deleted successfully.";
			}
		  } else {
		    $msg='Selected Business Users has been deleted successfully.';
		  }
		  
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/businessusers');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
		 
		 $common=new CommanController;
         $businessusers=$common->BusinessUsersDetails($id);
         $country = Country::where([['status', '=','1']])->get();
         //$country = Country::where([['status', '=','1'],['id', '=','221']])->get();
         $industrycategory=IndustryCategory::where([['isActive', '=','1']])->get();
         $businesswidth=$common->getImageSizeValue('business_image_width');
		 $businessheight=$common->getImageSizeValue('business_image_height');
         return view('admin.businessusers.addedit',compact('businessusers','country','industrycategory','businesswidth','businessheight'));
       }
	   
	   public function postEdit(BusinessUsersRequest $request, $id) {
		 
		     $businessusers = BusinessUsers::find($id);
			 $businessusers->companyName = isset($request->companyName)?(ltrim($request->companyName)):"";
			 $businessusers->emailAddress = isset($request->emailAddress)?(ltrim($request->emailAddress)):"";
			 $businessusers->contactPersonName = isset($request->contactPersonName)?(ltrim($request->contactPersonName)):"";
			 $businessusers->contactNo = isset($request->contactNo)?(ltrim($request->contactNo)):"";
			 
			 $businessusers->address1 = isset($request->address1)?(ltrim($request->address1)):"";
			 $businessusers->address2 = isset($request->address2)?(ltrim($request->address2)):"";

			 if (isset($request->password) && $request->password!='') {
			 $businessusers->password =(ltrim(bcrypt($request->password)));
			 }
		
		 /*if($request->hasFile('profilePicture')) {
			 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
			 $file = $request->file('profilePicture');
			 $filename = $file->getClientOriginalName();
			 $extension = $file->getClientOriginalExtension();
			 $check=in_array($extension,$allowedfileExtension);
			 if($check) {
			   $destinationPath = 'businessusers';
			   $file->move($destinationPath,$filename);
			   $businessusers->profilePicture=$filename;						   
			 }
		  }*/

		  if ($request->file('profilePicture')) {
				 $file = $request->file('profilePicture');
				 $size = getimagesize($file);
				 $ratio = $size[0]/$size[1];
				 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

				 $common=new CommanController;
				 $businesswidth=$common->getImageSizeValue('business_image_width');
				 $businessheight=$common->getImageSizeValue('business_image_height');
				 if( $ratio > 1) {
					$width = $businesswidth;
					$height = $businessheight/$ratio;
				 }
				 else {
					$width = $businesswidth;
					$height = $businessheight;
				 }
				 
				 $extension = strtolower($file->getClientOriginalExtension());

				 $category_image=time().$file->getClientOriginalName();
				 $category_destinationPath = public_path('/businessusers/thumbnail_images');
				//$thumb_img = Image::make($file->getRealPath())->resize(150, 150);
				 $check=in_array($extension,$allowedfileExtension);

				 if($check) {
				 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
				 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
				 $filename=$file->getClientOriginalName();
				 $destinationPath = 'businessusers';
				 $file->move($destinationPath,$file->getClientOriginalName());
				 $businessusers->profilePicture=$category_image;
				}
			 }

         $businessusers->updatedDate=date('Y-m-d H:i:s');

		 $businessusers->countryId = isset($request->countryId)?(ltrim($request->countryId)):0;
		 $businessusers->stateId = isset($request->stateId)?(ltrim($request->stateId)):0;
		 $businessusers->cityId = isset($request->cityId)?(ltrim($request->cityId)):0;
		 $businessusers->industryCategoryId = isset($request->industryCategoryId)?(ltrim($request->industryCategoryId)):0;

		
		 $businessusers->isActive=$request->status;
		 $businessusers->save();
		 

		
		 
		 flash()->success('Business Users has updated successfully.');
		 return redirect()->to('/admin/businessusers');
	   }
}
