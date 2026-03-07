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

class MissionController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
		$missionmanagement = Mission::orderby('id','desc')->get();
       return view('admin.missionmanagement.index',compact('missionmanagement'));
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
	   return view('admin.missionmanagement.addedit',compact('businessusers','idprooftype','areaofinterest','businessusersbeacon','levelmanagement','multiple_image_note','missionimagewidth','missionimageheight'));
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


    public function missionSubmitDetails($id,$missionId,$customerId,$missionStatus) {
        
        $userId=$customerId;
        $common=new CommanController;
        $mission=$common->MissionDetails($missionId);
    	$Sql=DB::select( DB::raw("Select tcmm.id as custmissionmilestoneId,tcmm.customerId,tcmm.milestoneId,tcmm.submitDate,missionmilestone.title,missionmilestone.type from tblcustomermissionmilestone as tcmm
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
                


        		$alldata[]=array("custmissionmilestoneId"=>$custmissionmilestoneId,"milestoneId"=>$milestoneId,"submitDate"=>$submitDate,"title"=>$title,"type"=>$type,"milestoneImages"=>$milestoneImages,"questionlist"=>$questionlist);
        	}
        }

         return view('admin.missionmanagement.pendingmission',compact('alldata','mission','id','userId','missionStatus'));
        //print_r($alldata);
        //exit();

    }

	
	
	 public function postCreate(MissionRequest $request) {
		 
		// $checkduplicate = DB::table('tblcustomer')->where([['fname', '=',$request->fname],['lname', '=',$request->lname]])->count();
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
		
		 if ($countemail==0) {
             
             $proofTypeId=isset($request->proofTypeId)?(($request->proofTypeId)):0;
             $interestId=isset($request->interestId)?(($request->interestId)):0;
             $levelId=isset($request->levelId)?(($request->levelId)):0;

			 $mission = new Mission();
			 $mission->missionName = isset($request->missionName)?(ltrim($request->missionName)):"";
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
			 
			 $mission->isActive=$request->isActive;
			 $mission->save();
			 $missionId=$mission->id;

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
                               //$businesswidth=$common->getImageSizeValue('business_image_width');
						       //$businessheight=$common->getImageSizeValue('business_image_height');

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
								 //$businessusers->profilePicture=$category_image;
						         }

							}

						}

						/*if ($request->file('profilePicture')) {
						 $file = $request->file('profilePicture');
						 $size = getimagesize($file);
						 $ratio = $size[0]/$size[1];
						 $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

						 $common=new CommanController;
						 $businesswidth=$common->getImageSizeValue('business_image_width');
						 $businessheight=$common->getImageSizeValue('business_image_height');
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
						 $category_destinationPath = public_path('/businessusers/thumbnail_images');
						//$thumb_img = Image::make($file->getRealPath())->resize(150, 150);
						 $check=in_array($extension,$allowedfileExtension);
						 if($check) {
						 $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
						 $thumb_img->save($category_destinationPath.'/'.$category_image,80);
						 $filename=$file->getClientOriginalName();
						 $destinationPath = 'businessusers';
						 $file->move($destinationPath,$file->getClientOriginalName());
						 $businessusers->profilePicture=$category_image;
						}
			        }*/
								   
			 
			 
			
			 flash()->success('Mission has  added successfully.');
			 return redirect()->to('/admin/missionmanagement');
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
		  $mission->save();
		  flash()->success('Mission status has updated successfully.');
		 return redirect()->to('/admin/missionmanagement');
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
	   	$missionenroll=DB::select( DB::raw("
Select customermission.*,customer.*,customermission.id as custmissionId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where  mission.isActive=1 and  mission.id=".$missionId." and customer.isActive=1  order by mission.startDate"));
	   	return view('admin.missionmanagement.enrollment',compact('mission','missionenroll','missionId','missionStatus'));	   
	   }

	   

	   public function missionUnleave($custmissionId) {
           $common=new CommanController;
           $delete1=DB::delete("delete from tblcustomermission where id=$custmissionId");
		   $msg='You are successfully unleave this mission.';
		   echo $msg;
		   exit();
		   //flash()->success($msg);
		  //return redirect()->to('/admin/missionmanagement/enrollment/'.$missionId);

	   }


	   public function missionenolldata(Request $request,$missionId,$status) {

           //exit;
         
	   	   $sWhere="";

	   	  // echo print_r($request->all());
	   	   //exit;

           if (isset($_REQUEST['searchtxt']) && $_REQUEST['searchtxt'] != "") {
           $sSearch =trim($_REQUEST['searchtxt']); 
      $sWhere .= "and ( `customer`.`fname` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR `customer`.lname LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR  `customer`.`email` LIKE '%".trim($_REQUEST['searchtxt']) ."%' OR `mission`.missionName LIKE '%".trim($_REQUEST['searchtxt']) ."%'";
      $sWhere .= " )";
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
           
          $draw=1; 
          if($_REQUEST['draw']) {
          	$draw=$_REQUEST['draw'];
          }

           if ($isActive!='') {
              $sWhere.=" and customermission.status='".$isActive."'";
            }

            if ($status!=0 && $isActive=='') {
                $sWhere.=" and customermission.status='".$status."'";
            }

          $missionenrollcn=DB::select( DB::raw("
Select customermission.*,customer.*,customermission.id as custmissionId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where  mission.isActive=1 and  mission.id=".$missionId." and customer.isActive=1 ".$sWhere." order by mission.startDate"));

          $missionenroll=DB::select( DB::raw("
Select customermission.*,customer.*,customermission.id as custmissionId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where  mission.isActive=1 and  mission.id=".$missionId." and customer.isActive=1 ".$sWhere." order by mission.startDate $order"));
           
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
                      } else {
                      	$statusName='';
                      }

                      $link=url('/').'/admin/missionmanagement/viewdetails/'.$custmissionId;
                      $unleave=url('/').'/admin/missionmanagement/unleavemission/'.$missionId."/".$custmissionId;
                      
                      $action='<a href="javascript:void(0)" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-list"></i>View Details</a>';
                      
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
	   
	   public function getEdit($id)
       {
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

         return view('admin.missionmanagement.addedit',compact('mission','idprooftype','areaofinterest','missionAreaofInterestList','missionTargetLevelList','levelmanagement','missionImageList','businessusers','businessusersbeacon','enrollcount','quote_validation','multiple_image_note','missionimagewidth','missionimageheight'));
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

         $pendingmission=DB::select( DB::raw("
Select customer.*,customermission.id as custmissionId,mission.id as missionId,customermission.status as missionStatus,customermission.submitDate from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where  mission.isActive=1 and  mission.id=".$id." and customer.isActive=1 and customermission.status=3  order by mission.startDate"));

         $pendingmissionCount=count($pendingmission);

         $completedmission=DB::select( DB::raw("
Select customer.*,customermission.id as custmissionId,mission.id as missionId,customermission.status as missionStatus,customermission.submitDate from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where  mission.isActive=1 and  mission.id=".$id." and customer.isActive=1 and customermission.status=4  order by mission.startDate"));
         $completedmissionCount=count($completedmission);

         $expiredmission=DB::select( DB::raw("
Select customer.*,customermission.id as custmissionId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where  mission.isActive=1 and  mission.id=".$id." and customer.isActive=1 and customermission.status=5  order by mission.startDate"));

         $expiredmissionCount=count($expiredmission);


         $allmission=DB::select( DB::raw("
Select customer.*,customermission.id as custmissionId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where  mission.isActive=1 and  mission.id=".$id." and customer.isActive=1   order by mission.startDate"));
         $allmissionCount=count($allmission);

         return view('admin.missionmanagement.summary',compact('mission','missionImageList','missionTargetLevelName','missionAreaofInterestName','pendingmission','completedmission','pendingmissionCount','completedmissionCount','expiredmissionCount','allmissionCount'));
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
		//$surveyquestion = SurveyQuestion::find($id);

		$surveyquestion = SurveyQuestion::where('questionaireId',$questionaireId)->get();
		//$mileStoneImageList=$common->mileStoneImageList($milestoneId);
		//echo print_r($mileStoneImageList);
		//exit;
        $questionaire = MissionMilestone::find($questionaireId);
         
         //$getoptionList=DB::select( DB::raw("Select group_concat(optionName) from tblsurveyquestionoption as surveyquestionoption where questionId"));
         
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

			  flash()->success('Mission Questionaire Question has added successfully.');
			 return redirect()->to('/admin/missionmanagement/questionaire/edit/'.$id."/".$questionaireId);

              
	   }

	   public function editpostMissionQuestionaireQuestionOptionsQuestions(Request $request,$id,$questionaireId,$questionId) {
               $common=new CommanController;
	   	       
	   	       /*print_r($request->all());
	   	       exit();*/

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

			   //$questionId=$surveyquestion->id;

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

			  flash()->success('Mission Questionaire Question has updated successfully.');
			 return redirect()->to('/admin/missionmanagement/questionaire/edit/'.$id."/".$questionaireId);

              
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

               flash()->success('Mission Questionaire has  added successfully.');
			 return redirect()->to('/admin/missionmanagement/questionaire/edit/'.$id."/".$questionaire->id);

	  	     } else {
	  	     	 flash()->error('This Mission Questionaire title has been already taken. Please try with another title.');
			 return redirect()->to('/admin/missionmanagement/questionaire/add/'.$id);
	  	     }


	  }
          
       public function completedMission($id,$missionId,$customerId) {
       	  $common=new CommanController;

       	  $mission = Mission::find($missionId);
       	  $cashReward=$mission->cashReward;
       	  $points=$mission->points;

       	  $custDetails=$common->CustomerDetails($customerId);

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
          echo "1";
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
            
            $house=($request->house)?($request->house):"";
			$landmark=($request->landmark)?($request->landmark):"";
			$search_location=($request->search_location)?($request->search_location):"";
			$lat=($request->lat)?($request->lat):0;
			$long=($request->long)?($request->long):0;
			$vicinityInMiles=($request->vicinityInMiles)?($request->vicinityInMiles):0;
			$country=($request->country)?($request->country):"";
		    $state=($request->state)?($request->state):"";
		    $city=($request->city)?($request->city):"";


		    $mission = Mission::find($id);
		    $interestId=isset($request->interestId)?(($request->interestId)):0;
            $levelId=isset($request->levelId)?(($request->levelId)):0;
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
		    $mission->save();

		    if ($interestId!=0) {
		    	$delete1=DB::delete("delete from tblmissiontargetareaofinterest where missionId=$id");
			   foreach ($interestId as  $value) {
				 DB::table('tblmissiontargetareaofinterest')->insert(
                  ['missionId'=>$id,'interestId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 if ($levelId!=0) {
			 	$delete2=DB::delete("delete from tblmissiontargetlevel where missionId=$id");
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
								 //$businessusers->profilePicture=$category_image;
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
			                        /*DB::table('tblmissionimages')->insert(
			                  ['missionId'=>$missionId,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$category_image]);*/
								 //$businessusers->profilePicture=$category_image;
						         }

							}

						}		

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
