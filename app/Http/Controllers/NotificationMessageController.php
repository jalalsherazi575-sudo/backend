<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\NotificationMessageRequest;
use Illuminate\Http\Request;
use Laraspace\Language;
use Laraspace\NotificationMessage;
use Laraspace\NotificationMessageTranslation;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class NotificationMessageController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
		
	   $generalmessage = NotificationMessage::get();
	   $language = Language::where('status','Active')->get();
	   $notificationmessage=array();
	   if ($generalmessage->count() > 0) {
	      foreach ($generalmessage as $values) {
			  $Id=$values->id;
			  $title_key=$values->title_key;
			  $isActive=$values->isActive;
			  $translation = NotificationMessageTranslation::where('notification_message_id',$Id)->get();
		    $notificationmessage[]=array("id"=>$values->id,"title_key"=>$values->title_key,"isActive"=>$values->isActive,"translation"=>$translation);
		  }
		  //echo '<pre>'; print_r($messages);
		  //exit;
	   }
	   
       return view('admin.notificationmessage.index',compact('notificationmessage','language'));
	}
	
	public function add() {
		$language = Language::where('status','Active')->get();
	   return view('admin.notificationmessage.addedit')->with('language', $language);
	}
	
	 public function postCreate(NotificationMessageRequest $request) {
		 
		 $checkduplicate = DB::table('tblnotificationmessage')->where([['title_key', '=',$request->title_key]])->count();
		 
		 if ($checkduplicate==0) {
			 $notificationmessage = new NotificationMessage();
			 $notificationmessage->title_key = $request->title_key;
			 $notificationmessage->isActive=$request->isActive;
			 
			 //$bank->createdDate=date('Y-m-d H:i:s');
			 $notificationmessage->save();
			 if ($request->general_message) {
				 
			   foreach ($request->general_message as $key => $value) {
				 $generalmessagetranslation = new NotificationMessageTranslation();  
			     $generalmessagetranslation->notification_message_id=$notificationmessage->id;
				 $generalmessagetranslation->title_value=$value;
				 $generalmessagetranslation->lang_id=$key;
				 $generalmessagetranslation->save();
			   }
			 }
			 
			 flash()->success('Notification Message has  added successfully.');
			 return redirect()->to('/admin/notificationmessage');
		 } else {
		     flash()->error('This Notification Message title has been already taken. Please try with another name.');
			 return redirect()->to('/admin/notificationmessage/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      $status;
		  $notificationmessage = NotificationMessage::find($id);
		  $notificationmessage->isActive=$status;
		  $notificationmessage->save();
		  if ($request->general_message) {
				 $user = NotificationMessageTranslation::where('notification_message_id',$id);
                 $user->delete();
			   foreach ($request->general_message as $key => $value) {
				 $generalmessagetranslation = new NotificationMessageTranslation();  
			     $generalmessagetranslation->notification_message_id=$id;
				 $generalmessagetranslation->title_value=$value;
				 $generalmessagetranslation->lang_id=$key;
				 $generalmessagetranslation->save();
			   }
			 }
		  flash()->success('Notification Message status has updated successfully.');
		 return redirect()->to('/admin/notificationmessage');
		  
	   }
	   
	   
	   
	   
	   
	   public function getEdit($id)
       {
		   $notificationmessage = NotificationMessage::where('id',$id)->first();
		   $language = Language::where('status','Active')->get();
           $translation = NotificationMessageTranslation::where('notification_message_id',$id)->get();
         return view('admin.notificationmessage.addedit',compact('language','notificationmessage','translation'));
       }
	   
	   public function postEdit(NotificationMessageRequest $request, $id) {
	      $notificationmessage = NotificationMessage::find($id);
		  $notificationmessage->title_key = $request->title_key;
		  $notificationmessage->isActive=$request->isActive;
		  $notificationmessage->save();
		  if ($request->general_message) {
				 $user = NotificationMessageTranslation::where('notification_message_id',$id);
                 $user->delete();
			   foreach ($request->general_message as $key => $value) {
				 $generalmessagetranslation = new NotificationMessageTranslation();  
			     $generalmessagetranslation->notification_message_id=$id;
				 $generalmessagetranslation->title_value=$value;
				 $generalmessagetranslation->lang_id=$key;
				 $generalmessagetranslation->save();
			   }
			 }
		  flash()->success('Notification Message has updated successfully.');
		  return redirect()->to('/admin/notificationmessage');
	   }
}
