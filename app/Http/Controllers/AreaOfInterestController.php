<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\AreaOfInterestRequest;
use Illuminate\Http\Request;
use Laraspace\AreaOfInterest;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class AreaOfInterestController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
	   //$language = Language::where('status','Active')->get();	
	   $areaofinterest = AreaOfInterest::all();
       return view('admin.areaofinterest.index',compact('areaofinterest'));
	}
	
	public function add() {
		
	   return view('admin.areaofinterest.addedit');
	}

	public function importcsv() {
		 $common=new CommanController;
	   return view('admin.areaofinterest.import');
	}

	public function postImportCsv(Request $request) {

           $common=new CommanController;

            $finallogs='';
            
            if($request->hasFile('import_csv')) {
              
              $files = $request->file('import_csv');
              $extension = strtolower($files->getClientOriginalExtension());
                
                if ($extension=='csv') {

                	$path = request()->file('import_csv')->getRealPath();
			              $file = file($path);
			              $data = array_slice($file, 1);
						  $i = 1;
						  $j=0;
			              $inserted='';
			             foreach($data as  $line) {
				          
				            $logs='';
				            $alldata=explode(",",$line);
				            $countR=count($alldata);

			               if ($countR==1) {
			             
				             $Name=strip_tags(trim($alldata[0]));
				             $error=0;
			         
					         try {

					         	
					             
					            $customer_infoTo=  DB::select( DB::raw("select count(areaofinterest.id) as cn from tblareaofinterest as areaofinterest  
			                        where areaofinterest.name like '%".$Name."%' and areaofinterest.isActive=1"));
			                    
			                     $countarea=isset($customer_infoTo[0]->cn)?($customer_infoTo[0]->cn):0;

					             

					             if ($Name=='') {
					              $logs.='Area of interest name is blank.'."\n";
					               $error=1;
					             }

					                 
					             if($Name!='' &&  (!preg_match("/^[a-z0-9 .\-]+$/i",$Name))) {
					                   $logs.='Area of interest name ('.$Name.') is invalid.'."\n";
					                   $error=1;
					             }
                                  
                                  if ($Name!='' && strlen($Name) > 100) {
                                   $logs.='Area of interest name ('.$Name.') is too big.'."\n";
                                   $error=1;
                                   }
					            
					             if ($countarea > 0) {
					               $logs.="Area of interest (".$Name.") is already exist."."\n";
					               $error=1;
					             }


					              if($error==0) {
					              	$custId=DB::table('tblareaofinterest')->insertGetId(
					                  ['name'=>$Name,'createdDate'=>date("Y-m-d H:i:s"),'isActive'=>1]);	
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
			          return redirect()->to('/admin/areaofinterest/importcsv');
                } 

			              

          } 

          return view('admin.areaofinterest.import',compact('finallogs'));

	}
	
	 public function postCreate(AreaOfInterestRequest $request) {
		 
		 $checkduplicate = DB::table('tblareaofinterest')->where([['name', '=',$request->name]])->count();
		 
		 if ($checkduplicate==0) {
			 $areaofinterest = new AreaOfInterest();
			 if ($request->name) {
			 $areaofinterest->name = $request->name;
			 }
			 $areaofinterest->isActive=$request->status;
			 $areaofinterest->createdDate=date('Y-m-d H:i:s');
			 $areaofinterest->save();

			 

			 flash()->success('Area of Interest has  added successfully.');
			 return redirect()->to('/admin/areaofinterest');
		 } else {
		     flash()->error('This Area of Interest name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/areaofinterest/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      
		  $areaofinterest = AreaOfInterest::find($id);
		  $areaofinterest->isActive=$status;
		  $areaofinterest->save();
		  flash()->success('Area of Interest status has updated successfully.');
		 return redirect()->to('/admin/areaofinterest');
		  
	   }
	   
	   public function Delete($id) {
		   
		      $user = AreaOfInterest::find($id);
              $user->delete();
			  echo 2;
		   //}
		   exit();
	   }
	   
	   /*public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  $categoryname1='';
		  foreach ($request->del as $val) {
		    $checkvendor = DB::table('tblvender')->where([['proofTypeId', '=',$val]])->count();
		    if ($checkvendor > 0) {
			$vendor = DB::table('tblvender')->where([['proofTypeId', '=',$val]])->first();
			$vendorName=$vendor->fname." ".$vendor->lname;
			$section .=$vendorName.",";
			//$error='Some IdProofType can not delete because vendors are using this category.';
			$err=1;
			} else {
			  $category1 = DB::table('tblidprooftype')->where([['id', '=',$val]])->first();
			  $categoryname1=$category1->name;
              $section2 .=$categoryname1.",";	
			  $business = IdProofType::find($val);
              $business->delete();
			  //$error='Selected Id Proof Type has been deleted successfully.';
			}
		  }
		  
		  if ($err==1 && $section!='') {
			  $section=substr($section,0,-1);
			  $section2=substr($section2,0,-1);
		    $msg='Warning! Selected record is related to following vendor('.$section.') therefore deleting operation can’t be performed. For deleting the record needs to remove related records from listed vendor. ';
		    if ($section2!='') {
			$msg .="But $categoryname1 has been deleted successfully.";
			}
		  } else {
		    $msg='Selected Id Proof Type has been deleted successfully.';
		  }
		  
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/areaofinterest');
		  //exit;
	   }*/
	   
	   public function getEdit($id)
       {
       	 
         $areaofinterest = AreaOfInterest::find($id);
         return view('admin.areaofinterest.addedit',compact('areaofinterest'));
       }
	   
	   public function postEdit(AreaOfInterestRequest $request,$id) {

	   	  $checkduplicate = DB::table('tblareaofinterest')->where([['name', '=',$request->name],['id', '!=',$id]])->count();
          if ($checkduplicate==0) {

		      $areaofinterest = AreaOfInterest::find($id);
			  if ($request->name) {
			  $areaofinterest->name = $request->name;
			  }
			  $areaofinterest->isActive=$request->status;
			  $areaofinterest->updatedDate=date('Y-m-d H:i:s');
			  $areaofinterest->save();
	 
			  flash()->success('Area of Interest has updated successfully.');
			  return redirect()->to('/admin/areaofinterest');
			} else {
				 flash()->error('This Area of Interest name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/areaofinterest/edit/'.$id);
			} 
	   }
}
