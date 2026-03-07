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

           $sort='order by customerconsumerforum.id desc';

          

           $sWhere="";
          if ($searchtxt!='') {
           $sSearch =trim($searchtxt); 
           $sWhere .= "and ( `customer`.`fname` LIKE '%".trim($sSearch) ."%' OR `customer`.lname LIKE '%".trim($sSearch) ."%' OR  `customer`.`email` LIKE '%".trim($sSearch) ."%' OR  `customerconsumerforum`.`organisationName` LIKE '%".trim($sSearch) ."%' OR  `customerconsumerforum`.`subject` LIKE '%".trim($sSearch) ."%'";
      $sWhere .= " )";
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
                         $statusName='Active';
                      } else if ($status==2) {
                         $statusName='Completed';
                      } else {
                        $statusName='';
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
                        
                        

                    $i++;
                    }
                }


              });   
            })->download($type); 
    }

    public function consumerForumDetails($id) {

            $ConsumerForumCn=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email,customer.phone from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.id=".$id.""));
             $consumerforum=$ConsumerForumCn[0];

            return view('admin.consumerforum.show',compact('consumerforum'));
                 
    }

    public function ministryOfHealthDetails($id) {
             $ConsumerForumCn=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email,customer.phone from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.id=".$id.""));
             $consumerforum=$ConsumerForumCn[0];

            return view('admin.consumerforum.ministryofhealth',compact('consumerforum'));
                 
    }

    public function consumerForumPost(Request $request) {
        
        $common=new CommanController;
        $forumId=isset($request->forumId)?($request->forumId):0;
        $fromuserId=isset($request->fromuserId)?($request->fromuserId):0;
        $touserId=isset($request->touserId)?($request->touserId):0;
        $content=isset($request->content)?($request->content):0;

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
                   $chatContent=DB::table('tblchatcontent')->insert(
                   ['chatSessionId'=>$chatSessionId,'customerId'=>$touserId,'createdate'=>date('Y-m-d H:i:s'),'consumerManagerId'=>$fromuserId,'content'=>$content,"senderdate"=>$createdate,"receiverdate"=>$createdate,"chatType"=>$insertType,"type"=>$type,"uniqueId"=>$uniqueId,"sentBy"=>$sentBy,'chatType'=>1]);
                   $flag=true;
            $message = $common->get_msg("sent_you_message")?$common->get_msg("sent_you_message"):"";  
            $message=str_replace('#username#',$Username,$message);
            echo $message;
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

          /*if ($isActive!='') {
              $sWhere.=" and customerconsumerforum.consumerForumType='".$isActive."'";
          }
*/
          $ConsumerForumCn=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.consumerForumType=1 ".$sWhere." order by customerconsumerforum.id desc"));

          $ConsumerForum=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.consumerForumType=1 ".$sWhere." order by customerconsumerforum.id desc $order"));

           $counTotal=count($ConsumerForumCn);
          
	          $output = array(
	          "recordsTotal" => $counTotal,
	          "recordsFiltered" => $counTotal,
	          "draw" => $draw,
	          "data" => array()
	          );

	          if ($ConsumerForum) {
                   $i=1;
                  foreach ($ConsumerForum as  $cust) {

                      $row = array();
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
                      	$statusName='Active';
                      } elseif ($status==2) {
                      	$statusName='Completed';
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

                      $link=url('/').'/admin/consumerForum/viewDetails/'.$id;
                      //$complete=url('/').'/admin/payout/completerequest/'.$id;
                      //$unleave=url('/').'/admin/missionmanagement/unleavemission/'.$missionId."/".$custmissionId;
                      $action='<a href="'.$link.'" class="btn btn-default btn-sm"  title="View Details" ><i class="icon-fa icon-fa-list"></i>View Details</a>';

                     


                      $row[]=$i;
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

           $sort='order by customerconsumerforum.id desc';

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
                         $statusName='Active';
                      } else if ($status==2) {
                         $statusName='Completed';
                      } else {
                        $statusName='';
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
          $where='and chatcontent.id > '.$lastchatId.'';
        }

      $sql=DB::select( DB::raw("Select chatcontent.id from tblchatcontent as chatcontent 
inner join tblchatsession as chatsession On chatcontent.chatSessionId=chatsession.chatSessionId
where  chatsession.forumId=".$id." ".$where."  order by chatcontent.id desc Limit 1"));
       $ChatList=isset($sql[0]->id)?($sql[0]->id):0;
        echo $ChatList;
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
       
       $sql=DB::select( DB::raw("Select chatcontent.* from tblchatcontent as chatcontent 
inner join tblchatsession as chatsession On chatcontent.chatSessionId=chatsession.chatSessionId
where  chatsession.forumId=".$id." ".$where."  order by chatcontent.id asc ".$ordervy.""));
       $date = date("Y-m-d H:i:s");
       $feedData = '';
       //$feedData .=  "<ul>";
       //$feedData .=  '<div class="chatbox"><div class="scrl">';
       if ($sql) {
        
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
            if ($sentBy==2) {
               $className='time-right';
            }
            $data='';

            if ($type==0) {
              $data=$content;
            }

            if ($type==1) {
             $data='<a href="'.$photo_url.'" target="_blank"><img src="'.$photo_url.'"></a>';
            }
            
              $hidden='';
              if ($i==$cn) {
                  $hidden='<input type="hidden" id="lastchatId" value="'.$chatId.'">';
              }

            $feedData .='<div class="container">
  <img src="https://www.w3schools.com/w3images/bandmember.jpg" alt="Avatar" style="width:100%;">
  <p>'.$data.'</p>
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

         /* if ($isActive!='') {
              $sWhere.=" and customerconsumerforum.consumerForumType='".$isActive."'";
          }*/

          $ConsumerForumCn=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.consumerForumType=2 ".$sWhere." order by customerconsumerforum.id desc"));

          $ConsumerForum=DB::select( DB::raw("Select customerconsumerforum.*,customer.fname,customer.lname,customer.email from tblcustomerconsumerforum as customerconsumerforum
             inner join tblcustomer as customer on customerconsumerforum.customerId=customer.id
             where customer.isActive=1 and customerconsumerforum.consumerForumType=2 ".$sWhere." order by customerconsumerforum.id desc $order"));

           $counTotal=count($ConsumerForumCn);
          
            $output = array(
            "recordsTotal" => $counTotal,
            "recordsFiltered" => $counTotal,
            "draw" => $draw,
            "data" => array()
            );

            if ($ConsumerForum) {
                   $i=1;
                  foreach ($ConsumerForum as  $cust) {

                      $row = array();
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
                        $statusName='Active';
                      } elseif ($status==2) {
                        $statusName='Completed';
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

                      $link=url('/').'/admin/ministryofhealth/viewDetails/'.$id;
                      //$complete=url('/').'/admin/payout/completerequest/'.$id;
                      //$unleave=url('/').'/admin/missionmanagement/unleavemission/'.$missionId."/".$custmissionId;
                      $action='<a href="'.$link.'" class="btn btn-default btn-sm"  title="View Details" ><i class="icon-fa icon-fa-list"></i>View Details</a>';

                     


                      $row[]=$i;
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


}
