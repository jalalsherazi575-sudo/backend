<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\BannerRequest;
use Illuminate\Http\Request;
use Laraspace\Banner;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class BannerController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
  	public function index() {
		
	   $banner = Banner::all();
	   return view('admin.banner.index',compact('banner'));
	}
	
	public function add() {
	  return view('admin.banner.addedit');
	}

	public function postCreate(BannerRequest $request) {
		$common=new CommanController;
		$checkduplicate = DB::table('bannermaster')->where([['bannerTitle', '=',$request->bannerTitle]])->count();
		if ($checkduplicate==0) {
			$banner = new Banner();
			if ($request->bannerTitle) {
			 	$banner->bannerTitle = $request->bannerTitle;
			}
			$banner->bannerUrl=$request->bannerUrl;
			$banner->startDate=$request->startDate;
			$banner->endDate=$request->endDate;
			$banner->created_at=date('Y-m-d H:i:s');
			$banner->updated_at=date('Y-m-d H:i:s');
			 
		   	if(!empty($request->file('bannerImage'))){
	            $imageGellary    = $request->file('bannerImage');
	            $imagesize = getimagesize($imageGellary);
	            $width = $imagesize[0];
	            $height = $imagesize[1];
	            /*set image size*/
	            $imagew = 500;  
	            $imageh = 500;  
	            $imageheight_width = array();
	            $setheight = 500;
	            $setwidth = 500;  
	            $size = $common->proposnallyimage($height,$width,$setheight,$setwidth);
	            $new_name = md5(uniqid(rand(), true)).'.'.$imageGellary->getClientOriginalExtension();

	            Image::make($imageGellary)->resize($size['NewWidth'],$size['Newheight'])->save(public_path('images/banner'.'/'.$new_name));
	            $banner->bannerImage = $new_name;
	        }
			 
			$banner->save();
			 
			session()->flash('success','Banner has added successfully.');
			return redirect()->to('/admin/banner');
		} else {
		     flash()->error('This Bank name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/banner/add');
		}
		 //echo $request->name;
		 //exit;
    }
	   
	   
	public function getEdit($id){
		$common=new CommanController;
		$banner = Banner::find($id);
        return view('admin.banner.addedit',compact('banner'));
    }
	   
   	public function postEdit(BannerRequest $request, $id)
   	{
   		$common=new CommanController;
	    $banner = Banner::find($id);
	    $banner->bannerTitle = $request->bannerTitle;
	    $banner->bannerUrl=$request->bannerUrl;
		$banner->startDate=$request->startDate;
		$banner->endDate=$request->endDate;
		$banner->updated_at=date('Y-m-d H:i:s');
		$oldimage = $banner->bannerImage;
		if(!empty($request->file('bannerImage'))){
			$imagePath = public_path('images/banner/' . $oldimage);
           	if ( !empty($oldimage) && file_exists($imagePath)) {
               unlink($imagePath);
            }
            $imageGellary    = $request->file('bannerImage');
            $imagesize = getimagesize($imageGellary);
            $width = $imagesize[0];
            $height = $imagesize[1];
            /*set image size*/
            $imagew = 500;  
            $imageh = 500;  
            $imageheight_width = array();
            $setheight = 500;
            $setwidth = 500;  
            $size = $common->proposnallyimage($height,$width,$setheight,$setwidth);
            $new_name = md5(uniqid(rand(), true)).'.'.$imageGellary->getClientOriginalExtension();

            Image::make($imageGellary)->resize($size['NewWidth'],$size['Newheight'])->save(public_path('images/banner'.'/'.$new_name));
            $banner->bannerImage = $new_name;
        }
		 
		$banner->save();
		session()->flash('success','Banner has updated successfully.');
		return redirect()->to('/admin/banner');
   	}

   	public function Delete($id)
   	 {
   	 	$common=new CommanController;
	    $banner = Banner::find($id);
        if (!empty($banner->bannerImage)) {
            $imagePath = public_path('images/banner/' . $banner->bannerImage);
            if (file_exists($imagePath)) {
                unlink($imagePath); // Delete the image file from the server
            }
        }
        $banner->delete();
        return Response(['status'=>'success','message'=>'Banner deleted successfully']);
    }
   
}
