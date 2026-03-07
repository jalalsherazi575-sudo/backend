<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\HowDidYouKnowRequest;
use Illuminate\Http\Request;
use Laraspace\HowDidYouKnow;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;

class HowDidYouKnowController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
		
	   $howdidyouknow = HowDidYouKnow::all();
	   $language = Language::where('status','Active')->get();
       return view('admin.howdidyouknow.index',compact('howdidyouknow','language'));
	}
	
	public function add() {
	   $language = Language::where('status','Active')->get();	
	   return view('admin.howdidyouknow.addedit',compact('language'));
	}
	
	 public function postCreate(HowDidYouKnowRequest $request) {
		 
		 $checkduplicate = DB::table('tblhowdidyouknow')->where([['name', '=',$request->name[1]]])->count();
		 
		 if ($checkduplicate==0) {
			 $howdidyouknow = new HowDidYouKnow();
			 if ($request->name) {
			 $howdidyouknow->name = $request->name[1];
			 }
			 $howdidyouknow->isActive=$request->status;
			 $howdidyouknow->createdDate=date('Y-m-d H:i:s');
			 $howdidyouknow->save();
			 if ($request->name) {
				 
			   foreach ($request->name as $key => $value) {
				 DB::table('tblhowdidyouknowtranslation')->insert(
               ['hdykId'=>$howdidyouknow->id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }
			 flash()->success('HowDidYouKnow Type has  added successfully.');
			 return redirect()->to('/admin/howdidyouknow');
		 } else {
		     flash()->error('This HowDidYouKnow type has been already taken. Please try with another name.');
			 return redirect()->to('/admin/howdidyouknow/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      
		  $howdidyouknow = HowDidYouKnow::find($id);
		  $howdidyouknow->isActive=$status;
		  $howdidyouknow->save();
		  flash()->success('HowDidYouKnow Type status has updated successfully.');
		 return redirect()->to('/admin/howdidyouknow');
		  
	   }
	   
	   public function Delete($id) {
		   
		      $user = HowDidYouKnow::find($id);
              $user->delete();
			  echo 2;
		      exit();
	   }
	   
	   public function Deleteall(Request $request) {
	      foreach ($request->del as $val) {
			  $howdidyouknow = HowDidYouKnow::find($val);
              $howdidyouknow->delete();
			}
		  
		  $err=1;
		  $msg='Selected HowDidYouKnow Type has been deleted successfully.';
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/howdidyouknow');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
         $howdidyouknow = HowDidYouKnow::find($id);
         $language = Language::where('status','Active')->get();
         return view('admin.howdidyouknow.addedit',compact('howdidyouknow','language'));
       }
	   
	   public function postEdit(HowDidYouKnowRequest $request, $id) {
	      $howdidyouknow = HowDidYouKnow::find($id);
	      if ($request->name) {
		  $howdidyouknow->name = $request->name[1];
		  }
		  $howdidyouknow->isActive=$request->status;
		  $howdidyouknow->createdDate=date('Y-m-d H:i:s');
		  $howdidyouknow->save();
		  if ($request->name) {
				 $delete=DB::delete('delete from tblhowdidyouknowtranslation where hdykId = ?',[$id]);
			   foreach ($request->name as $key => $value) {
				 DB::table('tblhowdidyouknowtranslation')->insert(
               ['hdykId'=>$id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }
		  flash()->success('HowDidYouKnow Type has updated successfully.');
		  return redirect()->to('/admin/howdidyouknow');
	   }
}
