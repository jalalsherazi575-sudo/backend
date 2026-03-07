<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\AdsRequest;
use Illuminate\Http\Request;
use Laraspace\Customer;
use Laraspace\Ads;
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

class AdsController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {

		$ads= Ads::orderby('id','desc')->get();
       return view('admin.adsmanagement.index',compact('ads'));
	}

	public function getAdsData(Request $request) {

		 $sWhere="";

		 if (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != "") {
           $sSearch =trim($_REQUEST['search']['value']); 
           $sWhere .= "and ( `ads`.`adsName` LIKE '%".trim($sSearch) ."%' OR `businessusers`.companyName LIKE '%".trim($sSearch) ."%' OR  DATE_FORMAT(`ads`.`startDate`,'%d %M %Y') LIKE '%".trim($sSearch) ."%' OR  DATE_FORMAT(`ads`.`endDate`,'%d %M %Y') LIKE '%".trim($sSearch) ."%' OR  DATE_FORMAT(`ads`.`createdDate`,'%d %M %Y') LIKE '%".trim($sSearch) ."%'";
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

          $sort=' order by  ads.id desc';

          if ($orderbycolm==1) {
              $sort="order by  ads.adsName $orderbydir";
          }
          
          if ($orderbycolm==2) {
              $sort="order by  businessusers.companyName $orderbydir";
          }
          
          if ($orderbycolm==3) {
              $sort="order by  ads.startDate $orderbydir";
          }
          
          if ($orderbycolm==4) {
              $sort="order by   ads.endDate $orderbydir";
          }
          
          if ($orderbycolm==5) {
              $sort="order by  ads.createdDate $orderbydir";
          }

          if ($orderbycolm==7) {
              $sort="order by  ads.isActive $orderbydir";
          }

          $AdCn=DB::select( DB::raw("Select ads.*,businessusers.companyName  from tblads as ads
             inner join tblbusinessusers as businessusers on ads.businessId=businessusers.id
             where 1=1 ".$sWhere." ".$sort.""));

          $Ad=DB::select( DB::raw("Select ads.*,businessusers.companyName  from tblads as ads
             inner join tblbusinessusers as businessusers on ads.businessId=businessusers.id
             where 1=1 ".$sWhere." ".$sort." ".$order.""));

           $counTotal=count($AdCn);
          
	          $output = array(
	          "recordsTotal" => $counTotal,
	          "recordsFiltered" => $counTotal,
	          "draw" => $draw,
	          "data" => array()
	          );

	         if ($Ad) {
                   $i=1;
                   $totalUn=0;

                   $url=url('/');
                   $currentMenuId=$this->getcurrent();
                  
                  foreach ($Ad as  $cust) {

                      $row = array();

                      $id=($cust->id)?($cust->id):0;
                      $adsName=($cust->adsName)?($cust->adsName):"";
                      $companyName=($cust->companyName)?($cust->companyName):"";
                      $startDate=($cust->startDate)?($cust->startDate):"";
                      $endDate=($cust->endDate)?($cust->endDate):"";
                      $createdDate=($cust->createdDate)?($cust->createdDate):"";
                      $isActive=($cust->isActive)?($cust->isActive):"";
                      $pushAdsBeacon=($cust->pushAdsBeacon)?($cust->pushAdsBeacon):"";
                      $pushAdsGps=($cust->pushAdsGps)?($cust->pushAdsGps):0;
                     // $pendingmissionCount=($cust->pendingmissionCount)?($cust->pendingmissionCount):0;
                      
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
                      } else {
                      	  $st='InActive';
                      }
                      
                      $adstype='';

                      if ($pushAdsGps!=0) {
                         $adstype='GPS';
                      }

                      if ($pushAdsBeacon!=0) {
                         $adstype.=' Beacon';
                      }
                      
                      $action="";

                     

                      if(checkPermission(Auth::user()->id,'update',$currentMenuId)) {
                          $action.='<a href="'.$url.'/admin/adsmanagement/edit/'.$id.'" class="btn btn-default btn-sm" title="Edit Ads"><i class="icon-fa icon-fa-edit"></i>Edit</a>';
                      }

                      if(checkPermission(Auth::user()->id,'delete',$currentMenuId)) {
                          $action.='<a onclick="return check_delete('.$id.');" class="btn btn-default btn-sm" data-token="'.csrf_token().'" title="Delete Ads" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i>Delete</a>';
                      }

                      if(checkPermission(Auth::user()->id,'update',$currentMenuId)) {
                          if ($isActive==1) {
                           $action.='<a href="'.$url.'/admin/adsmanagement/status/0/'.$id.'" class="btn btn-default btn-sm" title="Inactive Ads"><i class="icon-fa icon-fa-lock"></i>Inactive</a>';
                          } else {
                           $action.='<a href="'.$url.'/admin/adsmanagement/status/1/'.$id.'" class="btn btn-default btn-sm" title="Active Ads"><i class="icon-fa icon-fa-lock"></i>Active</a>';
                          }
                      }



                      
                      $row[]='<input type="checkbox" class="uniquechk" name="del[]" value="'.$id.'">';
                      $row[]=$adsName;
                      $row[]=$companyName;
                      $row[]=$stDate;
                      $row[]=$enDate;
                      $row[]=$crDate;
                      $row[]=$adstype;
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
		$businessusers = BusinessUsers::where([['isActive', '=','1']])->orderby('companyName','asc')->get(['id','companyName']);
		$idprooftype=IdProofType::where([['isActive', '=','1']])->get();
		$areaofinterest=AreaOfInterest::where([['isActive', '=','1']])->get();
		$levelmanagement = LevelManagement::where([['isActive', '=','1']])->get(['id','name']);
		$addsimagewidth=$common->getImageSizeValue('ads_image_width');
		$addsimageheight=$common->getImageSizeValue('ads_image_height');
	   return view('admin.adsmanagement.addedit',compact('businessusers','idprooftype','areaofinterest','businessusersbeacon','levelmanagement','addsimagewidth','addsimageheight'));
	}

	


    

	
	
	 public function postCreate(AdsRequest $request) {
		 $countemail = DB::table('tblads')->where('adsName', '=', $request->adsName)->count();
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

			 $ads = new Ads();
			 $ads->adsName = isset($request->adsName)?(ltrim($request->adsName)):"";
			 $ads->businessId = isset($request->businessId)?(ltrim($request->businessId)):0;
			 $ads->startDate = isset($request->startDate)?(ltrim(date("Y-m-d",strtotime($request->startDate)))):"";

			 $ads->startTime = isset($request->startTime)?(ltrim($request->startTime)):"";
			 $ads->adsClickRedirectUrl = isset($request->adsClickRedirectUrl)?(ltrim($request->adsClickRedirectUrl)):"";

             $ads->endTime = isset($request->endTime)?(ltrim($request->endTime)):"";


			 $ads->endDate = isset($request->endDate)?(ltrim(date("Y-m-d",strtotime($request->endDate)))):"";
			 

			 $ads->pushAdsBeacon = isset($request->pushAdsBeacon)?(ltrim($request->pushAdsBeacon)):0;
			 $ads->footfallCalcBeacon = isset($request->footfallCalcBeacon)?(ltrim($request->footfallCalcBeacon)):0;
			 $ads->pushAdsGps = isset($request->pushAdsGps)?(ltrim($request->pushAdsGps)):0;

			 $ads->ageFrom = isset($request->ageFrom)?(ltrim($request->ageFrom)):0;
			 $ads->ageTo = isset($request->ageTo)?(ltrim($request->ageTo)):0;
			 $ads->isMale = isset($request->isMale)?(ltrim($request->isMale)):0;
			 $ads->isFemale = isset($request->isFemale)?(ltrim($request->isFemale)):0;
			  
			 
			 
			
             $ads->vicinityInMiles=$vicinityInMiles;
			 $ads->location=$search_location;
			 $ads->latitude=$lat;
			 $ads->longitude=$long;
			 $ads->house_flatNo=$house;
			 $ads->landmark=$landmark;
			 $ads->country=$country;
			 $ads->state=$state;
			 $ads->city=$city;
             
             
             
             
			
			 $ads->createdDate=date('Y-m-d H:i:s');
			 
			 $ads->isActive=$request->isActive;
			 $ads->save();
			 $adsId=$ads->id;

			 if ($interestId!=0) {
			   foreach ($interestId as  $value) {
				 DB::table('tbladstargetareaofinterest')->insert(
                  ['adsId'=>$adsId,'interestId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 if ($levelId!=0) {
			   foreach ($levelId as  $value) {
				 DB::table('tbladstargetlevel')->insert(
                  ['adsId'=>$adsId,'levelId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 

						if($request->hasFile('photo')) {
                            $common=new CommanController;
							$allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
							$files = $request->file('photo');
							foreach($files as $file) {
                               $files =$file;
                               $size = getimagesize($files);
                               $ratio = $size[0]/$size[1];
						       $missionimagewidth=$common->getImageSizeValue('ads_image_width');
		                       $missionimageheight=$common->getImageSizeValue('ads_image_height'); 
						       
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
						         $category_destinationPath = public_path('/adsimages/thumbnail_images');
						         $check=in_array($extension,$allowedfileExtension);
						          if($check) {
								 //$thumb_img = Image::make($files->getRealPath())->resize($width, $height);
								 //$thumb_img->save($category_destinationPath.'/'.$category_image,80);
								 $filename=$files->getClientOriginalName();
								 $destinationPath = 'adsimages/thumbnail_images';
								 $files->move($destinationPath,$files->getClientOriginalName());
								    $isUploaded=1;
			                                 DB::table('tbladsimages')->insert(
			                  ['adsId'=>$adsId,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$filename]);
								 //$businessusers->profilePicture=$category_image;
						         }

							}

						}

			 
			
			 flash()->success('Ads has  added successfully.');
			 return redirect()->to('/admin/adsmanagement');
		 } else {
		     flash()->error('This Ads name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/adsmanagement/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	   	  $common=new CommanController;
		  $mission = Ads::find($id);
		  $mission->isActive=$status;
		  $mission->save();
		  flash()->success('Ads status has updated successfully.');
		 return redirect()->to('/admin/adsmanagement');
	   }
	   
	   public function Delete($id) {
	   	    $common=new CommanController;
		         $user = Ads::find($id);
                 $user->delete();
			     echo 2;
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
		   
			  
			  $ads = Ads::find($val);
              $ads->delete();
			
		  }
		  
		   $msg='Selected Ads has been deleted successfully.';
		  
		  flash()->success($msg);
		  
		  return redirect()->to('/admin/adsmanagement');
		  //exit;
	   }
	   
	   public function getEdit($id)
       {
        $common=new CommanController;
        $ads=$common->AdsDetails($id);
        $businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1']])->get(['id','beaconName']);
		$businessusers = BusinessUsers::where([['isActive', '=','1']])->orderby('companyName','asc')->get(['id','companyName']);
		$idprooftype=IdProofType::where([['isActive', '=','1']])->get();
		$areaofinterest=AreaOfInterest::where([['isActive', '=','1']])->get();
		$levelmanagement = LevelManagement::where([['isActive', '=','1']])->get(['id','name']);
		$adsAreaofInterestList=$common->adsAreaofInterestList($id);
		$adsTargetLevelList=$common->adsTargetLevelList($id);
		$adsImageList=$common->adsImageList($id);
		
		$addsimagewidth=$common->getImageSizeValue('ads_image_width');
		$addsimageheight=$common->getImageSizeValue('ads_image_height');

         return view('admin.adsmanagement.addedit',compact('ads','idprooftype','areaofinterest','adsAreaofInterestList','adsTargetLevelList','levelmanagement','adsImageList','businessusers','businessusersbeacon','addsimagewidth','addsimageheight'));
       }

       public function getShow($id) {
	   	 $common=new CommanController;
         $ads=$common->AdsDetails($id);
         $adsImageList=$common->adsImageList($id);
         $adsTargetLevelName=$common->adsTargetLevelName($id);
         $adsAreaofInterestName=$common->adsAreaofInterestName($id);
         return view('admin.adsmanagement.show',compact('ads','adsImageList','adsTargetLevelName','adsAreaofInterestName'));
	   }

	  
	   
	   public function deleteImages($id) {
	   	$delete=DB::delete("delete from tbladsimages where id=$id");
	   	echo 1;
	   	exit();
	   }

	   
	   public function postEdit(AdsRequest $request, $id) {
            
            $house=($request->house)?($request->house):"";
			$landmark=($request->landmark)?($request->landmark):"";
			$search_location=($request->search_location)?($request->search_location):"";
			$lat=($request->lat)?($request->lat):0;
			$long=($request->long)?($request->long):0;
			$vicinityInMiles=($request->vicinityInMiles)?($request->vicinityInMiles):0;
			$country=($request->country)?($request->country):"";
		    $state=($request->state)?($request->state):"";
		    $city=($request->city)?($request->city):"";


		    $ads = Ads::find($id);
		    $interestId=isset($request->interestId)?(($request->interestId)):0;
            $levelId=isset($request->levelId)?(($request->levelId)):0;
			$ads->adsName = isset($request->adsName)?(ltrim($request->adsName)):"";
			$ads->businessId = isset($request->businessId)?(ltrim($request->businessId)):0;
			$ads->startDate = isset($request->startDate)?(ltrim(date("Y-m-d",strtotime($request->startDate)))):"";
			$ads->endDate = isset($request->endDate)?(ltrim(date("Y-m-d",strtotime($request->endDate)))):"";
			$ads->startTime = isset($request->startTime)?(ltrim($request->startTime)):"";
            $ads->endTime = isset($request->endTime)?(ltrim($request->endTime)):"";
            $ads->adsClickRedirectUrl = isset($request->adsClickRedirectUrl)?(ltrim($request->adsClickRedirectUrl)):"";
			//$ads->quotaLimit = isset($request->quotaLimit)?(ltrim($request->quotaLimit)):0;
			//$ads->durationOfMisson = isset($request->durationOfMisson)?(ltrim($request->durationOfMisson)):0;
			//$ads->title = isset($request->title)?(ltrim($request->title)):"";
            //$ads->description = isset($request->description)?(ltrim($request->description)):"";
            //$ads->estimationTime = isset($request->estimationTime)?(ltrim($request->estimationTime)):"";
			//$ads->rewardDescription = isset($request->rewardDescription)?(ltrim($request->rewardDescription)):"";
			//$ads->eligibiltyCriteria = isset($request->eligibiltyCriteria)?(ltrim($request->eligibiltyCriteria)):"";
			//$ads->cashReward = isset($request->cashReward)?(ltrim($request->cashReward)):0;
			//$ads->points = isset($request->points)?(ltrim($request->points)):0;
			$ads->pushAdsBeacon = isset($request->pushAdsBeacon)?(ltrim($request->pushAdsBeacon)):0;
			$ads->footfallCalcBeacon = isset($request->footfallCalcBeacon)?(ltrim($request->footfallCalcBeacon)):0;
			$ads->pushAdsGps = isset($request->pushAdsGps)?(ltrim($request->pushAdsGps)):0;
			$ads->ageFrom = isset($request->ageFrom)?(ltrim($request->ageFrom)):0;
			$ads->ageTo = isset($request->ageTo)?(ltrim($request->ageTo)):0;
			$ads->isMale = isset($request->isMale)?(ltrim($request->isMale)):0;
			$ads->isFemale = isset($request->isFemale)?(ltrim($request->isFemale)):0;
			//$ads->isVerified = isset($request->isVerified)?(ltrim($request->isVerified)):0;
			//$ads->isUnverified = isset($request->isUnverified)?(ltrim($request->isUnverified)):0;
            $ads->updatedDate=date('Y-m-d H:i:s');
            $ads->vicinityInMiles=$vicinityInMiles;
			$ads->location=$search_location;
			$ads->latitude=$lat;
			$ads->longitude=$long;
			$ads->house_flatNo=$house;
			$ads->landmark=$landmark;
			$ads->country=$country;
			$ads->state=$state;
			$ads->city=$city;
		    $ads->isActive=$request->isActive;
		    $ads->save();

		    if ($interestId!=0) {
		    	$delete1=DB::delete("delete from tbladstargetareaofinterest where adsId=$id");
			   foreach ($interestId as  $value) {
				 DB::table('tbladstargetareaofinterest')->insert(
                  ['adsId'=>$id,'interestId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 if ($levelId!=0) {
			 	$delete2=DB::delete("delete from tbladstargetlevel where adsId=$id");
			   foreach ($levelId as  $value) {
				 DB::table('tbladstargetlevel')->insert(
                  ['adsId'=>$id,'levelId'=>$value,'createdDate'=>date('Y-m-d H:i:s')]);
			   }
			 }

			 
			     if($request->hasFile('photo')) {

                            $common=new CommanController;
							$allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif','heic'];
							$files = $request->file('photo');
							
							foreach($files as $file) {

                               $files =$file;
                               $size = getimagesize($files);
                               $ratio = $size[0]/$size[1];
						       $missionimagewidth=$common->getImageSizeValue('ads_image_width');
		                       $missionimageheight=$common->getImageSizeValue('ads_image_height'); 
						       
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
						         $category_destinationPath = public_path('/adsimages/thumbnail_images');
						         $check=in_array($extension,$allowedfileExtension);
						          if($check) {
								// $thumb_img = Image::make($files->getRealPath())->resize($width, $height);
								// $thumb_img->save($category_destinationPath.'/'.$category_image,80);
								 $filename=$files->getClientOriginalName();
								 $destinationPath = 'adsimages/thumbnail_images';
								 $files->move($destinationPath,$files->getClientOriginalName());
								    $isUploaded=1;
			                                 DB::table('tbladsimages')->insert(
			                  ['adsId'=>$id,'createdDate'=>date('Y-m-d H:i:s'),'image'=>$filename]);
								 //$businessusers->profilePicture=$category_image;
						         }

							}

					}

		        flash()->success('Ads has updated successfully.');
		        return redirect()->to('/admin/adsmanagement');
	   }

	   
	   public function deleteQuestionaire($id) {
		         $user = MissionMilestone::find($id);
                 $user->delete();
			     echo 2;
		         exit();
	   }


}
