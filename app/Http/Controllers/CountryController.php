<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\CountryRequest;
use Illuminate\Http\Request;
use Laraspace\Country;
use Image;
use Laraspace\Language;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    public function index() {
	   $language = Language::where('status','Active')->get();
	   $country = Country::all();
       return view('admin.country.index',compact('country','language'));
	}
	
	public function add() {
		 $common=new CommanController;
		 $language = Language::where('status','Active')->get();
		 
	   return view('admin.country.addedit',compact('language'));
	}
	
	 public function postCreate(CountryRequest $request) {
		
		 $checkduplicate = DB::table('tblcountries')->where([['name', '=',$request->name[1]]])->count();
		 if ($checkduplicate==0) {
			 $country = new Country();
			 if ($request->name) {
		      $country->name = $request->name[1];
		     }
		     $country->currency=$request->currency;

		     $country->iso2=isset($request->iso2)?($request->iso2):"";
		     $country->symbol=isset($request->symbol)?($request->symbol):"";

			 $country->status=$request->status;
			 //$country->createdDate=date('Y-m-d H:i:s');
			 $country->save();
			 
			 if ($request->name) {
			   foreach ($request->name as $key => $value) {
				 DB::table('tblcountrytranslation')->insert(
               ['countryId'=>$country->id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 flash()->success('Country has  added successfully.');
			 return redirect()->to('/admin/country');
		 } else {
		     flash()->error('This Country name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/country/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      //$status;
		  $country = Country::find($id);
		  $country->status=$status;
		  $country->save();
		  flash()->success('Country status has updated successfully.');
		 return redirect()->to('/admin/country');
		  
	   }
	   
	   public function Delete($id) {
		   
		      $user = Country::find($id);
              $user->delete();
			  echo 2;
		      exit();
	   }
	   
	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  foreach ($request->del as $val) {
		     $user = Country::find($val);
              $user->delete();
		  }
		  $msg='Selected Countries has been deleted successfully.';
		  
		  flash()->success($msg);
		  return redirect()->to('/admin/country');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
		  $common=new CommanController;
		  $language = Language::where('status','Active')->get();  
		  $country = Country::find($id);
         return view('admin.country.addedit',compact('country','language'));
       }
	   
	   public function postEdit(CountryRequest $request, $id) {
		  $country = Country::find($id);
		  if ($request->name) {
		  $country->name = $request->name[1];
		   }
		 $country->status=$request->status;
		 $country->iso2=isset($request->iso2)?($request->iso2):"";
		 $country->symbol=isset($request->symbol)?($request->symbol):"";

		// $country->createdDate=date('Y-m-d H:i:s');
		 $country->currency=$request->currency;
		 $country->save();
         if ($request->name) {
				 $delete=DB::delete('delete from tblcountrytranslation where countryId = ?',[$id]);
			   foreach ($request->name as $key => $value) {
				 DB::table('tblcountrytranslation')->insert(
               ['countryId'=>$id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }
		 flash()->success('Country has updated successfully.');
		 return redirect()->to('/admin/country');
	   }
}
