<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\RateTypeRequest;
use Illuminate\Http\Request;
use Laraspace\RateType;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class RateTypeController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
	   $language = Language::where('status','Active')->get();	
	   $ratetype = RateType::all();
       return view('admin.ratetype.index',compact('ratetype','language'));
	}
	
	public function add() {
		$language = Language::where('status','Active')->get();
	   return view('admin.ratetype.addedit',compact('language'));
	}
	
	 public function postCreate(RateTypeRequest $request) {
		 
		 $checkduplicate = DB::table('tblratetype')->where([['name', '=',$request->name[1]]])->count();
		 
		 if ($checkduplicate==0) {
			 $ratetype = new RateType();
			 if ($request->name) {
			 $ratetype->name = $request->name[1];
			 }
			 $ratetype->isActive=$request->status;
			 $ratetype->createdDate=date('Y-m-d H:i:s');
			 $ratetype->save();

			 if ($request->name) {
				 
			   foreach ($request->name as $key => $value) {
				 DB::table('tblratetypetranslation')->insert(
               ['proofIdType'=>$idprooftype->id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 flash()->success('Rate Type has  added successfully.');
			 return redirect()->to('/admin/ratetype');
		 } else {
		     flash()->error('This Rate Type name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/ratetype/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      
		  $ratetype = RateType::find($id);
		  $ratetype->isActive=$status;
		  $ratetype->save();
		  flash()->success('Rate Type status has updated successfully.');
		 return redirect()->to('/admin/ratetype');
		  
	   }
	   
	   public function Delete($id) {
		   $checkvendor = DB::table('tblleadratetype')->where([['typeId', '=',$id]])->count();
	       if ($checkvendor > 0) {
		     echo 1;
		   } else {
		      $user = RateType::find($id);
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
		    $checkvendor = DB::table('tblleadratetype')->where([['proofTypeId', '=',$val]])->count();
		    if ($checkvendor > 0) {
			$vendor = DB::table('tblvender')->where([['proofTypeId', '=',$val]])->first();
			$vendorName=$vendor->fname." ".$vendor->lname;
			$section .=$vendorName.",";
			//$error='Some IdProofType can not delete because vendors are using this category.';
			$err=1;
			} else {
			  $category1 = DB::table('tblratetype')->where([['id', '=',$val]])->first();
			  $categoryname1=$category1->name;
              $section2 .=$categoryname1.",";	
			  $business = RateType::find($val);
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
		  return redirect()->to('/admin/ratetype');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
       	 $language = Language::where('status','Active')->get();
         $ratetype = RateType::find($id);
         return view('admin.ratetype.addedit',compact('ratetype','language'));
       }
	   
	   public function postEdit(RateTypeRequest $request, $id) {
	      $ratetype = RateType::find($id);
		  if ($request->name) {
		  $ratetype->name = $request->name[1];
		  }
		  $ratetype->isActive=$request->status;
		  $ratetype->createdDate=date('Y-m-d H:i:s');
		  $ratetype->save();

		  if ($request->name) {
				 $delete=DB::delete('delete from tblratetypetranslation where rateTypeId = ?',[$id]);
			   foreach ($request->name as $key => $value) {
				 DB::table('tblratetypetranslation')->insert(
               ['rateTypeId'=>$id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }
			 
		  flash()->success('Rate Type has updated successfully.');
		  return redirect()->to('/admin/ratetype');
	   }
}
