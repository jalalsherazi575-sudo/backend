<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\ConsumerManagerRequest;
use Illuminate\Http\Request;
use Laraspace\ConsumerManager;
use Image;
use Laraspace\Country;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use PDF;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class ConsumerManagerController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
		
	   $consumermanager = ConsumerManager::all();
       return view('admin.consumermanager.index',compact('consumermanager'));
	}
	
	public function add() {
		$country = Country::where([['status', '=','1']])->get();
	   return view('admin.consumermanager.addedit',compact('country'));
	}

     public function exportconsumermanagerreport(Request $request) {
      
            $common=new CommanController;
            $type=($request->type)?($request->type):"xlsx";
            $searchtxt=($request->searchtxt)?($request->searchtxt):"";
            $start=($request->start)?($request->start):0;
            $length=($request->length)?($request->length):0;
            $isActive=($request->isActive)?($request->isActive):"";
            

          $orderbycolm=($request->order[0]['column'])?($request->order[0]['column']):0;
          $orderbydir=($request->order[0]['dir'])?($request->order[0]['dir']):'asc';
          //exit();
           $order='';
           
           if ($length!=-1) {
            $order="LIMIT $start,$length";
           }

           $sort='order by consumermanager.id asc';

          /*if ($orderbycolm==1) {
              $sort="order by  concat(customer.fname, ' ', customer.lname) $orderbydir";
          }
          if ($orderbycolm==2) {
              $sort="order by  customer.email $orderbydir";
          }
          if ($orderbycolm==4) {
              $sort="order by   customer.phone $orderbydir";
          }
          if ($orderbycolm==5) {
              $sort="order by  customer.loginType $orderbydir";
          }
          if ($orderbycolm==6) {
              $sort="order by  customer.isActive $orderbydir";
          }*/

           $sWhere="";
          if ($searchtxt!='') {
           $sSearch =trim($searchtxt); 
           $sWhere .= "and ( `consumermanager`.`name` LIKE '%".trim($sSearch) ."%' OR `countries`.name LIKE '%".trim($sSearch) ."%' OR  `consumermanager`.`email` LIKE '%".trim($sSearch) ."%'";
      $sWhere .= " )";
        }



          $where="";
        
          
          
          

        


            $customer_info=  DB::select( DB::raw("Select consumermanager.*,consumermanager.countryId,countries.name as countryName  from tblconsumermanager as consumermanager  
 inner join tblcountries as countries on  consumermanager.countryId=countries.id
 where   consumermanager.isActive=1 ".$where." ".$sWhere."
          $sort"));

           return Excel::create('consumermanagerreport', function($excel) use ($customer_info,$common) {
            $excel->sheet('mySheet', function($sheet) use ($customer_info,$common)
            {
                $sheet->cell('A1', function($cell) {$cell->setValue('Name');   });
                $sheet->cell('B1', function($cell) {$cell->setValue('Email');   });
                $sheet->cell('C1', function($cell) {$cell->setValue('Country');   });
                $sheet->cell('D1', function($cell) {$cell->setValue('Status');   });
                

                if (!empty($customer_info)) {
                          $i=2;
                    foreach ($customer_info as  $cust) {
                          
                      $name=($cust->name)?($cust->name):"";
                      $email=($cust->email)?($cust->email):"";
                      $countryName=($cust->countryName)?($cust->countryName):"";
                      $countryId=($cust->countryId)?($cust->countryId):"";
                      $isActive=($cust->isActive)?($cust->isActive):0;
                      $id=($cust->id)?($cust->id):0;

                     
                      
                      if ($isActive==1) {
                        $statusName='Active';
                      } elseif ($isActive==0) {
                        $statusName='Inactive';
                      }   else {
                        $statusName='';
                      }

                      
                          


                        $sheet->cell('A'.$i, $name);
                        $sheet->cell('B'.$i, $email);
                        $sheet->cell('C'.$i, $countryName);
                        $sheet->cell('D'.$i, $statusName);
                        
                        
                        

                    $i++;
                    }
                }


              });   
            })->download($type); 
    }

	
	
	 public function postCreate(ConsumerManagerRequest $request) {
		 $countemail = DB::table('tblconsumermanager')->where('email', '=', $request->email)->count();
		 $countphone = DB::table('tblconsumermanager')->where('name', '=', $request->name)->count();

		 $countryId=isset($request->countryId)?(ltrim($request->countryId)):0;
		 $country = DB::table('tblconsumermanager')->where('countryId', '=', $request->countryId)->count();
         if ($country > 0) {
		 	flash()->error('you can not add consumer manager with in same country.');
			 return redirect()->to('/admin/consumermanager/add');
		 } elseif ($countemail==0 && $countphone==0) {
			 
			 $consumermanager = new ConsumerManager();
			 $consumermanager->name = isset($request->name)?(ltrim($request->name)):"";
			 $consumermanager->email = isset($request->email)?(ltrim($request->email)):"";
			 if (isset($request->password) && $request->password!='') {
			 $consumermanager->password =(ltrim(bcrypt($request->password)));
			 }
			 
			 $consumermanager->countryId = isset($request->countryId)?(ltrim($request->countryId)):0;
			 $consumermanager->createdDate=date('Y-m-d H:i:s');
			 $consumermanager->isActive=$request->isActive;
			 $consumermanager->save();
			 $consumermanagerId=$consumermanager->id;

			 
						 
                
			 
			
			 flash()->success('Consumer Manager has  added successfully.');
			 return redirect()->to('/admin/consumermanager');
		 } 
		 else {
		     flash()->error('This Consumer Manager name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/consumermanager/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      //$status;
		  $consumermanager = ConsumerManager::find($id);
		  $consumermanager->isActive=$status;
		  $consumermanager->save();
		  flash()->success('consumer Manager status has updated successfully.');
		 return redirect()->to('/admin/consumermanager');
		  
	   }
	   
	   public function Delete($id) {
		         //$delete1=DB::delete("delete from tblcustomeridproofdoc where id=$id");
		        // $delete2=DB::delete("delete from tblcustomerareaofinterest where customerId=$id");
		      $user = ConsumerManager::find($id);
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
		    //$checkcustomer = DB::table('tbllead')->where([['customerId', '=',$val]])->whereIn('status', [1, 2, 3])->count();
		    $checkcustomer=0;
			if ($checkcustomer > 0) {
			
			$customer = DB::table('tblcustomer')->where([['id', '=',$val]])->first();
			$customerName=$customer->fname." ".$customer->lname;
			$section .=$customerName.",";
			//$error='Some Category can not delete because vendors are using this category.';
			$err=1;
			} else {
			  $category1 = DB::table('tblconsumermanager')->where([['id', '=',$val]])->first();
			  $customerName=$category1->fname." ".$category1->lname;
              $section2 .=$customerName.",";			  
			  $customer = ConsumerManager::find($val);
              $customer->delete();
			  
			 
			  
			  //$error='Selected Categories has been deleted successfully.';
			}
		  }
		  
		  if ($err==1 && $section!='') {
			  $section=substr($section,0,-1);
			  $section2=substr($section2,0,-1);
		    $msg='Warning! Selected record is related to following Consumer Manager('.$section.') therefore deleting operation can’t be performed. For deleting the record needs to remove related records from listed customers. ';
		    if ($section2!='') {
			$msg .="But $section2 has been deleted successfully.";
			}
		  } else {
		    $msg='Selected Consumer Manager has been deleted successfully.';
		  }
		  
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/consumermanager');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
		
		 $country = Country::where([['status', '=','1']])->get(); 
         $common=new CommanController;
         //$customer=$common->CustomerDetails($id);
         $consumermanager=ConsumerManager::find($id);
         
         return view('admin.consumermanager.addedit',compact('consumermanager','country'));
       }
	   
	   

	   public function postEdit(ConsumerManagerRequest $request, $id) {
		     $consumermanager = ConsumerManager::find($id);
			 $consumermanager->name = isset($request->name)?(ltrim($request->name)):"";
			 $consumermanager->email = isset($request->email)?(ltrim($request->email)):"";
			 if (isset($request->password) && $request->password!='') {
			 $consumermanager->password =(ltrim(bcrypt($request->password)));
			 }
			 $consumermanager->countryId = isset($request->countryId)?(ltrim($request->countryId)):0;
		     $consumermanager->updatedDate=date('Y-m-d H:i:s');
		     $consumermanager->isActive=$request->isActive;
		     $consumermanager->save();
		  
		 flash()->success('Consumer Manager has updated successfully.');
		 return redirect()->to('/admin/consumermanager');
	   }
}
