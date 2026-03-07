<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\PayoutRequest;
use Illuminate\Http\Request;
use Laraspace\Payout;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use PDF;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class PayoutController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
		
	   $payout = Payout::all();
       return view('admin.payout.index',compact('payout'));
	}

  public function exportpayoutreport(Request $request) {
      
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

           $sort=' order by usercredithistory.id desc';

          if ($orderbycolm==1) {
              $sort="order by  customer.fname $orderbydir";
          }
          if ($orderbycolm==2) {
              $sort="order by  customer.lname $orderbydir";
          }
          if ($orderbycolm==3) {
              $sort="order by  customer.email $orderbydir";
          }
          if ($orderbycolm==4) {
              $sort="order by   customer.cashReward $orderbydir";
          }
          if ($orderbycolm==5) {
              $sort="order by  usercredithistory.cashReward $orderbydir";
          }
          if ($orderbycolm==7) {
              $sort="order by  usercredithistory.createdDate $orderbydir";
          }



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
           $sWhere .= "and ( `customer`.`fname` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR `customer`.lname LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customer`.`email` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customer`.`cashReward` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `usercredithistory`.`cashReward` LIKE '%".trim($_REQUEST['searchtxt']) ."%'";
      $sWhere .= " )";
        }


          if ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and usercredithistory.createdDate >='".$fromDate."' and usercredithistory.createdDate <='".$toDate."' ";

           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] == "")) {
                 
                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];            
                 
                 $sWhere .= " and usercredithistory.createdDate >='".$fromDate."'";
           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] == "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and usercredithistory.createdDate <='".$toDate."' ";

           } else {
            
           }



          $where="";
        
          
          
          if ($isActive!='') {
              if ($isActive==2) {
                $isActive=0;
              }
              $where.=" and usercredithistory.status='".$isActive."'";
            }

        


            $customer_info=  DB::select( DB::raw("Select usercredithistory.*,customer.fname,customer.lname,customer.cashReward as custReward,customer.email from tblusercredithistory as usercredithistory  
 inner join tblcustomer as customer on  usercredithistory.customerId=customer.id
 where  usercredithistory.isPayout=1 and customer.isActive=1 ".$where." ".$sWhere."
          $sort"));

           return Excel::create('payoutreport', function($excel) use ($customer_info,$common) {
            $excel->sheet('mySheet', function($sheet) use ($customer_info,$common)
            {
                $sheet->cell('A1', function($cell) {$cell->setValue('First Name');   });
                $sheet->cell('B1', function($cell) {$cell->setValue('Last Name');   });
                $sheet->cell('C1', function($cell) {$cell->setValue('Email');   });
                $sheet->cell('D1', function($cell) {$cell->setValue('Balance Amount (TT$)');   });
                $sheet->cell('E1', function($cell) {$cell->setValue('Amount Requested (TT$)');   });
                $sheet->cell('F1', function($cell) {$cell->setValue('Payment To');   });
                $sheet->cell('G1', function($cell) {$cell->setValue('Request Date');   });
                $sheet->cell('H1', function($cell) {$cell->setValue('Payout Status');   });
                

                if (!empty($customer_info)) {
                          $i=2;
                    foreach ($customer_info as  $cust) {
                          
                      $fname=($cust->fname)?($cust->fname):"";
                      $lname=($cust->lname)?($cust->lname):"";
                      $email=($cust->email)?($cust->email):"";
                      $cashReward=($cust->cashReward)?($cust->cashReward):"0.00";
                      $custReward=($cust->custReward)?($cust->custReward):"";
                      $paymentType=($cust->paymentType)?($cust->paymentType):"";
                      $status=($cust->status)?($cust->status):0;
                      $id=($cust->id)?($cust->id):0;

                      $createdDate=($cust->createdDate)?(date("d M Y",strtotime($cust->createdDate))):"";
                      
                      if ($status==1) {
                        $statusName='Pending';
                      } elseif ($status==2) {
                        $statusName='Completed';
                      } elseif ($status==3) {
                        //$statusName='Cancelled';
                        $statusName='Cancel';

                      }  else {
                        $statusName='';
                      }

                      if ($paymentType==1) {
                         $paymentTypeName='Wipay';
                      } else if ($paymentType==2) {
                         $paymentTypeName='Bank';
                      } else {
                        $paymentTypeName='';
                      }

                          


                        $sheet->cell('A'.$i, $fname);
                        $sheet->cell('B'.$i, $lname);
                        $sheet->cell('C'.$i, $email);
                        $sheet->cell('D'.$i, $custReward);
                        $sheet->cell('E'.$i, $cashReward);
                        $sheet->cell('F'.$i, $paymentTypeName);
                        $sheet->cell('G'.$i, $createdDate);
                        $sheet->cell('H'.$i, $statusName);
                        
                        

                    $i++;
                    }
                }


              });   
            })->download($type); 
    }

	public function payoutdata(Request $request) {

           //exit;
         
	   	   $sWhere="";

	   	  // echo print_r($request->all());
	   	   //exit;

           if (isset($_REQUEST['searchtxt']) && $_REQUEST['searchtxt'] != "") {
           $sSearch =trim($_REQUEST['searchtxt']); 
      $sWhere .= "and ( `customer`.`fname` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR `customer`.lname LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customer`.`email` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customer`.`cashReward` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `usercredithistory`.`cashReward` LIKE '%".trim($_REQUEST['searchtxt']) ."%'";
      $sWhere .= " )";
           }

           if ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and usercredithistory.createdDate >='".$fromDate."' and usercredithistory.createdDate <='".$toDate."' ";

           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] == "")) {
                 
                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];            
                 
                 $sWhere .= " and usercredithistory.createdDate >='".$fromDate."'";
           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] == "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and usercredithistory.createdDate <='".$toDate."' ";

           } else {

           }

           //echo $_REQUEST['searchtxt'];
           //exit;

           /*if ($request->missionstatus) {
            $missionstatus=($request->missionstatus)?($request->missionstatus):0;
            $sWhere .=" and customermission.status=".$missionstatus."";
           }*/

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

          $sort=' order by usercredithistory.id desc';

          if ($orderbycolm==1) {
              $sort="order by  customer.fname $orderbydir";
          }
          if ($orderbycolm==2) {
              $sort="order by  customer.lname $orderbydir";
          }
          if ($orderbycolm==3) {
              $sort="order by  customer.email $orderbydir";
          }
          if ($orderbycolm==4) {
              $sort="order by   customer.cashReward $orderbydir";
          }
          if ($orderbycolm==5) {
              $sort="order by  usercredithistory.cashReward $orderbydir";
          }
          if ($orderbycolm==7) {
              $sort="order by  usercredithistory.createdDate $orderbydir";
          }
           
          $draw=1; 
          if($_REQUEST['draw']) {
          	$draw=$_REQUEST['draw'];
          }

           if ($isActive!='') {
              $sWhere.=" and usercredithistory.status='".$isActive."'";
            }

          $missionenrollcn=DB::select( DB::raw("
Select usercredithistory.*,customer.fname,customer.lname,customer.cashReward as custReward,customer.email from tblusercredithistory as usercredithistory  
 inner join tblcustomer as customer on  usercredithistory.customerId=customer.id
 where  usercredithistory.isPayout=1 and customer.isActive=1 ".$sWhere." $sort"));

          $missionenroll=DB::select( DB::raw("
Select usercredithistory.*,customer.fname,customer.lname,customer.cashReward as custReward,customer.email from tblusercredithistory as usercredithistory  
 inner join tblcustomer as customer on  usercredithistory.customerId=customer.id
 where  usercredithistory.isPayout=1 and customer.isActive=1 ".$sWhere." $sort $order"));
           
              $counTotal=count($missionenrollcn);
          
	          $output = array(
	          "recordsTotal" => $counTotal,
	          "recordsFiltered" => $counTotal,
	          "draw" => $draw,
	          "data" => array()
	          );

            if ($missionenroll) {
                   $i=1;
                  foreach ($missionenroll as  $cust) {

                      $row = array();
                      $fname=($cust->fname)?($cust->fname):"";
                      $lname=($cust->lname)?($cust->lname):"";
                      $email=($cust->email)?($cust->email):"";
                      $cashReward=($cust->cashReward)?($cust->cashReward):"0.00";
                      $custReward=($cust->custReward)?($cust->custReward):"";
                      $paymentType=($cust->paymentType)?($cust->paymentType):"";
                      $status=($cust->status)?($cust->status):0;
                      $id=($cust->id)?($cust->id):0;

                      $createdDate=($cust->createdDate)?(date("d M Y",strtotime($cust->createdDate))):"";
                      
                      if ($status==1) {
                      	$statusName='Pending';
                      } elseif ($status==2) {
                      	$statusName='Completed';
                      } elseif ($status==3) {
                      	//$statusName='Cancelled';
                        $statusName='Cancel';
                        
                      }  else {
                      	$statusName='';
                      }

                      if ($paymentType==1) {
                         $paymentTypeName='Wipay';
                      } else if ($paymentType==2) {
                         $paymentTypeName='Bank';
                      } else {
                        $paymentTypeName='';
                      }

                      $link=url('/').'/admin/payout/cancelrequest/'.$id;
                      $complete=url('/').'/admin/payout/completerequest/'.$id;
                      //$unleave=url('/').'/admin/missionmanagement/unleavemission/'.$missionId."/".$custmissionId;
                      $action='';

                      if ($status==1) {
                      $action .='<a onclick="return check_cancell('.$id.');" class="btn btn-default btn-sm" data-token="'.csrf_token().'" title="Cancell Payout Request" data-delete data-confirmation="notie"><i class="icon-fa icon-fa-trash"></i>Cancel</a>';
                       }

                      if ($status==1) { 
                      $action .='<a onclick="return check_complete('.$id.');" class="btn btn-default btn-sm" data-token="'.csrf_token().'" title="Complete Payout Request" data-delete data-confirmation="notie"><i class="icon-fa icon-fa-lock"></i>Completed</a>';
                      }


                      $row[]=$i;
                      $row[]=$fname;
                      $row[]=$lname;
                      $row[]=$email;
                      $row[]=$custReward;
                      $row[]=$cashReward;
                      $row[]=$paymentTypeName;
                      $row[]=$createdDate;
                      $row[]=$statusName;
                      $row[]=$action;
                      $output['data'][] = $row;

                  $i++;
                  }
             }

             echo json_encode($output);
             exit();     


	   }
	
	
	
	 
	   
	   public function cancelRequest($id) {
      $common=new CommanController;
		  $payout = Payout::find($id);
		  $customerId=$payout->customerId;
      $amount=$payout->cashReward;

      $customerReward=DB::table('tblcustomer')->where('id', '=',$customerId)->where('isActive', '=',1)->first(['cashReward']);
      $customerAmount=isset($customerReward->cashReward)?($customerReward->cashReward):0;
      $Amt=$customerAmount-$amount;  
      $customerRecord=DB::table('tblcustomer')->where('id',$customerId)->update(
               ['cashReward'=>$Amt]);

      
      $price='TT$'.$amount;

      $completemsg=$common->get_msg("cancel_payout_request",1)?$common->get_msg("cancel_payout_request",1):"Sorry! your #amt amount of Payout Request has been Cancelled.";  
      $receivermsg=str_replace("#amt",$price,$completemsg);

      $NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>1,'notifiedUserId'=>$customerId,'notificationType'=>5,'notification'=>$receivermsg,'createdDate'=>date('Y-m-d H:i:s'),'isCustomerNotification'=>1,'missionId'=>0]);

      $deviceTokenList= DB::table('tbldevicetoken')->where('customerId', '=',$customerId)->where('loginStatus', '=',1)->get();

      $notificationCount=$common->NotificationCountCustomer($customerId);

                                                $andDeviceToken=array();
                                                $iPhoneDeviceToken=array();

                                                if ($deviceTokenList) {
                                                   foreach ($deviceTokenList as  $value) {
                                                    $deviceType=($value->deviceType)?($value->deviceType):0;
                                                    $deviceToken=($value->deviceToken)?($value->deviceToken):"";
                                                    $loginStatus=($value->loginStatus)?($value->loginStatus):0;
                                                    if ($deviceType==1 && strlen($deviceToken) > 40) {
                                                             $andDeviceToken[]=$deviceToken;
                                                    }
                                                    if ($deviceType==2 && strlen($deviceToken) > 40) {
                                                             $iPhoneDeviceToken[]=$deviceToken;
                                                    }
                                                   }
                                                }

                                                if ($andDeviceToken) {
                                                   $ExtraInfo = array('notificationType'=>5,'message'=>$receivermsg,'title'=>'Mission App','notificationId'=>$NotificationID,'customerId'=>$customerId,'icon'=>'myicon','sound'=>'mySound','missionId'=>0,'MissionName' =>'','totalUnreadCount'=>$notificationCount);
                                                   $common->firebasepushCustomer($ExtraInfo,$andDeviceToken);
                                                 }

                                                 if ($iPhoneDeviceToken) {
                                                   $body['aps'] = array('alert' => $receivermsg, 'sound' => 'default', 'badge' =>$notificationCount, 'content-available' => 1,'notificationType'=>5,'notificationId'=>$NotificationID,'missionId'=>0,'MissionName' =>'','customerId'=>$customerId);
                                                   $common->iPhonePushBookCustomer($iPhoneDeviceToken,$body);
                                                 }


      $customerReward=DB::table('tblcustomer')->where('id', '=',$customerId)->where('isActive', '=',1)->first(['cashReward']);
      $customerAmount=isset($customerReward->cashReward)?($customerReward->cashReward):0;
      $Amt=$customerAmount+$amount;  
      $customerRecord=DB::table('tblcustomer')->where('id',$customerId)->update(
               ['cashReward'=>$Amt]);
      $payout->status=3;
      $payout->cancelledBy=1;
      $payout->cancelledDate=date("Y-m-d H:i:s");
		  $payout->save();
		  flash()->success('You have successfully cancelled this payout request.');
		 return redirect()->to('/admin/payoutmanagement');
	   }

     public function completeRequest($id) {
      $common=new CommanController;
      $payout = Payout::find($id);
      $customerId=$payout->customerId;
      $amount=$payout->cashReward;

      $customerReward=DB::table('tblcustomer')->where('id', '=',$customerId)->where('isActive', '=',1)->first(['cashReward']);
      $customerAmount=isset($customerReward->cashReward)?($customerReward->cashReward):0;
      $Amt=$customerAmount-$amount;  
      
      if ($Amt >= 0) {

          $customerRecord=DB::table('tblcustomer')->where('id',$customerId)->update(
                   ['cashReward'=>$Amt]);

          $payout->status=2;
          $payout->approvedBy=1;
          $payout->approvedDate=date("Y-m-d H:i:s");
          $payout->save();

          $price='TT$'.$amount;

      //$completemsg=$common->get_msg("complete_payout_request",1)?$common->get_msg("complete_payout_request",1):"Congratulation! your #amt amount of Payout Request has been Approved."; 

          $completemsg=$common->get_msg("complete_payout_request",1)?$common->get_msg("complete_payout_request",1):"Congratulations! Your #amt payout request has been  approved and successfully credited to your preferred payment option.";  

      

          $receivermsg=str_replace("#amt",$price,$completemsg);

          $NotificationID=DB::table('tblnotification')->insertGetId(
                   ['notifiedByUserId'=>1,'notifiedUserId'=>$customerId,'notificationType'=>5,'notification'=>$receivermsg,'createdDate'=>date('Y-m-d H:i:s'),'isCustomerNotification'=>1,'missionId'=>0]);

          $deviceTokenList= DB::table('tbldevicetoken')->where('customerId', '=',$customerId)->where('loginStatus', '=',1)->get();

          $notificationCount=$common->NotificationCountCustomer($customerId);

                                                $andDeviceToken=array();
                                                $iPhoneDeviceToken=array();

                                                if ($deviceTokenList) {
                                                   foreach ($deviceTokenList as  $value) {
                                                    $deviceType=($value->deviceType)?($value->deviceType):0;
                                                    $deviceToken=($value->deviceToken)?($value->deviceToken):"";
                                                    $loginStatus=($value->loginStatus)?($value->loginStatus):0;
                                                    if ($deviceType==1 && strlen($deviceToken) > 40) {
                                                             $andDeviceToken[]=$deviceToken;
                                                    }
                                                    if ($deviceType==2 && strlen($deviceToken) > 40) {
                                                             $iPhoneDeviceToken[]=$deviceToken;
                                                    }
                                                   }
                                                }

                                                if ($andDeviceToken) {
                                                   $ExtraInfo = array('notificationType'=>5,'message'=>$receivermsg,'title'=>'Mission App','notificationId'=>$NotificationID,'customerId'=>$customerId,'icon'=>'myicon','sound'=>'mySound','missionId'=>0,'MissionName' =>'','totalUnreadCount'=>$notificationCount);
                                                   $common->firebasepushCustomer($ExtraInfo,$andDeviceToken);
                                                 }

                                                 if ($iPhoneDeviceToken) {
                                                   $body['aps'] = array('alert' => $receivermsg, 'sound' => 'default', 'badge' =>$notificationCount, 'content-available' => 1,'notificationType'=>5,'notificationId'=>$NotificationID,'missionId'=>0,'MissionName' =>'','customerId'=>$customerId);
                                                   $common->iPhonePushBookCustomer($iPhoneDeviceToken,$body);
                                                 }
                echo "You have successfully completed this payout request";
                exit();
            } else {
                echo "You can not process this request because you have only $".$customerAmount." amount";
                exit();
            }                                      

                             //flash()->success('You have successfully completed this payout request.');
                             //return redirect()->to('/admin/payoutmanagement');
     }
	   
	   public function Delete($id) {
		   
		      $user = Payout::find($id);
              $user->delete();
			    echo 2;
		      exit();
	   }
	   
	   
	   
	   public function getEdit($id)
       {
         $payout = Payout::find($id);
        
         return view('admin.payout.addedit',compact('payout'));
       }
	   
	   
}
