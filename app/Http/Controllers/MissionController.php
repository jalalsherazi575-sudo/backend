<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\MissionRequest;
use Illuminate\Http\Request;
use Laraspace\Customer;
use Laraspace\Mission;
use Laraspace\MissionMilestone;
use Laraspace\MissionQuestionaire;
use Laraspace\LevelManagement;
use Laraspace\BusinessUsersBeacon;
use Laraspace\BusinessUsers;
use Image;
use Laraspace\BusinessCategory;
use Laraspace\Country;
use Laraspace\IdProofType;
use Laraspace\AreaOfInterest;
use Laraspace\SurveyQuestion;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Auth;
use PDF;
use Maatwebsite\Excel\Facades\Excel;

class MissionController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
		$missionmanagement = Mission::orderby('id','desc')->get();
       return view('admin.missionmanagement.index',compact('missionmanagement'));
	}

	public function getMissionData(Request $request) {

		 $sWhere="";

		 if (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != "") {
           $sSearch =trim($_REQUEST['search']['value']); 
           $sWhere .= "and ( `mission`.`missionName` LIKE '%".trim($sSearch) ."%' OR `businessusers`.companyName LIKE '%".trim($sSearch) ."%' OR  DATE_FORMAT(`mission`.`startDate`,'%d %M %Y') LIKE '%".trim($sSearch) ."%' OR  DATE_FORMAT(`mission`.`endDate`,'%d %M %Y') LIKE '%".trim($sSearch) ."%' OR  DATE_FORMAT(`mission`.`createdDate`,'%d %M %Y') LIKE '%".trim($sSearch) ."%'";
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

          $sort=' order by  mission.id desc';

          if ($orderbycolm==1) {
              $sort="order by  mission.missionName $orderbydir";
          }
          
          if ($orderbycolm==2) {
              $sort="order by  businessusers.companyName $orderbydir";
          }
          
          if ($orderbycolm==3) {
              $sort="order by  mission.startDate $orderbydir";
          }
          
          if ($orderbycolm==4) {
              $sort="order by   mission.endDate $orderbydir";
          }
          
          if ($orderbycolm==5) {
              $sort="order by  pendingmissionCount $orderbydir";
          }

          if ($orderbycolm==6) {
              $sort="order by  mission.createdDate $orderbydir";
          }

          if ($orderbycolm==7) {
              $sort="order by  mission.isActive $orderbydir";
          }

          $MissionCn=DB::select( DB::raw("Select mission.*,businessusers.companyName,(Select count(tblcustomermission.id) from tblcustomermission inner join tblcustomer as customer on  tblcustomermission.userId=customer.id where customer.isActive=1 and tblcustomermission.status IN (3) and tblcustomermission.missionId=mission.id) as pendingmissionCount  from tblmission as mission
             inner join tblbusinessusers as businessusers on mission.businessId=businessusers.id
             where 1=1 ".$sWhere." ".$sort.""));

          $Mission=DB::select( DB::raw("Select mission.*,businessusers.companyName,(Select count(tblcustomermission.id) from tblcustomermission inner join tblcustomer as customer on  tblcustomermission.userId=customer.id where customer.isActive=1 and tblcustomermission.status IN (3) and tblcustomermission.missionId=mission.id) as pendingmissionCount  from tblmission as mission
             inner join tblbusinessusers as businessusers on mission.businessId=businessusers.id
             where 1=1 ".$sWhere." ".$sort." ".$order.""));

           $counTotal=count($MissionCn);
          
	          $output = array(
	          "recordsTotal" => $counTotal,
	          "recordsFiltered" => $counTotal,
	          "draw" => $draw,
	          "data" => array()
	          );

	         if ($Mission) {
                   $i=1;
                   $totalUn=0;

                   $url=url('/');
                   $currentMenuId=$this->getcurrent();
                  
                  foreach ($Mission as  $cust) {

                      $row = array();

                      $id=($cust->id)?($cust->id):0;
                      $missionName=($cust->missionName)?($cust->missionName):"";
                      $companyName=($cust->companyName)?($cust->companyName):"";
                      $startDate=($cust->startDate)?($cust->startDate):"";
                      $endDate=($cust->endDate)?($cust->endDate):"";
                      $createdDate=($cust->createdDate)?($cust->createdDate):"";
                      $isActive=($cust->isActive)?($cust->isActive):"";
                      $expiredDate=($cust->expiredDate)?($cust->expiredDate):"";
                      $isreOpen=($cust->isreOpen)?($cust->isreOpen):0;
                      $pendingmissionCount=($cust->pendingmissionCount)?($cust->pendingmissionCount):0;
                      
                      $stDate='';
                      $enDate='';
                      $crDate='';

                      if ($startDate!='' && $startDate!='1970-01-01' && $startDate!='0000-00-00') {
                      	  $stDate=date("d F Y",strtotime($startDate));
                      }

                      if ($endDate!='' && $endDate!='1970-01-01' && $endDate!='0000-00-00') {
                      	  $enDate=date("d F Y",strtotime($endDate));
                      }

                      if ($createdDate!='' && $createdDate!='1970-01-01' && $createdDate!='0000-00-00') {
                      	  $crDate=date("d F Y",strtotime($createdDate));
                      }

                      if ($isActive==1) {
                      	  $st='Active';
                      } else if($isActive==0) {
                      	  $st='InActive';
                      } else if($isreOpen==1) {
                          $st='Re-Open';
                      } else {
                      	  $st='InActive';
                      }
                      
                      $action="";

                     

                      if(checkPermission(Auth::user()->id,'update',$currentMenuId)) {
                          $action.='<a href="'.$url.'/admin/missionmanagement/edit/'.$id.'" class="btn btn-default btn-sm" title="Edit Mission"><i class="icon-fa icon-fa-edit"></i>Edit</a>';
                      }

                      if(checkPermission(Auth::user()->id,'delete',$currentMenuId)) {
                          $action.='<a onclick="return check_delete('.$id.');" class="btn btn-default btn-sm" data-token="'.csrf_token().'" title="Delete Mission" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i>Delete</a>';
                      }

                      if(checkPermission(Auth::user()->id,'update',$currentMenuId)) {
                          
                          if ($isActive==1) {
                           $action.='<a href="'.$url.'/admin/missionmanagement/status/0/'.$id.'" class="btn btn-default btn-sm" title="Inactive Mission"><i class="icon-fa icon-fa-lock"></i>Inactive</a>';
                          } else {
                           $action.='<a href="'.$url.'/admin/missionmanagement/status/1/'.$id.'" class="btn btn-default btn-sm" title="Active Mission"><i class="icon-fa icon-fa-lock"></i>Active</a>';
                          }
                          
                          if ($expiredDate!='' && $isreOpen==0) {
                          	$action.='<a onclick="return check_reopen('.$id.');" class="btn btn-default btn-sm" data-token="'.csrf_token().'" title="ReOpen Mission" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i>ReOpen Mission</a>';
                          }

                          $action.='<a href="'.$url.'/admin/missionmanagement/summary/'.$id.'" class="btn btn-default btn-sm" title="View Mission"><i class="icon-fa icon-fa-lock"></i>View Details</a>';
                      
                          $action.='<a href="'.$url.'/admin/missionmanagement/cloneMission/'.$id.'" class="btn btn-default btn-sm" title="Clone Mission"><i class="icon-fa icon-fa-lock"></i>Clone Mission</a>';
                          
                      }



                      
                      $row[]='<input type="checkbox" class="uniquechk" name="del[]" value="'.$id.'">';
                      $row[]=$missionName;
                      $row[]=$companyName;
                      $row[]=$stDate;
                      $row[]=$enDate;
                      $row[]=$pendingmissionCount;
                      $row[]=$crDate;
                      $row[]=$st;
                      $row[]='<div class="dropdown">
                              <a class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                             <span></span><span></span><span></span></a>                       
                             <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                             '.$action.'
                             </div></div>';

                      $output['data'][] = $row;
                      $i++;

                  }

            }

            echo json_encode($output);
             exit();         

	}


	
	public function add() {
		$common=new CommanController;
		$businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1']])->get(['id','beaconName']);
		$businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
		$idprooftype=IdProofType::where([['isActive', '=','1']])->get();
		$areaofinterest=AreaOfInterest::where([['isActive', '=','1']])->get();
		$levelmanagement = LevelManagement::where([['isActive', '=','1']])->get(['id','name']);
		$multiple_image_note=$common->get_msg('multiple_image_note',1)?$common->get_msg('multiple_image_note',1):"Hold shift button for selecting multiple images.";
		$missionimagewidth=$common->getImageSizeValue('mission_image_width');
		$missionimageheight=$common->getImageSizeValue('mission_image_height');
		$missionlogowidth=$common->getImageSizeValue('mission_logo_width');
		$missionlogoheight=$common->getImageSizeValue('mission_logo_height');
		$customer = Customer::where('isActive',1)->orderby('email','asc')->get(['id','email']);
	   return view('admin.missionmanagement.addedit',compact('businessusers','idprooftype','areaofinterest','businessusersbeacon','levelmanagement','multiple_image_note','missionimagewidth','missionimageheight','missionlogowidth','missionlogoheight','customer'));
	}

	public function addnew() {
		$common=new CommanController;
		$businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1']])->get(['id','beaconName']);
		$businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
		$idprooftype=IdProofType::where([['isActive', '=','1']])->get();
		$areaofinterest=AreaOfInterest::where([['isActive', '=','1']])->get();
		$levelmanagement = LevelManagement::where([['isActive', '=','1']])->get(['id','name']);
		$multiple_image_note=$common->get_msg('multiple_image_note',1)?$common->get_msg('multiple_image_note',1):"Hold shift button for selecting multiple images.";
		$missionimagewidth=$common->getImageSizeValue('mission_image_width');
		$missionimageheight=$common->getImageSizeValue('mission_image_height');
	   return view('admin.missionmanagement.addedit2',compact('businessusers','idprooftype','areaofinterest','businessusersbeacon','levelmanagement','multiple_image_note','missionimagewidth','missionimageheight'));
	}

	public function MissionMilestoneQuestionAnswer($custmilestoneId) {

		$Questions=DB::select( DB::raw("Select pcs.id,pcs.questionId,pcs.questionType,pcs.comment,pcs.createdDate,surveyquestion.question from tblpostcustomersurvey as pcs  
                    inner join tblsurveyquestion as surveyquestion on  pcs.questionId=surveyquestion.id
                	where pcs.custmilestoneId=".$custmissionmilestoneId.""));

		  if ($Questions) {
	                	
	                	foreach($Questions as $que) {
	                          $surveyId=$que->id;

	                		$OptionList=DB::select( DB::raw("Select sqo.id,sqo.optionName from tblsurveyquestionoption as sqo  
	                    inner join tblpostcustomersurveyanswer as postcustomersurveyanswer on  sqo.id=postcustomersurveyanswer.optionId
	                	where postcustomersurveyanswer.surveyId=".$surveyId." and postcustomersurveyanswer.custmilestoneId=".$custmissionmilestoneId.""));
	                             $questionoptionlist=array();

	                            if ($OptionList) { 
	                                 foreach ($OptionList as $opslist) {
	                                   $questionoptionlist=array("optionId"=>$opslist->id,"optionName"=>$opslist->optionName);
	                                 }
	                            }
	                             

	                       $questionlist[]=array("question"=>$que->question,"questionId"=>$que->questionId,"questionType"=>$que->questionType,"comment"=>$que->comment,"createdDate"=>$que->createdDate,"questionoptionlist"=>$questionoptionlist);
	                            }
	                	}
	        return view('admin.missionmanagement.customermissionmilestonequestionanswer',compact('alldata','mission','id','userId','missionStatus'));        	

	}
    
    /* customer Survey Question Answer */

    public function customerSurveyQuestionaire($custmissionmilestoneId,$missionId,$customerId) {
         $common=new CommanController;
         $mission=$common->MissionDetails($missionId);
         $questionlist=array();

        /* echo "Select pcs.id,pcs.questionId,pcs.questionType,pcs.comment,pcs.createdDate,surveyquestion.question from tblpostcustomersurvey as pcs  
                    inner join tblsurveyquestion as surveyquestion on  pcs.questionId=surveyquestion.id
                	where pcs.custmilestoneId=".$custmissionmilestoneId."";
         exit;   */   
         $opsName=''; 	
    	 $Questions=DB::select( DB::raw("Select pcs.id,pcs.questionId,pcs.questionType,pcs.comment,pcs.createdDate,surveyquestion.question from tblpostcustomersurvey as pcs  
                    inner join tblsurveyquestion as surveyquestion on  pcs.questionId=surveyquestion.id
                	where pcs.custmilestoneId=".$custmissionmilestoneId.""));
            
               
              if ($Questions) {
	                	
	                	foreach($Questions as $que) {
	                		
	                          $surveyId=$que->id;


	                		$OptionList=DB::select( DB::raw("Select sqo.id,sqo.optionName from tblsurveyquestionoption as sqo  
	                    inner join tblpostcustomersurveyanswer as postcustomersurveyanswer on  sqo.id=postcustomersurveyanswer.optionId
	                	where postcustomersurveyanswer.surveyId=".$surveyId." and postcustomersurveyanswer.custmilestoneId=".$custmissionmilestoneId.""));
	                             $questionoptionlist=array();
                                 $opsName='';
	                            if ($OptionList) { 
	                                 foreach ($OptionList as $opslist) {
	                                   $questionoptionlist=array("optionId"=>$opslist->id,"optionName"=>$opslist->optionName);
	                                  $opsName.=$opslist->optionName.",";
	                                 }
	                            }
                                
                                if ($opsName!='') {
                                	$opsName=substr($opsName,0,-1);
                                }
	                            
	                             

	                       $questionlist[]=array("question"=>$que->question,"questionId"=>$que->questionId,"questionType"=>$que->questionType,"comment"=>$que->comment,"createdDate"=>$que->createdDate,"questionoptionlist"=>$questionoptionlist,'opsName'=>$opsName);
	                            }
	                            
	               }

	               

	       return view('admin.missionmanagement.customerquestionaire',compact('questionlist','mission','custmissionmilestoneId','customerId'));        
    }

    
    public function getCustomerEnrollmentsDetail($missionId,$id,$customerId,$encustmissionmilestoneId) {

        
        $userId=$customerId;
        $common=new CommanController;
        $mission=$common->MissionDetails($missionId);
        $customerDetails=$common->CustomerDetails($customerId);

    	$Sql=DB::select( DB::raw("Select tcmm.id as custmissionmilestoneId,tcmm.customerId,tcmm.milestoneId,tcmm.submitDate,missionmilestone.title,missionmilestone.type,customer.fname,customer.lname from tblcustomermissionmilestone as tcmm
    	 inner join tblcustomer as customer	On tcmm.customerId=customer.id
         inner join tblmissionmilestone as missionmilestone on  tcmm.milestoneId=missionmilestone.id
         where tcmm.status=1 and tcmm.milestoneId=".$id." and tcmm.customerId=".$customerId.""));
        $alldata=array();
        if ($Sql) {
        	foreach($Sql as $rows) {
        		$custmissionmilestoneId=isset($rows->custmissionmilestoneId)?($rows->custmissionmilestoneId):0;
        		$customerId=isset($rows->customerId)?($rows->customerId):0;
        		$milestoneId=isset($rows->milestoneId)?($rows->milestoneId):0;
        		$submitDate=isset($rows->submitDate)?($rows->submitDate):"";
        		$title=isset($rows->title)?($rows->title):"";
        		$type=isset($rows->type)?($rows->type):0;
        		$fname=isset($rows->fname)?($rows->fname):"";
        		$lname=isset($rows->lname)?($rows->lname):"";

        		$milestoneImages=$common->mileStoneCustomerImageList($milestoneId,$customerId);
                
                $Questions=DB::select( DB::raw("Select pcs.id,pcs.questionId,pcs.questionType,pcs.comment,pcs.createdDate,surveyquestion.question from tblpostcustomersurvey as pcs  
                    inner join tblsurveyquestion as surveyquestion on  pcs.questionId=surveyquestion.id
                	where pcs.custmilestoneId=".$custmissionmilestoneId.""));
                $questionlist=array();

                 if ($type==2) {
	                
	                if ($Questions) {
	                	
	                	foreach($Questions as $que) {
	                          $surveyId=$que->id;

	                		$OptionList=DB::select( DB::raw("Select sqo.id,sqo.optionName from tblsurveyquestionoption as sqo  
	                    inner join tblpostcustomersurveyanswer as postcustomersurveyanswer on  sqo.id=postcustomersurveyanswer.optionId
	                	where postcustomersurveyanswer.surveyId=".$surveyId." and postcustomersurveyanswer.custmilestoneId=".$custmissionmilestoneId.""));
	                             $questionoptionlist=array();

	                            if ($OptionList) { 
	                                 foreach ($OptionList as $opslist) {
	                                   $questionoptionlist=array("optionId"=>$opslist->id,"optionName"=>$opslist->optionName);
	                                 }
	                            }
	                             

	                       $questionlist[]=array("question"=>$que->question,"questionId"=>$que->questionId,"questionType"=>$que->questionType,"comment"=>$que->comment,"createdDate"=>$que->createdDate,"questionoptionlist"=>$questionoptionlist);
	                            }
	                	}
	              }	
                


        		$alldata[]=array("custmissionmilestoneId"=>$custmissionmilestoneId,"milestoneId"=>$milestoneId,"submitDate"=>$submitDate,"title"=>$title,"type"=>$type,"milestoneImages"=>$milestoneImages,"questionlist"=>$questionlist,"customerId"=>$customerId,"fname"=>$fname,"lname"=>$lname);
        	}
        }
         

        // print_r($alldata);
         //exit();
         return view('admin.missionmanagement.customerenrolldetail',compact('alldata','mission','id','userId','customerDetails','encustmissionmilestoneId'));
        

    }

    public function exportMilestones($missionId,$id) {
        
        //$userId=$customerId;
        $common=new CommanController;
        $mission=$common->MissionDetails($missionId);
        //$customerDetails=$common->CustomerDetails($customerId);
        
       //$customerName=$customerDetails["fname"]." ".$customerDetails["lname"];
        //$customerEmail=$customerDetails["email"];
        $missionName=$mission['missionName'];

        $fileName=$missionName." Mission Milesstone List";

        $type="csv";
      
      $Sql=DB::select( DB::raw("Select tcmm.id as custmissionmilestoneId,tcmm.customerId,tcmm.milestoneId,tcmm.submitDate,missionmilestone.title,missionmilestone.type,customer.fname,customer.lname,customer.email,tcmm.latitude,tcmm.longitude from tblcustomermissionmilestone as tcmm
       inner join tblcustomer as customer On tcmm.customerId=customer.id
         inner join tblmissionmilestone as missionmilestone on  tcmm.milestoneId=missionmilestone.id
         where tcmm.status=1 and tcmm.milestoneId=".$id.""));
     
     return Excel::create($fileName, function($excel) use ($common,$Sql,$missionName) {
            $excel->sheet('mySheet', function($sheet) use ($common,$Sql,$missionName)
            {

              $sheet->cell('A1', function($cell) {$cell->setValue('Mission Name');   });
                $sheet->cell('B1', function($cell) {$cell->setValue('Milestone Name');   });
                $sheet->cell('C1', function($cell) {$cell->setValue('Milestone Type');   });
                $sheet->cell('D1', function($cell) {$cell->setValue('Milestone Submit Date');   });
                $sheet->cell('E1', function($cell) {$cell->setValue('Milestone Images');   });
                $sheet->cell('F1', function($cell) {$cell->setValue('Milestone Questionaire');   });
                $sheet->cell('G1', function($cell) {$cell->setValue('Milestone Submitted Location');   });
                $sheet->cell('H1', function($cell) {$cell->setValue('Customer Name');   });
                $sheet->cell('I1', function($cell) {$cell->setValue('Customer Email');   });
               

                

                if ($Sql) {
                  $i=2;

                  foreach($Sql as $rows) {

                        $custmissionmilestoneId=isset($rows->custmissionmilestoneId)?($rows->custmissionmilestoneId):0;
                        $customerId=isset($rows->customerId)?($rows->customerId):0;
                        $milestoneId=isset($rows->milestoneId)?($rows->milestoneId):0;
                        $submitDate=isset($rows->submitDate)?(date("d/m/Y H:i:s",strtotime($rows->submitDate))):"";
                        $title=isset($rows->title)?($rows->title):"";
                        $type=isset($rows->type)?($rows->type):0;
                        $fname=isset($rows->fname)?($rows->fname):"";
                        $lname=isset($rows->lname)?($rows->lname):"";
                        $email=isset($rows->email)?($rows->email):"";
                        $latitude=isset($rows->latitude)?($rows->latitude):"";
                        $longitude=isset($rows->longitude)?($rows->longitude):"";
                        $milestoneImages=$common->mileStoneCustomerImageList($milestoneId,$customerId);
                        
                        $customerName=$fname." ".$lname;

                        $locationlink='';

                        if ($latitude!='' && $longitude!='') {
                            $locationlink='https://www.google.co.in/maps/place/'.$latitude.','.$longitude;  
                        }
                        
                        $typeName='';

                        if ($type==1) {
                          $typeName='Milestone';
                        }

                        if ($type==2) {
                          $typeName='Questionaire';
                        }
                        
                        $images='';
                        
                        if ($milestoneImages) {
                            
                            foreach($milestoneImages as $vals) {
                               $images.=$vals['fileName']."\n";
                               $images .= "\n";
                            } 
                        }


                         $Questions=DB::select( DB::raw("Select pcs.id,pcs.questionId,pcs.questionType,pcs.comment,pcs.createdDate,surveyquestion.question from tblpostcustomersurvey as pcs  
                    inner join tblsurveyquestion as surveyquestion on  pcs.questionId=surveyquestion.id
                  where pcs.custmilestoneId=".$custmissionmilestoneId.""));
                             
                             $questionlist=array();

                       if ($type==2) {
                        
                        if ($Questions) {
                          
                          foreach($Questions as $que) {
                                  $surveyId=$que->id;

                            $OptionList=DB::select( DB::raw("Select sqo.id,sqo.optionName from tblsurveyquestionoption as sqo  
                            inner join tblpostcustomersurveyanswer as postcustomersurveyanswer on  sqo.id=postcustomersurveyanswer.optionId
                          where postcustomersurveyanswer.surveyId=".$surveyId." and postcustomersurveyanswer.custmilestoneId=".$custmissionmilestoneId.""));
                                     $questionoptionlist=array();
                                             
                                             $ops='';

                                    if ($OptionList) { 
                                         foreach ($OptionList as $opslist) {
                                           $questionoptionlist=array("optionId"=>$opslist->id,"optionName"=>$opslist->optionName);
                                           $ops.=$opslist->optionName.",";
                                         }

                                         if ($ops!='') {
                                          $ops=substr($ops,0,-1);
                                         }
                                    }
                                     

                               $questionlist[]=array("question"=>$que->question,"questionId"=>$que->questionId,"questionType"=>$que->questionType,"comment"=>$que->comment,"createdDate"=>$que->createdDate,"questionoptionlist"=>$questionoptionlist,'optionName'=>$ops);
                                    }
                          }
                      } 

                        $subject='';
                                
                                $content= "";

                                if ($questionlist) {
                                     
                                     $content= "\n";
                                     
                                     foreach($questionlist as $rows) {
                                          
                                          $postDate=date("d/m/Y H:i:s",strtotime($rows['createdDate']));

                                        $content .="Question :  ".$rows['question']."\n";
                                        
                                        if ($rows['questionType']==1) {
                                           $content .="Answer :  ".$rows['comment']."\n";
                                        } else {
                                           $content .="Answer :  ".$rows['optionName']."\n";
                                        }

                                        $content .="Post Date :  ".$postDate."\n";
                                          
                                          $content .= "\n";
                                     }

                                }


                            $sheet->cell('A'.$i, $missionName);
                            $sheet->cell('B'.$i, $title);
                            $sheet->cell('C'.$i, $typeName);
                            $sheet->cell('D'.$i, $submitDate);
                            $sheet->cell('E'.$i, $images);
                            $sheet->cell('F'.$i, $content);
                            $sheet->cell('G'.$i, $locationlink);
                            $sheet->cell('H'.$i, $customerName);
                            $sheet->cell('I'.$i, $email);
                            

                    $i++;
                  }
               }   

            }); 
         
         })->download($type); 


        
         
        

    }    

     public function exportMissionMilestone($id,$missionId,$customerId) {
        
        $userId=$customerId;
        $common=new CommanController;
        $mission=$common->MissionDetails($missionId);
        $customerDetails=$common->CustomerDetails($customerId);
        
        $customerName=$customerDetails["fname"]." ".$customerDetails["lname"];
        $customerEmail=$customerDetails["email"];
        $missionName=$mission['missionName'];

        $fileName=$missionName." Mission Milesstone List of ".$customerName;

        $type="csv";
    	
    	$Sql=DB::select( DB::raw("Select tcmm.id as custmissionmilestoneId,tcmm.customerId,tcmm.milestoneId,tcmm.submitDate,missionmilestone.title,missionmilestone.type,customer.fname,customer.lname,tcmm.latitude,tcmm.longitude from tblcustomermissionmilestone as tcmm
    	 inner join tblcustomer as customer	On tcmm.customerId=customer.id
         inner join tblmissionmilestone as missionmilestone on  tcmm.milestoneId=missionmilestone.id
         where tcmm.status=1 and tcmm.custmissionId=".$id.""));
     
     return Excel::create($fileName, function($excel) use ($customerName,$common,$Sql,$missionName,$customerEmail) {
            $excel->sheet('mySheet', function($sheet) use ($customerName,$common,$Sql,$missionName,$customerEmail)
            {

            	$sheet->cell('A1', function($cell) {$cell->setValue('Mission Name');   });
                $sheet->cell('B1', function($cell) {$cell->setValue('Milestone Name');   });
                $sheet->cell('C1', function($cell) {$cell->setValue('Milestone Type');   });
                $sheet->cell('D1', function($cell) {$cell->setValue('Milestone Submit Date');   });
                $sheet->cell('E1', function($cell) {$cell->setValue('Milestone Images');   });
                $sheet->cell('F1', function($cell) {$cell->setValue('Milestone Questionaire');   });
                $sheet->cell('G1', function($cell) {$cell->setValue('Milestone Submitted Location');   });
                $sheet->cell('H1', function($cell) {$cell->setValue('Customer Name');   });
                $sheet->cell('I1', function($cell) {$cell->setValue('Customer Email');   });
               

                

                if ($Sql) {
                	$i=2;

        	        foreach($Sql as $rows) {

        	        	    $custmissionmilestoneId=isset($rows->custmissionmilestoneId)?($rows->custmissionmilestoneId):0;
        		        		$customerId=isset($rows->customerId)?($rows->customerId):0;
        		        		$milestoneId=isset($rows->milestoneId)?($rows->milestoneId):0;
        		        		$submitDate=isset($rows->submitDate)?(date("d/m/Y H:i:s",strtotime($rows->submitDate))):"";
        		        		$title=isset($rows->title)?($rows->title):"";
        		        		$type=isset($rows->type)?($rows->type):0;
        		        		$fname=isset($rows->fname)?($rows->fname):"";
        		        		$lname=isset($rows->lname)?($rows->lname):"";
        		        		$latitude=isset($rows->latitude)?($rows->latitude):"";
        		        		$longitude=isset($rows->longitude)?($rows->longitude):"";
                        $milestoneImages=$common->mileStoneCustomerImageList($milestoneId,$customerId);
                        
                        $locationlink='';

                        if ($latitude!='' && $longitude!='') {
                            $locationlink='https://www.google.co.in/maps/place/'.$latitude.','.$longitude;  
                        }
                        
                        $typeName='';

                        if ($type==1) {
                        	$typeName='Milestone';
                        }

                        if ($type==2) {
                        	$typeName='Questionaire';
                        }
                        
                        $images='';
                        
                        if ($milestoneImages) {
                            
                            foreach($milestoneImages as $vals) {
                               $images.=$vals['fileName']."\n";
                               $images .= "\n";
                            } 
                        }


                         $Questions=DB::select( DB::raw("Select pcs.id,pcs.questionId,pcs.questionType,pcs.comment,pcs.createdDate,surveyquestion.question from tblpostcustomersurvey as pcs  
                    inner join tblsurveyquestion as surveyquestion on  pcs.questionId=surveyquestion.id
                	where pcs.custmilestoneId=".$custmissionmilestoneId.""));
                             
                             $questionlist=array();

			                 if ($type==2) {
				                
				                if ($Questions) {
				                	
				                	foreach($Questions as $que) {
				                          $surveyId=$que->id;

				                		$OptionList=DB::select( DB::raw("Select sqo.id,sqo.optionName from tblsurveyquestionoption as sqo  
				                    inner join tblpostcustomersurveyanswer as postcustomersurveyanswer on  sqo.id=postcustomersurveyanswer.optionId
				                	where postcustomersurveyanswer.surveyId=".$surveyId." and postcustomersurveyanswer.custmilestoneId=".$custmissionmilestoneId.""));
				                             $questionoptionlist=array();
                                             
                                             $ops='';

				                            if ($OptionList) { 
				                                 foreach ($OptionList as $opslist) {
				                                   $questionoptionlist=array("optionId"=>$opslist->id,"optionName"=>$opslist->optionName);
				                                   $ops.=$opslist->optionName.",";
				                                 }

				                                 if ($ops!='') {
				                                 	$ops=substr($ops,0,-1);
				                                 }
				                            }
				                             

				                       $questionlist[]=array("question"=>$que->question,"questionId"=>$que->questionId,"questionType"=>$que->questionType,"comment"=>$que->comment,"createdDate"=>$que->createdDate,"questionoptionlist"=>$questionoptionlist,'optionName'=>$ops);
				                            }
				                	}
				              }	

				                $subject='';
                                
                                $content= "";

                                if ($questionlist) {
                                     
                                     $content= "\n";
                                     
                                     foreach($questionlist as $rows) {
                                          
                                          $postDate=date("d/m/Y H:i:s",strtotime($rows['createdDate']));

                                     	  $content .="Question :  ".$rows['question']."\n";
                                     	  
                                     	  if ($rows['questionType']==1) {
                                           $content .="Answer :  ".$rows['comment']."\n";
                                     	  } else {
                                           $content .="Answer :  ".$rows['optionName']."\n";
                                     	  }

                                     	  $content .="Post Date :  ".$postDate."\n";
                                          
                                          $content .= "\n";
                                     }

                                }


				                $sheet->cell('A'.$i, $missionName);
		                        $sheet->cell('B'.$i, $title);
		                        $sheet->cell('C'.$i, $typeName);
		                        $sheet->cell('D'.$i, $submitDate);
		                        $sheet->cell('E'.$i, $images);
		                        $sheet->cell('F'.$i, $content);
		                        $sheet->cell('G'.$i, $locationlink);
		                        $sheet->cell('H'.$i, $customerName);
		                        $sheet->cell('I'.$i, $customerEmail);
		                        

        	          $i++;
        	        }
        	     }   

            });	
         
         })->download($type); 


        
         
        

    }    

    public function missionSubmitDetails($id,$missionId,$customerId,$missionStatus) {
        
        $userId=$customerId;
        $common=new CommanController;
        $mission=$common->MissionDetails($missionId);
        $customerDetails=$common->CustomerDetails($customerId);

    	$Sql=DB::select( DB::raw("Select tcmm.id as custmissionmilestoneId,tcmm.customerId,tcmm.milestoneId,tcmm.submitDate,missionmilestone.title,missionmilestone.type,customer.fname,customer.lname,tcmm.latitude,tcmm.longitude from tblcustomermissionmilestone as tcmm
    	 inner join tblcustomer as customer	On tcmm.customerId=customer.id
         inner join tblmissionmilestone as missionmilestone on  tcmm.milestoneId=missionmilestone.id
         where tcmm.status=1 and tcmm.custmissionId=".$id.""));
        $alldata=array();
        if ($Sql) {
        	foreach($Sql as $rows) {
        		$custmissionmilestoneId=isset($rows->custmissionmilestoneId)?($rows->custmissionmilestoneId):0;
        		$customerId=isset($rows->customerId)?($rows->customerId):0;
        		$milestoneId=isset($rows->milestoneId)?($rows->milestoneId):0;
        		$submitDate=isset($rows->submitDate)?($rows->submitDate):"";
        		$title=isset($rows->title)?($rows->title):"";
        		$type=isset($rows->type)?($rows->type):0;
        		$fname=isset($rows->fname)?($rows->fname):"";
        		$lname=isset($rows->lname)?($rows->lname):"";
        		$latitude=isset($rows->latitude)?($rows->latitude):"";
        		$longitude=isset($rows->longitude)?($rows->longitude):"";

        		$milestoneImages=$common->mileStoneCustomerImageList($milestoneId,$customerId);
                
                $Questions=DB::select( DB::raw("Select pcs.id,pcs.questionId,pcs.questionType,pcs.comment,pcs.createdDate,surveyquestion.question from tblpostcustomersurvey as pcs  
                    inner join tblsurveyquestion as surveyquestion on  pcs.questionId=surveyquestion.id
                	where pcs.custmilestoneId=".$custmissionmilestoneId.""));
                $questionlist=array();

                 if ($type==2) {
	                
	                if ($Questions) {
	                	
	                	foreach($Questions as $que) {
	                          $surveyId=$que->id;

	                		$OptionList=DB::select( DB::raw("Select sqo.id,sqo.optionName from tblsurveyquestionoption as sqo  
	                    inner join tblpostcustomersurveyanswer as postcustomersurveyanswer on  sqo.id=postcustomersurveyanswer.optionId
	                	where postcustomersurveyanswer.surveyId=".$surveyId." and postcustomersurveyanswer.custmilestoneId=".$custmissionmilestoneId.""));
	                             $questionoptionlist=array();

	                            if ($OptionList) { 
	                                 foreach ($OptionList as $opslist) {
	                                   $questionoptionlist=array("optionId"=>$opslist->id,"optionName"=>$opslist->optionName);
	                                 }
	                            }
	                             

	                       $questionlist[]=array("question"=>$que->question,"questionId"=>$que->questionId,"questionType"=>$que->questionType,"comment"=>$que->comment,"createdDate"=>$que->createdDate,"questionoptionlist"=>$questionoptionlist);
	                            }
	                	}
	              }	
                


        		$alldata[]=array("custmissionmilestoneId"=>$custmissionmilestoneId,"milestoneId"=>$milestoneId,"submitDate"=>$submitDate,"title"=>$title,"type"=>$type,"milestoneImages"=>$milestoneImages,"questionlist"=>$questionlist,"customerId"=>$customerId,"fname"=>$fname,"lname"=>$lname,'latitude'=>$latitude,'longitude'=>$longitude);
        	}
        }
         
        // print_r($alldata);
         //exit();
         return view('admin.missionmanagement.pendingmission',compact('alldata','mission','id','userId','missionStatus','customerDetails'));
        

    }

	public function cloneMission($id) {
	 	  

	 	  $mission = Mission::find($id);
	 	  $MissionName=$mission->missionName;
          
          $match=strstr($MissionName,"copy");

	 	  if ($match) {
	 	  	  $firstchar=substr($match,-1);
              $inc=$firstchar+1;
              //$str=substr()
	 	  	  $copytext='copy'.$inc;
              $new=0;
	 	  	  $misName=str_replace($match,$copytext,$MissionName);
	 	  } else {
	 	  	  $new=1;
	 	  	  $copytext='copy1';
	 	  	  $misName=$MissionName." ".$copytext;
	 	  }

	 	  $cn= DB::table('tblmission')->where('missionName',$misName)->count();

          if ($cn > 0) {

          	   if ($new==0) {
          	     $lastMissionName=substr($MissionName,0,-6);	
          	   } else {
          	   	 $lastMissionName=$MissionName;
          	   } 
          	   
          	   
               $mistext=$lastMissionName." "."copy";
               //echo $mistext;

                 /*echo "select missionName from tblmission where missionName like '%".$mistext."%' order by id desc limit 1";
          	   exit;*/
          	   $MissionNames=DB::select( DB::raw("select missionName from tblmission where missionName like '%".$mistext."%' order by id desc limit 1"));
               $latestMis=isset($MissionNames[0]->missionName)?($MissionNames[0]->missionName):$misName;
               $substring=substr($latestMis,-1);
               $lastInc=$substring+1;
               $string=substr($latestMis,0,-1);
               $misName=$string.$lastInc;

          } 


	 	$cloneMission=DB::select( DB::raw("INSERT INTO tblmission (missionName,businessId,startDate,endDate,startTime,endTime,quotaLimit,durationOfMisson,title,description,vicinityInMiles,location,latitude,longitude,house_flatNo,landmark,rewardDescription,eligibiltyCriteria,pushMissionBeacon,footfallCalcBeacon,pushMissionGps,ageFrom,ageTo,isMale,isVerified,isUnverified,isFemale,isActive,countryId,locality,city,state,country,estimationTime,createdDate)
SELECT '$misName',businessId,Now(),Now(),startTime,endTime,quotaLimit,durationOfMisson,title,description,vicinityInMiles,location,latitude,longitude,house_flatNo,landmark,rewardDescription,eligibiltyCriteria,pushMissionBeacon,footfallCalcBeacon,pushMissionGps,ageFrom,ageTo,isMale,isVerified,isUnverified,isFemale,isActive,countryId,locality,city,state,country,estimationTime,Now() FROM tblmission
  WHERE id ='$id'"));

	 	$lastMission= DB::table('tblmission')->orderby('id','desc')->first();
	 	$lastMissionId=$lastMission->id;

	 	$cloneInterest=DB::select( DB::raw("INSERT INTO tblmissiontargetareaofinterest (missionId,interestId,createdDate)
SELECT $lastMissionId,interestId,Now()  FROM tblmissiontargetareaofinterest
  WHERE missionId ='$id'"));

	 	$cloneTarget=DB::select( DB::raw("INSERT INTO tblmissiontargetlevel (missionId,levelId,createdDate)
SELECT $lastMissionId,levelId,Now()  FROM tblmissiontargetlevel
  WHERE missionId ='$id'"));

	 	$cloneMilestone=DB::select( DB::raw("INSERT INTO tblmissionmilestone (missionId,title,description,isImageRequired,IsValidateByGps,IsValidateByBeacon,beaconId,vicinityInMiles,location,latitude,longitude,house_flatNo,landmark,isActive,countryId,city,state,country,type,createdDate)
SELECT $lastMissionId,title,description,isImageRequired,IsValidateByGps,IsValidateByBeacon,beaconId,vicinityInMiles,location,latitude,longitude,house_flatNo,landmark,isActive,countryId,city,state,country,type,Now()  FROM tblmissionmilestone
  WHERE missionId ='$id'"));

	 	$getSurveyQ= DB::table('tblsurveyquestion')->where('missionId',$id)->get();
         
         if ($getSurveyQ) {

         	 foreach ($getSurveyQ as $QueAns) {
                  $quesId=($QueAns->id)?($QueAns->id):0;
                  $cloneSurveyQuestion=DB::select( DB::raw("INSERT INTO tblsurveyquestion (missionId,question,answerType,sortOrder,maxNumberOfOption,dependAnswerQuestion,pipedQuestionId,isMandotarytoAnswer,isConditionalBranchingLogic,questionaireId,questionImage,isActive,dependentQuestionId,createdDate)
SELECT $lastMissionId,question,answerType,`sortOrder`+1,maxNumberOfOption,dependAnswerQuestion,pipedQuestionId,isMandotarytoAnswer,isConditionalBranchingLogic,questionaireId,questionImage,isActive,dependentQuestionId,Now()  FROM tblsurveyquestion
  WHERE id='$quesId'"));

                  $lastQuest= DB::table('tblsurveyquestion')->orderby('id','desc')->first();
	 	          $lastQuestionId=$lastMission->id;

	 	          $cloneDepedent=DB::select( DB::raw("INSERT INTO tblsurveyquestiondepenedoption (parentquestionId,subquestionId,optionId,optionName,createdDate)
SELECT $lastQuestionId,subquestionId,optionId,optionName,Now()  FROM tblsurveyquestiondepenedoption WHERE parentquestionId ='$quesId'"));

	 	            $cloneOption=DB::select( DB::raw("INSERT INTO tblsurveyquestionoption (questionId,optionName,sortOrder,createdDate)
SELECT $lastQuestionId,optionName,'`sortOrder`+1',Now()  FROM tblsurveyquestionoption WHERE questionId ='$quesId'"));

         	 }
         }
          
          flash()->success(''.$MissionName.' Mission  has been successfully cloned.');
			 return redirect()->to('/admin/missionmanagement');

	 }
	
	 public function postCreate(MissionRequest $request) {
		 
		 $common=new CommanController;
		 
		 $countemail = DB::table('tblmission')->where('missionName', '=', $request->missionName)->count();

		 $house=($request->house)?($request->house):"";
		 $landmark=($request->landmark)?($request->landmark):"";
		 $search_location=($request->search_location)?($request->search_location):"";
		 $lat=($request->lat)?($request->lat):0;
		 $long=($request->long)?($request->long):0;
		 $vicinityInMiles=($request->vicinityInMiles)?($request->vicinityInMiles):0;

		 $country=($request->country)?($request->country):"";
		 $state=($request->state)?($request->state):"";
		 $city=($request->city)?($request->city):"";
		 
		 $customNotificationMessage = isset($request->customNotificationMessage)?(ltrim($request->customNotificationMessage)):"";
         
     $missionName = isset($request->missionName)?(ltrim($request->missionName)):"";

		 if ($countemail==0) {
             
             $proofTypeId=isset($request->proofTypeId)?(($request->proofTypeId)):0;
             $interestId=isset($request->interestId)?(($request->interestId)):0;
             $levelId=isset($request->levelId)?(($request->levelId)):0;
             $email=isset($request->email)?(($request->email)):"";

      			 $mission = new Mission();
      			 $mission->missionName = $missionName;
      			 $mission->businessId = isset($request->businessId)?(ltrim($request->businessId)):0;
      			 $mission->startDate = isset($request->startDate)?(ltrim(date("Y-m-d",strtotime($request->startDate)))):"";

			       $mission->startTime = isset($request->startTime)?(ltrim($request->startTime)):"";
             $mission->endTime = isset($request->endTime)?(ltrim($request->endTime)):"";


			       $mission->endDate = isset($request->endDate)?(ltrim(date("Y-m-d",strtotime($request->endDate)))):"";
			       $mission->quotaLimit = isset($request->quotaLimit)?(ltrim($request->quotaLimit)):0;
             $mission->durationOfMisson = isset($request->durationOfMisson)?(ltrim($request->durationOfMisson)):0;
             $mission->title = isset($request->title)?(ltrim($request->title)):"";
             $mission->description = isset($request->description)?(ltrim($request->description)):"";
             $mission->estimationTime = isset($request->estimationTime)?(ltrim($request->estimationTime)):"";

      			 $mission->rewardDescription = isset($request->rewardDescription)?(ltrim($request->rewardDescription)):"";
      			 $mission->eligibiltyCriteria = isset($request->eligibiltyCriteria)?(ltrim($request->eligibiltyCriteria)):"";
      			 $mission->cashReward = isset($request->cashReward)?(ltrim($request->cashReward)):0;
      			 $mission->points = isset($request->points)?(ltrim($request->points)):0;

      			 $mission->pushMissionBeacon = isset($request->pushMissionBeacon)?(ltrim($request->pushMissionBeacon)):0;
      			 $mission->footfallCalcBeacon = isset($request->footfallCalcBeacon)?(ltrim($request->footfallCalcBeacon)):0;
      			 $mission->pushMissionGps = isset($request->pushMissionGps)?(ltrim($request->pushMissionGps)):0;

      			 $mission->ageFrom = isset($request->ageFrom)?(ltrim($request->ageFrom)):0;
      			 $mission->ageTo = isset($request->ageTo)?(ltrim($request->ageTo)):0;
      			 $mission->isMale = isset($request->isMale)?(ltrim($request->isMale)):0;
      			 $mission->isFemale = isset($request->isFemale)?(ltrim($request->isFemale)):0;
      			 $mission->isVerified = isset($request->isVerified)?(ltrim($request->isVerified)):0;
      			 $mission->isUnverified = isset($request->isUnverified)?(ltrim($request->isUnverified)):0;
      			 
      			 $mission->customNotificationMessage = $customNotificationMessage;
      			 
      			
             $mission->vicinityInMiles=$vicinityInMiles;
      			 $mission->location=$search_location;
      			 $mission->latitude=$lat;
      			 $mission->longitude=$long;
      			 $mission->house_flatNo=$house;
      			 $mission->landmark=$landmark;
      			 $mission->country=$country;
      			 $mission->state=$state;
      			 $mission->city=$city;
                   
                   
                   
                   
      			
      			 $mission->createdDate=date('Y-m-d H:i:s');

      			 $mission->modifiedDate=date('Y-m-d H:i:s');
      			 
      			 $mission->isActive=$request->isActive;

      			 if ($request->file('logo')) {

        				 $file = $request->file('logo');
        				 $size = getimagesize($file);
        				 $ratio = $size[0]/$size[1];
        				 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

        				 $common=new CommanController;
        				 $businesswidth=$common->getImageSizeValue('mission_logo_width');
        				 $businessheight=$common->getImageSizeValue('mission_logo_height');
        				 if( $ratio > 1) {
        					$width = $businesswidth;
        					$height = $businessheight/$ratio;
        				 }
        				 else {
        					$width = $businesswidth;
        					$height = $businessheight;
        				 }
        				 
        				 $extension = strtolower($file->getClientOriginalExtension());

        				 $category_image=time().$file->getClientOriginalName();
        				 $category_destinationPath = public_path('/missionlogo/thumbnail_images');
        				//$thumb_img = Image::make($file->getRealPath())->resize(150, 150);
        				 $check=in_array($extension,$allowedfileExtension);

          				 if($check) {

          				 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
          				 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
          				 $filename=$file->getClientOriginalName();
          				 $destinationPath = 'missionlogo';
          				 $file->move($destinationPath,$file->getClientOriginalName());
          				 $mission->logo=$category_image;

          				}

      			 }



      			 $mission->save();
      			 $missionId=$mission->id;

      			 if ($email!='') {
      			   foreach ($email as  $value) {
      			   	$custDetailId= DB::table('tblcustomer')->where('email', '=',$value)->first(['id']);
      			   	$custId=isset($custDetailId->id)?($custDetailId->id):0;

      				 DB::table('tblmissiontargetemailaddress')->insert(
                        ['missionId'=>$missionId,'email'=>$value,'createdDate'=>date('Y-m-d H:i:s'),'customerId'=>$custId]);
      			   }
      			 }

      			 if ($interestId!=0) {
      			   foreach ($interestId as  $value) {
      				 DB::table('tblmissiontargetareaofinterest')->insert(
                        ['missionId'=>$missionId,'interestId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
      			   }
      			 }

      			 if ($levelId!=0) {
      			   foreach ($levelId as  $value) {
      				 DB::table('tblmissiontargetlevel')->insert(
                        ['missionId'=>$missionId,'levelId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
      			   }
      			 }

			 

						 
                

						 /*if($request->hasFile('photo')) {
								   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
								   $files = $request->file('photo');
								    
								    foreach($files as $file) {
									 $rand=rand(10,1000);
			                         $extension = strtolower($file->getClientOriginalExtension());
									 $filename = $rand.time().".".$extension;
			                         
			                         $check=in_array($extension,$allowedfileExtension);
										 if($check) {
										   $destinationPath = 'missionimages';
				                           $file->move($destinationPath,$filename); 
										   $isUploaded=1;
			                                 DB::table('tblmissionimages')->insert(
			                  ['missionId'=>$missionId,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$filename]);

										 }
								   }
						}*/

						if($request->hasFile('photo')) {
              
              $common=new CommanController;
							$allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
							$files = $request->file('photo');
							
                  foreach($files as $file) {
                         
                         $files =$file;
                         $size = getimagesize($files);
                         $ratio = $size[0]/$size[1];

    						       $missionimagewidth=$common->getImageSizeValue('mission_image_width');
    		               $missionimageheight=$common->getImageSizeValue('mission_image_height'); 
    						       
    						        if( $ratio > 1) {
        									$width = $missionimagewidth;
        									$height = $missionimageheight/$ratio;
        								 }
        								 else {
        									$width = $missionimagewidth;
        									$height = $missionimageheight;
        								 }

    								     $extension = strtolower($files->getClientOriginalExtension());
    								     $category_image=time().$files->getClientOriginalName();
    						         $category_destinationPath = public_path('/missionimages/thumbnail_images');
    						         $check=in_array($extension,$allowedfileExtension);
    						          
                          if($check) {

          								 $thumb_img = Image::make($files->getRealPath())->resize($width, $height);
          								 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
          								 $filename=$files->getClientOriginalName();
          								 $destinationPath = 'missionimages';
          								 $files->move($destinationPath,$files->getClientOriginalName());
          								    $isUploaded=1;
          			           DB::table('tblmissionimages')->insert(
          			                  ['missionId'=>$missionId,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$category_image]);
    								 
    						         }

    							}

						  }

				
				  $cond='';

          if ($interestId!=0) { 
              $cond.=" and (";
              $OrCond="";
              $i=1;
              foreach ($interestId as  $value) {
               $OrCond="OR";
               if (count($interestId)==$i) {
                   $OrCond="";
               }

               $cond.="custinterest.interestId IN (".$value.") ".$OrCond." ";
               $i++;
              }
              $cond.=")";
          }

           if ($levelId!=0) { 
               
               $cond.=" and (";
               $OrCond="";
               $i=1;

               foreach ($levelId as $ls) {

                   $OrCond="OR";

                   if (count($levelId)==$i) {
                    $OrCond="";
                   }

                   $levels = LevelManagement::where('isActive','=',1)->where('id','=',$ls)->first();
                   $fromPoints=isset($levels->fromPoints)?($levels->fromPoints):0;
                   $toPoints=isset($levels->toPoints)?($levels->toPoints):0;
                   $cond.=" customer.rewardPoint between ".$fromPoints." And ".$toPoints." ".$OrCond." ";

                   $i++;
               }

               $cond.=")";

           }
          
          if ((isset($request->isMale) && $request->isMale==1) && (isset($request->isFemale) && $request->isFemale==1)) {
              $cond.=" and customer.gender IN (1,2) ";
          } elseif ((isset($request->isMale) && $request->isMale==1) && (!isset($request->isFemale))) {
              $cond.=" and customer.gender=1 ";
          } elseif ((isset($request->isFemale) && $request->isFemale==1)  && (!isset($request->isMale))) {
              $cond.=" and customer.gender=2 ";
          } else {

          }

          if ((isset($request->isVerified) && $request->isVerified==1) && (isset($request->isUnverified) && $request->isUnverified==1)) {
              $cond.=" and customer.isVerify IN (0,1) ";
          } elseif ((isset($request->isVerified) && $request->isVerified==1) && (!isset($request->isUnverified))) {
              $cond.=" and customer.isVerify=1 ";
          } elseif ((isset($request->isUnverified) && $request->isUnverified==1)  && (!isset($request->isVerified))) {
              $cond.=" and customer.isVerify=0 ";
          } else {
            
          }
           
           $curDate=date("Y-m-d");

           if ($email!='') {
               $implodeemail=implode(",",$email);
               $cond.=" and customer.email IN ('".$implodeemail."') ";
           }

            if ((isset($request->ageFrom) && $request->ageFrom!=0) && (isset($request->ageTo) && $request->ageTo!=0)) {
              $cond.=" and TIMESTAMPDIFF(YEAR, customer.birthDate, CURDATE()) >= ".$request->ageFrom."  and TIMESTAMPDIFF(YEAR, customer.birthDate, CURDATE()) <= ".$request->ageTo." ";
            } elseif ((isset($request->ageFrom) && $request->ageFrom!=0) && (!isset($request->ageTo))) {
                $cond.=" and TIMESTAMPDIFF(YEAR, customer.birthDate, CURDATE()) >= ".$request->ageFrom." ";
            } elseif ((isset($request->ageTo) && $request->ageTo==1)  && (!isset($request->ageFrom))) {
                $cond.=" and TIMESTAMPDIFF(YEAR, customer.birthDate, CURDATE()) <= ".$request->ageTo." ";
            } else {
              
            }
            
            



            $emailList=DB::select( DB::raw("Select customer.id as customerId from tblcustomer as customer left join tblcustomerareaofinterest as custinterest ON customer.id=custinterest.customerId  where isActive=1 ".$cond." group by customer.id"));

          //$emailList= DB::table('tblmissiontargetemailaddress')->where('missionId', '=',$id)->get();

          if ($emailList) {

           foreach ($emailList as $emailaudience) {

                            $custId=isset($emailaudience->customerId)?($emailaudience->customerId):0;
                            $deviceTokenList= DB::table('tbldevicetoken')->where('customerId', '=',$custId)->where('loginStatus', '=',1)->get();
                             
                            $customerName=$common->customerName($custId);
                            
                            $andDeviceToken=array();
                            $iPhoneDeviceToken=array();

                            $notificationCount=$common->NotificationCountCustomer($custId);
                             
                            if ($customNotificationMessage!='') {
                              $receivermsg=$customNotificationMessage;
                            } else {
                              $msg=$common->get_msg('new_mission_request_join',1)?$common->get_msg('new_mission_request_join',1):"Hello! #custname New #mission has created we have sent request to join mission.";
                              $msg1=str_replace("#custname",$customerName,$msg);
                              $msg2=str_replace("#mission",$missionName,$msg1);
                              $receivermsg=$msg2;
                            }  

                            if ($deviceTokenList) {
                                 
                                 $notificationType=6;

                                 $NotificationID=DB::table('tblnotification')->insertGetId(
                                 ['notifiedByUserId'=>1,'notifiedUserId'=>$custId,'notificationType'=>$notificationType,'notification'=>$receivermsg,'createdDate'=>date('Y-m-d H:i:s'),'isCustomerNotification'=>1,'missionId'=>$missionId]);

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

                                    
                                    if ($andDeviceToken) {
                                         $ExtraInfo = array('notificationType'=>$notificationType,'message'=>$receivermsg,'title'=>'Mission App','notificationId'=>$NotificationID,'customerId'=>$custId,'icon'=>'myicon','sound'=>'mySound','missionId'=>(int)$missionId,'MissionName' =>$missionName,'totalUnreadCount'=>$notificationCount);
                                         $common->firebasepushCustomer($ExtraInfo,$andDeviceToken);
                                    }

                                    if ($iPhoneDeviceToken) {
                                       $body['aps'] = array('alert' => $receivermsg, 'sound' => 'default', 'badge' =>$notificationCount, 'content-available' => 1,'notificationType'=>$notificationType,'notificationId'=>$NotificationID,'missionId'=>(int)$missionId,'MissionName' =>$missionName,'customerId'=>$custId);
                                       $common->iPhonePushBookCustomer($iPhoneDeviceToken,$body);
                                     }    
                            }

                      }
          
            } 
								   
			 
			 
			 flash()->success('You have successfully created Mission Now you can create milestone for this mission.');
			 return redirect()->to('/admin/missionmanagement/milestones/'.$missionId);
			 //flash()->success('Mission has  added successfully.');
			 //return redirect()->to('/admin/missionmanagement');
		 } else {
		     flash()->error('This Mission name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/missionmanagement/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	   	  $common=new CommanController;
		    $mission = Mission::find($id);
  		  $mission->isActive=$status;
  		  $mission->modifiedDate=date('Y-m-d H:i:s');
  		  $mission->save();
  		  flash()->success('Mission status has updated successfully.');
  		 return redirect()->to('/admin/missionmanagement');
	   }

	   public function ReOpen($status,$id) {
	   	    $common=new CommanController;
		      $mission = Mission::find($id);
		      $mission->isreOpen=1;
          $mission->isActive=1;
          $mission->reopenDate=date('Y-m-d H:i:s');
		      $mission->save();
    		  echo 2;
    		  exit();
	   }
	   
	   public function Delete($id) {

	   	         $common=new CommanController;
	   	         $checkcustomer = DB::table('tblcustomermission')->where([['missionId', '=',$id]])->count();

               if ($checkcustomer==0) {
                 
                 $delete1=DB::delete("delete from tblmissionimages where missionId=$id");
		             $delete2=DB::delete("delete from tblmissiontargetlevel where missionId=$id");
		             $delete3=DB::delete("delete from tblmissiontargetareaofinterest where missionId=$id");
		             $user = Mission::find($id);
                 $user->delete();
			           echo 2;
		             exit();
               
               } else {

               	   $msg=$common->get_msg('delete_mission',1)?$common->get_msg('delete_mission',1):"You can not delete this mission because this mission enrolled by customers.";
                   echo $msg;
		               exit();
               }

		 }

	   public function missionEnrollments(Request $request,$missionId) {
	   
	   	$missionStatus=isset($request->status)?($request->status):0;
	   	$common=new CommanController;
	   	$mission=$common->MissionDetails($missionId);

	   	$missionenroll=DB::select( DB::raw("Select customermission.*,customer.*,customermission.id as custmissionId,customer.id as customerId from tblmission as mission  
         inner join tblcustomermission as customermission on  mission.id=customermission.missionId
         inner join tblcustomer as customer on  customermission.userId=customer.id
         where    mission.id=".$missionId." and customer.isActive=1  order by mission.startDate"));
	   	return view('admin.missionmanagement.enrollment',compact('mission','missionenroll','missionId','missionStatus'));	   
	   }

	   

	   public function missionUnleave($custmissionId) {
           $common=new CommanController;
           $delete1=DB::delete("delete from tblcustomermission where id=$custmissionId");
		       $msg='You are successfully unleave this mission.';
		       echo $msg;
		       exit();
      }


	   public function getCustomerEnrollments($missionId,$customerMissionId,$customerId) {
         $common=new CommanController;
	   	 $mission=$common->MissionDetails($missionId);
	   	 $custDetails=$common->CustomerDetails($customerId);
	   	 $points=$custDetails['rewardPoint'];

	   	 $levelName=$common->getLevelNameFromPoints($points);



	   	 /*$enrollMilestone=DB::select( DB::raw("Select  customermissionmilestone.* from tblcustomermissionmilestone as customermissionmilestone
inner join tblcustomermission as customermission On customermissionmilestone.custmissionId=customermission.id
inner join tblmission as mission  On customermission.missionId=mission.id
Where customermissionmilestone.custmissionId='$customerMissionId' and customermission.missionId='$missionId'"));*/
        /* echo "Select  customermissionmilestone.* from tblcustomermissionmilestone as customermissionmilestone
inner join tblcustomermission as customermission On customermissionmilestone.custmissionId=customermission.id
inner join tblmission as mission  On customermission.missionId=mission.id
Where customermissionmilestone.custmissionId='$customerMissionId' and customermission.missionId='$missionId'";
         exit();*/

         $enrollMilestoneData=DB::select( DB::raw("Select missionmilestone.id,missionmilestone.title,missionmilestone.title,type,(Select count(*) from tblcustomermissionmilestone where tblcustomermissionmilestone.milestoneId=missionmilestone.id and custmissionId='$customerMissionId' and tblcustomermissionmilestone.customerId='$customerId') as cn   from tblmissionmilestone as missionmilestone  where missionmilestone.missionId='$missionId'"));

	   	 //$enrolldetail=isset($enrollMilestone[0])?($enrollMilestone[0]):"";

	   	 //echo '<pre>'; print_r($enrollMilestoneData);
	   	 //exit();


	   	 return view('admin.missionmanagement.enrollmentdetail',compact('mission','enrollMilestoneData','missionId','customerMissionId','custDetails','levelName'));

	   }


	   public function missionenolldata(Request $request,$missionId,$status) {

           //exit;
         
	   	   $sWhere="";

	   	 
           if (isset($_REQUEST['searchtxt']) && $_REQUEST['searchtxt'] != "") {
           $sSearch =trim($_REQUEST['searchtxt']); 
      $sWhere .= "and ( `customer`.`fname` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR `customer`.lname LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customer`.`email` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR `mission`.missionName LIKE '%".trim($_REQUEST['searchtxt']) ."%'";
      $sWhere .= " )";
           }

           

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
           
          $draw=1; 
          if($_REQUEST['draw']) {
          	$draw=$_REQUEST['draw'];
          }

           /*if ($isActive!='') {
              $sWhere.=" and customermission.status='".$isActive."'";
            }*/

            if ($isActive!='') {

           	  if ($isActive==4) {
                $sWhere.=" and customermission.status IN (3,4,7)";
           	  } else {
           	    $sWhere.=" and customermission.status='".$isActive."'";	
           	  }
              
            }

            if ($status!=0 && $isActive=='') {
                $sWhere.=" and customermission.status='".$status."'";
            }

          $missionenrollcn=DB::select( DB::raw("
Select customermission.*,customer.*,customermission.id as custmissionId,customer.id as customerId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where    mission.id=".$missionId." and customer.isActive=1 ".$sWhere." order by mission.startDate"));

          $missionenroll=DB::select( DB::raw("
Select customermission.*,customer.*,customermission.id as custmissionId,customer.id as customerId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where    mission.id=".$missionId." and customer.isActive=1 ".$sWhere." order by mission.startDate $order"));
           
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
                      $email=($cust->gender)?($cust->email):"";
                      $status=($cust->status)?($cust->status):"";
                      $custmissionId=($cust->custmissionId)?($cust->custmissionId):"";

                      $customerId=($cust->customerId)?($cust->customerId):0;
                      
                      if ($status==1) {
                      	$statusName='Join';
                      } elseif ($status==2) {
                      	$statusName='Leave';
                      } elseif ($status==3) {
                      	$statusName='Submit';
                      } elseif ($status==4) {
                        $statusName='Completed';
                      } elseif ($status==5) {
                        $statusName='Expired';
                      } elseif ($status==6) {
                        $statusName='InReview';
                      } elseif ($status==7) {
                        $statusName='Rejected';    
                      } else {
                      	$statusName='';
                      }

                      $link=url('/').'/admin/missionmanagement/viewdetails/'.$custmissionId;
                      $unleave=url('/').'/admin/missionmanagement/unleavemission/'.$missionId."/".$custmissionId;
                      
                      $viewdetails=url('/').'/admin/missionmanagement/enrollment/viewdetails/'.$missionId."/".$custmissionId."/".$customerId;

                      $action='<a href="'.$viewdetails.'" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-list"></i>View Details</a>';
                      
                      if ($status==2) {
                      //$action.='<a href="'.$unleave.'" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-unlock-alt"></i>UnLeave Mission</a>';
                      $action.='<a onclick="return check_unleave('.$custmissionId.');" class="btn btn-default btn-sm" data-token="{{csrf_token()}}" title="Delete Mission" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i>UnLeave Mission</a>';
                      }

                      $row[]=$i;
                      $row[]=$fname;
                      $row[]=$lname;
                      $row[]=$email;
                      $row[]=$statusName;
                      $row[]=$action;
                      $output['data'][] = $row;

                  $i++;
                  }
             }

             echo json_encode($output);
             exit();     


	   }



	   public function deleteMilestone($id) {
	   	         $common=new CommanController;
	   	         $checkcustomer = DB::table('tblcustomermissionmilestone')->where([['milestoneId', '=',$id]])->count();
	   	       if ($checkcustomer==0) {  
		         $delete1=DB::delete("delete from tblmilestoneimages where milestoneId=$id");
		         $user = MissionMilestone::find($id);
                 $user->delete();
			     echo 2;
		         exit();
		       } else {
		       	  $msg=$common->get_msg('delete_mission_milestone',1)?$common->get_msg('delete_mission_milestone',1):"You can not delete this mission milestone because this milestone enrolled by customers.";
                   echo $msg;
		           exit();
		       }
	   }
	   
	   


	   public function Deleteall(Request $request) {
	      $error='';
		  $err=0;
		  $section='';
		  $section2='';
		  foreach ($request->del as $val) {
		    $checkcustomer = DB::table('tbllead')->where([['customerId', '=',$val]])->whereIn('status', [1, 2, 3])->count();
			if ($checkcustomer > 0) {
			$customer = DB::table('tblmission')->where([['id', '=',$val]])->first();
			$customerName=$customer->fname." ".$customer->lname;
			$section .=$customerName.",";
			$err=1;
			} else {
			  $category1 = DB::table('tblmission')->where([['id', '=',$val]])->first();
			  $customerName=$category1->fname." ".$category1->lname;
              $section2 .=$customerName.",";			  
			  $customer = Mission::find($val);
              $customer->delete();
			}
		  }
		  
		  if ($err==1 && $section!='') {
			  $section=substr($section,0,-1);
			  $section2=substr($section2,0,-1);
		    $msg='Warning! Selected record is related to following Customer('.$section.') therefore deleting operation can’t be performed. For deleting the record needs to remove related records from listed vendor. ';
		    if ($section2!='') {
			$msg .="But $section2 has been deleted successfully.";
			}
		  } else {
		    $msg='Selected Mission has been deleted successfully.';
		  }
		  
		  if ($err==1) {
		  flash()->error($msg);
		  } else {
		  flash()->success($msg);
		  }
		  return redirect()->to('/admin/missionmanagement');
		  //exit;
	   }
	   
	   public function getEdit($id) {

        $common=new CommanController;
        $mission=$common->MissionDetails($id);
        $businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1']])->get(['id','beaconName']);
    		$businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
    		$idprooftype=IdProofType::where([['isActive', '=','1']])->get();
    		$areaofinterest=AreaOfInterest::where([['isActive', '=','1']])->get();
    		$levelmanagement = LevelManagement::where([['isActive', '=','1']])->get(['id','name']);
    		$missionAreaofInterestList=$common->missionAreaofInterestList($id);
    		$missionTargetLevelList=$common->missionTargetLevelList($id);
    		$missionImageList=$common->missionImageList($id);
    		$enrollcount=$common->CountCustomerMissionEnroll($id);

    		$quote_validation=$common->get_msg('quote_validation',1)?$common->get_msg('quote_validation',1):"The quota limit for active missions cannot be changed.";
    		$multiple_image_note=$common->get_msg('multiple_image_note',1)?$common->get_msg('multiple_image_note',1):"Hold shift button for selecting multiple images.";
    		$missionimagewidth=$common->getImageSizeValue('mission_image_width');
    		$missionimageheight=$common->getImageSizeValue('mission_image_height');
        $missionlogowidth=$common->getImageSizeValue('mission_logo_width');
    		$missionlogoheight=$common->getImageSizeValue('mission_logo_height');
    		$customer = Customer::where('isActive',1)->orderby('email','asc')->get(['id','email']);
    		$missionCustomerEmailAddressList=$common->missionCustomerEmailAddressList($id);


         return view('admin.missionmanagement.addedit',compact('mission','idprooftype','areaofinterest','missionAreaofInterestList','missionTargetLevelList','levelmanagement','missionImageList','businessusers','businessusersbeacon','enrollcount','quote_validation','multiple_image_note','missionimagewidth','missionimageheight','missionlogowidth','missionlogoheight','customer','missionCustomerEmailAddressList'));
       }

     public function getShow($id) {
	   	   $common=new CommanController;
         $mission=$common->MissionDetails($id);
         $missionImageList=$common->missionImageList($id);
         $missionTargetLevelName=$common->missionTargetLevelName($id);
         $missionAreaofInterestName=$common->missionAreaofInterestName($id);
         return view('admin.missionmanagement.show',compact('mission','missionImageList','missionTargetLevelName','missionAreaofInterestName'));
	   }

	   public function getSummary($id) {

	   	   $common=new CommanController;
         $mission=$common->MissionDetails($id);
         $missionImageList=$common->missionImageList($id);
         $missionTargetLevelName=$common->missionTargetLevelName($id);
         $missionAreaofInterestName=$common->missionAreaofInterestName($id);

         $pendingmission=DB::select( DB::raw("Select customer.*,customermission.id as custmissionId,mission.id as missionId,customermission.status as missionStatus,customermission.submitDate from tblmission as mission  
           inner join tblcustomermission as customermission on  mission.id=customermission.missionId
           inner join tblcustomer as customer on  customermission.userId=customer.id
           where    mission.id=".$id." and customer.isActive=1 and customermission.status IN (3,4,7)  order by mission.startDate"));

         $pendingmissionCount=count($pendingmission);

          $subpendingmission=DB::select( DB::raw("Select customer.*,customermission.id as custmissionId,mission.id as missionId,customermission.status as missionStatus,customermission.submitDate from tblmission as mission  
           inner join tblcustomermission as customermission on  mission.id=customermission.missionId
           inner join tblcustomer as customer on  customermission.userId=customer.id
           where    mission.id=".$id." and customer.isActive=1 and customermission.status IN (3)  order by mission.startDate"));

         $subpendingmissionCount=count($subpendingmission);

         $completedmission=DB::select( DB::raw("Select customer.*,customermission.id as custmissionId,mission.id as missionId,customermission.status as missionStatus,customermission.submitDate from tblmission as mission  
           inner join tblcustomermission as customermission on  mission.id=customermission.missionId
           inner join tblcustomer as customer on  customermission.userId=customer.id
           where    mission.id=".$id." and customer.isActive=1 and customermission.status=4  order by mission.startDate"));
         $completedmissionCount=count($completedmission);

         $expiredmission=DB::select( DB::raw("Select customer.*,customermission.id as custmissionId from tblmission as mission  
         inner join tblcustomermission as customermission on  mission.id=customermission.missionId
         inner join tblcustomer as customer on  customermission.userId=customer.id
         where    mission.id=".$id." and customer.isActive=1 and customermission.status=5  order by mission.startDate"));

         $expiredmissionCount=count($expiredmission);

         $rejectedmission=DB::select( DB::raw("Select customer.*,customermission.id as custmissionId from tblmission as mission  
         inner join tblcustomermission as customermission on  mission.id=customermission.missionId
         inner join tblcustomer as customer on  customermission.userId=customer.id
         where    mission.id=".$id." and customer.isActive=1 and customermission.status=7  order by mission.startDate"));
         $rejectedmissionCount=count($rejectedmission);

         $quitmission=DB::select( DB::raw("Select customer.*,customermission.id as custmissionId from tblmission as mission  
         inner join tblcustomermission as customermission on  mission.id=customermission.missionId
         inner join tblcustomer as customer on  customermission.userId=customer.id
         where    mission.id=".$id." and customer.isActive=1 and customermission.status=2  order by mission.startDate"));
         $quitmissionCount=count($quitmission);


         $allmission=DB::select( DB::raw("Select customer.*,customermission.id as custmissionId from tblmission as mission  
         inner join tblcustomermission as customermission on  mission.id=customermission.missionId
         inner join tblcustomer as customer on  customermission.userId=customer.id
         where    mission.id=".$id." and customer.isActive=1   order by mission.startDate"));
         $allmissionCount=count($allmission);

         $footFallCount = DB::table('tblfootfallbeacon')->where('missionId','=',$id)->count();

         $totalPayable=DB::select( DB::raw("Select IFNULL(ROUND(sum(cashReward),2),0) as payableamt from tblusercredithistory where status=1 and missionId='$id'"));
         $totalPayableAmount=isset($totalPayable->payableamt)?($totalPayable->payableamt):0;
         
         
         $reVisit=DB::select( DB::raw("Select count(temp.dates) as totalcn from
         (Select  DATE_FORMAT(createdDate, '%y-%m-%d') as dates from tblfootfallbeacon where missionId='$id' GROUP BY DATE_FORMAT(createdDate, '%y-%m-%d'),customerId)  as temp"));
         $totalRevisit=isset($reVisit[0]->totalcn)?($reVisit[0]->totalcn):0;
         //echo $totalRevisit;
         //exit;

         return view('admin.missionmanagement.summary',compact('mission','missionImageList','missionTargetLevelName','missionAreaofInterestName','pendingmission','completedmission','pendingmissionCount','completedmissionCount','expiredmissionCount','allmissionCount','rejectedmissionCount','quitmissionCount','footFallCount','totalPayableAmount','totalRevisit','subpendingmission','subpendingmissionCount'));
	   }

	   public function getMilestones($id) {
	   	 $common=new CommanController;
         $mission=$common->MissionDetails($id);
         $missionmilestone=MissionMilestone::where([['missionId', '=',$id]])->get(['id','title','type']);
         $missionquestionaire=MissionQuestionaire::where([['isActive', '=','1'],['missionId', '=',$id]])->get(['id','title']);
         return view('admin.missionmanagement.milestones',compact('mission','missionmilestone','missionquestionaire'));
	   }

	   public function addMissionMilestone($id) {
	   	  $common=new CommanController;
	   	  $mission=$common->MissionDetails($id);
        $businessId=isset($mission)?($mission['businessId']):0;
    		$businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$businessId]])->get(['id','beaconName']);
    		$businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
    		$missionmilestoneimagewidth=$common->getImageSizeValue('mission_milestone_image_width');
    		$missionmilestoneimageheight=$common->getImageSizeValue('mission_milestone_image_height');
	    return view('admin.missionmanagement.addeditmilestone',compact('mission','businessusersbeacon','missionmilestoneimagewidth','missionmilestoneimageheight'));
	  }

	   public function getEditMilestone($id,$milestoneId)
       {

        $common=new CommanController;
        $mission=$common->MissionDetails($id);
        $businessId=isset($mission)?($mission['businessId']):0;
    		$businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$businessId]])->get(['id','beaconName']);
    		$businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
    		$mileStoneImageList=$common->mileStoneImageList($milestoneId);
    		$missionmilestoneimagewidth=$common->getImageSizeValue('mission_milestone_image_width');
    		$missionmilestoneimageheight=$common->getImageSizeValue('mission_milestone_image_height');
		//echo print_r($mileStoneImageList);
		//exit;
        $milestone = MissionMilestone::find($milestoneId);
         return view('admin.missionmanagement.addeditmilestone',compact('mission','businessusers','businessusersbeacon','mileStoneImageList','milestone','missionmilestoneimagewidth','missionmilestoneimageheight'));
       }

       public function getEditQuestionaire($id,$questionaireId)
       {

        $common=new CommanController;
        $mission=$common->MissionDetails($id);
        $businessId=isset($mission)?($mission['businessId']):0;
		    $businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$businessId]])->get(['id','beaconName']);
		    $businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
		
		    $surveyquestion = SurveyQuestion::where('questionaireId',$questionaireId)->get();
		    $questionaire = MissionMilestone::find($questionaireId);
               
         return view('admin.missionmanagement.addeditquestionaire',compact('mission','businessusers','businessusersbeacon','questionaire','surveyquestion'));
       }

	  public function MissionMilestonepostCreate(Request $request,$id) {
            
	  	    $title=($request->title)?($request->title):"";
	  	    $description=($request->description)?($request->description):"";
	  	    $house=($request->house)?($request->house):"";
	  	    $landmark=($request->landmark)?($request->landmark):"";
	  	    $search_location=($request->search_location)?($request->search_location):"";
	  	    $lat=($request->lat)?($request->lat):0;
	  	    $long=($request->long)?($request->long):0;
	  	    $vicinityInMiles=($request->vicinityInMiles)?($request->vicinityInMiles):0;
	  	    $beaconId=($request->beaconId)?($request->beaconId):0;
	  	    $isActive=($request->isActive)?($request->isActive):0;
	  	    $isImageRequired=($request->isImageRequired)?($request->isImageRequired):0;
	  	    $IsValidateByGps=($request->IsValidateByGps)?($request->IsValidateByGps):0;
	  	    $IsValidateByBeacon=($request->IsValidateByBeacon)?($request->IsValidateByBeacon):0;

	  	    $country=($request->country)?($request->country):"";
			    $state=($request->state)?($request->state):"";
			    $city=($request->city)?($request->city):"";

	  	    //$house_flatNo=($request->house_flatNo)?($request->house_flatNo):"";
	  	    $landmark=($request->landmark)?($request->landmark):"";

	  	     $countemail = DB::table('tblmissionmilestone')->where('title', '=',$title)->where('missionId', '=',$id)->count();

	  	     if ( $countemail==0) {
               $milestones = new MissionMilestone();
               $milestones->missionId=$id;
               $milestones->title=$title;
               $milestones->description=$description;
               $milestones->isImageRequired=$isImageRequired;
               $milestones->IsValidateByGps=$IsValidateByGps;
               $milestones->IsValidateByBeacon=$IsValidateByBeacon;
               $milestones->beaconId=$beaconId;
               $milestones->vicinityInMiles=$vicinityInMiles;
               $milestones->location=$search_location;
               $milestones->latitude=$lat;
               $milestones->longitude=$long;
               $milestones->isActive=$isActive;
               
               $milestones->house_flatNo=$house;
               $milestones->landmark=$landmark;

               $milestones->country=$country;
      			   $milestones->state=$state;
      			   $milestones->city=$city;
      			   $milestones->type=1;

               $milestones->createdDate=date("Y-m-d H:i:s");
               $milestones->save();

               $milestoneId=$milestones->id;
               /*if($request->hasFile('photo')) {
					   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
					   $files = $request->file('photo');
					    
					    foreach($files as $file) {
						 $rand=rand(10,1000);
                         $extension = strtolower($file->getClientOriginalExtension());
						 $filename = $rand.time().".".$extension;
                         $check=in_array($extension,$allowedfileExtension);
							 if($check) {
							   $destinationPath = 'milestoneimages';
	                           $file->move($destinationPath,$filename); 
							   $isUploaded=1;
                                 DB::table('tblmilestoneimages')->insert(
                  ['milestoneId'=>$milestoneId,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$filename]);

							 }
					   }
				}*/

				if($request->hasFile('photo')) {
              $common=new CommanController;
							$allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
							$files = $request->file('photo');
							
              foreach($files as $file) {
                     $files =$file;
                     $size = getimagesize($files);
                     $ratio = $size[0]/$size[1];
                               //$businesswidth=$common->getImageSizeValue('business_image_width');
						       //$businessheight=$common->getImageSizeValue('business_image_height');

						       $missionimagewidth=$common->getImageSizeValue('mission_milestone_image_width');
		               $missionimageheight=$common->getImageSizeValue('mission_milestone_image_height'); 
						       
						    if( $ratio > 1) {
									$width = $missionimagewidth;
									$height = $missionimageheight/$ratio;
								 }
								 else {
									$width = $missionimagewidth;
									$height = $missionimageheight;
								 }

								 $extension = strtolower($files->getClientOriginalExtension());
								 $category_image=time().$files->getClientOriginalName();
						         $category_destinationPath = public_path('/milestoneimages/thumbnail_images');
						         $check=in_array($extension,$allowedfileExtension);
						          if($check) {
								 $thumb_img = Image::make($files->getRealPath())->resize($width, $height);
								 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
								 $filename=$files->getClientOriginalName();
								 $destinationPath = 'milestoneimages';
								 $files->move($destinationPath,$files->getClientOriginalName());
								 $isUploaded=1;
			                                 
			                       DB::table('tblmilestoneimages')->insert(
                  ['milestoneId'=>$milestoneId,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$category_image]);
								 //$businessusers->profilePicture=$category_image;
						         }

							}

						}

						$mission = Mission::find($id);
						$mission->modifiedDate=date('Y-m-d H:i:s');
						$mission->save();

               flash()->success('Mission Milestone has added successfully.');
			 return redirect()->to('/admin/missionmanagement/milestones/'.$id);

	  	     } else {
	  	     	 flash()->error('This Mission Milestone title has been already taken. Please try with another title.');
			 return redirect()->to('/admin/missionmanagement/milestones/add/'.$id);
	  	     }


	  }

	   public function addMissionQuestionaire($id) {
	   	$common=new CommanController;
	   	$mission=$common->MissionDetails($id);

        $businessId=isset($mission)?($mission['businessId']):0;
		$businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$businessId]])->get(['id','beaconName']);
		$businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
	    return view('admin.missionmanagement.addeditquestionaire',compact('mission','businessusersbeacon','businessusers'));
	  }

	  public function addMissionQuestionaireQuestionOptions(Request $request,$id) {
	  	 
	   	 $common=new CommanController;
         $mission=$common->MissionDetails($id);
         $rank=$common->getQuestionMaxRank();
         $pipedquestion=DB::table('tblsurveyquestion')->Join('tblsurveyquestionoption', 'tblsurveyquestion.id', '=', 'tblsurveyquestionoption.questionId')->select('tblsurveyquestion.id','tblsurveyquestion.question')->whereIn('tblsurveyquestion.answerType',[2,3])->groupby('tblsurveyquestion.id')->get();
         $question=DB::table('tblsurveyquestion')->Join('tblsurveyquestionoption', 'tblsurveyquestion.id', '=', 'tblsurveyquestionoption.questionId')->select('tblsurveyquestion.id','tblsurveyquestion.question')->whereIn('tblsurveyquestion.answerType',[2,3])->groupby('tblsurveyquestion.id')->get();

         return view('admin.missionmanagement.addeditquestionairequestionoption',compact('mission','rank','question','pipedquestion'));
	   }
	   public function addMissionQuestionaireQuestionOptionsQuestions(Request $request,$id,$questionaireId) {
	  	 
	   	   $common=new CommanController;
         $mission=$common->MissionDetails($id);
         $rank=$common->getQuestionMaxRank();
         $pipedquestion=DB::table('tblsurveyquestion')->Join('tblsurveyquestionoption', 'tblsurveyquestion.id', '=', 'tblsurveyquestionoption.questionId')->select('tblsurveyquestion.id','tblsurveyquestion.question')->where('tblsurveyquestion.missionId',$id)->where('tblsurveyquestion.questionaireId',$questionaireId)->whereIn('tblsurveyquestion.answerType',[1,2,3])->groupby('tblsurveyquestion.id')->get();
         $question=DB::table('tblsurveyquestion')->Join('tblsurveyquestionoption', 'tblsurveyquestion.id', '=', 'tblsurveyquestionoption.questionId')->select('tblsurveyquestion.id','tblsurveyquestion.question')->where('tblsurveyquestion.missionId',$id)->where('tblsurveyquestion.questionaireId',$questionaireId)->whereIn('tblsurveyquestion.answerType',[1,2,3])->groupby('tblsurveyquestion.id')->get();
         $missionmilestonequestionaireimagewidth=$common->getImageSizeValue('mission_milestone_questionaire_image_width');
		     $missionmilestonequestionaireimageheight=$common->getImageSizeValue('mission_milestone_questionaire_image_height');
         return view('admin.missionmanagement.addeditquestionairequestionoption',compact('mission','rank','question','pipedquestion','questionaireId','missionmilestonequestionaireimagewidth','missionmilestonequestionaireimageheight'));
	   }

	   public function editMissionQuestionaireQuestionOptionsQuestions(Request $request,$id,$questionaireId,$questionId) {
	  	 
	   	 $common=new CommanController;
         
         $mission=$common->MissionDetails($id);
         $rank=$common->getQuestionMaxRank();
         $pipedquestion=DB::table('tblsurveyquestion')->Join('tblsurveyquestionoption', 'tblsurveyquestion.id', '=', 'tblsurveyquestionoption.questionId')->select('tblsurveyquestion.id','tblsurveyquestion.question')->where('tblsurveyquestion.id','!=',$questionId)->where('tblsurveyquestion.missionId',$id)->where('tblsurveyquestion.questionaireId',$questionaireId)->whereIn('tblsurveyquestion.answerType',[1,2,3])->groupby('tblsurveyquestion.id')->get();
         $question=DB::table('tblsurveyquestion')->Join('tblsurveyquestionoption', 'tblsurveyquestion.id', '=', 'tblsurveyquestionoption.questionId')->select('tblsurveyquestion.id','tblsurveyquestion.question')->where('tblsurveyquestion.id','!=',$questionId)->where('tblsurveyquestion.missionId',$id)->where('tblsurveyquestion.questionaireId',$questionaireId)->whereIn('tblsurveyquestion.answerType',[1,2,3])->groupby('tblsurveyquestion.id')->get();
         $surveyquestion = SurveyQuestion::where('id',$questionId)->first();
         

         $surveyoptions=DB::table('tblsurveyquestionoption')->select('tblsurveyquestionoption.id','tblsurveyquestionoption.optionName')->where('tblsurveyquestionoption.questionId',$questionId)->get();

         $depedentquestion=DB::table('tblsurveyquestiondepenedoption')->select('tblsurveyquestiondepenedoption.subquestionId')->where('tblsurveyquestiondepenedoption.parentquestionId',$questionId)->first();

         $depedentquestionIDS=DB::table('tblsurveyquestiondepenedoption')->select('tblsurveyquestiondepenedoption.optionId')->where('tblsurveyquestiondepenedoption.parentquestionId',$questionId)->get();
         
         $depedentquestionoption=array();

         if ($depedentquestionIDS) {
         	 
         	 foreach($depedentquestionIDS as $arrlist) {
                 $depedentquestionoption[]=($arrlist->optionId)?($arrlist->optionId):0;
         	 }

         }

         $missionmilestonequestionaireimagewidth=$common->getImageSizeValue('mission_milestone_questionaire_image_width');
		     $missionmilestonequestionaireimageheight=$common->getImageSizeValue('mission_milestone_questionaire_image_height');


         //print_r($depedentquestionoption);
         //exit;


         return view('admin.missionmanagement.addeditquestionairequestionoption',compact('mission','rank','question','pipedquestion','questionaireId','surveyquestion','surveyoptions','depedentquestion','depedentquestionoption','missionmilestonequestionaireimagewidth','missionmilestonequestionaireimageheight'));
	   }

	   public function postMissionQuestionaireQuestionOptionsQuestions(Request $request,$id,$questionaireId) {
               $common=new CommanController;

            $countemail = DB::table('tblsurveyquestion')->where('question', '=',$request->question)->where('questionaireId', '=',$questionaireId)->count(); 

            if ($countemail==0) {  
	   	       
		   	         $maxNumberOfOption=($request->maxNumberOfOption)?($request->maxNumberOfOption):0;
	               $dependAnswerQuestion=($request->dependAnswerQuestion)?($request->dependAnswerQuestion):0;
	               $depedentquestionoption=($request->depedentquestionoption && $request->depedentquestionoption!='')?($request->depedentquestionoption):'';
	               $depedentquestion=($request->depedentquestion)?($request->depedentquestion):0;
	               $rank=($request->rank)?($request->rank):0;
	               $question=($request->question)?($request->question):"";
	               $options=($request->options)?($request->options):"";


	               $pipedQuestionId=($request->pipedQuestionId)?($request->pipedQuestionId):0;
	               $isMandotarytoAnswer=($request->isMandotarytoAnswer)?($request->isMandotarytoAnswer):0;
	               $isConditionalBranchingLogic=($request->isConditionalBranchingLogic)?($request->isConditionalBranchingLogic):0;

	               $surveyquestion = new SurveyQuestion();
      				   $surveyquestion->question =$request->question;
      				   $surveyquestion->answerType=$request->answerType;
      				   
      				   if ($request->answerType==3) {
      				   $surveyquestion->maxNumberOfOption=$maxNumberOfOption;
      				   } else if ($request->answerType==2) {
      	         $surveyquestion->maxNumberOfOption=1;
      				   } else {
      				   $surveyquestion->maxNumberOfOption=0;	
      				   }

      				   $surveyquestion->dependAnswerQuestion=$dependAnswerQuestion;
      				   $surveyquestion->sortOrder=$rank;
      				   $surveyquestion->missionId=$id;
      				   $surveyquestion->questionaireId=$questionaireId;
      				   
      				   $surveyquestion->pipedQuestionId=$pipedQuestionId;
      				   $surveyquestion->createdDate=date('Y-m-d H:i:s');
      				   $surveyquestion->isMandotarytoAnswer=$isMandotarytoAnswer;
      				   $surveyquestion->isConditionalBranchingLogic=$isConditionalBranchingLogic;

      				   if ($dependAnswerQuestion==1) {
      				   	 $surveyquestion->dependentQuestionId=$depedentquestion;
      				   }
	               
	               /*if($request->hasFile('photo')) {
					 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
					 $file = $request->file('photo');
					 $filename = time().$file->getClientOriginalName();
					 $extension = strtolower($file->getClientOriginalExtension());
					 $check=in_array($extension,$allowedfileExtension);
					 if($check) {
					   $destinationPath = 'questionimage';
					   $file->move($destinationPath,$filename);
					   $surveyquestion->questionImage=$filename;						   
					 }
				 }*/

    				if ($request->file('photo')) {

    					 $file = $request->file('photo');
    					 $size = getimagesize($file);
    					 $ratio = $size[0]/$size[1];
    					 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

    					 $common=new CommanController;
    					 $businesswidth=$common->getImageSizeValue('mission_milestone_questionaire_image_width');
    					 $businessheight=$common->getImageSizeValue('mission_milestone_questionaire_image_height');
    					 if( $ratio > 1) {
    						$width = $businesswidth;
    						$height = $businessheight/$ratio;
    					 }
    					 else {
    						$width = $businesswidth;
    						$height = $businessheight;
    					 }
    					 
    					 $extension = strtolower($file->getClientOriginalExtension());

    					 $category_image=time().$file->getClientOriginalName();
    					 $category_destinationPath = public_path('/questionimage/thumbnail_images');
    					//$thumb_img = Image::make($file->getRealPath())->resize(150, 150);
    					 $check=in_array($extension,$allowedfileExtension);
    					 if($check) {
    					 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
    					 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
    					 $filename=$file->getClientOriginalName();
    					 $destinationPath = 'questionimage';
    					 $file->move($destinationPath,$file->getClientOriginalName());
    					 $surveyquestion->questionImage=$category_image;
    					}
    				 }


				   $surveyquestion->isActive =($request->isActive)?($request->isActive):0;

				   $surveyquestion->save();

				   $questionId=$surveyquestion->id;

				   

				 if ($depedentquestionoption!='' && $depedentquestion!=0 && $dependAnswerQuestion==1) {

				 	   foreach ($depedentquestionoption as  $LID) {
	                      $VenD=DB::table('tblsurveyquestiondepenedoption')->insertGetId(
	                ['parentquestionId'=>$questionId,'subquestionId'=>$depedentquestion,'optionId'=>$LID,'createdDate'=>date('Y-m-d H:i:s')]);

	                     }
				 }

				  if ($options) {
				  	 foreach ($options as  $value) {
				  	 	$rank=$common->getQuestionOptionMaxRank($questionId);
				  	 	$checkduplicate = DB::table('tblsurveyquestionoption')->where([['questionId', '=',$questionId],['optionName', '=',$value]])->count();
				  	 	if ($checkduplicate==0) {
	                        $insert=DB::table('tblsurveyquestionoption')->insert(
				               ['questionId'=>$questionId,'optionName'=>$value,'createdDate'=>date('Y-m-d H:i:s'),'sortOrder'=>$rank]);
				  	 	}
				  	 }
				  }

				  $mission = Mission::find($id);
				  $mission->modifiedDate=date('Y-m-d H:i:s');
				  $mission->save();

				  flash()->success('Mission Questionaire Question has added successfully.');
				 return redirect()->to('/admin/missionmanagement/questionaire/edit/'.$id."/".$questionaireId);
			} else {

				  flash()->error('This Mission Questionaire Question has been already taken. Please try with another title.');
			    return redirect()->to('/admin/missionmanagement/questionaire/questionoptions/add/'.$id."/".$questionaireId);

			} 	 

              
	   }

	   public function editpostMissionQuestionaireQuestionOptionsQuestions(Request $request,$id,$questionaireId,$questionId) {
               $common=new CommanController;
	   	       
	   	       /*print_r($request->all());
	   	       exit();*/

	   	     $countemail = DB::table('tblsurveyquestion')->where('question', '=',$request->question)->where('questionaireId', '=',$questionaireId)->where('id', '!=',$questionId)->count();

	   	     if ($countemail==0) {

		   	       $maxNumberOfOption=($request->maxNumberOfOption)?($request->maxNumberOfOption):0;
	               $dependAnswerQuestion=($request->dependAnswerQuestion)?($request->dependAnswerQuestion):0;
	               $depedentquestionoption=($request->depedentquestionoption && $request->depedentquestionoption!='')?($request->depedentquestionoption):'';
	               $depedentquestion=($request->depedentquestion)?($request->depedentquestion):0;
	               $rank=($request->rank)?($request->rank):0;
	               $question=($request->question)?($request->question):"";
	               $options=($request->options)?($request->options):"";


	               $pipedQuestionId=($request->pipedQuestionId)?($request->pipedQuestionId):0;
	               $isMandotarytoAnswer=($request->isMandotarytoAnswer)?($request->isMandotarytoAnswer):0;
	               $isConditionalBranchingLogic=($request->isConditionalBranchingLogic)?($request->isConditionalBranchingLogic):0;

	               $surveyquestion = SurveyQuestion::find($questionId);
      				   $surveyquestion->question =$request->question;
      				   $surveyquestion->answerType=$request->answerType;
      				   
      				   if ($request->answerType==3) {
      				   $surveyquestion->maxNumberOfOption=$maxNumberOfOption;
      				   } else if ($request->answerType==2) {
      	               $surveyquestion->maxNumberOfOption=1;
      				   } else {
      				   $surveyquestion->maxNumberOfOption=0;	
      				   }

      				   $surveyquestion->dependAnswerQuestion=$dependAnswerQuestion;
      				   $surveyquestion->sortOrder=$rank;
      				   $surveyquestion->missionId=$id;
      				   $surveyquestion->questionaireId=$questionaireId;
      				   
      				   $surveyquestion->pipedQuestionId=$pipedQuestionId;
      				   $surveyquestion->updatedDate=date('Y-m-d H:i:s');
      				   $surveyquestion->isMandotarytoAnswer=$isMandotarytoAnswer;
      				   $surveyquestion->isConditionalBranchingLogic=$isConditionalBranchingLogic;

      				   if ($dependAnswerQuestion==1) {
      				   	 $surveyquestion->dependentQuestionId=$depedentquestion;
      				   }
	               
	               /*if($request->hasFile('photo')) {
					 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];
					 $file = $request->file('photo');
					 $filename = time().$file->getClientOriginalName();
					 $extension = strtolower($file->getClientOriginalExtension());
					 $check=in_array($extension,$allowedfileExtension);
					 if($check) {
					   $destinationPath = 'questionimage';
					   $file->move($destinationPath,$filename);
					   $surveyquestion->questionImage=$filename;						   
					 }
				 }*/

				   if ($request->file('photo')) {
					 $file = $request->file('photo');
					 $size = getimagesize($file);
					 $ratio = $size[0]/$size[1];
					 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

					 $common=new CommanController;
					 $businesswidth=$common->getImageSizeValue('mission_milestone_questionaire_image_width');
					 $businessheight=$common->getImageSizeValue('mission_milestone_questionaire_image_height');
					 if( $ratio > 1) {
						$width = $businesswidth;
						$height = $businessheight/$ratio;
					 }
					 else {
						$width = $businesswidth;
						$height = $businessheight;
					 }
					 
					 $extension = strtolower($file->getClientOriginalExtension());

					 $category_image=time().$file->getClientOriginalName();
					 $category_destinationPath = public_path('/questionimage/thumbnail_images');
					//$thumb_img = Image::make($file->getRealPath())->resize(150, 150);
					 $check=in_array($extension,$allowedfileExtension);
					 if($check) {
					 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
					 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
					 $filename=$file->getClientOriginalName();
					 $destinationPath = 'questionimage';
					 $file->move($destinationPath,$file->getClientOriginalName());
					 $surveyquestion->questionImage=$category_image;
					}
				 }


				   $surveyquestion->isActive =($request->isActive)?($request->isActive):0;

				   $surveyquestion->save();


				 if ($depedentquestionoption!='' && $depedentquestion!=0 && $dependAnswerQuestion==1) {
	                    $delete1=DB::delete("delete from tblsurveyquestiondepenedoption where parentquestionId=$questionId");
	                    
				 	   foreach ($depedentquestionoption as  $LID) {
	                      $VenD=DB::table('tblsurveyquestiondepenedoption')->insertGetId(
	                ['parentquestionId'=>$questionId,'subquestionId'=>$depedentquestion,'optionId'=>$LID,'createdDate'=>date('Y-m-d H:i:s')]);

	                     }
				 }

				  if ($options) {
				  	 $delete1=DB::delete("delete from tblsurveyquestionoption where questionId=$questionId");

				  	 foreach ($options as  $value) {
				  	 	$rank=$common->getQuestionOptionMaxRank($questionId);
				  	 	$checkduplicate = DB::table('tblsurveyquestionoption')->where([['questionId', '=',$questionId],['optionName', '=',$value]])->count();
				  	 	if ($checkduplicate==0) {
	                        $insert=DB::table('tblsurveyquestionoption')->insert(
				               ['questionId'=>$questionId,'optionName'=>$value,'createdDate'=>date('Y-m-d H:i:s'),'sortOrder'=>$rank]);
				  	 	}
				  	 }
				  }

				  $mission = Mission::find($id);
				  $mission->modifiedDate=date('Y-m-d H:i:s');
				  $mission->save();

				  flash()->success('Mission Questionaire Question has updated successfully.');
				 return redirect()->to('/admin/missionmanagement/questionaire/edit/'.$id."/".$questionaireId);

			} else {
				  flash()->error('This Mission Questionaire Question has been already taken. Please try with another title.');
			     return redirect()->to('/admin/missionmanagement/questionaire/questionoptions/edit/'.$id."/".$questionaireId."/".$questionId);
			}	 

              
	   }

	   

	  public function MissionQuestionairepostCreate(Request $request,$id) {
            
	  	    $title=($request->title)?($request->title):"";
	  	    $description=($request->description)?($request->description):"";
	  	    $isActive=($request->isActive)?($request->isActive):0;
	  	    $IsValidateByGps=($request->IsValidateByGps)?($request->IsValidateByGps):0;
	  	    $IsValidateByBeacon=($request->IsValidateByBeacon)?($request->IsValidateByBeacon):0;
	  	    $house=($request->house)?($request->house):"";
    			$landmark=($request->landmark)?($request->landmark):"";
    			$search_location=($request->search_location)?($request->search_location):"";
    			$lat=($request->lat)?($request->lat):0;
    			$long=($request->long)?($request->long):0;
    			$beaconId=($request->beaconId)?($request->beaconId):0;
    			$vicinityInMiles=($request->vicinityInMiles)?($request->vicinityInMiles):0;
    			$country=($request->country)?($request->country):"";
    			$state=($request->state)?($request->state):"";
    			$city=($request->city)?($request->city):"";

	  	     $countemail = DB::table('tblmissionmilestone')->where('title', '=',$title)->where('missionId', '=',$id)->count();
		         

	  	     if ( $countemail==0) {
               $questionaire = new MissionMilestone();
               $questionaire->missionId=$id;
               $questionaire->title=$title;
               $questionaire->description=$description;
               $questionaire->IsValidateByGps=$IsValidateByGps;
               $questionaire->IsValidateByBeacon=$IsValidateByBeacon;
               $questionaire->beaconId=$beaconId;
      			   $questionaire->vicinityInMiles=$vicinityInMiles;
      			   $questionaire->location=$search_location;
      			   $questionaire->latitude=$lat;
      			   $questionaire->longitude=$long;
      			   $questionaire->house_flatNo=$house;
      			   $questionaire->landmark=$landmark;
      			   $questionaire->type=2;
      			   $questionaire->country=$country;
      			   $questionaire->state=$state;
      			   $questionaire->city=$city;
      			   $questionaire->isActive=$isActive;
               $questionaire->createdDate=date("Y-m-d H:i:s");
               $questionaire->save();

               $mission = Mission::find($id);
      			   $mission->modifiedDate=date('Y-m-d H:i:s');
      			   $mission->save();

               flash()->success('Mission Questionaire has  added successfully.');
			         return redirect()->to('/admin/missionmanagement/questionaire/edit/'.$id."/".$questionaire->id);

	  	     } else {
	  	     	 flash()->error('This Mission Questionaire title has been already taken. Please try with another title.');
			       return redirect()->to('/admin/missionmanagement/questionaire/add/'.$id);
	  	     }


	  }
          
       /*public function completedMission($id,$missionId,$customerId) {
       	  $common=new CommanController;

       	  $mission = Mission::find($missionId);
       	  $cashReward=$mission->cashReward;
       	  $points=$mission->points;

       	  $custDetails=$common->CustomerDetails($customerId);
          
          $MissionName=$common->getMissionName($missionId);

       	  $completemsg=$common->get_msg("mission_complete_message",1)?$common->get_msg("mission_complete_message",1):"Congratulation! You have successfully completed #mission Mission.";  
          $receivermsg=str_replace("#mission",$MissionName,$completemsg);

          $NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>1,'notifiedUserId'=>$customerId,'notificationType'=>4,'notification'=>$receivermsg,'createdDate'=>date('Y-m-d H:i:s'),'isCustomerNotification'=>1,'missionId'=>$missionId]);

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
                                                 	 $ExtraInfo = array('notificationType'=>4,'message'=>$receivermsg,'title'=>'Mission App','notificationId'=>$NotificationID,'customerId'=>$customerId,'icon'=>'myicon','sound'=>'mySound','missionId'=>(int)$missionId,'MissionName' =>$MissionName,'totalUnreadCount'=>$notificationCount);
                                                 	 $common->firebasepushCustomer($ExtraInfo,$andDeviceToken);
                                                 }

                                                 if ($iPhoneDeviceToken) {
                                                 	 $body['aps'] = array('alert' => $receivermsg, 'sound' => 'default', 'badge' =>$notificationCount, 'content-available' => 1,'notificationType'=>4,'notificationId'=>$NotificationID,'missionId'=>(int)$missionId,'MissionName' =>$MissionName,'customerId'=>$customerId);
                                                 	 $common->iPhonePushBookCustomer($iPhoneDeviceToken,$body);
                                                 }
          

       	  

       	  $userReward=isset($custDetails['cashReward'])?($custDetails['cashReward']):0;
       	  $userPoint=isset($custDetails['rewardPoint'])?($custDetails['rewardPoint']):0;
          
          $customerMissionId=DB::table('tblcustomermission')->where('id',$id)->update(
										  ['status'=>4,"verifyDate"=>date('Y-m-d H:i:s')]);

          $totalReward=$cashReward+$userReward;
          $totalPoints=$points+$userPoint;

          $customerUpdate=DB::table('tblcustomer')->where('id',$customerId)->update(
										  ['cashReward'=>$totalReward,"rewardPoint"=>$totalPoints]);

          $customerHistory=DB::table('tblusercredithistory')->insert(
										  ['cashReward'=>$cashReward,"rewardPoint"=>$points,"customerId"=>$customerId,"missionId"=>$missionId,"createdDate"=>date("Y-m-d H:i:s")]);

          $mission->modifiedDate=date('Y-m-d H:i:s');
		  $mission->save();

          echo "1";
          exit();

       }   */

        public function completedMission($id,$missionId,$customerId,$status) {
       	  $common=new CommanController;

       	  $mission = Mission::find($missionId);
       	  $cashReward=$mission->cashReward;
       	  $points=$mission->points;

       	  $custDetails=$common->CustomerDetails($customerId);

          $MissionName=$common->getMissionName($missionId);

          if ($status==4) {
          	$completemsg=$common->get_msg("mission_complete_message",1)?$common->get_msg("mission_complete_message",1):"Congratulation! You have successfully completed #mission Mission.";
          	$receivermsg=str_replace("#mission",$MissionName,$completemsg);
          	 $notificationType=4;
          }

          if ($status==7) {
             $rejectmsg=$common->get_msg("mission_reject_message",1)?$common->get_msg("mission_reject_message",1):"Ohh! You have rejected  #mission Mission.";    
             $receivermsg=str_replace("#mission",$MissionName,$rejectmsg);
             $notificationType=7;
          }

       	    
          

          $NotificationID=DB::table('tblnotification')->insertGetId(
               ['notifiedByUserId'=>1,'notifiedUserId'=>$customerId,'notificationType'=>$notificationType,'notification'=>$receivermsg,'createdDate'=>date('Y-m-d H:i:s'),'isCustomerNotification'=>1,'missionId'=>$missionId]);

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
                           	 $ExtraInfo = array('notificationType'=>$notificationType,'message'=>$receivermsg,'title'=>'Mission App','notificationId'=>$NotificationID,'customerId'=>$customerId,'icon'=>'myicon','sound'=>'mySound','missionId'=>(int)$missionId,'MissionName' =>$MissionName,'totalUnreadCount'=>$notificationCount);
                           	 $common->firebasepushCustomer($ExtraInfo,$andDeviceToken);
                           }

                           if ($iPhoneDeviceToken) {
                           	 $body['aps'] = array('alert' => $receivermsg, 'sound' => 'default', 'badge' =>$notificationCount, 'content-available' => 1,'notificationType'=>$notificationType,'notificationId'=>$NotificationID,'missionId'=>(int)$missionId,'MissionName' =>$MissionName,'customerId'=>$customerId);
                           	 $common->iPhonePushBookCustomer($iPhoneDeviceToken,$body);
                           }
         

       	  $userReward=isset($custDetails['cashReward'])?($custDetails['cashReward']):0;
       	  $userPoint=isset($custDetails['rewardPoint'])?($custDetails['rewardPoint']):0;
          
           if ($status==4) {
          $customerMissionId=DB::table('tblcustomermission')->where('id',$id)->update(
										  ['status'=>4,"verifyDate"=>date('Y-m-d H:i:s')]);
            }

            if ($status==7) {
          $customerMissionId=DB::table('tblcustomermission')->where('id',$id)->update(
										  ['status'=>7,"rejectedDate"=>date('Y-m-d H:i:s')]);
            }

            if ($status==4) {

                $totalReward=$cashReward+$userReward;
                $totalPoints=$points+$userPoint;

                $customerUpdate=DB::table('tblcustomer')->where('id',$customerId)->update(
      										  ['cashReward'=>$totalReward,"rewardPoint"=>$totalPoints]);

                $customerHistory=DB::table('tblusercredithistory')->insert(
      										  ['cashReward'=>$cashReward,"rewardPoint"=>$points,"customerId"=>$customerId,"missionId"=>$missionId,"createdDate"=>date("Y-m-d H:i:s")]);

                $mission->modifiedDate=date('Y-m-d H:i:s');
      		      $mission->save();

  		      }

          echo "1";
          exit();

       }   
	   

    public function deleteLogo($id) {
	   	$delete=DB::delete("update tblmission SET logo='' where id=$id");
	   	echo 1;
	   	exit();
	   }
	   
	   public function deleteImages($id) {
	   	$delete=DB::delete("delete from tblmissionimages where id=$id");
	   	echo 1;
	   	exit();
	   }

	   public function deleteQuestionaireImages($id) {
	   	$user = SurveyQuestion::find($id);
        $user->questionImage='';
        $user->save();
	   	echo 1;
	   	exit();
	   }

	    public function deleteMilestoneImages($id) {
	   	$delete=DB::delete("delete from tblmilestoneimages where id=$id");
	   	echo 1;
	   	exit();
	   }

	   public function postEdit(MissionRequest $request, $id) {

            $common=new CommanController;
            $house=($request->house)?($request->house):"";
      			$landmark=($request->landmark)?($request->landmark):"";
      			$search_location=($request->search_location)?($request->search_location):"";
      			$lat=($request->lat)?($request->lat):0;
      			$long=($request->long)?($request->long):0;
      			$vicinityInMiles=($request->vicinityInMiles)?($request->vicinityInMiles):0;
    			  $country=($request->country)?($request->country):"";
    		    $state=($request->state)?($request->state):"";
    		    $city=($request->city)?($request->city):"";

		        $customNotificationMessage = isset($request->customNotificationMessage)?(ltrim($request->customNotificationMessage)):"";
            $missionName = isset($request->missionName)?(ltrim($request->missionName)):"";


    		    $mission = Mission::find($id);
    		    $interestId=isset($request->interestId)?(($request->interestId)):0;
            $levelId=isset($request->levelId)?(($request->levelId)):0;
            $email=isset($request->email)?(($request->email)):"";

      			$mission->missionName = isset($request->missionName)?(ltrim($request->missionName)):"";
      			$mission->businessId = isset($request->businessId)?(ltrim($request->businessId)):0;
      			$mission->startDate = isset($request->startDate)?(ltrim(date("Y-m-d",strtotime($request->startDate)))):"";
      			$mission->endDate = isset($request->endDate)?(ltrim(date("Y-m-d",strtotime($request->endDate)))):"";
      			$mission->startTime = isset($request->startTime)?(ltrim($request->startTime)):"";
            $mission->endTime = isset($request->endTime)?(ltrim($request->endTime)):"";
      			$mission->quotaLimit = isset($request->quotaLimit)?(ltrim($request->quotaLimit)):0;
      			$mission->durationOfMisson = isset($request->durationOfMisson)?(ltrim($request->durationOfMisson)):0;
      			$mission->title = isset($request->title)?(ltrim($request->title)):"";
            $mission->description = isset($request->description)?(ltrim($request->description)):"";
            $mission->estimationTime = isset($request->estimationTime)?(ltrim($request->estimationTime)):"";
      			$mission->rewardDescription = isset($request->rewardDescription)?(ltrim($request->rewardDescription)):"";
      			$mission->eligibiltyCriteria = isset($request->eligibiltyCriteria)?(ltrim($request->eligibiltyCriteria)):"";
      			$mission->cashReward = isset($request->cashReward)?(ltrim($request->cashReward)):0;
      			$mission->points = isset($request->points)?(ltrim($request->points)):0;
      			$mission->pushMissionBeacon = isset($request->pushMissionBeacon)?(ltrim($request->pushMissionBeacon)):0;
      			$mission->footfallCalcBeacon = isset($request->footfallCalcBeacon)?(ltrim($request->footfallCalcBeacon)):0;
      			$mission->pushMissionGps = isset($request->pushMissionGps)?(ltrim($request->pushMissionGps)):0;
      			$mission->ageFrom = isset($request->ageFrom)?(ltrim($request->ageFrom)):0;
      			$mission->ageTo = isset($request->ageTo)?(ltrim($request->ageTo)):0;
      			$mission->isMale = isset($request->isMale)?(ltrim($request->isMale)):0;
      			$mission->isFemale = isset($request->isFemale)?(ltrim($request->isFemale)):0;
      			$mission->isVerified = isset($request->isVerified)?(ltrim($request->isVerified)):0;
      			$mission->isUnverified = isset($request->isUnverified)?(ltrim($request->isUnverified)):0;
            $mission->updatedDate=date('Y-m-d H:i:s');
            $mission->modifiedDate=date('Y-m-d H:i:s');
            $mission->vicinityInMiles=$vicinityInMiles;
      			$mission->location=$search_location;
      			$mission->latitude=$lat;
      			$mission->longitude=$long;
      			$mission->house_flatNo=$house;
      			$mission->landmark=$landmark;
      			$mission->country=$country;
      			$mission->state=$state;
      			$mission->city=$city;
		        $mission->isActive=$request->isActive;
		        $mission->customNotificationMessage = isset($request->customNotificationMessage)?(ltrim($request->customNotificationMessage)):"";

    		    if ($request->file('logo')) {

        				 $file = $request->file('logo');
        				 $size = getimagesize($file);
        				 $ratio = $size[0]/$size[1];
        				 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

        				 $common=new CommanController;
        				 $businesswidth=$common->getImageSizeValue('mission_logo_width');
        				 $businessheight=$common->getImageSizeValue('mission_logo_height');

        				 if( $ratio > 1) {
        					$width = $businesswidth;
        					$height = $businessheight/$ratio;
        				 }
        				 else {
        					$width = $businesswidth;
        					$height = $businessheight;
        				 }
        				 
        				 $extension = strtolower($file->getClientOriginalExtension());

        				 $category_image=time().$file->getClientOriginalName();
        				 $category_destinationPath = public_path('/missionlogo/thumbnail_images');
        				//$thumb_img = Image::make($file->getRealPath())->resize(150, 150);
        				 $check=in_array($extension,$allowedfileExtension);

        				 if($check) {
        				 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
        				 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
        				 $filename=$file->getClientOriginalName();
        				 $destinationPath = 'missionlogo';
        				 $file->move($destinationPath,$file->getClientOriginalName());
        				 $mission->logo=$category_image;
        				}

    			}

    			 if ($request->isActive==2) {
        				$mission->isActive=1;
        			 	$mission->isreOpen=1;
        			 	$mission->reopenDate=date('Y-m-d H:i:s');
    			 }	

		        $mission->save();
            
            $delete3=DB::delete("delete from tblmissiontargetemailaddress where missionId=$id");
            $delete1=DB::delete("delete from tblmissiontargetareaofinterest where missionId=$id");
            $delete2=DB::delete("delete from tblmissiontargetlevel where missionId=$id");
            
            if ($email!='') {
                              	
      			   foreach ($email as  $value) {
      			   	$custDetailId= DB::table('tblcustomer')->where('email', '=',$value)->first(['id']);
      			   	$custId=isset($custDetailId->id)?($custDetailId->id):0;

      				 DB::table('tblmissiontargetemailaddress')->insert(
                        ['missionId'=>$id,'email'=>$value,'createdDate'=>date('Y-m-d H:i:s'),'customerId'=>$custId]);
      			   }
			     }

    		    if ($interestId!=0) {
    		    	
    			   foreach ($interestId as  $value) {
    				 DB::table('tblmissiontargetareaofinterest')->insert(
                      ['missionId'=>$id,'interestId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
    			   }

    			 }

    			 if ($levelId!=0) {
    			 	
    			   foreach ($levelId as  $value) {
    				 DB::table('tblmissiontargetlevel')->insert(
                      ['missionId'=>$id,'levelId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
    			   }

    			 }

              			 /*if($request->hasFile('photo')) {
              					//$delete3=DB::delete("delete from tblmissionimages where missionId=$id");
              						   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
              						   $files = $request->file('photo');
              						    foreach($files as $file) {
              							 $rand=rand(10,1000);
              	                         $extension = strtolower($file->getClientOriginalExtension());
              							 $filename = $rand.time().".".$extension;
              	                         $check=in_array($extension,$allowedfileExtension);
              								 if($check) {
              								   $destinationPath = 'missionimages';
              		                           $file->move($destinationPath,$filename); 
              								   $isUploaded=1;
              	                                 DB::table('tblmissionimages')->insert(
              	                                       ['missionId'=>$id,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$filename]);

              								 }
              						   }
              			 }*/

			     if($request->hasFile('photo')) {

              $common=new CommanController;
							$allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
							$files = $request->file('photo');
							
              foreach($files as $file) {

                  $files =$file;
                  $size = getimagesize($files);
                  $ratio = $size[0]/$size[1];
                  $missionimagewidth=$common->getImageSizeValue('mission_image_width');
                 $missionimageheight=$common->getImageSizeValue('mission_image_height'); 
						       
						    if( $ratio > 1) {
									$width = $missionimagewidth;
									$height = $missionimageheight/$ratio;
								 }
								 else {
									$width = $missionimagewidth;
									$height = $missionimageheight;
								 }

								 $extension = strtolower($files->getClientOriginalExtension());
								 $category_image=time().$files->getClientOriginalName();
                 $category_destinationPath = public_path('/missionimages/thumbnail_images');
						     $check=in_array($extension,$allowedfileExtension);
						     
                 if($check) {
								 $thumb_img = Image::make($files->getRealPath())->resize($width, $height);
								 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
								 $filename=$files->getClientOriginalName();
								 $destinationPath = 'missionimages';
								 $files->move($destinationPath,$files->getClientOriginalName());
								    $isUploaded=1;
			            DB::table('tblmissionimages')->insert(
			                  ['missionId'=>$id,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$category_image]);
								 
						         }
                }

						}
          
          $cond='';

          if ($interestId!=0) { 

              $cond.=" and (";
              $OrCond="";
              $i=1;

              foreach ($interestId as  $value) {

               $OrCond="OR";

               if (count($interestId)==$i) {
                   $OrCond="";
               }

               $cond.="custinterest.interestId IN (".$value.") ".$OrCond." ";
               $i++;

              }
              $cond.=")";
          }

          if ($levelId!=0) { 
               
               $cond.=" and (";
               $OrCond="";
               $i=1;

               foreach ($levelId as $ls) {

                   $OrCond="OR";

                   if (count($levelId)==$i) {
                    $OrCond="";
                   }

                   $levels = LevelManagement::where('isActive','=',1)->where('id','=',$ls)->first();
                   $fromPoints=isset($levels->fromPoints)?($levels->fromPoints):0;
                   $toPoints=isset($levels->toPoints)?($levels->toPoints):0;
                   $cond.=" customer.rewardPoint between ".$fromPoints." And ".$toPoints." ".$OrCond." ";

                   $i++;
               }

               $cond.=")";

           }
          
          if ((isset($request->isMale) && $request->isMale==1) && (isset($request->isFemale) && $request->isFemale==1)) {
              $cond.=" and customer.gender IN (1,2) ";
          } elseif ((isset($request->isMale) && $request->isMale==1) && (!isset($request->isFemale))) {
              $cond.=" and customer.gender=1 ";
          } elseif ((isset($request->isFemale) && $request->isFemale==1)  && (!isset($request->isMale))) {
              $cond.=" and customer.gender=2 ";
          } else {

          }

          if ((isset($request->isVerified) && $request->isVerified==1) && (isset($request->isUnverified) && $request->isUnverified==1)) {
              $cond.=" and customer.isVerify IN (0,1) ";
          } elseif ((isset($request->isVerified) && $request->isVerified==1) && (!isset($request->isUnverified))) {
              $cond.=" and customer.isVerify=1 ";
          } elseif ((isset($request->isUnverified) && $request->isUnverified==1)  && (!isset($request->isVerified))) {
              $cond.=" and customer.isVerify=0 ";
          } else {
            
          }
           
           $curDate=date("Y-m-d");

           if ($email!='') {
               $implodeemail=implode(",",$email);
               $cond.=" and customer.email IN ('".$implodeemail."') ";
           }

            if ((isset($request->ageFrom) && $request->ageFrom!=0) && (isset($request->ageTo) && $request->ageTo!=0)) {
              $cond.=" and TIMESTAMPDIFF(YEAR, customer.birthDate, CURDATE()) >= ".$request->ageFrom."  and TIMESTAMPDIFF(YEAR, customer.birthDate, CURDATE()) <= ".$request->ageTo." ";
            } elseif ((isset($request->ageFrom) && $request->ageFrom!=0) && (!isset($request->ageTo))) {
                $cond.=" and TIMESTAMPDIFF(YEAR, customer.birthDate, CURDATE()) >= ".$request->ageFrom." ";
            } elseif ((isset($request->ageTo) && $request->ageTo==1)  && (!isset($request->ageFrom))) {
                $cond.=" and TIMESTAMPDIFF(YEAR, customer.birthDate, CURDATE()) <= ".$request->ageTo." ";
            } else {
              
            }
            
           
            
            $emailList=DB::select( DB::raw("Select customer.id as customerId from tblcustomer as customer left join tblcustomerareaofinterest as custinterest ON customer.id=custinterest.customerId  where isActive=1 ".$cond." group by customer.id"));

          //$emailList= DB::table('tblmissiontargetemailaddress')->where('missionId', '=',$id)->get();

          if ($emailList) {

           foreach ($emailList as $emailaudience) {

                            $custId=isset($emailaudience->customerId)?($emailaudience->customerId):0;
                            $deviceTokenList= DB::table('tbldevicetoken')->where('customerId', '=',$custId)->where('loginStatus', '=',1)->get();
                             
                            $customerName=$common->customerName($custId);
                            
                            $andDeviceToken=array();
                            $iPhoneDeviceToken=array();

                            $notificationCount=$common->NotificationCountCustomer($custId);
                             
                            if ($customNotificationMessage!='') {
                              $receivermsg=$customNotificationMessage;
                            } else {
                              $msg=$common->get_msg('new_mission_request_join',1)?$common->get_msg('new_mission_request_join',1):"Hello! #custname New #mission has created we have sent request to join mission.";
                              $msg1=str_replace("#custname",$customerName,$msg);
                              $msg2=str_replace("#mission",$missionName,$msg1);
                              $receivermsg=$msg2;
                            }  

                            if ($deviceTokenList) {
                                 
                                 $notificationType=6;

                                 $NotificationID=DB::table('tblnotification')->insertGetId(
                                 ['notifiedByUserId'=>1,'notifiedUserId'=>$custId,'notificationType'=>$notificationType,'notification'=>$receivermsg,'createdDate'=>date('Y-m-d H:i:s'),'isCustomerNotification'=>1,'missionId'=>$id]);

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

                                    
                                    if ($andDeviceToken) {
                                         $ExtraInfo = array('notificationType'=>$notificationType,'message'=>$receivermsg,'title'=>'Mission App','notificationId'=>$NotificationID,'customerId'=>$custId,'icon'=>'myicon','sound'=>'mySound','missionId'=>(int)$id,'MissionName' =>$missionName,'totalUnreadCount'=>$notificationCount);
                                         $common->firebasepushCustomer($ExtraInfo,$andDeviceToken);
                                    }

                                    if ($iPhoneDeviceToken) {
                                       $body['aps'] = array('alert' => $receivermsg, 'sound' => 'default', 'badge' =>$notificationCount, 'content-available' => 1,'notificationType'=>$notificationType,'notificationId'=>$NotificationID,'missionId'=>(int)$id,'MissionName' =>$missionName,'customerId'=>$custId);
                                       $common->iPhonePushBookCustomer($iPhoneDeviceToken,$body);
                                     }    
                            }

                      }
          
            } 

					 flash()->success('Mission has updated successfully.');
					 return redirect()->to('/admin/missionmanagement');
	   }

	   public function MissionMilestonepostEdit(Request $request, $id,$milestoneId) {
            
                $title=($request->title)?($request->title):"";
      	  	    $description=($request->description)?($request->description):"";
      	  	    $house=($request->house)?($request->house):"";
      	  	    $landmark=($request->landmark)?($request->landmark):"";
      	  	    $search_location=($request->search_location)?($request->search_location):"";
      	  	    $lat=($request->lat)?($request->lat):0;
      	  	    $long=($request->long)?($request->long):0;
      	  	    $vicinityInMiles=($request->vicinityInMiles)?($request->vicinityInMiles):0;
      	  	    $beaconId=($request->beaconId)?($request->beaconId):0;
      	  	    $isActive=($request->isActive)?($request->isActive):0;
      	  	    $isImageRequired=($request->isImageRequired)?($request->isImageRequired):0;
      	  	    $IsValidateByGps=($request->IsValidateByGps)?($request->IsValidateByGps):0;
      	  	    $IsValidateByBeacon=($request->IsValidateByBeacon)?($request->IsValidateByBeacon):0;

      	  	    $country=($request->country)?($request->country):"";
          			$state=($request->state)?($request->state):"";
          			$city=($request->city)?($request->city):"";

      	  	    //$house_flatNo=($request->house_flatNo)?($request->house_flatNo):"";
      	  	    $landmark=($request->landmark)?($request->landmark):"";


		            $milestones = MissionMilestone::find($milestoneId);
		    
		            $milestones->missionId=$id;
                $milestones->title=$title;
                $milestones->description=$description;
                $milestones->isImageRequired=$isImageRequired;
                $milestones->IsValidateByGps=$IsValidateByGps;
                $milestones->IsValidateByBeacon=$IsValidateByBeacon;
                $milestones->beaconId=$beaconId;
                $milestones->vicinityInMiles=$vicinityInMiles;
                $milestones->location=$search_location;
                $milestones->latitude=$lat;
                $milestones->longitude=$long;
                $milestones->isActive=$isActive;
               
                $milestones->house_flatNo=$house;
                $milestones->landmark=$landmark;
                $milestones->type=1;
                $milestones->country=$country;
			          $milestones->state=$state;
			          $milestones->city=$city;

                $milestones->updatedDate=date('Y-m-d H:i:s');
            
		            $milestones->save();

		    

			 /*if($request->hasFile('photo')) {
								   $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
								   $files = $request->file('photo');
								    
								    foreach($files as $file) {
									 $rand=rand(10,1000);
			                         $extension = strtolower($file->getClientOriginalExtension());
									 $filename = $rand.time().".".$extension;
			                         $check=in_array($extension,$allowedfileExtension);
										 if($check) {
										   $destinationPath = 'milestoneimages';
				                           $file->move($destinationPath,$filename); 
										   $isUploaded=1;
			                                 DB::table('tblmilestoneimages')->insert(
			                  ['milestoneId'=>$milestoneId,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$filename]);

										 }
								   }
						}*/

				if($request->hasFile('photo')) {

              $common=new CommanController;
							$allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
							$files = $request->file('photo');

							foreach($files as $file) {

                   $files =$file;
                   $size = getimagesize($files);
                   $ratio = $size[0]/$size[1];
						       
						       $missionimagewidth=$common->getImageSizeValue('mission_milestone_image_width');
		               $missionimageheight=$common->getImageSizeValue('mission_milestone_image_height'); 
						       
						        if( $ratio > 1) {
    									$width = $missionimagewidth;
    									$height = $missionimageheight/$ratio;
    								 }
    								 else {
    									$width = $missionimagewidth;
    									$height = $missionimageheight;
    								 }

								  $extension = strtolower($files->getClientOriginalExtension());
								  $category_image=time().$files->getClientOriginalName();
						         
                     $category_destinationPath = public_path('/milestoneimages/thumbnail_images');
						         $check=in_array($extension,$allowedfileExtension);
						          
                      if($check) {

        								 $thumb_img = Image::make($files->getRealPath())->resize($width, $height);
        								 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
        								 $filename=$files->getClientOriginalName();
        								 $destinationPath = 'milestoneimages';
        								 $files->move($destinationPath,$files->getClientOriginalName());
								         $isUploaded=1;
								          DB::table('tblmilestoneimages')->insert(
			                    ['milestoneId'=>$milestoneId,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$category_image]);
			                        
						         }
                }

						}	

    				$mission = Mission::find($id);
    				$mission->modifiedDate=date('Y-m-d H:i:s');
    				$mission->save();			

		        flash()->success('Mission Milestone has updated successfully.');
		        return redirect()->to('/admin/missionmanagement/milestones/'.$id);

	   }

	   public function MissionQuestionairepostEdit(Request $request, $id,$questionaireId) {
            
            $title=($request->title)?($request->title):"";
	  	    $description=($request->description)?($request->description):"";
	  	    $house=($request->house)?($request->house):"";
	  	    $landmark=($request->landmark)?($request->landmark):"";
	  	    $search_location=($request->search_location)?($request->search_location):"";
	  	    $lat=($request->lat)?($request->lat):0;
	  	    $long=($request->long)?($request->long):0;
	  	    $vicinityInMiles=($request->vicinityInMiles)?($request->vicinityInMiles):0;
	  	    $beaconId=($request->beaconId)?($request->beaconId):0;
	  	    $isActive=($request->isActive)?($request->isActive):0;
	  	    $isImageRequired=($request->isImageRequired)?($request->isImageRequired):0;
	  	    $IsValidateByGps=($request->IsValidateByGps)?($request->IsValidateByGps):0;
	  	    $IsValidateByBeacon=($request->IsValidateByBeacon)?($request->IsValidateByBeacon):0;

	  	    $country=($request->country)?($request->country):"";
			$state=($request->state)?($request->state):"";
			$city=($request->city)?($request->city):"";

	  	    //$house_flatNo=($request->house_flatNo)?($request->house_flatNo):"";
	  	    $landmark=($request->landmark)?($request->landmark):"";


		    $questionaire = MissionMilestone::find($questionaireId);
		    
		       $questionaire->missionId=$id;
               $questionaire->title=$title;
               $questionaire->description=$description;
               //$questionaire->isImageRequired=$isImageRequired;
               $questionaire->IsValidateByGps=$IsValidateByGps;
               $questionaire->IsValidateByBeacon=$IsValidateByBeacon;
               $questionaire->beaconId=$beaconId;
               $questionaire->vicinityInMiles=$vicinityInMiles;
               $questionaire->location=$search_location;
               $questionaire->latitude=$lat;
               $questionaire->longitude=$long;
               $questionaire->isActive=$isActive;
               
               $questionaire->house_flatNo=$house;
               $questionaire->landmark=$landmark;
               $questionaire->type=2;
               $questionaire->country=$country;
			   $questionaire->state=$state;
			   $questionaire->city=$city;

            $questionaire->updatedDate=date('Y-m-d H:i:s');
            
		    $questionaire->save();

		    
            $mission = Mission::find($id);
			$mission->modifiedDate=date('Y-m-d H:i:s');
			$mission->save();
			 

		 flash()->success('Mission Milestone has updated successfully.');
		 return redirect()->to('/admin/missionmanagement/milestones/'.$id);
	   }

	   public function deleteQuestionaire($id) {
		         //$delete1=DB::delete("delete from tblmilestoneimages where milestoneId=$id");
		         $user = MissionMilestone::find($id);
                 $user->delete();
			     echo 2;
		         exit();
	   }


	   public function deleteQuestionaireQuestion($id) {
		         $delete1=DB::delete("delete from tblsurveyquestionoption where questionId=$id");
		         $user = SurveyQuestion::find($id);
                 $user->delete();
			     echo 2;
		         exit();
	   }
}
