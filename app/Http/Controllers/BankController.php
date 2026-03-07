<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\BankRequest;
use Illuminate\Http\Request;
use Laraspace\Bank;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class BankController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
		
	   $bank = Bank::all();
	   //$language = Language::where('status','Active')->get();
       return view('admin.bank.index',compact('bank'));
	}
	
	public function add() {
	   $common=new CommanController;
       $language = Language::where('status','Active')->get();
	   $bankwidth=$common->getImageSizeValue('bank_icon_image_width');
	   $bankheight=$common->getImageSizeValue('bank_icon_image_height');	
	   return view('admin.bank.addedit',compact('bankwidth','bankheight','language'));
	}

	public function importcsv() {
		 $common=new CommanController;
	   return view('admin.bank.import');
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
			               if (($countR==1)) {
			             
				             $Name=strip_tags(trim($line[0]));
				             //echo $Name;
				             //exit;
				             //$Description=isset($line[1])?(strip_tags(trim($line[1]))):"";
				             $error=0;

			         
					         try {

					         	
					             
					            $customer_infoTo=  DB::select( DB::raw("select count(banks.id) as cn from tblbanks as banks  
			                        where banks.name like '%".$Name."%' and banks.isActive=1"));
			                    
			                     $countarea=isset($customer_infoTo[0]->cn)?($customer_infoTo[0]->cn):0;

					             $logs='';

					             if ($Name=='') {
					              $logs.='Bank name is blank.'."\n";
					               $error=1;
					             }

					                 
					             if($Name!='' &&  (!preg_match("/^[a-z0-9 .\-]+$/i",$Name))) {
					                   $logs.='Bank name ('.$Name.') is invalid.'."\n";
					                   $error=1;
					             }
                                  
                                  if ($Name!='' && strlen($Name) > 100) {
                                   $logs.='Bank name ('.$Name.') is too big.'."\n";
                                   $error=1;
                                   }
					            
					             if ($countarea > 0) {
					               $logs.="Bank (".$Name.") is already exist."."\n";
					               $error=1;
					             }


					              if($error==0) {
					              	$custId=DB::table('tblbanks')->insertGetId(
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
			          return redirect()->to('/admin/bank/importcsv');
                } 

			              

          } 

          return view('admin.bank.import',compact('finallogs'));

	}
	
	 public function postCreate(BankRequest $request) {
		 $checkduplicate = DB::table('tblbanks')->where([['name', '=',$request->name]])->count();
		 if ($checkduplicate==0) {
			 $bank = new Bank();
			 if ($request->name) {
			 $bank->name = $request->name;
			 }
			 $bank->isActive=$request->status;
			 $bank->createdDate=date('Y-m-d H:i:s');
			 $filename='';
			 $bank_image='';
			   if ($request->file('icon')) {
				 $file = $request->file('icon');
				 $size = getimagesize($file);
				 $ratio = $size[0]/$size[1];
				 $common=new CommanController;
				 $bankwidth=$common->getImageSizeValue('bank_icon_image_width');
				 $bankheight=$common->getImageSizeValue('bank_icon_image_height');
				 if( $ratio > 1) {
					$width = $bankwidth;
					$height = $bankheight/$ratio;
				 }
				 else {
					$width = $bankwidth;
					$height = $bankheight;
				 }
				 $extension = $file->getClientOriginalExtension();
				 $bank_image=time().$file->getClientOriginalName();
				 $bank_destinationPath = public_path('/bankicon/thumbnail_images');
				 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
				 $thumb_img->save($bank_destinationPath.'/'.$bank_image,80);
				 $filename=$file->getClientOriginalName();
				 $destinationPath = 'bankicon';
				 $file->move($destinationPath,$file->getClientOriginalName());
			 }
			 $bank->icon = $bank_image;
			 $bank->save();
			 

			 flash()->success('Bank has  added successfully.');
			 return redirect()->to('/admin/bank');
		 } else {
		     flash()->error('This Bank name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/bank/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
		  $bank = Bank::find($id);
		  $bank->isActive=$status;
		  $bank->save();
		  flash()->success('Bank status has updated successfully.');
		 return redirect()->to('/admin/bank');
	   }
	   
	   public function Delete($id) {
		      $user = Bank::find($id);
              $user->delete();
			  echo 2;
		   exit();
	   }
	   
	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  $categoryname1='';
		  
		  foreach ($request->del as $val) {
			  $category1 = DB::table('tblbanks')->where([['id', '=',$val]])->first();
			  $categoryname1=$category1->name;
              $section2 .=$categoryname1.",";	
			  $business = Bank::find($val);
              $business->delete();
		  }
		  
		  
		  $msg='Selected Bank has been deleted successfully.';

		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/bank');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
		 $common=new CommanController;
		 $bankwidth=$common->getImageSizeValue('bank_icon_image_width');
	     $bankheight=$common->getImageSizeValue('bank_icon_image_height');	  
         $bank = Bank::find($id);
         return view('admin.bank.addedit',compact('bank','bankwidth','bankheight'));
       }
	   
	   public function postEdit(BankRequest $request, $id) {
	      $bank = Bank::find($id);
	      
	      if ($request->name) {
		  $bank->name = $request->name;
		  }
		  
		  $bank->isActive=$request->status;
		  $bank->createdDate=date('Y-m-d H:i:s');
		  if ($request->file('icon')) {
			 $file = $request->file('icon');
			 $size = getimagesize($file);
			 $ratio = $size[0]/$size[1];
			 $common=new CommanController;
			  $bankwidth=$common->getImageSizeValue('bank_icon_image_width');
			 $bankheight=$common->getImageSizeValue('bank_icon_image_height');
			 if( $ratio > 1) {
				$width = $bankwidth;
				$height = $bankheight/$ratio;
			 }
			 else {
				$width = $bankwidth;
				$height = $bankheight;
			 }
			 
			 $extension = $file->getClientOriginalExtension();
			 $bank_image=time().$file->getClientOriginalName();
			 $bank_destinationPath = public_path('/bankicon/thumbnail_images');
			//$thumb_img = Image::make($file->getRealPath())->resize(150, 150);
			 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
			 $thumb_img->save($bank_destinationPath.'/'.$bank_image,80);
			 
			 
			 $filename=$file->getClientOriginalName();
			 $destinationPath = 'bankicon';
             $file->move($destinationPath,$file->getClientOriginalName());
			 $bank->icon = $bank_image;
		 }

		  $bank->save();
		  
		  flash()->success('Bank has updated successfully.');
		  return redirect()->to('/admin/bank');
	   }
}
