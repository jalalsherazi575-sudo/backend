<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\SettingRequest;
use Illuminate\Http\Request;
use Laraspace\Setting;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index() {
		
	   $setting = Setting::get();
       return view('admin.setting.index',compact('setting'));
	}
	
	public function add() {
		
	   return view('admin.setting.addedit');
	}
	
	 public function postCreate(SettingRequest $request) {
		 
		 $checkduplicate = DB::table('settings')->where([['option', '=',$request->option]])->count();
		 
		 if ($checkduplicate==0) {
			 $setting = new Setting();
			 $setting->option = $request->option;
			 $setting->value=$request->value;
			 $setting->created_at=date('Y-m-d H:i:s');
			 $setting->save();
			 
			 
			 flash()->success('Setting Option  has  added successfully.');
			 return redirect()->to('/admin/setting');
		 } else {
		     flash()->error('This Setting Option has been already taken.Please try with another name.');
			 return redirect()->to('/admin/setting/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   
	   public function getEdit($id)
       {
           $setting = Setting::find($id);
           
		  return view('admin.setting.addedit',compact('setting'));
       }
	   
	   public function postEdit(SettingRequest $request, $id) {
	      $setting = Setting::find($id);
		  $setting->option = $request->option;
		  $setting->value=$request->value;
		  $setting->updated_at=date('Y-m-d H:i:s');
		  $setting->save();
		  
		  flash()->success('Setting has updated successfully.');
		  return redirect()->to('/admin/setting');
	   }
}
