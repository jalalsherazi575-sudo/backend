<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\BusinessUsersBeaconRequest;
use Illuminate\Http\Request;
use Laraspace\BusinessUsers;
use Laraspace\BusinessUsersBeacon;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Mail;
use Laraspace\Country;
use Laraspace\State;
use Laraspace\City;
use Laraspace\Mail\GeneralMail;

class BusinessUsersBeaconController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function beaconindex() {
		
	   //$beacon = BusinessUsersBeacon::where('businessId',$businessId)->get();
	   $beacon = DB::table('tblbusinessusersbeacon')
            ->leftJoin('tblbusinessusers', 'tblbusinessusersbeacon.businessId', '=', 'tblbusinessusers.id')
            ->select('tblbusinessusersbeacon.*', 'tblbusinessusers.companyName')
            ->orderby('tblbusinessusersbeacon.id','desc')
            ->get();

       return view('admin.individualbeacon.index',compact('beacon'));
	}

	public function addbeacon() {
		$businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
	   return view('admin.individualbeacon.addedit',compact('businessusers'));
	}

	public function beaconpostCreate(BusinessUsersBeaconRequest $request) {
		 
		 $checkduplicate = DB::table('tblbusinessusersbeacon')->where([['beaconName', '=',$request->beaconName],['iBeaconUUID', '=',$request->iBeaconUUID],['iBeaconMinor', '=',$request->iBeaconMinor],['iBeaconMajor', '=',$request->iBeaconMajor]])->count();
		 $countemail = DB::table('tblbusinessusersbeacon')->where([['nameSpaceId', '=',$request->nameSpaceId],['instanceId', '=',$request->instanceId]])->count();
		 
		 $search_location=($request->search_location)?($request->search_location):"";
		
		 if ($checkduplicate==0 && $countemail==0) {

			 $beacon = new BusinessUsersBeacon();
			 $beacon->businessId = isset($request->businessId)?(ltrim($request->businessId)):0;
			 $beacon->beaconName = isset($request->beaconName)?(ltrim($request->beaconName)):"";
			 $beacon->nameSpaceId = isset($request->nameSpaceId)?(ltrim($request->nameSpaceId)):"";
			 $beacon->instanceId = isset($request->instanceId)?(ltrim($request->instanceId)):"";

			 $beacon->iBeaconUUID = isset($request->iBeaconUUID)?(ltrim($request->iBeaconUUID)):"";
			 $beacon->EddystoneUIDNamespace = isset($request->EddystoneUIDNamespace)?(ltrim($request->EddystoneUIDNamespace)):"";
			 $beacon->EddystoneUIDInstanceId = isset($request->EddystoneUIDInstanceId)?(ltrim($request->EddystoneUIDInstanceId)):"";
			 
			 $beacon->building = isset($request->building)?(ltrim($request->building)):"";
			 $beacon->floor = isset($request->floor)?(ltrim($request->floor)):"";

			 $beacon->iBeaconMinor = isset($request->iBeaconMinor)?(ltrim($request->iBeaconMinor)):"";
			 $beacon->iBeaconMajor = isset($request->iBeaconMajor)?(ltrim($request->iBeaconMajor)):"";
			 
			 $beacon->createdDate=date('Y-m-d H:i:s');

			 $beacon->department = isset($request->department)?(ltrim($request->department)):0;
			 $beacon->lattitude = isset($request->lattitude)?(ltrim($request->lattitude)):0;
			 $beacon->longitude = isset($request->longitude)?(ltrim($request->longitude)):0;
			 $beacon->beaconType = isset($request->beaconType)?(ltrim($request->beaconType)):0;
			 $beacon->location=$search_location;
			 //$beacon->businessId = $businessId;
			 
			 $beacon->isActive=$request->status;
			 $beacon->save();
			 
			 
			 
			 
			 flash()->success('Beacon has  added successfully.');
			 return redirect()->to('/admin/assbeacon');
		 } else {
		     flash()->error('duplicate records please add another.');
			 return redirect()->to('/admin/assbeacon/add');
		 }
		 //echo $request->name;
		 //exit;
       }

       public function beaconStatus($status,$id) {
		  $beacon = BusinessUsersBeacon::find($id);
		  $beacon->isActive=$status;
		  $beacon->save();
		  flash()->success('Beacon status has updated successfully.');
		 return redirect()->to('/admin/assbeacon/');
	   }
	   
	   public function beaconDelete($id) {
	   	     $beacon = BusinessUsersBeacon::find($id);
             $beacon->delete();
             $msg='';
             echo $msg; 
	         exit();
	   }

	   public function beacongetEdit($id)
       {
		 
		 $common=new CommanController;
		 $beacon = BusinessUsersBeacon::find($id);
		 $businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
         return view('admin.individualbeacon.addedit',compact('beacon','businessusers'));
       }
	   
	   public function beaconpostEdit(BusinessUsersBeaconRequest $request,$id) {
		   
		   $checkduplicate = DB::table('tblbusinessusersbeacon')->where([['beaconName', '=',$request->beaconName],['iBeaconUUID', '=',$request->iBeaconUUID],['iBeaconMinor', '=',$request->iBeaconMinor],['iBeaconMajor', '=',$request->iBeaconMajor],['id', '!=',$id]])->count();
		   $countemail = DB::table('tblbusinessusersbeacon')->where([['nameSpaceId', '=',$request->nameSpaceId],['instanceId', '=',$request->instanceId],['id', '!=',$id]])->count();

		   if ($checkduplicate==0 && $countemail==0) {

			     $beacon = BusinessUsersBeacon::find($id);
			     $search_location=($request->search_location)?($request->search_location):"";

				 $beacon->beaconName = isset($request->beaconName)?(ltrim($request->beaconName)):"";
				 $beacon->nameSpaceId = isset($request->nameSpaceId)?(ltrim($request->nameSpaceId)):"";
				 $beacon->instanceId = isset($request->instanceId)?(ltrim($request->instanceId)):"";

				 $beacon->iBeaconUUID = isset($request->iBeaconUUID)?(ltrim($request->iBeaconUUID)):"";
				 $beacon->EddystoneUIDNamespace = isset($request->EddystoneUIDNamespace)?(ltrim($request->EddystoneUIDNamespace)):"";
				 $beacon->EddystoneUIDInstanceId = isset($request->EddystoneUIDInstanceId)?(ltrim($request->EddystoneUIDInstanceId)):"";
				 
				 $beacon->building = isset($request->building)?(ltrim($request->building)):"";
				 $beacon->floor = isset($request->floor)?(ltrim($request->floor)):"";
			
			     $beacon->department = isset($request->department)?(ltrim($request->department)):0;
				 $beacon->lattitude = isset($request->lattitude)?(ltrim($request->lattitude)):0;
				 $beacon->longitude = isset($request->longitude)?(ltrim($request->longitude)):0;
				 $beacon->beaconType = isset($request->beaconType)?(ltrim($request->beaconType)):0;
				 $beacon->businessId =isset($request->businessId)?(ltrim($request->businessId)):0;

				 $beacon->iBeaconMinor = isset($request->iBeaconMinor)?(ltrim($request->iBeaconMinor)):"";
				 $beacon->iBeaconMajor = isset($request->iBeaconMajor)?(ltrim($request->iBeaconMajor)):"";

			     $beacon->updatedDate=date('Y-m-d H:i:s');
				 $beacon->isActive=$request->status;
				 $beacon->location=$search_location;
				 $beacon->save();
			 

			     flash()->success('Beacon has updated successfully.');
			     return redirect()->to('/admin/assbeacon/');
			} else {
				 flash()->error('duplicate records please add another.');
			     return redirect()->to('/admin/assbeacon/edit/'.$id);
			}     

	   }

    public function index($businessId) {
		
	   $beacon = BusinessUsersBeacon::where('businessId',$businessId)->get();
	   $businessusers = BusinessUsers::find($businessId);
	   
       return view('admin.beacon.index',compact('beacon','businessId','businessusers'));
	}
	
	public function add($businessId) {
		$businessusers = BusinessUsers::find($businessId);
	   return view('admin.beacon.addedit',compact('businessId','businessusers'));
	}
	
	 public function postCreate(BusinessUsersBeaconRequest $request,$businessId) {
		 
		 $checkduplicate = DB::table('tblbusinessusersbeacon')->where([['beaconName', '=',$request->beaconName],['iBeaconUUID', '=',$request->iBeaconUUID],['iBeaconMinor', '=',$request->iBeaconMinor],['iBeaconMajor', '=',$request->iBeaconMajor]])->count();
		 $countemail = DB::table('tblbusinessusersbeacon')->where([['nameSpaceId', '=',$request->nameSpaceId],['instanceId', '=',$request->instanceId]])->count();


		 $search_location=($request->search_location)?($request->search_location):"";
		
		 if ($checkduplicate==0 && $countemail==0) {

			 $beacon = new BusinessUsersBeacon();
			 $beacon->beaconName = isset($request->beaconName)?(ltrim($request->beaconName)):"";
			 $beacon->nameSpaceId = isset($request->nameSpaceId)?(ltrim($request->nameSpaceId)):"";
			 $beacon->instanceId = isset($request->instanceId)?(ltrim($request->instanceId)):"";

			 $beacon->iBeaconUUID = isset($request->iBeaconUUID)?(ltrim($request->iBeaconUUID)):"";
			 $beacon->EddystoneUIDNamespace = isset($request->EddystoneUIDNamespace)?(ltrim($request->EddystoneUIDNamespace)):"";
			 $beacon->EddystoneUIDInstanceId = isset($request->EddystoneUIDInstanceId)?(ltrim($request->EddystoneUIDInstanceId)):"";
			 
			 $beacon->building = isset($request->building)?(ltrim($request->building)):"";
			 $beacon->floor = isset($request->floor)?(ltrim($request->floor)):"";

			 $beacon->iBeaconMinor = isset($request->iBeaconMinor)?(ltrim($request->iBeaconMinor)):"";
			 $beacon->iBeaconMajor = isset($request->iBeaconMajor)?(ltrim($request->iBeaconMajor)):"";
			 
			 $beacon->createdDate=date('Y-m-d H:i:s');

			 $beacon->department = isset($request->department)?(ltrim($request->department)):0;
			 $beacon->lattitude = isset($request->lattitude)?(ltrim($request->lattitude)):0;
			 $beacon->longitude = isset($request->longitude)?(ltrim($request->longitude)):0;
			 $beacon->beaconType = isset($request->beaconType)?(ltrim($request->beaconType)):0;
			 $beacon->businessId = $businessId;
			 $beacon->location=$search_location;
			 
			 $beacon->isActive=$request->status;
			 $beacon->save();
			 
			 
			 
			 
			 flash()->success('Beacon has  added successfully.');
			 return redirect()->to('/admin/businessusersbeacon/'.$businessId);
		 } else {
		     flash()->error('duplicate records please add another.');
			 return redirect()->to('/admin/businessusersbeacon/add/'.$businessId);
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      //$status;
		  $beacon = BusinessUsersBeacon::find($id);
		  $beacon->isActive=$status;
		  $beacon->save();


		  flash()->success('Beacon status has updated successfully.');
		 return redirect()->to('/admin/businessusersbeacon/'.$id);
		  
	   }
	   
	   
	   
	   public function Delete($id) {

	   	     $beacon = BusinessUsersBeacon::find($id);
              $beacon->delete();
              
                $msg='';
               echo $msg; 
	           exit();

		  
		   
	   }

	   public function getShow($id) {

	   	 $common=new CommanController;
         $businessusers=$common->BusinessUsersDetails($id);
         $beacon=BusinessUsersBeacon::where('businessId',$id)->get();
         $country = Country::where([['status', '=','1'],['id', '=','221']])->get();
         return view('admin.businessusers.show',compact('businessusers','country','beacon'));

	   }
	   
	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  foreach ($request->del as $val) {
		    $checkvendor = DB::table('tblleadvender')->where([['venderId', '=',$val]])->count();
		    
			if ($checkvendor > 0) {
			
			$vendor = DB::table('tblvender')->where([['id', '=',$val]])->first();
			$vendorName=$vendor->fname." ".$vendor->lname;
			$section .=$vendorName.",";
			//$error='Some Category can not delete because vendors are using this category.';
			$err=1;
			} else {
			  $category1 = DB::table('tblvender')->where([['id', '=',$val]])->first();
			  $vendorName=$category1->fname." ".$category1->lname;
              $section2 .=$vendorName.",";			  
			  $vendor = BusinessUsers::find($val);
              $vendor->delete();
			  
			 
			  
			  //$error='Selected Categories has been deleted successfully.';
			}
		  }
		  
		  if ($err==1 && $section!='') {
			  $section=substr($section,0,-1);
			  $section2=substr($section2,0,-1);
		    $msg='Warning! Selected record is related to following vendor('.$section.') therefore deleting operation can’t be performed. For deleting the record needs to remove related records from listed vendor. ';
		    if ($section2!='') {
			$msg .="But $section2 has been deleted successfully.";
			}
		  } else {
		    $msg='Selected Vendor has been deleted successfully.';
		  }
		  
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/businessusers');
		  //exit;
	   }
	   
	   public function getEdit($businessId,$id)
       {
		 
		 $businessusers = BusinessUsers::find($businessId);
		 $common=new CommanController;
		 $beacon = BusinessUsersBeacon::find($id);
         
         
         return view('admin.beacon.addedit',compact('businessusers','beacon'));
       }
	   
	   public function postEdit(BusinessUsersBeaconRequest $request,$businessId,$id) {
		    
		   $checkduplicate = DB::table('tblbusinessusersbeacon')->where([['beaconName', '=',$request->beaconName],['iBeaconUUID', '=',$request->iBeaconUUID],['iBeaconMinor', '=',$request->iBeaconMinor],['iBeaconMajor', '=',$request->iBeaconMajor],['id', '!=',$id]])->count();
		   $countemail = DB::table('tblbusinessusersbeacon')->where([['nameSpaceId', '=',$request->nameSpaceId],['instanceId', '=',$request->instanceId],['id', '!=',$id]])->count();
           
           if ($checkduplicate==0 && $countemail==0) {

			     $beacon = BusinessUsersBeacon::find($id);
			     $search_location=($request->search_location)?($request->search_location):"";

				 $beacon->beaconName = isset($request->beaconName)?(ltrim($request->beaconName)):"";
				 $beacon->nameSpaceId = isset($request->nameSpaceId)?(ltrim($request->nameSpaceId)):"";
				 $beacon->instanceId = isset($request->instanceId)?(ltrim($request->instanceId)):"";

				 $beacon->iBeaconUUID = isset($request->iBeaconUUID)?(ltrim($request->iBeaconUUID)):"";
				 $beacon->EddystoneUIDNamespace = isset($request->EddystoneUIDNamespace)?(ltrim($request->EddystoneUIDNamespace)):"";
				 $beacon->EddystoneUIDInstanceId = isset($request->EddystoneUIDInstanceId)?(ltrim($request->EddystoneUIDInstanceId)):"";
				 
				 $beacon->building = isset($request->building)?(ltrim($request->building)):"";
				 $beacon->floor = isset($request->floor)?(ltrim($request->floor)):"";
			     $beacon->location=$search_location;
			     $beacon->department = isset($request->department)?(ltrim($request->department)):0;
				 $beacon->lattitude = isset($request->lattitude)?(ltrim($request->lattitude)):0;
				 $beacon->longitude = isset($request->longitude)?(ltrim($request->longitude)):0;
				 $beacon->beaconType = isset($request->beaconType)?(ltrim($request->beaconType)):0;
				 $beacon->businessId = $businessId;

				 $beacon->iBeaconMinor = isset($request->iBeaconMinor)?(ltrim($request->iBeaconMinor)):"";
				 $beacon->iBeaconMajor = isset($request->iBeaconMajor)?(ltrim($request->iBeaconMajor)):"";

			     $beacon->updatedDate=date('Y-m-d H:i:s');
				 $beacon->isActive=$request->status;
				 $beacon->save();
		 
                 flash()->success('Beacon has updated successfully.');
		         return redirect()->to('/admin/businessusersbeacon/'.$businessId);
	   
	     } else {
                 
                 flash()->error('duplicate records please add another.');
			     return redirect()->to('/admin/businessusersbeacon/edit/'.$businessId.'/'.$id);
         }


	 }    

}
