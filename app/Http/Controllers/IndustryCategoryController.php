<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\IndustryCategoryRequest;
use Illuminate\Http\Request;
use Laraspace\IndustryCategory;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class IndustryCategoryController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
	   //$language = Language::where('status','Active')->get();	
	   $industrycategory = IndustryCategory::all();
       return view('admin.industrycategory.index',compact('industrycategory'));
	}

	public function importcsv() {
		 $common=new CommanController;
	   return view('admin.industrycategory.import');
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
			               $the_big_array = array(); 
			               if (($h = fopen($path, "r")) !== FALSE) 
							{
							  // Each line in the file is converted into an individual array that we call $data
							  // The items of the array are comma separated
							  while (($data = fgetcsv($h, 1000, ",")) !== FALSE) 
							  {
							    // Each individual array is being pushed into the nested array
							    $the_big_array[] = $data;		
							  }

							  // Close the file
							  fclose($h);
							}
                            
                            $data = array_slice($the_big_array, 1);
							//print_r($data);
							//exit();


			              //$data = array_slice($file, 1);
						  $i = 1;
						  $j=0;
			              $inserted='';
                          
			             foreach($data as  $line) {
				             //print_r($line);
				             //exit;
				            //$logs='';
				            //$alldata=explode(",",$line);
				            
				            $countR=count($line);
                              //echo $countR;
                              //exit();
			               if (($countR==2) || ($countR==1)) {
			             
				             $Name=strip_tags(trim($line[0]));
				             $Description=isset($line[1])?(strip_tags(trim($line[1]))):"";
				             $error=0;

			         
					         try {

					         	
					             
					            $customer_infoTo=  DB::select( DB::raw("select count(industrycategory.id) as cn from tblindustrycategory as industrycategory  
			                        where industrycategory.name like '%".$Name."%' and industrycategory.isActive=1"));
			                    
			                     $countarea=isset($customer_infoTo[0]->cn)?($customer_infoTo[0]->cn):0;

					             $logs='';

					             if ($Name=='') {
					              $logs.='Industry Category name is blank.'."\n";
					               $error=1;
					             }

					                 
					             if($Name!='' &&  (!preg_match("/^[a-z0-9 .\-]+$/i",$Name))) {
					                   $logs.='Industry Category name ('.$Name.') is invalid.'."\n";
					                   $error=1;
					             }
                                  
                                  if ($Name!='' && strlen($Name) > 100) {
                                   $logs.='Industry Category name ('.$Name.') is too big.'."\n";
                                   $error=1;
                                   }
					            
					             if ($countarea > 0) {
					               $logs.="Industry Category (".$Name.") is already exist."."\n";
					               $error=1;
					             }


					              if($error==0) {
					              	$custId=DB::table('tblindustrycategory')->insertGetId(
					                  ['name'=>$Name,'createdDate'=>date("Y-m-d H:i:s"),'description'=>$Description,'isActive'=>1]);	
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
			          return redirect()->to('/admin/industrycategory/importcsv');
                } 

			              

          } 

          return view('admin.industrycategory.import',compact('finallogs'));

	}
	
	public function add() {
		
	   return view('admin.industrycategory.addedit');
	}
	
	 public function postCreate(IndustryCategoryRequest $request) {
		 
		 $checkduplicate = DB::table('tblindustrycategory')->where([['name', '=',$request->name]])->count();
		 
		 if ($checkduplicate==0) {
			 $industrycategory = new IndustryCategory();
			 if ($request->name) {
			 $industrycategory->name = $request->name;
			 }
			 $industrycategory->description = $request->description;
			 $industrycategory->isActive=$request->status;
			 $industrycategory->createdDate=date('Y-m-d H:i:s');
			 $industrycategory->save();

			 

			 flash()->success('Industry Category has  added successfully.');
			 return redirect()->to('/admin/industrycategory');
		 } else {
		     flash()->error('This Industry Category name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/industrycategory/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      
		  $industrycategory = IndustryCategory::find($id);
		  $industrycategory->isActive=$status;
		  $industrycategory->save();
		  flash()->success('Industry Category status has updated successfully.');
		 return redirect()->to('/admin/industrycategory');
		  
	   }
	   
	   public function Delete($id) {
		   
		      $user = IndustryCategory::find($id);
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
       	 
         $industrycategory = IndustryCategory::find($id);
         return view('admin.industrycategory.addedit',compact('industrycategory'));
       }
	   
	   public function postEdit(IndustryCategoryRequest $request,$id) {

	   	  $checkduplicate = DB::table('tblindustrycategory')->where([['name', '=',$request->name],['id', '!=',$id]])->count();
          if ($checkduplicate==0) {

		      $industrycategory = IndustryCategory::find($id);
			  if ($request->name) {
			  $industrycategory->name = $request->name;
			  }
			  $industrycategory->description = $request->description;
			  $industrycategory->isActive=$request->status;
			  $industrycategory->updatedDate=date('Y-m-d H:i:s');
			  $industrycategory->save();
	 
			  flash()->success('Industry Category has updated successfully.');
			  return redirect()->to('/admin/industrycategory');
			} else {
				 flash()->error('This Industry Category name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/industrycategory/edit/'.$id);
			} 
	   }
}
