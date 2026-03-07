<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\ConsumerForumRequest;
use Illuminate\Http\Request;
use Laraspace\ConsumerManager;
use Laraspace\ConsumerForum;
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

class ConsumerForumController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }

    public function getDownload(Request $request,$id) {
    // prepare content
    $common=new CommanController;

    $chatRecord=DB::select( DB::raw("Select chatcontent.*  from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
              where chatsession.forumId='$id'"));

     $forumData = DB::table('tblcustomerconsumerforum')->where([['id', '=',$id]])->first(); 
     $subject=isset($forumData->subject)?($forumData->subject):"";
     $description=isset($forumData->description)?($forumData->description):"";
     $customerId=isset($forumData->customerId)?($forumData->customerId):"";
     $Name=$common->customerName($customerId);
    
    $content = "Chat History \n";
    $content .= "\n";

     //$content= "\n";
                      //$content .=$Name.'';
    $content .=$Name." (Forum Description):  ".$description."\n";
    $content .=$Name." (Forum Subject):  ".$subject."\n";
    $content .= "\n";

    if ($chatRecord) {
        
        foreach($chatRecord as $rows) {

             $chatId=isset($rows->id)?($rows->id):0;
             $customerId=isset($rows->customerId)?($rows->customerId):0;
             $consumerManagerId=isset($rows->consumerManagerId)?($rows->consumerManagerId):0;
             $contents=isset($rows->content)?(base64_decode($rows->content)):"";
             $createdate=isset($rows->createdate)?(date("d/m/Y H:i:s",strtotime($rows->createdate))):"";
             $sentBy=isset($rows->sentBy)?($rows->sentBy):0;
             
             $name='';
             if ($sentBy==1) {
                $name=$common->customerName($customerId);
             }
             
             if ($sentBy==2) {
             $name=$common->consumerManagerUserName($consumerManagerId);
             }

             
             if ($sentBy==1) {
             $content .=$name." (Customer):  ".$contents."\n";
             $content .="                    ".$createdate;
             }

             if ($sentBy==2) {
             $content .=$name." (Consumer Manager):  ".$contents."\n";
             $content .="                    ".$createdate;
             }

             $content .= "\n";

        }
    }
     
   
    $clength=explode(" ",$content);
    //echo $content;
    //exit;

    //$logs = ConsumerForum::all();
    //print_r($logs);
    //exit;
    /*$content = "Logs \n";
    foreach ($logs as $log) {
      $content .= $log->id;
      $content .= "\n";
    }*/

    // file name that will be used in the download
    $fileName = $subject.".txt";

   // $size = filesize($fileName);
    $length = strlen($content);

    // use headers in order to generate the download
    $headers = [
      'Content-type' => 'text/plain', 
      'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
      'Content-Length' => $length
    ];

    // make a response, with the content, a 200 response code and the headers
    return Response::make($content, 200, $headers);
}
    
    public function index() {
		
	   $consumerforum = ConsumerForum::where('consumerForumType',1)->get();
       return view('admin.consumerforum.index',compact('consumerforum'));
	 }
	
  public function exportconsumerforumreport(Request $request) {
      
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

           $sort=' order by customerconsumerforum.organisationName desc';

          if ($orderbycolm==1) {
              $sort="order by  customerconsumerforum.subject $orderbydir";
          }
          if ($orderbycolm==2) {
              $sort="order by  customerconsumerforum.organisationName $orderbydir";
          }
          if ($orderbycolm==3) {
              $sort="order by  customer.fname $orderbydir";
          }
          if ($orderbycolm==4) {
              $sort="order by   customer.lname $orderbydir";
          }
          if ($orderbycolm==5) {
              $sort="order by  customer.email $orderbydir";
          }
          if ($orderbycolm==6) {
              $sort="order by  customerconsumerforum.createdDate $orderbydir";
          }
          

           $sWhere="";
          if ($searchtxt!='') {
           $sSearch =trim($searchtxt); 
           $sWhere .= "and ( `customer`.`fname` LIKE '%".trim($sSearch) ."%' OR `customer`.lname LIKE '%".trim($sSearch) ."%' OR  `customer`.`email` LIKE '%".trim($sSearch) ."%' OR  `customerconsumerforum`.`organisationName` LIKE '%".trim($sSearch) ."%' OR  `customerconsumerforum`.`subject` LIKE '%".trim($sSearch) ."%'";
           $sWhere .= " )";
          }
           
          if ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and customerconsumerforum.createdDate >='".$fromDate."' and customerconsumerforum.createdDate <='".$toDate."' ";

           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] == "")) {
                 
                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];            
                 
                 $sWhere .= " and customerconsumerforum.createdDate >='".$fromDate."'";
           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] == "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and customerconsumerforum.createdDate <='".$toDate."' ";

           } else {

           }


           $where="";
        
          
          
          /*if ($isActive!='') {
              if ($isActive==2) {
                $isActive=0;
              }
              $where.=" and customerconsumerforum.consumerForumType='".$isActive."'";
            }*/

        


            $customer_info=  DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.consumerForumType=1 ".$where." ".$sWhere."
          $sort"));

           return Excel::create('consumeraffairsreport', function($excel) use ($customer_info,$common) {
            $excel->sheet('mySheet', function($sheet) use ($customer_info,$common)
            {
                $sheet->cell('A1', function($cell) {$cell->setValue('Customer Name');   });
                $sheet->cell('B1', function($cell) {$cell->setValue('Customer Email');   });
                $sheet->cell('C1', function($cell) {$cell->setValue('Organisation Name');   });
                $sheet->cell('D1', function($cell) {$cell->setValue('Organisation Location');   });
                $sheet->cell('E1', function($cell) {$cell->setValue('Subject');   });
                $sheet->cell('F1', function($cell) {$cell->setValue('Description');   });
                $sheet->cell('G1', function($cell) {$cell->setValue('Consumer Forum Type');   });
                $sheet->cell('H1', function($cell) {$cell->setValue('Status');   });
                $sheet->cell('I1', function($cell) {$cell->setValue('Created Date');   });
                $sheet->cell('J1', function($cell) {$cell->setValue('Chat History');   });
               // $sheet->cell('J1', function($cell) {$cell->setValue('Chat History');   });
                

                if (!empty($customer_info)) {
                          $i=2;
                    foreach ($customer_info as  $cust) {
                          
                      $fname=($cust->fname)?($cust->fname):"";
                      $lname=($cust->lname)?($cust->lname):"";
                      $email=($cust->email)?($cust->email):"";
                      $organisationName=($cust->organisationName)?($cust->organisationName):"0.00";
                      $organisationLocation=($cust->organisationLocation)?($cust->organisationLocation):"";
                      $subject=($cust->subject)?($cust->subject):"";
                      $description=($cust->description)?($cust->description):"";
                      $consumerForumType=($cust->consumerForumType)?($cust->consumerForumType):"";

                      $Name=$fname." ".$lname;

                      $status=($cust->status)?($cust->status):0;
                      $id=($cust->id)?($cust->id):0;

                      $createdDate=($cust->createdDate)?(date("d M Y",strtotime($cust->createdDate))):"";
                      
                      if ($consumerForumType==1) {
                        $consumerForumTypeName='Consumer Affairs';
                      } elseif ($consumerForumType==2) {
                        $consumerForumTypeName='Ministry Of Health';
                      }   else {
                        $consumerForumTypeName='';
                      }

                      if ($status==1) {
                         $statusName='Open';
                      } else if ($status==2) {
                         $statusName='Closed';
                      } else {
                        $statusName='';
                      }

                      $chatRecord=DB::select( DB::raw("Select chatcontent.*  from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
              where chatsession.forumId='$id'"));
                      
                      $content= "\n";
                      //$content .=$Name.'';
                      $content .=$Name." (Forum Description):  ".$description."\n";
                      $content .=$Name." (Forum Subject):  ".$subject."\n";
                      $content .= "\n";
                     
                     $count=count($chatRecord);

                      if ($count > 0) {
                           
                      //$content = "Chat History \r";
                      //$content= "\n";

                        foreach($chatRecord as $rows) {

                             $chatId=isset($rows->id)?($rows->id):0;
                             $customerId=isset($rows->customerId)?($rows->customerId):0;
                             $consumerManagerId=isset($rows->consumerManagerId)?($rows->consumerManagerId):0;
                             $contents=isset($rows->content)?(base64_decode($rows->content)):"";
                             $createdate=isset($rows->createdate)?(date("d/m/Y H:i:s",strtotime($rows->createdate))):"";
                             $sentBy=isset($rows->sentBy)?($rows->sentBy):0;
                             
                             $name='';
                             if ($sentBy==1) {
                                $name=$common->customerName($customerId);
                             }
                             
                             if ($sentBy==2) {
                             $name=$common->consumerManagerUserName($consumerManagerId);
                             }

                             
                             if ($sentBy==1) {
                             $content .=$name." (Customer):  ".$contents."\n";
                             $content .="                    ".$createdate;
                             }

                             if ($sentBy==2) {
                             $content .=$name." (Consumer Manager):  ".$contents."\n";
                             $content .="                    ".$createdate;
                             }

                             $content .= "\n";

                          }
                     }   

                      /*$chatRecord=DB::select( DB::raw("Select chatcontent.*  from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
              where chatsession.forumId='$id'"));
                      
                      $content='';

                      if ($chatRecord) {
                           
                      $content = "Chat History \n";
                      $content .= "\n";

                        foreach($chatRecord as $rows) {

                             $chatId=isset($rows->id)?($rows->id):0;
                             $customerId=isset($rows->customerId)?($rows->customerId):0;
                             $consumerManagerId=isset($rows->consumerManagerId)?($rows->consumerManagerId):0;
                             $contents=isset($rows->content)?(base64_decode($rows->content)):"";
                             $createdate=isset($rows->createdate)?(date("d/m/Y H:i:s",strtotime($rows->createdate))):"";
                             $sentBy=isset($rows->sentBy)?($rows->sentBy):0;
                             
                             $name='';
                             if ($sentBy==1) {
                                $name=$common->customerName($customerId);
                             }
                             
                             if ($sentBy==2) {
                             $name=$common->consumerManagerUserName($consumerManagerId);
                             }

                             
                             if ($sentBy==1) {
                             $content .=$name." (Customer):  ".$contents."\n";
                             $content .="                    ".$createdate;
                             }

                             if ($sentBy==2) {
                             $content .=$name." (Consumer Manager):  ".$contents."\n";
                             $content .="                    ".$createdate;
                             }

                             $content .= "\n";

                          }
                     }*/

                          


                        $sheet->cell('A'.$i, $Name);
                        $sheet->cell('B'.$i, $email);
                        $sheet->cell('C'.$i, $organisationName);
                        $sheet->cell('D'.$i, $organisationLocation);
                        $sheet->cell('E'.$i, $subject);
                        $sheet->cell('F'.$i, $description);
                        $sheet->cell('G'.$i, $consumerForumTypeName);
                        $sheet->cell('H'.$i, $statusName);
                        $sheet->cell('I'.$i, $createdDate);
                        $sheet->cell('J'.$i, $content);
                       // $sheet->cell('J'.$i, $content);
                        
                        

                    $i++;
                    }
                }


              });   
            })->download($type); 
    }

    public function consumerForumDetails($id) {
           $common=new CommanController;
            $ConsumerForumCn=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email,customer.phone from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.id=".$id.""));
             $consumerforum=$ConsumerForumCn[0];
             $countryId=isset($consumerforum->countryId)?($consumerforum->countryId):0;
             $consumerManagerUserId=$common->consumerManagerUserId($countryId);


             $qry2=DB::select( DB::raw("UPDATE tblchatcontent
           JOIN tblchatsession
           ON tblchatcontent.chatSessionId = tblchatsession.chatSessionId
           SET  IsRead='1' where  tblchatcontent.chatType=1 and tblchatcontent.sentBy='1' and tblchatsession.forumId='$id'"));

             $chatRecord=DB::select( DB::raw("Select count(chatcontent.id) as cntotal from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
              where chatsession.forumId='$id'"));
             $chatCount=isset($chatRecord[0]->cntotal)?($chatRecord[0]->cntotal):"";

            return view('admin.consumerforum.show',compact('consumerforum','consumerManagerUserId','chatCount'));
                 
    }

    public function ministryOfHealthDetails($id) {
      $common=new CommanController;
            $ConsumerForumCn=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email,customer.phone from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.id=".$id.""));
             $consumerforum=$ConsumerForumCn[0];
             $countryId=isset($consumerforum->countryId)?($consumerforum->countryId):0;
             $consumerManagerUserId=$common->consumerManagerUserId($countryId);


             $qry2=DB::select( DB::raw("UPDATE tblchatcontent
           JOIN tblchatsession
           ON tblchatcontent.chatSessionId = tblchatsession.chatSessionId
           SET  IsRead='1' where  tblchatcontent.chatType=1 and tblchatcontent.sentBy='1' and tblchatsession.forumId='$id'"));

             $chatRecord=DB::select( DB::raw("Select count(chatcontent.id) as cntotal from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
              where chatsession.forumId='$id'"));
             $chatCount=isset($chatRecord[0]->cntotal)?($chatRecord[0]->cntotal):"";

            return view('admin.ministryofhealth.show',compact('consumerforum','consumerManagerUserId','chatCount'));
                 
    }

    public function consumerForumPost(Request $request) {
        
        $common=new CommanController;
        $forumId=isset($request->forumId)?($request->forumId):0;
        $fromuserId=isset($request->fromuserId)?($request->fromuserId):0;
        $touserId=isset($request->touserId)?($request->touserId):0;
        $content=isset($request->content)?($request->content):0;

        $ForumName=$common->getForumType($forumId);

        $forumData = DB::table('tblcustomerconsumerforum')->where([['id', '=',$forumId]])->first();

        $consumerForumType=($forumData->consumerForumType)?($forumData->consumerForumType):1; 

         $qry=DB::select( DB::raw("SELECT main.chatSessionId FROM `tblchatsession` as main LEFT JOIN tblchatcontent as sub ON main.chatSessionId = sub.chatSessionId WHERE (sub.customerId = '$touserId' AND sub.consumerManagerId = '$fromuserId') AND main.forumId='$forumId' AND main.chatType = '1'"));
                if($qry) {
                        foreach ($qry as $values) {
                          $chatSessionId=$values->chatSessionId;
                        }
                } else {

                  session_start();
                  $new_sessionid = session_id();
                  $chatSessionId=DB::table('tblchatsession')->insertGetId(
                   ['sessionId'=>$new_sessionid,'chatType'=>1,'createdate'=>date('Y-m-d H:i:s'),'forumId'=>$forumId]);

                }

          $createdate=date('Y-m-d H:i:s');
          $insertType = '1';
          $uniqueId = uniqid();  
          $Username="Admin";
          if($content!='') {
                   $type=0;
                   $sentBy=2;
                   $cnt=base64_encode($content);
                   $chatContent=DB::table('tblchatcontent')->insertGetId(
                   ['chatSessionId'=>$chatSessionId,'customerId'=>$touserId,'createdate'=>date('Y-m-d H:i:s'),'consumerManagerId'=>$fromuserId,'content'=>$cnt,"senderdate"=>$createdate,"receiverdate"=>$createdate,"chatType"=>$insertType,"type"=>$type,"uniqueId"=>$uniqueId,"sentBy"=>$sentBy,'chatType'=>1]);

                  

                   if($content!='') {

                                         $nomsg=$common->get_msg("forum_chat_notification",1)?($common->get_msg("forum_chat_notification",1)):'New message from the #type';
                                         $nomsgreplace=str_replace("#type",$ForumName,$nomsg);
                                         
                                         $Description=$nomsgreplace;
                                          
                                          $NotificationID=0;

                                          $deviceTokenList= DB::table('tbldevicetoken')->where('customerId', '=',$touserId)->where('loginStatus', '=',1)->get();
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

                                                $notificationCount=$common->NotificationCountCustomer($touserId);

                                               if ($andDeviceToken) {
                                                   $ExtraInfo = array('notificationType'=>3,'message'=>$Description,'title'=>'Mission App','notificationId'=>$NotificationID,'customerId'=>$touserId,'icon'=>'myicon','sound'=>'mySound','missionId'=>0,'MissionName' =>"",'consumerManagerId'=>$fromuserId,'forumId'=>$forumId,'totalUnreadCount'=>$notificationCount,'consumerForumType'=>$consumerForumType);
                                                   $common->firebasepushCustomer($ExtraInfo,$andDeviceToken);
                                                 }

                                                 if ($iPhoneDeviceToken) {
                                                   $body['aps'] = array('alert' => $Description, 'sound' => 'default', 'badge' =>$notificationCount, 'content-available' => 1,'notificationType'=>3,'notificationId'=>$NotificationID,'missionId'=>0,'MissionName' =>"",'consumerManagerId'=>$fromuserId,'customerId'=>$touserId,'forumId'=>$forumId,'consumerForumType'=>$consumerForumType);
                                                   $common->iPhonePushBookCustomer($iPhoneDeviceToken,$body);
                                                 }     
             

                  }



                   $flag=true;
            $message = $common->get_msg("sent_you_message")?$common->get_msg("sent_you_message"):"";  
            $message=str_replace('#username#',$Username,$message);
            echo $chatContent;
            exit();
                }    

    }

	public function getConsumerForumData(Request $request) {

		     $sWhere="";
         
         if (isset($_REQUEST['searchtxt']) && $_REQUEST['searchtxt'] != "") {
           $sSearch =trim($_REQUEST['searchtxt']); 
           $sWhere .= "and ( `customer`.`fname` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR `customer`.lname LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customer`.`email` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customerconsumerforum`.`organisationName` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customerconsumerforum`.`subject` LIKE '%".trim($_REQUEST['searchtxt']) ."%'";
            $sWhere .= " )";
           }

           if ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and customerconsumerforum.createdDate >='".$fromDate."' and customerconsumerforum.createdDate <='".$toDate."' ";

           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] == "")) {
                 
                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];            
                 
                 $sWhere .= " and customerconsumerforum.createdDate >='".$fromDate."'";
           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] == "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and customerconsumerforum.createdDate <='".$toDate."' ";

           } else {

           }

		     $start=($request->start)?($request->start):0;
         $length=($request->length)?($request->length):0;
         $isActive=($request->isActive)?($request->isActive):"";

         $orderbycolm=($request->order[0]['column'])?($request->order[0]['column']):0;
         $orderbydir=($request->order[0]['dir'])?($request->order[0]['dir']):'asc';
           
         $order='';
          
          if ($length!=-1) {
            $order="LIMIT $start,$length";
          }
           
          $draw=1; 
          if($_REQUEST['draw']) {
          	$draw=$_REQUEST['draw'];
          }

          $sort=' order by  customerconsumerforum.organisationName desc';

          if ($orderbycolm==1) {
              $sort="order by  customerconsumerforum.subject $orderbydir";
          }
          if ($orderbycolm==2) {
              $sort="order by  customerconsumerforum.organisationName $orderbydir";
          }
          if ($orderbycolm==3) {
              $sort="order by  customer.fname $orderbydir";
          }
          if ($orderbycolm==4) {
              $sort="order by   customer.lname $orderbydir";
          }
          if ($orderbycolm==5) {
              $sort="order by  customer.email $orderbydir";
          }
          if ($orderbycolm==6) {
              $sort="order by  customerconsumerforum.createdDate $orderbydir";
          }

          /*if ($isActive!='') {
              $sWhere.=" and customerconsumerforum.consumerForumType='".$isActive."'";
          }
*/
          $ConsumerForumCn=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.consumerForumType=1 ".$sWhere." ".$sort.""));

          $ConsumerForum=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.consumerForumType=1 ".$sWhere." ".$sort." $order"));

           $counTotal=count($ConsumerForumCn);
          
	          $output = array(
	          "recordsTotal" => $counTotal,
	          "recordsFiltered" => $counTotal,
	          "draw" => $draw,
	          "data" => array()
	          );

	          if ($ConsumerForum) {
                   $i=1;
                   $totalUn=0;
                  
                  foreach ($ConsumerForum as  $cust) {

                      $row = array();
                      $forumId=($cust->id)?($cust->id):0;

                      $fname=($cust->fname)?($cust->fname):"";
                      $lname=($cust->lname)?($cust->lname):"";
                      $email=($cust->email)?($cust->email):"";
                      $subject=($cust->subject)?($cust->subject):"";
                      $description=($cust->description)?($cust->description):"";
                      $consumerForumType=($cust->consumerForumType)?($cust->consumerForumType):0;

                      $createdDate=($cust->createdDate)?($cust->createdDate):"";
                      $organisationName=($cust->organisationName)?($cust->organisationName):"";
                      $organisationLocation=($cust->organisationLocation)?($cust->organisationLocation):"";
                      $latitude=($cust->latitude)?($cust->latitude):0;
                      $longitude=($cust->longitude)?($cust->longitude):0;

                      $countryId=($cust->countryId)?($cust->countryId):0;
                      $countryName=($cust->countryName)?($cust->countryName):"";

                      $status=($cust->status)?($cust->status):0;
                      $id=($cust->id)?($cust->id):0;

                      $createdDate=($cust->createdDate)?(date("d M Y",strtotime($cust->createdDate))):"";
                      
                      if ($status==1) {
                      	$statusName='Open';
                      } elseif ($status==2) {
                      	$statusName='Closed';
                      }   else {
                      	$statusName='';
                      }

                      if ($consumerForumType==1) {
                         $consumerForumTypeName='Consumer Affairs';
                      } else if ($consumerForumType==2) {
                         $consumerForumTypeName='Ministry Of Health';
                      } else {
                        $consumerForumTypeName='';
                      }

                      $chatRecord=DB::select( DB::raw("Select count(chatcontent.id) as cntotal from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
                       where chatsession.forumId='$id' and chatcontent.IsRead='0' and chatcontent.sentBy=1"));
                      $chatCount=isset($chatRecord[0]->cntotal)?($chatRecord[0]->cntotal):0;

                      $chatRecordCus=DB::select( DB::raw("Select count(chatcontent.id) as cntotal from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
                       where chatsession.forumId='$id' and chatcontent.IsRead='0' and chatcontent.sentBy=1"));
                      $chatCountCust=isset($chatRecordCus[0]->cntotal)?($chatRecordCus[0]->cntotal):0;

                      $totalUn=$totalUn+$chatCountCust;

                      $badgeclass='badge';
                      if ($chatCountCust > 0) {
                          $badgeclass='unreadbadge';
                      }

                      $link=url('/').'/admin/consumerForum/viewDetails/'.$id;
                      //$complete=url('/').'/admin/payout/completerequest/'.$id;
                      //$unleave=url('/').'/admin/missionmanagement/unleavemission/'.$missionId."/".$custmissionId;
                      $action='<a href="'.$link.'" class="btn btn-default btn-sm"  title="View Details" ><i class="icon-fa icon-fa-list"></i>View Details</a>';
                      $action.=' <a onclick="return check_delete('.$id.');" data-token="'.csrf_token().'" data-delete="" data-confirmation="notie" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-trash"></i> Delete</a>';
                      $action.=' <a href="'.$link.'"  class="notification"><span>New Messages</span><span class="'.$badgeclass.'" id="'.$forumId.'">'.$chatCount.'</span></a>';

                      if ($counTotal==$i) {
                        $action.='<input type="hidden" id="totalUnreadCount" value="'.$totalUn.'">';
                      }

                     


                      $row[]='<input type="checkbox" class="uniquechk" name="del[]" value="'.$forumId.'">';
                      $row[]=$subject;
                      $row[]=$organisationName;
                      $row[]=$fname;
                      $row[]=$lname;
                      $row[]=$email;
                      //$row[]=$paymentTypeName;
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

  public function completeComplain($id) {
      
      $user = ConsumerForum::find($id);
      $user->status=2;
      $user->save();
      echo 2;
       exit();

  }

  /* Ministry of Health */

   public function ministryOfHealth() {
    
     $ministryofhealth = ConsumerForum::where('consumerForumType',2)->get();
       return view('admin.ministryofhealth.index',compact('ministryofhealth'));
   }
  
  public function exportministryOfHealthreport(Request $request) {
      
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

           $sort='order by  customerconsumerforum.organisationName desc';

          if ($orderbycolm==1) {
              $sort="order by  customerconsumerforum.subject $orderbydir";
          }
          if ($orderbycolm==2) {
              $sort="order by  customerconsumerforum.organisationName $orderbydir";
          }
          if ($orderbycolm==3) {
              $sort="order by  customer.fname $orderbydir";
          }
          if ($orderbycolm==4) {
              $sort="order by   customer.lname $orderbydir";
          }
          if ($orderbycolm==5) {
              $sort="order by  customer.email $orderbydir";
          }
          if ($orderbycolm==6) {
              $sort="order by  customerconsumerforum.createdDate $orderbydir";
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
           $sWhere .= "and ( `customer`.`fname` LIKE '%".trim($sSearch) ."%' OR `customer`.lname LIKE '%".trim($sSearch) ."%' OR  `customer`.`email` LIKE '%".trim($sSearch) ."%' OR  `customerconsumerforum`.`organisationName` LIKE '%".trim($sSearch) ."%' OR  `customerconsumerforum`.`subject` LIKE '%".trim($sSearch) ."%'";
      $sWhere .= " )";
          }

          if ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and customerconsumerforum.createdDate >='".$fromDate."' and customerconsumerforum.createdDate <='".$toDate."' ";

           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] == "")) {
                 
                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];            
                 
                 $sWhere .= " and customerconsumerforum.createdDate >='".$fromDate."'";
           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] == "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and customerconsumerforum.createdDate <='".$toDate."' ";

           } else {

           }



          $where="";
        
          
          
         /* if ($isActive!='') {
              if ($isActive==2) {
                $isActive=0;
              }
              $where.=" and customerconsumerforum.consumerForumType='".$isActive."'";
            }*/

        


            $customer_info=  DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.consumerForumType=2 ".$where." ".$sWhere."
          $sort"));

           return Excel::create('ministryofhealthreport', function($excel) use ($customer_info,$common) {
            $excel->sheet('mySheet', function($sheet) use ($customer_info,$common)
            {
                $sheet->cell('A1', function($cell) {$cell->setValue('Customer Name');   });
                $sheet->cell('B1', function($cell) {$cell->setValue('Customer Email');   });
                $sheet->cell('C1', function($cell) {$cell->setValue('Organisation Name');   });
                $sheet->cell('D1', function($cell) {$cell->setValue('Organisation Location');   });
                $sheet->cell('E1', function($cell) {$cell->setValue('Subject');   });
                $sheet->cell('F1', function($cell) {$cell->setValue('Description');   });
                $sheet->cell('G1', function($cell) {$cell->setValue('Consumer Forum Type');   });
                $sheet->cell('H1', function($cell) {$cell->setValue('Status');   });
                $sheet->cell('I1', function($cell) {$cell->setValue('Created Date');   });
                $sheet->cell('J1', function($cell) {$cell->setValue('Chat History');   });
                

                if (!empty($customer_info)) {
                          $i=2;
                    foreach ($customer_info as  $cust) {
                          
                      $fname=($cust->fname)?($cust->fname):"";
                      $lname=($cust->lname)?($cust->lname):"";
                      $email=($cust->email)?($cust->email):"";
                      $organisationName=($cust->organisationName)?($cust->organisationName):"0.00";
                      $organisationLocation=($cust->organisationLocation)?($cust->organisationLocation):"";
                      $subject=($cust->subject)?($cust->subject):"";
                      $description=($cust->description)?($cust->description):"";
                      $consumerForumType=($cust->consumerForumType)?($cust->consumerForumType):"";

                      $Name=$fname." ".$lname;

                      $status=($cust->status)?($cust->status):0;
                      $id=($cust->id)?($cust->id):0;

                      $createdDate=($cust->createdDate)?(date("d M Y",strtotime($cust->createdDate))):"";
                      
                      if ($consumerForumType==1) {
                        $consumerForumTypeName='Consumer Affairs';
                      } elseif ($consumerForumType==2) {
                        $consumerForumTypeName='Ministry Of Health';
                      }   else {
                        $consumerForumTypeName='';
                      }

                      if ($status==1) {
                         $statusName='Open';
                      } else if ($status==2) {
                         $statusName='Closed';
                      } else {
                        $statusName='';
                      }


                      $chatRecord=DB::select( DB::raw("Select chatcontent.*  from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
              where chatsession.forumId='$id'"));
                      
                      //$content="";


                      $content= "\n";
                      //$content .=$Name.'';
                      $content .=$Name." (Forum Description):  ".$description."\n";
                      $content .=$Name." (Forum Subject):  ".$subject."\n";
                      $content .= "\n";
                     
                     $count=count($chatRecord);

                      if ($count > 0) {
                           
                      //$content = "Chat History \r";
                      //$content= "\n";

                        foreach($chatRecord as $rows) {

                             $chatId=isset($rows->id)?($rows->id):0;
                             $customerId=isset($rows->customerId)?($rows->customerId):0;
                             $consumerManagerId=isset($rows->consumerManagerId)?($rows->consumerManagerId):0;
                             $contents=isset($rows->content)?(base64_decode($rows->content)):"";
                             $createdate=isset($rows->createdate)?(date("d/m/Y H:i:s",strtotime($rows->createdate))):"";
                             $sentBy=isset($rows->sentBy)?($rows->sentBy):0;
                             
                             $name='';
                             if ($sentBy==1) {
                                $name=$common->customerName($customerId);
                             }
                             
                             if ($sentBy==2) {
                             $name=$common->consumerManagerUserName($consumerManagerId);
                             }

                             
                             if ($sentBy==1) {
                             $content .=$name." (Customer):  ".$contents."\n";
                             $content .="                    ".$createdate;
                             }

                             if ($sentBy==2) {
                             $content .=$name." (Consumer Manager):  ".$contents."\n";
                             $content .="                    ".$createdate;
                             }

                             $content .= "\n";

                          }
                     }   

                          


                        $sheet->cell('A'.$i, $Name);
                        $sheet->cell('B'.$i, $email);
                        $sheet->cell('C'.$i, $organisationName);
                        $sheet->cell('D'.$i, $organisationLocation);
                        $sheet->cell('E'.$i, $subject);
                        $sheet->cell('F'.$i, $description);
                        $sheet->cell('G'.$i, $consumerForumTypeName);
                        $sheet->cell('H'.$i, $statusName);
                        $sheet->cell('I'.$i, $createdDate);
                        $sheet->cell('J'.$i, $content);
                        
                        

                    $i++;
                    }
                }


              });   
            })->download($type); 
    }

    public function lastchatId(Request $request,$id) {
       $lastchatId=isset($request->lastchatId)?($request->lastchatId):0;
        $where='';
        if ($lastchatId!=0) {
          //$where='and chatcontent.id > '.$lastchatId.'';
        }

      $sql=DB::select( DB::raw("Select chatcontent.id from tblchatcontent as chatcontent 
        inner join tblchatsession as chatsession On chatcontent.chatSessionId=chatsession.chatSessionId
        where  chatsession.forumId=".$id." ".$where."  order by chatcontent.id desc Limit 1"));
       $ChatList=isset($sql[0]->id)?($sql[0]->id):0;
        echo $ChatList;
        exit();

    }


    public function getUnreadMessage(Request $request) {
       
       $type=isset($request->type)?($request->type):0;

      $chatRecordCus=DB::select( DB::raw("Select count(chatcontent.id) as cntotal from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
        join tblcustomerconsumerforum as customerconsumerforum ON chatsession.forumId=customerconsumerforum.id
        inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
                       where customerconsumerforum.consumerForumType='$type'  and chatcontent.IsRead='0' and chatcontent.sentBy=1 and customer.isActive=1"));
      $chatCountCust=isset($chatRecordCus[0]->cntotal)?($chatRecordCus[0]->cntotal):0;
       echo $chatCountCust;
       exit();
    }

    public function consumerForumgetDataChatList(Request $request,$id) {
        $lastchatId=isset($request->lastchatId)?($request->lastchatId):0;
        $where='';
        
        $ordervy='';
        if ($lastchatId!=0) {
          $where=' and chatcontent.id > '.$lastchatId.'';
          $ordervy=' Limit 1';
        }
        /*if ($lastchatId!=0) {
          $where='and chatcontent.id > '.$lastchatId.'';
        }*/
       /* echo "Select chatcontent.* from tblchatcontent as chatcontent 
inner join tblchatsession as chatsession On chatcontent.chatSessionId=chatsession.chatSessionId
where  chatsession.forumId=".$id." ".$where."  order by chatcontent.id asc ".$ordervy."";
        exit;*/
       $sql=DB::select( DB::raw("Select chatcontent.* from tblchatcontent as chatcontent 
inner join tblchatsession as chatsession On chatcontent.chatSessionId=chatsession.chatSessionId
where  chatsession.forumId=".$id." ".$where."  order by chatcontent.id asc ".$ordervy.""));
       $date = date("Y-m-d H:i:s");
       $feedData = '<input type="hidden" id="lastchatId" value="0">';
       //$feedData .=  "<ul>";
       //$feedData .=  '<div class="chatbox"><div class="scrl">';
       if ($sql) {
           $feedData = '';
           $i=1;
           $cn=count($sql);
          foreach($sql as $rows) {
            $chatId=($rows->id)?($rows->id):0;
            $customerId=($rows->customerId)?($rows->customerId):0;
            $consumerManagerId=($rows->consumerManagerId)?($rows->consumerManagerId):0;
            $content=($rows->content)?($rows->content):"";
            $type=($rows->type)?($rows->type):0;
            $chatType=($rows->chatType)?($rows->chatType):"";
            $sentBy=($rows->sentBy)?($rows->sentBy):2;
            $createdate=($rows->createdate)?($rows->createdate):"";
            $photo_url=($rows->photo_url)?($rows->photo_url):"";
             

           
            $className='time-left';
            $cls='left';
            if ($sentBy==2) {
               $className='time-right';
               $cls='right';
            }
            $data='';

            if ($type==0) {
              $cnt=base64_decode($content);
              $data=$cnt;
            }

            if ($type==1) {
             $data='<a href="'.$photo_url.'" target="_blank"><img src="'.$photo_url.'"></a>';
            }
            
              $hidden='';
              if ($i==$cn) {
                  $hidden='<input type="hidden" id="lastchatId" value="'.$chatId.'">';
              }

            $feedData .='<div class="container">
 
  <p class="'.$cls.'">'.$data.'</p>
  <span class="'.$className.'">'.$createdate.'</span>
  '.$hidden.'
</div>';
             
            //$feedData .= "<li><a href='' title=''>" . $rows->content . "</a></li>";
          $i++;
        }
       }

       //$feedData .='</div>';

       //$feedData .='';  
       //$feedData .="</ul>";
       //$feedData .= "";
       //$feedData .= "<p>Data current as at: ".$date."</p>";
       echo $feedData;

    }

  public function getMinistryOfHealthData(Request $request) {

         $sWhere="";
         
         if (isset($_REQUEST['searchtxt']) && $_REQUEST['searchtxt'] != "") {
           $sSearch =trim($_REQUEST['searchtxt']); 
      $sWhere .= "and ( `customer`.`fname` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR `customer`.lname LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customer`.`email` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customerconsumerforum`.`organisationName` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customerconsumerforum`.`subject` LIKE '%".trim($_REQUEST['searchtxt']) ."%'";
      $sWhere .= " )";
           }

         if ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and customerconsumerforum.createdDate >='".$fromDate."' and customerconsumerforum.createdDate <='".$toDate."' ";

           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] == "")) {
                 
                 $startDate=$_REQUEST['startDate'];
                 $strDate=explode("/",$startDate);
                 $fromDate=$strDate[2]."-".$strDate[0]."-".$strDate[1];            
                 
                 $sWhere .= " and customerconsumerforum.createdDate >='".$fromDate."'";
           } elseif ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] == "") && (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "")) {

                 $endDate=$_REQUEST['endDate'];
                 $enDate=explode("/",$endDate);
                 $toDate=$enDate[2]."-".$enDate[0]."-".$enDate[1];
               
                 $sWhere .= " and customerconsumerforum.createdDate <='".$toDate."' ";

           } else {

           }  

         $start=($request->start)?($request->start):0;
         $length=($request->length)?($request->length):0;
         $isActive=($request->isActive)?($request->isActive):"";

         $orderbycolm=($request->order[0]['column'])?($request->order[0]['column']):0;
         $orderbydir=($request->order[0]['dir'])?($request->order[0]['dir']):'asc';
           
         $order='';
          
          if ($length!=-1) {
            $order="LIMIT $start,$length";
          }
           
          $draw=1; 
          if($_REQUEST['draw']) {
            $draw=$_REQUEST['draw'];
          }

          $sort=' order by  customerconsumerforum.id desc';

          if ($orderbycolm==1) {
              $sort="order by  customerconsumerforum.subject $orderbydir";
          }
          if ($orderbycolm==2) {
              $sort="order by  customerconsumerforum.organisationName $orderbydir";
          }
          if ($orderbycolm==3) {
              $sort="order by  customer.fname $orderbydir";
          }
          if ($orderbycolm==4) {
              $sort="order by   customer.lname $orderbydir";
          }
          if ($orderbycolm==5) {
              $sort="order by  customer.email $orderbydir";
          }
          if ($orderbycolm==6) {
              $sort="order by  customerconsumerforum.createdDate $orderbydir";
          }

         /* if ($isActive!='') {
              $sWhere.=" and customerconsumerforum.consumerForumType='".$isActive."'";
          }*/

          $ConsumerForumCn=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.consumerForumType=2 ".$sWhere." ".$sort.""));

          $ConsumerForum=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.consumerForumType=2 ".$sWhere." ".$sort." $order"));

           $counTotal=count($ConsumerForumCn);
          
            $output = array(
            "recordsTotal" => $counTotal,
            "recordsFiltered" => $counTotal,
            "draw" => $draw,
            "data" => array()
            );

            if ($ConsumerForum) {
                   $i=1;
                   $totalUn=0;
                  foreach ($ConsumerForum as  $cust) {

                      $row = array();
                      $forumId=($cust->id)?($cust->id):0;
                      $fname=($cust->fname)?($cust->fname):"";
                      $lname=($cust->lname)?($cust->lname):"";
                      $email=($cust->email)?($cust->email):"";
                      $subject=($cust->subject)?($cust->subject):"";
                      $description=($cust->description)?($cust->description):"";
                      $consumerForumType=($cust->consumerForumType)?($cust->consumerForumType):0;

                      $createdDate=($cust->createdDate)?($cust->createdDate):"";
                      $organisationName=($cust->organisationName)?($cust->organisationName):"";
                      $organisationLocation=($cust->organisationLocation)?($cust->organisationLocation):"";
                      $latitude=($cust->latitude)?($cust->latitude):0;
                      $longitude=($cust->longitude)?($cust->longitude):0;

                      $countryId=($cust->countryId)?($cust->countryId):0;
                      $countryName=($cust->countryName)?($cust->countryName):"";

                      $status=($cust->status)?($cust->status):0;
                      $id=($cust->id)?($cust->id):0;

                      $createdDate=($cust->createdDate)?(date("d M Y",strtotime($cust->createdDate))):"";
                      
                      if ($status==1) {
                        $statusName='Open';
                      } elseif ($status==2) {
                        $statusName='Closed';
                      }   else {
                        $statusName='';
                      }

                      if ($consumerForumType==1) {
                         $consumerForumTypeName='Consumer Affairs';
                      } else if ($consumerForumType==2) {
                         $consumerForumTypeName='Ministry Of Health';
                      } else {
                        $consumerForumTypeName='';
                      }


                      $chatRecord=DB::select( DB::raw("Select count(chatcontent.id) as cntotal from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
                       where chatsession.forumId='$id' and IsRead='1'"));
                      $chatCount=isset($chatRecord[0]->cntotal)?($chatRecord[0]->cntotal):0;

                      $chatRecordCus=DB::select( DB::raw("Select count(chatcontent.id) as cntotal from tblchatcontent as chatcontent inner join tblchatsession as chatsession ON  chatcontent.chatSessionId=chatsession.chatSessionId
                       where chatsession.forumId='$id' and chatcontent.IsRead='0' and chatcontent.sentBy=1"));
                      $chatCountCust=isset($chatRecordCus[0]->cntotal)?($chatRecordCus[0]->cntotal):0;

                      $totalUn=$totalUn+$chatCountCust;

                      $link=url('/').'/admin/ministryofhealth/viewDetails/'.$id;

                      $badgeclass='badge';
                      if ($chatCountCust > 0) {
                          $badgeclass='unreadbadge';
                      }

                      $link=url('/').'/admin/ministryofhealth/viewDetails/'.$id;
                      //$complete=url('/').'/admin/payout/completerequest/'.$id;
                      //$unleave=url('/').'/admin/missionmanagement/unleavemission/'.$missionId."/".$custmissionId;
                      $action='<a href="'.$link.'" class="btn btn-default btn-sm"  title="View Details" ><i class="icon-fa icon-fa-list"></i>View Details</a>';
                      $action.=' <a onclick="return check_delete('.$id.');" data-token="'.csrf_token().'" data-delete="" data-confirmation="notie" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-trash"></i> Delete</a>';

                      $action.=' <a href="'.$link.'"  class="notification"><span>New Messages</span><span class="'.$badgeclass.'" id="'.$forumId.'">'.$chatCount.'</span></a>';

                     if ($counTotal==$i) {
                        $action.='<input type="hidden" id="totalUnreadCount" value="'.$totalUn.'">';
                      }

                     


                      $row[]='<input type="checkbox" class="uniquechk" name="del[]" value="'.$forumId.'">';
                      $row[]=$subject;
                      $row[]=$organisationName;
                      $row[]=$fname;
                      $row[]=$lname;
                      $row[]=$email;
                      //$row[]=$paymentTypeName;
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

   public function DeleteallConsumerForum(Request $request) {
      $error='';
      $err=0;
      $section='';
      $section2='';
      
      foreach ($request->del as $val) {
       
        
        $cf = ConsumerForum::find($val);
              $cf->delete();
      
      }
      
       $msg='Selected Consumer Affairs Forum has been deleted successfully.';
      
      flash()->success($msg);
      
      return redirect()->to('/admin/consumerForum');
      //exit;
     }

     public function Delete($id) {
           $cf = ConsumerForum::find($id);
           $cf->delete();
           echo 2;
           exit();
     }

     public function DeleteallMinistryOfHealth(Request $request) {
        $error='';
      $err=0;
      $section='';
      $section2='';
      
      foreach ($request->del as $val) {
       
        
        $cf = ConsumerForum::find($val);
              $cf->delete();
      
      }
      
       $msg='Selected Ministry Of Health Forum has been deleted successfully.';
      
      flash()->success($msg);
      
      return redirect()->to('/admin/ministryofhealth');
      //exit;
     }


}
