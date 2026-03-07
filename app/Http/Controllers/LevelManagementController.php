<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\LevelManagementRequest;
use Illuminate\Http\Request;
use Laraspace\LevelManagement;
use Laraspace\LessionManagement;
use Intervention\Image\Facades\Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class LevelManagementController extends Controller
{

	public function __construct() 
	{
    $this->middleware('auth');
  }
    
  public function index() 
  {
	  $levelmanagement = LevelManagement::all();
    return view('admin.levelmanagement.index',compact('levelmanagement'));
	}
	
	public function add() {
	   $common=new CommanController;
	   $rank=$common->getLevelMaxRank();	
	   return view('admin.levelmanagement.addedit',compact('rank'));
	}

	
 	public function postCreate(LevelManagementRequest $request) 
 	{
			$common=new CommanController;
		 	$sortOrder=isset($request->sortOrder)?($request->sortOrder):0;
			$levelmanagement = new LevelManagement();
			if ($request->levelName) {
			 	$levelmanagement->levelName = $request->levelName;
			}
			$levelmanagement->isActive=$request->status;
			$levelmanagement->sortOrder=$sortOrder;
			$levelmanagement->createdDate=date('Y-m-d H:i:s');
			if(!empty($request->file('catImage'))){
	            $imageGellary    = $request->file('catImage');
	            $imagesize = getimagesize($imageGellary);
	            $width = $imagesize[0];
	            $height = $imagesize[1];
	            /*set image size*/
	            $imagew = 300;  
	            $imageh = 300;  
	            $imageheight_width = array();
	            $setheight = 300;
	            $setwidth = 300;  
	            $size = $common->proposnallyimage($height,$width,$setheight,$setwidth);
	            $new_name = md5(uniqid(rand(), true)).'.'.$imageGellary->getClientOriginalExtension();

	            Image::make($imageGellary)->resize($size['NewWidth'],$size['Newheight'])->save(public_path('images/category'.'/'.$new_name));
	            $levelmanagement->catImage = $new_name;
	        }
		 	$levelmanagement->save();
		 	$add_level=$common->get_msg("add_level",1)?$common->get_msg("add_level",1):"Category has added successfully.";
			session()->flash('success',$add_level);
			return redirect()->to('/admin/levelmanagement');
  }

	public function getEdit($id) 
 	{
 	 	$common=new CommanController;
 	 	$rank=$common->getLevelMaxRank();
   	$levelmanagement = LevelManagement::find($id);
  	return view('admin.levelmanagement.addedit',compact('levelmanagement','rank'));
  }

	public function postEdit(LevelManagementRequest $request,$id)
	{
		$sortOrder=isset($request->sortOrder)?($request->sortOrder):0;
	  
	  $common=new CommanController;
		$levelmanagement = LevelManagement::findOrFail($id);
		if ($request->levelName) {
			$levelmanagement->levelName = $request->levelName;
		}
		$levelmanagement->sortOrder=$sortOrder;
		$levelmanagement->isActive=$request->status;
		$levelmanagement->updatedDate=date('Y-m-d H:i:s');
		$oldimage = $levelmanagement->catImage;
		if(!empty($request->file('catImage'))){
			$imagePath = public_path('images/category/' . $oldimage);
	   	if ( !empty($oldimage) && file_exists($imagePath)) {
	       unlink($imagePath);
	    }
	    $imageGellary    = $request->file('catImage');
	    $imagesize = getimagesize($imageGellary);
	    $width = $imagesize[0];
	    $height = $imagesize[1];
	    /*set image size*/
	    $imagew = 300;  
	    $imageh = 300;  
	    $imageheight_width = array();
	    $setheight = 300;
	    $setwidth = 300;  
	    $size = $common->proposnallyimage($height,$width,$setheight,$setwidth);
	    $new_name = md5(uniqid(rand(), true)).'.'.$imageGellary->getClientOriginalExtension();

	    Image::make($imageGellary)->resize($size['NewWidth'],$size['Newheight'])->save(public_path('images/category'.'/'.$new_name));
	    $levelmanagement->catImage = $new_name;
	  }
		$levelmanagement->save();
		$update_level=$common->get_msg("update_level",1)?$common->get_msg("update_level",1):"Category has updated successfully.";
		session()->flash('success',$update_level);
		return redirect()->to('/admin/levelmanagement');
	}

	public function Delete($id) 
	{
  
    $common=new CommanController;

    $checkassign = DB::table('subject')->where([['categoryId', '=',$id]])->count();

    //echo "<pre>";print_r($checkassign);exit;

    $assign_level_to_lesson=$common->get_msg("assign_level_to_lesson",1)?$common->get_msg("assign_level_to_lesson",1):"Warning! Selected level is related to assign lesson therefore deleting operation can’t be performed. For deleting the record needs to remove assigned lesson related records from level.";

    $delete_level=$common->get_msg("delete_level",1)?$common->get_msg("delete_level",1):"This Level has been successfully deleted.";
   
     if ($checkassign > 0) {
         return Response(['status'=>'error','message'=> $assign_level_to_lesson]);

     } else {

        $cat = LevelManagement::findOrFail($id);
        $oldimage = $cat->catImage;
        $imagePath = public_path('images/category/' . $oldimage);
		   	if ( !empty($oldimage) && file_exists($imagePath)) {
		       unlink($imagePath);
		    }
        $cat->delete();
    		return Response(['status'=>'success','message'=>'Category has been deleted successfully']);
     }
	}

  public function Deleteall(Request $request) 
  {
    $error='';
	  $err=0;
	  $section='';
	  $section2='';
	  $categoryname1='';

    $common=new CommanController;
    $delete_level=$common->get_msg("delete_level",1)?$common->get_msg("delete_level",1):"This Level has been successfully deleted.";

	  foreach ($request->del as $val) 
	  {
	    $checklession = DB::table('tblassignlessionlevel')->where([['levelId', '=',$val]])->count();

			if ($checklession > 0) {
			 	$level = LevelManagement::find($val);
			 	$section .=$level->levelName.",";
			 	$err=1;
			} else {
			  $cat = LevelManagement::find($val);
			  $oldimage = $cat->catImage;
        $imagePath = public_path('images/category/' . $oldimage);
		   	if ( !empty($oldimage) && file_exists($imagePath)) {
		       unlink($imagePath);
		    }
        $section2 .=$cat->levelName.",";	
		  	$cat->delete();
			}
	  }
	  
	  if ($err==1 && $section!='') {
		  $section=substr($section,0,-1);
		  $section2=substr($section2,0,-1);
	    $msg='Warning! Selected level is related to assign lesson('.$section.') therefore deleting operation can’t be performed. For deleting the record needs remove assigned lesson related records from level. ';
	    if ($section2!='') {
		    $msg .="But ".$section2." level has been deleted successfully.";
		  }

	  } else {
	    $msg=$delete_level;
	  }
	  
	  if ($err==1) {
	  	session()->flash('error',$msg);
	  } else {
	  	session()->flash('success',$msg);
	  }
	  return redirect()->to('/admin/levelmanagement');
	 
  }

  public function Status($status,$id) 
  {
    $common=new CommanController;
        
        //$update_level_status=$common->get_msg("update_level_status",1)?$common->get_msg("update_level_status",1):"Level status has updated successfully.";
		$update_level_status="Category status has updated successfully.";
	  $levelmanagement = LevelManagement::find($id);
	  $levelmanagement->isActive=$status;
	  $levelmanagement->save();
	  session()->flash('success',$update_level_status);
	  return redirect()->to('/admin/levelmanagement');
  }

  public function assignLession($id) 
  {
 	 $common=new CommanController;
 	 $levelmanagement = LevelManagement::find($id);
 	 $lessionmanagement = LessionManagement::where('isActive',1)->orderby('sortOrder','asc')->get();
   return view('admin.levelmanagement.assignlession',compact('levelmanagement','lessionmanagement'));
 	}

  public function postAssignLession(Request $request,$id) 
  {
   	 	$common=new CommanController;
	   	$levelmanagement = LevelManagement::find($id);
	   	$del=isset($request->del)?($request->del):"";
			$sortOrder=isset($request->sortorder)?($request->sortorder):"";
			$lessionName=isset($request->lessionName)?($request->lessionName):"";
	    $levelname=isset($levelmanagement->levelName)?($levelmanagement->levelName):"";
     	$deleteAssign=DB::select( DB::raw("delete from tblassignlessionlevel where `levelId`='$id'"));
			$assign_lesson_level=$common->get_msg("assign_lesson_level",1)?$common->get_msg("assign_lesson_level",1):"Lesson successfully assigned to level.";
	  	if ($del) 
	  	{       
       	foreach($del as $lessionkey => $lesson) {
             
             $insert=DB::table('tblassignlessionlevel')->insert(
				['levelId'=>$id,'levelName'=>$levelname,'createdDate'=>date('Y-m-d H:i:s'),'lessionId'=>$lessionkey,'lessionName'=>$lessionName[$lessionkey],'lessionsortOrder'=>$sortOrder[$lessionkey]]);

       }
	  	}
			session()->flash('success',$assign_lesson_level);
	  	return redirect()->to('/admin/levelmanagement');
  }
}
