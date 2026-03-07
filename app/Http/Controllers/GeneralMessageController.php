<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\GeneralMessageRequest;
use Illuminate\Http\Request;
use Laraspace\Language;
use Laraspace\GeneralMessage;
use Laraspace\GeneralMessageTranslation;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class GeneralMessageController extends Controller
{
    public function index() {
		
	   $generalmessage = GeneralMessage::get();
	   $language = Language::where('status','Active')->get();
	   $messages=array();
	   if ($generalmessage->count() > 0) {
	      foreach ($generalmessage as $values) {
			  $Id=$values->id;
			  $title_key=$values->title_key;
			  $is_app_msg=$values->is_app_msg;
			  $translation = GeneralMessageTranslation::where('general_message_id',$Id)->get();
		    $messages[]=array("id"=>$values->id,"title_key"=>$values->title_key,"is_app_msg"=>$values->is_app_msg,"translation"=>$translation);
		  }
		  //echo '<pre>'; print_r($messages);
		  //exit;
	   }
	   
       return view('admin.generalmessage.index',compact('messages','language'));
	}
	
	public function add() {
		$language = Language::where('status','Active')->get();
	   return view('admin.generalmessage.addedit')->with('language', $language);
	}
	
	 public function postCreate(GeneralMessageRequest $request) {
		 
		 $checkduplicate = DB::table('tblgeneralmessage')->where([['title_key', '=',$request->title_key]])->count();
		 
		 if ($checkduplicate==0) {
			 $generalmessage = new GeneralMessage();
			 $generalmessage->title_key = $request->title_key;
			 $generalmessage->is_app_msg=$request->is_app_msg;
			 
			 //$bank->createdDate=date('Y-m-d H:i:s');
			 $generalmessage->save();
			 if ($request->general_message) {
				 
			   foreach ($request->general_message as $key => $value) {
				 $generalmessagetranslation = new GeneralMessageTranslation();  
			     $generalmessagetranslation->general_message_id=$generalmessage->id;
				 $generalmessagetranslation->title_value=$value;
				 $generalmessagetranslation->lang_id=$key;
				 $generalmessagetranslation->save();
			   }
			 }
			 session()->flash('success','General Message has  added successfully.');
			 return redirect()->to('/admin/generalmessage');
		 } else {
		 	session()->flash('error','This General Message title has been already taken. Please try with another name.');
		     return redirect()->to('/admin/generalmessage/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      $status;
		  $generalmessage = GeneralMessage::find($id);
		  $generalmessage->isActive=$status;
		  $generalmessage->save();
		  if ($request->general_message) {
				 $user = GeneralMessageTranslation::where('general_message_id',$id);
                 $user->delete();
			   foreach ($request->general_message as $key => $value) {
				 $generalmessagetranslation = new GeneralMessageTranslation();  
			     $generalmessagetranslation->general_message_id=$id;
				 $generalmessagetranslation->title_value=$value;
				 $generalmessagetranslation->lang_id=$key;
				 $generalmessagetranslation->save();
			   }
			 }
			 session()->flash('success','General Message has been updated successfully.');
		  
		 return redirect()->to('/admin/bank');
		  
	   }
	   
	   
	   
	   
	   
	   public function getEdit($id)
       {
		   $message = GeneralMessage::where('id',$id)->first();
		   $language = Language::where('status','Active')->get();
           $translation = GeneralMessageTranslation::where('general_message_id',$id)->get();
         return view('admin.generalmessage.addedit',compact('language','message','translation'));
       }
	   
	   public function postEdit(GeneralMessageRequest $request, $id) {
	      $generalmessage = GeneralMessage::find($id);
		  $generalmessage->title_key = $request->title_key;
		  $generalmessage->is_app_msg=$request->is_app_msg;
		  $generalmessage->save();
		  if ($request->general_message) {
				 $user = GeneralMessageTranslation::where('general_message_id',$id);
                 $user->delete();
			   foreach ($request->general_message as $key => $value) {
				 $generalmessagetranslation = new GeneralMessageTranslation();  
			     $generalmessagetranslation->general_message_id=$id;
				 $generalmessagetranslation->title_value=$value;
				 $generalmessagetranslation->lang_id=$key;
				 $generalmessagetranslation->save();
			   }
			 }
		  session()->flash('success','General Message has been updated successfully.');
		  return redirect()->to('/admin/generalmessage');
	   }
}
