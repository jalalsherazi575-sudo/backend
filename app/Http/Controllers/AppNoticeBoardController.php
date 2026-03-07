<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\AppNoticeBoardRequest;
use Illuminate\Http\Request;
use Laraspace\AppNoticeBoard;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class AppNoticeBoardController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
	   
	   $appnoticeboard = AppNoticeBoard::all();
       return view('admin.appnoticeboard.index',compact('appnoticeboard'));
	}
	
	public function add() {
	   $common=new CommanController;
	   $rank=$common->getNoticeMaxRank();	
	   return view('admin.appnoticeboard.addedit',compact('rank'));
	}

	
	 public function postCreate(AppNoticeBoardRequest $request) {
		 
		 $common=new CommanController;
         $description=isset($request->description)?($request->description):0;
		 $sortOrder=isset($request->sortOrder)?($request->sortOrder):0;
         $add_notice=$common->get_msg("add_notice",1)?$common->get_msg("add_notice",1):"Notice has added successfully.";

		 $appnoticeboard = new AppNoticeBoard();
		 $appnoticeboard->isActive=$request->status;
		 $appnoticeboard->description=$description;
		 $appnoticeboard->sortOrder=$sortOrder;
		 $appnoticeboard->createdDate=date('Y-m-d H:i:s');

		 if($request->hasFile('photo')) {

			 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
			 $file = $request->file('photo');	 
			 $extension = strtolower($file->getClientOriginalExtension());
			 $filename=time().".".$extension;
			 $check=in_array($extension,$allowedfileExtension);
			 if($check) {
			   $destinationPath = 'appnoticeboard';
			   $file->move($destinationPath,$filename);
			   $appnoticeboard->photo=$filename;						   
			 } else {
			 	$isNotValidimage=1;
			 }

		 }

		 $appnoticeboard->save();

		 flash()->success($add_notice);
		 return redirect()->to('/admin/appnoticeboard');

	  }
	   
	   public function Status($status,$id) {
	      
	      $common=new CommanController;
          $update_notice_status=$common->get_msg("update_notice_status",1)?$common->get_msg("update_notice_status",1):"Notice status has updated successfully.";

		  $appnoticeboard = AppNoticeBoard::find($id);
		  $appnoticeboard->isActive=$status;
		  $appnoticeboard->save();
		  flash()->success($update_notice_status);
		 return redirect()->to('/admin/appnoticeboard');
		  
	   }
	   
	   public function Delete($id) {
		 $common=new CommanController;
         $delete_notice=$common->get_msg("delete_notice",1)?$common->get_msg("delete_notice",1):"This Notice has been successfully deleted.";
         $appnoticeboard = AppNoticeBoard::find($id);
         $appnoticeboard->delete();
		 echo $delete_notice;
         exit();
       }
	   
	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  $categoryname1='';
          $common=new CommanController;

          $delete_notice=$common->get_msg("delete_notice_all",1)?$common->get_msg("delete_notice_all",1):"Selected Notice has been deleted successfully.";

		  foreach ($request->del as $val) {
			  $appnoticeboard = AppNoticeBoard::find($val);
              $appnoticeboard->delete();
		  }
		  
		  $msg=$delete_notice;
		  flash()->success($msg);
		  return redirect()->to('/admin/appnoticeboard');
		 
	   }
	   
	   public function getEdit($id) {
	   	
       	 $common=new CommanController;
       	 $rank=$common->getNoticeMaxRank();
         $appnoticeboard = AppNoticeBoard::find($id);
         return view('admin.appnoticeboard.addedit',compact('appnoticeboard','rank'));

       }
	   
	   public function postEdit(AppNoticeBoardRequest $request,$id) {

            $common=new CommanController;
            $description=isset($request->description)?($request->description):0;
		    $sortOrder=isset($request->sortOrder)?($request->sortOrder):0;
            $isNotValidimage=0;
	   	    $update_notice=$common->get_msg("update_notice",1)?$common->get_msg("update_notice",1):"Notice has updated successfully.";
            $appnoticeboard = AppNoticeBoard::find($id);
			$appnoticeboard->description=$description;
			$appnoticeboard->sortOrder=$sortOrder;
			$appnoticeboard->isActive=$request->status;
			$appnoticeboard->updatedDate=date('Y-m-d H:i:s');

			if($request->hasFile('photo')) {

					 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
					 $file = $request->file('photo');	 
					 $extension = strtolower($file->getClientOriginalExtension());
					 $filename=time().".".$extension;
					 $check=in_array($extension,$allowedfileExtension);
					 if($check) {
					   $destinationPath = 'appnoticeboard';
					   $file->move($destinationPath,$filename);
					   $appnoticeboard->photo=$filename;						   
					 } else {
					 	$isNotValidimage=1;
					 }

			 }

			 if ($isNotValidimage==1) {
	               flash()->error('Please upload valid  File.');
			       return redirect()->to('/admin/appnoticeboard/add');
			 }	 

             $appnoticeboard->save();
	         flash()->success($update_notice);
			 return redirect()->to('/admin/appnoticeboard');

	}

	public function updatePhoto($id) {
	   	$update=DB::update("update tblappnoticeboard SET photo='' where id=$id");
	   	echo 1;
	   	exit();
	}

}
