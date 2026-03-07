<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\CityRequest;
use Illuminate\Http\Request;
use Laraspace\City;
use Laraspace\State;
use Laraspace\Country;
use Image;
use Laraspace\Language;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class CityController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index(Request $request) {
	   $language = Language::where('status','Active')->get();
       $q=isset($request->q)?($request->q):"";
	   $cityList=DB::table('tblcities')
            ->leftJoin('tblstates', 'tblcities.state_id', '=', 'tblstates.id')
            ->leftJoin('tblcountries', 'tblstates.country_id', '=', 'tblcountries.id')
            ->select('tblcities.*', 'tblstates.name as state','tblcountries.name as country')
            ->orderby('tblcities.name','asc');

        if ($q!='') {
            $cityList->Where( 'tblcities.name', 'LIKE', '%' . $q . '%' )->orWhere( 'tblstates.name', 'LIKE', '%' . $q . '%' )->orWhere( 'tblcountries.name', 'LIKE', '%' . $q . '%' );
         }    
            $city=$cityList->paginate(15);
       return view('admin.city.index',compact('city','language','q'));
	}
	
	public function add() {
		 $common=new CommanController;
		 $country=Country::where('status','1')->get(['id','name']);
		 $language = Language::where('status','Active')->get();
	   return view('admin.city.addedit',compact('language','country'));
	}

	public function importcsv() {
		 $common=new CommanController;
		 $country=Country::where('status','1')->get(['id','name']);
	   return view('admin.city.import',compact('country'));
	}

	public function postImportCsv(Request $request) {

           $common=new CommanController;
           $country_id=isset($request->countryId)?($request->countryId):0;
           $state_id=isset($request->state_id)?($request->state_id):0;
           
           $country=Country::where('status','1')->get(['id','name']);

            $finallogs='';
            
            if($request->hasFile('import_csv')) {
              
              $files = $request->file('import_csv');
              $extension = strtolower($files->getClientOriginalExtension());
                
                if ($extension=='csv') {

                	$path = request()->file('import_csv')->getRealPath();
			              $file = file($path);
			               $the_big_array = array(); 
			               if (($h = fopen($path, "r")) !== FALSE) 
							{
							  while (($data = fgetcsv($h, 1000, ",")) !== FALSE) 
							  {
							    $the_big_array[] = $data;		
							  }

							  fclose($h);
							}
                            
                            $data = array_slice($the_big_array, 1);

			              //$data = array_slice($file, 1);
						  $i = 1;
						  $j=0;
			              $inserted='';
                          
			             foreach($data as  $line) {
				            
				            $countR=count($line);
			               if (($countR==1)) {
			             
				             $Name=strip_tags(trim($line[0]));
				             $error=0;
			         
					         try {

					            $customer_infoTo=  DB::select( DB::raw("select count(cities.id) as cn from tblcities as cities  
			                        where cities.name like '%".$Name."%' and cities.state_id=".$state_id.""));
			                    
			                     $countarea=isset($customer_infoTo[0]->cn)?($customer_infoTo[0]->cn):0;

					             $logs='';

					             if ($Name=='') {
					              $logs.='City name is blank.'."\n";
					               $error=1;
					             }

					                 
					             if($Name!='' &&  (!preg_match("/^[a-z0-9 .\-]+$/i",$Name))) {
					                   $logs.='City name ('.$Name.') is invalid.'."\n";
					                   $error=1;
					             }
                                  
                                  if ($Name!='' && strlen($Name) > 100) {
                                   $logs.='City name ('.$Name.') is too big.'."\n";
                                   $error=1;
                                   }
					            
					             if ($countarea > 0) {
					               $logs.="City (".$Name.") is already exist."."\n";
					               $error=1;
					             }


					              if($error==0) {
					              	$custId=DB::table('tblcities')->insertGetId(
					                  ['name'=>$Name,'createdDate'=>date("Y-m-d H:i:s"),'state_id'=>$state_id]);	
			                       } 

					                
					                 
					              
					          } catch (Exception $e) {
					              report($e);
					              return false;
					          }

			      
					          if ($error==0) {
					                   $finallogs .="<strong>Record No ".$i."</strong>: Inserted Successfully \n";
					                  $finallogs.= "-------------------------\n";
					              } else {
					                $finallogs.="<strong>Record No ".$i."</strong>: \n";
					                $finallogs.="<strong style='color:red;'>Error </strong>: ".$logs;
					                $finallogs.= "-------------------------"."\n";
					              }
					        } else {
					                $invalid="Invalid Data"."\n";
					               $finallogs.="<strong>Record No ".$i."</strong>: \n";
					                $finallogs.="<strong style='color:red;'>Error </strong>: ".$invalid;
					                $finallogs.= "-------------------------"."\n";
					        }      

			             $i++;
			           }

      
			            $finallogs .="\n";
			            $finallogs .='Total Inserted Record:'.$j;

                } else {

                	  flash()->error('Please upload valid csv file.');
			          return redirect()->to('/admin/city/importcsv');
                } 

			              

          } 

          return view('admin.city.import',compact('finallogs','country'));

	}
	
	 public function postCreate(CityRequest $request) {
		
		 $checkduplicate = DB::table('tblcities')->where([['name', '=',$request->name[1]]])->count();
		 if ($checkduplicate==0) {
			 $city = new City();
			 
			 if ($request->name) {
		      $city->name = $request->name[1];
		     }
		     
		     $city->state_id=$request->state_id;
			 $city->save();
			 
			 if ($request->name) {
			   foreach ($request->name as $key => $value) {
				 DB::table('tblcitytranslation')->insert(
               ['cityId'=>$city->id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 flash()->success('City has  added successfully.');
			 return redirect()->to('/admin/city');
		 } else {
		     flash()->error('This City name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/city/add');
		 }
       
       }
	   
	   public function Status($status,$id) {
		  $city = City::find($id);
		  $city->status=$status;
		  $city->save();
		  flash()->success('City status has updated successfully.');
		 return redirect()->to('/admin/city');
	   }
	   
	   public function Delete($id) {
		      $user = City::find($id);
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
		     $user = City::find($val);
              $user->delete();
		  }
		  $msg='Selected Cities has been deleted successfully.';
		  flash()->success($msg);
		  return redirect()->to('/admin/city');
	   }
	   
	   public function getEdit($id)
       {
		  $common=new CommanController;
		  $language = Language::where('status','Active')->get();
		  $country=Country::where('status','1')->get(['id','name']);  
		  $city=DB::table('tblcities')
            ->leftJoin('tblstates', 'tblcities.state_id', '=', 'tblstates.id')
            ->leftJoin('tblcountries', 'tblstates.country_id', '=', 'tblcountries.id')
            ->select('tblcities.*','tblcountries.id as country_id')
            ->where('tblcities.id',$id)
            ->first();
         return view('admin.city.addedit',compact('country','city','language'));
       }
	   
	   public function postEdit(CityRequest $request, $id) {
		     
		     $city = City::find($id);
		     if ($request->name) {
		      $city->name = $request->name[1];
		     }
		     $city->state_id=$request->state_id;
			 $city->save();
            if ($request->name) {
				 $delete=DB::delete('delete from tblcitytranslation where cityId = ?',[$id]);
			   foreach ($request->name as $key => $value) {
				 DB::table('tblcitytranslation')->insert(
               ['cityId'=>$id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

		 flash()->success('City has updated successfully.');
		 return redirect()->to('/admin/city');
	   }
}
