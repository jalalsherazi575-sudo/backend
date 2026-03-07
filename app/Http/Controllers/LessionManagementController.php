<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\LessionManagementRequest;
use Illuminate\Http\Request;
use Laraspace\LessionManagement;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class LessionManagementController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
	   
	   $lessionmanagement = LessionManagement::all();
       return view('admin.lessionmanagement.index',compact('lessionmanagement'));
	}
	
	public function add() {
	   $common=new CommanController;
	   $rank=$common->getLessionMaxRank();	
	   return view('admin.lessionmanagement.addedit',compact('rank'));
	}

	
	 public function postCreate(LessionManagementRequest $request) {
		 
		 $common=new CommanController;

		 $checkduplicate = DB::table('tbllessionmanagement')->where([['lessionName', '=',$request->lessionName]])->count();
		 
		 $lessionDescription=isset($request->lessionDescription)?($request->lessionDescription):'';
		 
		 $sortOrder=isset($request->sortOrder)?($request->sortOrder):0;

		 $add_lesson=$common->get_msg("add_lesson",1)?$common->get_msg("add_lesson",1):"Lesson has added successfully.";

		 $already_same_lesson_name=$common->get_msg("already_same_lesson_name",1)?$common->get_msg("already_same_lesson_name",1):"This Lesson name has been already taken. Please try with another name.";
		

		 if ($checkduplicate==0) {

			 $lessionmanagement = new LessionManagement();
			 
			 if ($request->lessionName) {
			 $lessionmanagement->lessionName = $request->lessionName;
			 }

			 $lessionmanagement->isActive=$request->status;
			 $lessionmanagement->lessionDescription=$lessionDescription;
			 $lessionmanagement->sortOrder=$sortOrder;
			 $lessionmanagement->createdDate=date('Y-m-d H:i:s');
			 $lessionmanagement->save();

			 

			 flash()->success($add_lesson);
			 return redirect()->to('/admin/lessionmanagement');

		 } else {

		     flash()->error($already_same_lesson_name);
			 return redirect()->to('/admin/lessionmanagement/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      
	      $common=new CommanController;

	      $update_lesson_status=$common->get_msg("update_lesson_status",1)?$common->get_msg("update_lesson_status",1):"Lesson status has updated successfully.";

		  $lessionmanagement = LessionManagement::find($id);
		  $lessionmanagement->isActive=$status;
		  $lessionmanagement->save();
		  flash()->success($update_lesson_status);
		 return redirect()->to('/admin/lessionmanagement');
		  
	   }
	   
	   public function Delete($id) {
		   
		   $common=new CommanController;

		   $checkassign = DB::table('tblassignlessionlevel')->where([['lessionId', '=',$id]])->count();

		   $assignlessontolevel=$common->get_msg("assign_lesson_to_level",1)?$common->get_msg("assign_lesson_to_level",1):"Warning! Selected lesson is related to assign level therefore deleting operation can’t be performed. For deleting the record needs to remove related records from assigned level.";

		   $delete_lesson=$common->get_msg("delete_lesson",1)?$common->get_msg("delete_lesson",1):"This Lesson has been successfully deleted.";
           
           if ($checkassign > 0) {
               echo $assignlessontolevel;
           } else {
           	   $user = LessionManagement::find($id);
               $user->delete();
			   echo $delete_lesson;
           } 

		   exit();

	   }
	   
	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  $categoryname1='';
          $common=new CommanController;

          $delete_lesson=$common->get_msg("delete_lesson",1)?$common->get_msg("delete_lesson",1):"This Lesson has been successfully deleted.";

		  foreach ($request->del as $val) {

		    $checklession = DB::table('tblassignlessionlevel')->where([['lessionId', '=',$val]])->count();
		    
		    if ($checklession > 0) {

			 $lessons = LessionManagement::find($val);
			
			 $section .=$lessons->lessionName.",";
			 $err=1;
			
			} else {

			  $lesson = LessionManagement::find($val);
              $section2 .=$lesson->lessionName.",";	
			  //$business = IdProofType::find($val);
              $lesson->delete();
			  //$error='Selected Id Proof Type has been deleted successfully.';
			}

		  }
		  
		  if ($err==1 && $section!='') {

			  $section=substr($section,0,-1);
			  $section2=substr($section2,0,-1);
		      
		      $msg='Warning! Selected record is related to following lesson('.$section.') therefore deleting operation can’t be performed. For deleting the record needs to remove related records from listed lesson. ';
		    
		      if ($section2!='') {
			    $msg .="But ".$section2." lesson has been deleted successfully.";
			  }

		  } else {
		    $msg=$delete_lesson;
		  }
		  
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }

		  return redirect()->to('/admin/lessionmanagement');
		 
	   }
	   
	   public function getEdit($id) {
	   	
       	 $common=new CommanController;
       	 $rank=$common->getLessionMaxRank();
         $lessionmanagement = LessionManagement::find($id);
         return view('admin.lessionmanagement.addedit',compact('lessionmanagement','rank'));
       }
	   
	   public function postEdit(LessionManagementRequest $request,$id) {
            
            $common=new CommanController;

            $lessionDescription=isset($request->lessionDescription)?($request->lessionDescription):'';
		    $sortOrder=isset($request->sortOrder)?($request->sortOrder):0;
		    $lessionName=isset($request->lessionName)?($request->lessionName):'';

	   	   $checkduplicate = DB::table('tbllessionmanagement')->where([['lessionName', '=',$request->lessionName],['lessionId', '!=',$id]])->count();

	   	   $update_lesson=$common->get_msg("update_lesson",1)?$common->get_msg("update_lesson",1):"Lesson Management has updated successfully.";

		   $already_same_lesson_name=$common->get_msg("already_same_lesson_name",1)?$common->get_msg("already_same_lesson_name",1):"This Lesson name has been already taken. Please try with another name.";
	   	  
          if ($checkduplicate==0) {

		      $lessionmanagement = LessionManagement::find($id);
			  
			  if ($request->lessionName) {
			  $lessionmanagement->lessionName = $lessionName;
			  }

			  $lessionmanagement->lessionDescription=$lessionDescription;
			  $lessionmanagement->sortOrder=$sortOrder;
			  $lessionmanagement->isActive=$request->status;
			  $lessionmanagement->updatedDate=date('Y-m-d H:i:s');

			  
			  $lessionmanagement->save();

			  $updateQuestionLesson=DB::update("update tblquestion SET lessionName='".$lessionName."' where lessionId=".$id." ");
	 
			  flash()->success($update_lesson);
			  return redirect()->to('/admin/lessionmanagement');
			
			} else {

				 flash()->error($already_same_lesson_name);
			     return redirect()->to('/admin/lessionmanagement/edit/'.$id);
			
			} 
	   }
}
