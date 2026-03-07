<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\LanguageRequest;
use Illuminate\Http\Request;
use Laraspace\Language;

use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class LanguageController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
		
	   $languages = Language::all();
       return view('admin.languages.index')->with('languages', $languages);
	}
	
	public function add() {
	   $sortorder = DB::table('language')->orderBy('sort_order', 'desc')->take(1)->first();
	   $order=1;
	   if ($sortorder) {
	   	$lastorder=$sortorder->sort_order;

		   	if ($lastorder!='' && $lastorder!=0) {
		   	$order=$lastorder+1;
		    } else {
		   	 $order=1;
		    }

	   }
	   return view('admin.languages.addedit',compact('order'));
	}
	
	 public function postCreate(LanguageRequest $request) {
		 
		 $title=($request->title)?($request->title):"";
		 $lancode=($request->lancode)?($request->lancode):"";
		 $sort_order=($request->sort_order)?($request->sort_order):"";
		 $landir=($request->landir)?($request->landir):"";
		 $is_default=($request->is_default)?($request->is_default):"No";
		 $status=($request->status)?($request->status):"Active";

		 $checkduplicate = DB::table('language')->where([['title', '=',$title]])->count();
		 

		 if ($checkduplicate==0) {
			 $languages = new Language();
			 $languages->title =$title;
			 $languages->status=$status;
			 $languages->is_default=$is_default;
			 $languages->sort_order=$sort_order;
			 $languages->lancode=$lancode;
			 $languages->landir=$landir;
             $languages->createdDate=date('Y-m-d H:i:s');
			 $languages->save();
			 flash()->success('New Language has  added successfully.');
			 return redirect()->to('/admin/languages');
		 } else {
		     flash()->error('This Language name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/languages/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      
		  $languages = Language::find($id);
		  $languages->isActive=$status;
		  $languages->save();
		  flash()->success('Language  status has updated successfully.');
		 return redirect()->to('/admin/languages');
		  
	   }
	   
	   public function Delete($id) {
		   
		      $user = Language::find($id);
              $user->delete();
			  echo 2;
		      exit();
	   }
	   
	   public function Deleteall(Request $request) {
	      foreach ($request->del as $val) {
			  $languages = Language::find($val);
              $languages->delete();
			}
		  
		  $err=1;
		  $msg='Selected Language Type has been deleted successfully.';
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/languages');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
         $languages = Language::find($id);
         return view('admin.languages.addedit',compact('languages'));
       }
	   
	   public function postEdit(LanguageRequest $request, $id) {
	      $languages = Language::find($id);
          $title=($request->title)?($request->title):"";
		  $lancode=($request->lancode)?($request->lancode):"";
		  $sort_order=($request->sort_order)?($request->sort_order):"";
		  $landir=($request->landir)?($request->landir):"";
		  $is_default=($request->is_default)?($request->is_default):"No";
		  $status=($request->status)?($request->status):"Active";

		  $languages->title =$title;
		  $languages->status=$status;
		  $languages->is_default=$is_default;
		  $languages->sort_order=$sort_order;
		  $languages->lancode=$lancode;
		  $languages->landir=$landir;
          $languages->createdDate=date('Y-m-d H:i:s');
		  $languages->save();
		  flash()->success('Language Type has updated successfully.');
		  return redirect()->to('/admin/languages');
	   }
}
