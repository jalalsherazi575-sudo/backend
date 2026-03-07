<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\StateRequest;
use Illuminate\Http\Request;
use Laraspace\Country;
use Laraspace\State;
use Image;
use Laraspace\Language;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class StateController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    public function index() {
	   $language = Language::where('status','Active')->get();
	   $state = State::orderby('name','asc')->get();
       return view('admin.state.index',compact('state','language'));
	}
	
	public function add() {
		 $common=new CommanController;
		 $country=Country::where('status','1')->get(['id','name']);
		 //echo '<pre>';print_r($country);
		 //exit;
		 $language = Language::where('status','Active')->get();
		 
	   return view('admin.state.addedit',compact('language','country'));
	}

	public function importcsv() {
		 $common=new CommanController;
		 $country=Country::where('status','1')->get(['id','name']);
	   return view('admin.state.import',compact('country'));
	}

	public function postImportCsv(Request $request) {

           $common=new CommanController;
           $country_id=isset($request->countryId)?($request->countryId):0;
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

					            $customer_infoTo=  DB::select( DB::raw("select count(states.id) as cn from tblstates as states  
			                        where states.name like '%".$Name."%' and states.country_id=".$country_id.""));
			                    
			                     $countarea=isset($customer_infoTo[0]->cn)?($customer_infoTo[0]->cn):0;

					             $logs='';

					             if ($Name=='') {
					              $logs.='State name is blank.'."\n";
					               $error=1;
					             }

					                 
					             if($Name!='' &&  (!preg_match("/^[a-z0-9 .\-]+$/i",$Name))) {
					                   $logs.='State name ('.$Name.') is invalid.'."\n";
					                   $error=1;
					             }
                                  
                                  if ($Name!='' && strlen($Name) > 100) {
                                   $logs.='State name ('.$Name.') is too big.'."\n";
                                   $error=1;
                                   }
					            
					             if ($countarea > 0) {
					               $logs.="State (".$Name.") is already exist."."\n";
					               $error=1;
					             }


					              if($error==0) {
					              	$custId=DB::table('tblstates')->insertGetId(
					                  ['name'=>$Name,'createdDate'=>date("Y-m-d H:i:s"),'country_id'=>$country_id]);	
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
			          return redirect()->to('/admin/state/importcsv');
                } 

			              

          } 

          return view('admin.state.import',compact('finallogs','country'));

	}
	
	 public function postCreate(StateRequest $request) {
		
		 $checkduplicate = DB::table('tblstates')->where([['name', '=',$request->name[1]]])->count();
		 if ($checkduplicate==0) {
			 $state = new State();
			 if ($request->name) {
		      $state->name = $request->name[1];
		     }
		     $state->country_id=$request->country_id;
			 //$country->status=$request->status;
			 //$country->createdDate=date('Y-m-d H:i:s');
			 $state->save();
			 
			 if ($request->name) {
			   foreach ($request->name as $key => $value) {
				 DB::table('tblstatetranslation')->insert(
               ['stateId'=>$state->id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 flash()->success('State has  added successfully.');
			 return redirect()->to('/admin/state');
		 } else {
		     flash()->error('This State name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/state/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      //$status;
		  $state = State::find($id);
		  $state->status=$status;
		  $state->save();
		  flash()->success('State status has updated successfully.');
		 return redirect()->to('/admin/state');
		  
	   }
	   
	   public function Delete($id) {
		   
		      $user = State::find($id);
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
		     $user = State::find($val);
              $user->delete();
		  }
		  $msg='Selected States has been deleted successfully.';
		  
		  flash()->success($msg);
		  return redirect()->to('/admin/state');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
		  $common=new CommanController;
		  $language = Language::where('status','Active')->get();
		  $country=Country::where('status','1')->get(['id','name']);  
		  $state = State::find($id);
         return view('admin.state.addedit',compact('state','country','language'));
       }
	   
	   public function postEdit(StateRequest $request, $id) {
		  $state = State::find($id);
		  if ($request->name) {
		  $state->name = $request->name[1];
		   }
		   $state->country_id=$request->country_id;
		 //$country->status=$request->status;
		// $country->createdDate=date('Y-m-d H:i:s');
		 //$country->currency=$request->currency;
		 $state->save();
         if ($request->name) {
				 $delete=DB::delete('delete from tblstatetranslation where stateId = ?',[$id]);
			   foreach ($request->name as $key => $value) {
				 DB::table('tblstatetranslation')->insert(
               ['stateId'=>$id,'name'=>$value,'langId'=>$key,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }
		 flash()->success('State has updated successfully.');
		 return redirect()->to('/admin/state');
	   }
}
