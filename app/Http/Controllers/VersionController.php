<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\VersionRequest;
use Illuminate\Http\Request;
use Laraspace\Version;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class VersionController extends Controller
{
    public function index() {
		
	   $version = Version::all();
       return view('admin.version.index')->with('version', $version);
	}
	
	public function add() {
	   return view('admin.version.addedit');
	}
	
	 public function postCreate(VersionRequest $request) {
		 
		 
			 $version = new Version();
			 $version->app_version = ($request->app_version)?($request->app_version):"1.0";
			 $version->app_type=($request->app_type)?($request->app_type):1;
			 $version->app_url=($request->url)?($request->url):"";
			// $version->culture_code=($request->culture_code)?($request->culture_code):"";
			 $version->is_update_available=($request->is_update_available)?($request->is_update_available):0;
			 $version->is_approved=($request->is_approved)?($request->is_approved):0;
			 $version->createdDate=date('Y-m-d H:i:s');
			 $version->save();
			 session()->flash('success','Version has been added successfully.');
			 return redirect()->to('/admin/version');
       }
	   
	   public function getEdit($id)
       {
         $version = Version::find($id);
         return view('admin.version.addedit',compact('version'));
       }
	   
	   public function postEdit(VersionRequest $request, $id) {
	      $version = Version::find($id);
		  $version->app_version = ($request->app_version)?($request->app_version):"1.0";
		  $version->app_url=($request->url)?($request->url):"";
		 // $version->culture_code=($request->culture_code)?($request->culture_code):"";
		  $version->is_update_available=($request->is_update_available)?($request->is_update_available):"0";
		  $version->is_approved=($request->is_approved)?($request->is_approved):"0";
		  $version->createdDate=date('Y-m-d H:i:s');
		  $version->save();
		  session()->flash('success','Version has been updated successfully.');
		  return redirect()->to('/admin/version');
	   }
}
