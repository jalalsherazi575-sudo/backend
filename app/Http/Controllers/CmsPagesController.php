<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\CmsPagesRequest;
use Illuminate\Http\Request;
use Laraspace\CmsPages;
//use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class CmsPagesController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
   }
    
  public function index() {
	   $cmspages = CmsPages::orderby('name','asc')->get();
       return view('admin.cmspages.index',compact('cmspages'));
	}
	
	public function add() {
	   return view('admin.cmspages.addedit');
	}
	
	public function postCreate(CmsPagesRequest $request) 
	{
	 	$cmspages = new CmsPages();
		$cmspages->name = $request->name;
	  if ($request->description) {
		 $cmspages->description = $request->description;
		 }
		$cmspages->isActive=$request->status;
		$cmspages->createdDate=date('Y-m-d H:i:s');
		$cmspages->save();
	 	session()->flash('success','Cms page has been added successfully');
		return redirect()->to('/admin/cmspages');
		
   }
	  
	  public function getEdit($id)
   {
     $cmspages = CmsPages::find($id);
     return view('admin.cmspages.addedit',compact('cmspages'));
   }
	   
	   public function postEdit(CmsPagesRequest $request, $id) 
	  {
	      $cmspages = CmsPages::findOrFail($id);
		     
	      if ($request->name) {
		   $cmspages->name = $request->name;
		  }

		  if ($request->description) {
		  $cmspages->description = $request->description;
		  }

		  $cmspages->isActive=$request->status;
		  $cmspages->createdDate=date('Y-m-d H:i:s');
		  $cmspages->save();
		  session()->flash('success','Cms page has been updated successfully');
		  return redirect()->to('/admin/cmspages');
	  }
	   public function Status($status,$id) {
	      
		  $cmspages = CmsPages::find($id);
		  $cmspages->isActive=$status;
		  $cmspages->save();
		  session()->flash('success','Cms page status has been updated successfully');
		 return redirect()->to('/admin/cmspages');
		  
	   }
	   
	   public function Delete($id) {
	   	$cmspage = CmsPages::find($id);
      $cmspage->delete();
      return Response(['status'=>'success','message'=>'CMS Page deleted successfully']);

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
			$section .=$planName.",";
			$err=1;
			} else {
				$deletesub=DB::select( DB::raw("delete from tblsubscriptionplanstranslation where `subscriptionPlanId`='$val'"));
			  $plans= DB::table('tblsubscriptionplans')->where([['id', '=',$val]])->first();
			  $planName=$plans->name;
              $section2 .=$planName.",";	
			  $subscriptionplan = CmsPages::find($val);
              $subscriptionplan->delete();
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
		  session()->flash('error',$msg);
		  } else {
		  session()->flash('success',$msg);
		  }
		  return redirect()->to('/admin/cmspages');
		  //exit;
	   }
	   
	  
}
