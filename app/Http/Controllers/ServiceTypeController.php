<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\ServiceTypeRequest;
use Illuminate\Http\Request;
use Laraspace\ServiceType;
use Laraspace\BusinessCategory;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class ServiceTypeController extends Controller
{
	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
	   $language = Language::where('status','Active')->get();	
	   $servicetype = ServiceType::all();
       return view('admin.servicetype.index',compact('servicetype','language'));
	}
	
	public function add() {
		$common=new CommanController;
		$language = Language::where('status','Active')->get();
		$servicewidth=$common->getImageSizeValue('service_type_image_width');
		$serviceheight=$common->getImageSizeValue('service_type_image_height');
		$businesscategory = BusinessCategory::where([['isActive', '=',1]])->get();
	   return view('admin.servicetype.addedit',compact('businesscategory','servicewidth','serviceheight','language'));
	}
	
	 public function postCreate(ServiceTypeRequest $request) {
		 
		 $checkduplicate = DB::table('tblservicetype')->where([['name', '=',$request->name[1]]])->count();
		 if ($checkduplicate==0) {
			 $servicetype = new ServiceType();
			 if ($request->name) {
			 $servicetype->name = $request->name[1];
			}
			 $filename='';
			 $category_image='';
			   if ($request->file('photo')) {
				 $file = $request->file('photo');
				 $size = getimagesize($file);
				 $ratio = $size[0]/$size[1];
				 $common=new CommanController;
				 $categorywidth=$common->getImageSizeValue('service_type_image_width');
				 $categoryheight=$common->getImageSizeValue('service_type_image_height');
				 if( $ratio > 1) {
					$width = $categorywidth;
					$height = $categoryheight/$ratio;
				 }
				 else {
					$width = $categorywidth;
					$height = $categoryheight;
				 }
				 
				 $extension = $file->getClientOriginalExtension();
				 $category_image=time().$file->getClientOriginalName();
				 $category_destinationPath = public_path('/servicetypephoto/thumbnail_images');
				//$thumb_img = Image::make($file->getRealPath())->resize(150, 150);
				 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
				 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
				 
				 
				 $filename=$file->getClientOriginalName();
				 $destinationPath = 'servicetypephoto';
				 $file->move($destinationPath,$file->getClientOriginalName());
			 }
			 $servicetype->businessCategoryId=$request->businessCategoryId;
			 $servicetype->photo = $category_image;
			 $servicetype->isActive=$request->status;
			 $servicetype->createdDate=date('Y-m-d H:i:s');
			 $servicetype->noOfVenders=0;
			 $servicetype->save();
			 if ($request->name) {
				 
			   foreach ($request->name as $key => $value) {
				 DB::table('tblservicetypetranslation')->insert(
               ['serviceTypeId'=>$servicetype->id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 flash()->success('Service Type has  added successfully.');
			 return redirect()->to('/admin/servicetype');
		 } else {
		     flash()->error('This Service Type name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/servicetype/add');
		 }
		 
       }
	   
	   public function Status($status,$id) {
	      $status;
		  $servicetype = ServiceType::find($id);
		  $servicetype->isActive=$status;
		  $servicetype->save();
		  flash()->success('Service Type status has updated successfully.');
		 return redirect()->to('/admin/servicetype');
		  
	   }
	   
	   public function Delete($id) {
		   $checkvendor = DB::table('tblvenderservicetype')->where([['serviceTypeId', '=',$id]])->count();
	       if ($checkvendor > 0) {
		     echo 1;
		   } else {
		      $user = ServiceType::find($id);
              $user->delete();
			  echo 2;
		   }
		   exit();
	   }
	   
	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  foreach ($request->del as $val) {
		    $checkvendor = DB::table('tblvenderservicetype')->where([['serviceTypeId', '=',$val]])->count();
		    if ($checkvendor > 0) {
			$vendor = DB::table('tblvenderservicetype')->where([['serviceTypeId', '=',$val]])->first();
			
			$error='Some ServiceType can not delete because vendors are using this ServiceType.';
			$err=1;
			} else {
			  $servicetype = ServiceType::find($val);
              $servicetype->delete();
			  $error='Selected Service Type has been deleted successfully.';
			}
		  }
		  if ($err==1) {
		  flash()->success($error);
		  } else {
		  flash()->success($error);
		  }
		  return redirect()->to('/admin/servicetype');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
		 $common=new CommanController;
		 $language = Language::where('status','Active')->get();
		 $servicewidth=$common->getImageSizeValue('service_type_image_width');
		 $serviceheight=$common->getImageSizeValue('service_type_image_height');  
         $servicetype = ServiceType::find($id);
		 $businesscategory = BusinessCategory::all();
         return view('admin.servicetype.addedit',compact('servicetype','businesscategory','servicewidth','serviceheight','language'));
       }
	   
	   public function postEdit(ServiceTypeRequest $request, $id) {
	      $servicetype = ServiceType::find($id);
	      if ($request->name) {
		  $servicetype->name = $request->name[1];
		   }
		   if ($request->file('photo')) {
			 $file = $request->file('photo');
			 $size = getimagesize($file);
			 $ratio = $size[0]/$size[1];
			 $common=new CommanController;
			 $categorywidth=$common->getImageSizeValue('service_type_image_width');
			 $categoryheight=$common->getImageSizeValue('service_type_image_height');
			 if( $ratio > 1) {
				$width = $categorywidth;
				$height = $categoryheight/$ratio;
			 }
			 else {
				$width = $categorywidth;
				$height = $categoryheight;
			 }
			 
			 $extension = $file->getClientOriginalExtension();
			 $category_image=time().$file->getClientOriginalName();
			 $category_destinationPath = public_path('/servicetypephoto/thumbnail_images');
			//$thumb_img = Image::make($file->getRealPath())->resize(150, 150);
			 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
			 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
			 
			 
			 $filename=$file->getClientOriginalName();
			 $destinationPath = 'servicetypephoto';
             $file->move($destinationPath,$file->getClientOriginalName());
			 $servicetype->photo = $category_image;
		 }
		 $servicetype->businessCategoryId=$request->businessCategoryId;
		 $servicetype->isActive=$request->status;
		 $servicetype->createdDate=date('Y-m-d H:i:s');
		 $servicetype->noOfVenders=0;
		 $servicetype->save();
		 if ($request->name) {
				 $delete=DB::delete('delete from tblservicetypetranslation where serviceTypeId = ?',[$id]);
			   foreach ($request->name as $key => $value) {
				 DB::table('tblservicetypetranslation')->insert(
               ['serviceTypeId'=>$id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }
		 flash()->success('Service Type has updated successfully.');
		 return redirect()->to('/admin/servicetype');
	   }
}
