<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\ProductCategoryRequest;
use Illuminate\Http\Request;
use Laraspace\ProductCategory;
use Image;
use Laraspace\Language;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    public function index() {
	   $language = Language::where('status','Active')->get();
	   $productcategory = ProductCategory::all();
       return view('admin.productcategory.index',compact('productcategory','language'));
	}
	
	public function add() {
		 $common=new CommanController;
		 $language = Language::where('status','Active')->get();
		 
	   return view('admin.productcategory.addedit',compact('language'));
	}
	
	 public function postCreate(ProductCategoryRequest $request) {
		
		 $checkduplicate = DB::table('tblproductcategory')->where([['name', '=',$request->name[1]]])->count();
		 if ($checkduplicate==0) {
			 $productcategory = new ProductCategory();
			 if ($request->name) {
		      $productcategory->name = $request->name[1];
		     }
		     
			 $productcategory->isActive=$request->status;
			 $productcategory->createdDate=date('Y-m-d H:i:s');
			 $productcategory->save();
			 
			 if ($request->name) {
			   foreach ($request->name as $key => $value) {
				 DB::table('tblproductcategorytranslation')->insert(
               ['productCategoryId'=>$productcategory->id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 flash()->success('Product Category has  added successfully.');
			 return redirect()->to('/admin/productcategory');
		 } else {
		     flash()->error('This Product Category name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/productcategory/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      //$status;
		  $productcategory = ProductCategory::find($id);
		  $productcategory->isActive=$status;
		  $productcategory->save();
		  flash()->success('Product Category status has updated successfully.');
		 return redirect()->to('/admin/productcategory');
		  
	   }
	   
	   public function Delete($id) {
		   $checkvendor = DB::table('tblcustomerproduct')->where([['categoryId', '=',$id]])->count();
	       if ($checkvendor > 0) {
		     echo 1;
		   } else {
		      $user = ProductCategory::find($id);
              $user->delete();
			  echo 2;
		   }
		   exit();
	   }
	   
	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  foreach ($request->del as $val) {
		    $checkvendor = DB::table('tblcustomerproduct')->where([['categoryId', '=',$val]])->count();
			if ($checkvendor > 0) {
			$vendor = DB::table('tblcustomerproduct')->where([['categoryId', '=',$val]])->first();
			$vendorName=$vendor->name;
			$category = DB::table('tblproductcategory')->where([['id', '=',$val]])->first();
			$categoryname=$category->name;
			$section .=$vendorName.",";
			//$error='Some Category can not delete because vendors are using this category.';
			$err=1;
			} else {
			  $category1 = DB::table('tblproductcategory')->where([['id', '=',$val]])->first();
			  $categoryname1=$category1->name;
              $section2 .=$categoryname1.",";			  
			  $business = ProductCategory::find($val);
              $business->delete();
			}
		  }
		  if ($err==1 && $section!='') {
			  $section=substr($section,0,-1);
			  $section2=substr($section2,0,-1);
		    $msg='Warning! Selected record is related to following product('.$section.') therefore deleting operation can’t be performed. For deleting the record needs to remove related records from listed product. ';
		    if ($section2!='') {
			$msg .="But $categoryname1 has been deleted successfully.";
			}
		  } else {
		    $msg='Selected Categories has been deleted successfully.';
		  }
		  
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/productcategory');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
		  $common=new CommanController;
		  $language = Language::where('status','Active')->get();  
		  $productcategory = ProductCategory::find($id);
         return view('admin.productcategory.addedit',compact('productcategory','language'));
       }
	   
	   public function postEdit(ProductCategoryRequest $request, $id) {
		  $productcategory = ProductCategory::find($id);
		  if ($request->name) {
		  $productcategory->name = $request->name[1];
		   }
		 $productcategory->isActive=$request->status;
		 $productcategory->createdDate=date('Y-m-d H:i:s');
		 $productcategory->save();
         if ($request->name) {
				 $delete=DB::delete('delete from tblproductcategorytranslation where productCategoryId = ?',[$id]);
			   foreach ($request->name as $key => $value) {
				 DB::table('tblproductcategorytranslation')->insert(
               ['productCategoryId'=>$id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }
		 flash()->success('Product Category has updated successfully.');
		 return redirect()->to('/admin/productcategory');
	   }
}
