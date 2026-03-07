<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\AdsRequest;
use Laraspace\Http\Requests\BusinessUsersRequest;
use Laraspace\Http\Requests\BusinessUsersBeaconRequest;
use Laraspace\Http\Requests\MissionRequest;
use Illuminate\Http\Request;
use Laraspace\Customer;
use Laraspace\Mission;
use Laraspace\MissionMilestone;
use Laraspace\MissionQuestionaire;
use Laraspace\Ads;
use Laraspace\LevelManagement;
use Laraspace\BusinessUsersBeacon;
use Laraspace\BusinessUsers;
use Laraspace\MissionSubscriptionPlan;
use Laraspace\AdsSubscriptionPlan;
use Image;
use Laraspace\BusinessCategory;
use Laraspace\Country;
use Laraspace\IdProofType;
use Laraspace\AreaOfInterest;
use Laraspace\SurveyQuestion;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\IndustryCategory;
use Illuminate\Contracts\Auth\Guard;
use Auth;
//use Illuminate\Foundation\Auth\AuthenticatesUsers;

class BusinessLoginController extends Controller
{

	  //use AuthenticatesUsers;

//    protected $redirectTo = '/businessusers/';

    public function __construct()
    {
        $this->middleware('guest:businessuser')->except('logout');

        //$this->middleware('guest:employee')->except('logout');
    }

    public function businesslogin()
    {
        return view('admin.sessions.businesslogin');
    }

    public function adsPlan($id) {
        $common=new CommanController;
      $businessusers=$common->BusinessUsersDetails($id);
      $adsplan = DB::table('tbladstranscation')->where([['businessId', '=',$id]])->orderby('id','desc')->get();
      return view('admin.businessusers.businessadsplan',compact('businessusers','adsplan'));
    }

    public function missionPlan($id) {
        $common=new CommanController;
      $businessusers=$common->BusinessUsersDetails($id);
      $missionplan = DB::table('tblmissiontranscation')->where([['businessId', '=',$id]])->orderby('id','desc')->get();
      return view('admin.businessusers.businessmissionplan',compact('businessusers','missionplan'));
    }
    
    public function postBusinessLogin(Request $request) {
        
        if (BusinessUsers::login($request)) {
        	  //print_r(session()->all());
            //exit();
            $user = Auth::guard('businessuser')->user();
            $loginUserId=$user->id;


            return redirect()->to('/businessuser/show/'.$loginUserId);
            flash()->success('Welcome to Mission App.');
            
        }
        flash()->error('Invalid Login Credentials');
        
        return redirect()->back();

       /*$checkUser=BusinessUsers::where('emailAddress',$request->email)->first();

       if ($checkUser) {
           print_r(session()->all());
           exit();
           return redirect()->to('/');


       } else {
        flash()->error('Invalid Login Credentials');
        
        return redirect()->back();
       }*/
    }



    public function MissiongetSummary($id) {

         $common=new CommanController;
         $mission=$common->MissionDetails($id);
         $missionImageList=$common->missionImageList($id);
         $missionTargetLevelName=$common->missionTargetLevelName($id);
         $missionAreaofInterestName=$common->missionAreaofInterestName($id);

         $pendingmission=DB::select( DB::raw("
Select customer.*,customermission.id as custmissionId,mission.id as missionId,customermission.status as missionStatus,customermission.submitDate from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where  mission.isActive=1 and  mission.id=".$id." and customer.isActive=1 and customermission.status IN (3,4,7)  order by mission.startDate"));

         $pendingmissionCount=count($pendingmission);


         $subpendingmission=DB::select( DB::raw("
Select customer.*,customermission.id as custmissionId,mission.id as missionId,customermission.status as missionStatus,customermission.submitDate from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where    mission.id=".$id." and customer.isActive=1 and customermission.status IN (3)  order by mission.startDate"));

         $subpendingmissionCount=count($subpendingmission);

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

         $rejectedmission=DB::select( DB::raw("
Select customer.*,customermission.id as custmissionId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where    mission.id=".$id." and customer.isActive=1 and customermission.status=7  order by mission.startDate"));
         $rejectedmissionCount=count($rejectedmission);

         $quitmission=DB::select( DB::raw("
Select customer.*,customermission.id as custmissionId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where    mission.id=".$id." and customer.isActive=1 and customermission.status=2  order by mission.startDate"));
         $quitmissionCount=count($quitmission);



         $allmission=DB::select( DB::raw("
Select customer.*,customermission.id as custmissionId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where  mission.isActive=1 and  mission.id=".$id." and customer.isActive=1   order by mission.startDate"));
         $allmissionCount=count($allmission);

         $footFallCount = DB::table('tblfootfallbeacon')->where('missionId','=',$id)->count();

         $totalPayable=DB::select( DB::raw("Select IFNULL(ROUND(sum(cashReward),2),0) as payableamt from tblusercredithistory where status=1 and missionId='$id'"));
         $totalPayableAmount=isset($totalPayable->payableamt)?($totalPayable->payableamt):0;

         $reVisit=DB::select( DB::raw("Select count(temp.dates) as totalcn from
(Select  DATE_FORMAT(createdDate, '%y-%m-%d') as dates from tblfootfallbeacon where missionId='$id' GROUP BY DATE_FORMAT(createdDate, '%y-%m-%d'),customerId)  as temp
"));
         $totalRevisit=isset($reVisit[0]->totalcn)?($reVisit[0]->totalcn):0;

         return view('admin.missionmanagement.summarybusiness',compact('mission','missionImageList','missionTargetLevelName','missionAreaofInterestName','pendingmission','completedmission','pendingmissionCount','completedmissionCount','expiredmissionCount','allmissionCount','rejectedmissionCount','quitmissionCount','footFallCount','totalPayableAmount','totalRevisit','subpendingmission','subpendingmissionCount'));
     }

     public function getMilestonesMission($id) {
         $common=new CommanController;
         $mission=$common->MissionDetails($id);
         $missionmilestone=MissionMilestone::where([['missionId', '=',$id]])->get(['id','title','type']);
         $missionquestionaire=MissionQuestionaire::where([['isActive', '=','1'],['missionId', '=',$id]])->get(['id','title']);
         return view('admin.missionmanagement.milestonesbusiness',compact('mission','missionmilestone','missionquestionaire'));
     }

     public function addMissionMilestoneMission($id) {
      $common=new CommanController;
      $mission=$common->MissionDetails($id);
        $businessId=isset($mission)?($mission['businessId']):0;
    $businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$businessId]])->get(['id','beaconName']);
    $businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
    $missionmilestoneimagewidth=$common->getImageSizeValue('mission_milestone_image_width');
    $missionmilestoneimageheight=$common->getImageSizeValue('mission_milestone_image_height');
      return view('admin.missionmanagement.addeditmilestonebusiness',compact('mission','businessusersbeacon','missionmilestoneimagewidth','missionmilestoneimageheight'));
    }

    public function getEditMilestoneMission($id,$milestoneId)
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
         return view('admin.missionmanagement.addeditmilestonebusiness',compact('mission','businessusers','businessusersbeacon','mileStoneImageList','milestone','missionmilestoneimagewidth','missionmilestoneimageheight'));
       }

       public function getEditQuestionaireMission($id,$questionaireId)
       {
        $common=new CommanController;
        $mission=$common->MissionDetails($id);
        $businessId=isset($mission)?($mission['businessId']):0;
        $businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$businessId]])->get(['id','beaconName']);
        $businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
        $surveyquestion = SurveyQuestion::where('questionaireId',$questionaireId)->get();
        $questionaire = MissionMilestone::find($questionaireId);

         return view('admin.missionmanagement.addeditquestionairebusiness',compact('mission','businessusers','businessusersbeacon','questionaire','surveyquestion'));
       }

       public function MissionMilestonepostCreateMission(Request $request,$id) {
            
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
                     //$businessusers->profilePicture=$category_image;
                         }

                  }

            }

               flash()->success('Mission Milestone has added successfully.');
       return redirect()->to('/businessuser/missionmanagement/milestones/'.$id);

           } else {
             flash()->error('This Mission Milestone title has been already taken. Please try with another title.');
       return redirect()->to('/businessuser/missionmanagement/milestones/add/'.$id);
           }


    }

    public function addMissionQuestionaireQuestionOptionsMission(Request $request,$id) {
       
       $common=new CommanController;
         $mission=$common->MissionDetails($id);
         $rank=$common->getQuestionMaxRank();
         $pipedquestion=DB::table('tblsurveyquestion')->Join('tblsurveyquestionoption', 'tblsurveyquestion.id', '=', 'tblsurveyquestionoption.questionId')->select('tblsurveyquestion.id','tblsurveyquestion.question')->whereIn('tblsurveyquestion.answerType',[2,3])->groupby('tblsurveyquestion.id')->get();
         $question=DB::table('tblsurveyquestion')->Join('tblsurveyquestionoption', 'tblsurveyquestion.id', '=', 'tblsurveyquestionoption.questionId')->select('tblsurveyquestion.id','tblsurveyquestion.question')->whereIn('tblsurveyquestion.answerType',[2,3])->groupby('tblsurveyquestion.id')->get();

         return view('admin.missionmanagement.addeditquestionairequestionoptionbusiness',compact('mission','rank','question','pipedquestion'));
     }

     public function addMissionQuestionaireQuestionOptionsQuestionsMission(Request $request,$id,$questionaireId) {
       
         $common=new CommanController;
         $mission=$common->MissionDetails($id);
         $rank=$common->getQuestionMaxRank();
         $pipedquestion=DB::table('tblsurveyquestion')->Join('tblsurveyquestionoption', 'tblsurveyquestion.id', '=', 'tblsurveyquestionoption.questionId')->select('tblsurveyquestion.id','tblsurveyquestion.question')->where('tblsurveyquestion.missionId',$id)->where('tblsurveyquestion.questionaireId',$questionaireId)->whereIn('tblsurveyquestion.answerType',[1,2,3])->groupby('tblsurveyquestion.id')->get();
         $question=DB::table('tblsurveyquestion')->Join('tblsurveyquestionoption', 'tblsurveyquestion.id', '=', 'tblsurveyquestionoption.questionId')->select('tblsurveyquestion.id','tblsurveyquestion.question')->where('tblsurveyquestion.missionId',$id)->where('tblsurveyquestion.questionaireId',$questionaireId)->whereIn('tblsurveyquestion.answerType',[1,2,3])->groupby('tblsurveyquestion.id')->get();
         $missionmilestonequestionaireimagewidth=$common->getImageSizeValue('mission_milestone_questionaire_image_width');
     $missionmilestonequestionaireimageheight=$common->getImageSizeValue('mission_milestone_questionaire_image_height');
         return view('admin.missionmanagement.addeditquestionairequestionoptionbusiness',compact('mission','rank','question','pipedquestion','questionaireId','missionmilestonequestionaireimagewidth','missionmilestonequestionaireimageheight'));
     }

     public function editMissionQuestionaireQuestionOptionsQuestionsMission(Request $request,$id,$questionaireId,$questionId) {
       
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


         return view('admin.missionmanagement.addeditquestionairequestionoptionbusiness',compact('mission','rank','question','pipedquestion','questionaireId','surveyquestion','surveyoptions','depedentquestion','depedentquestionoption','missionmilestonequestionaireimagewidth','missionmilestonequestionaireimageheight'));
     }

     public function postMissionQuestionaireQuestionOptionsQuestionsMission(Request $request,$id,$questionaireId) {
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
       return redirect()->to('/businessuser/missionmanagement/questionaire/edit/'.$id."/".$questionaireId);

              
     }

     public function editpostMissionQuestionaireQuestionOptionsQuestionsMission(Request $request,$id,$questionaireId,$questionId) {
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
       return redirect()->to('/businessuser/missionmanagement/questionaire/edit/'.$id."/".$questionaireId);

              
     }

     public function MissiondeleteImages($id) {
      $delete=DB::delete("delete from tblmissionimages where id=$id");
      echo 1;
      exit();
     }


      public function MissiondeleteLogo($id) {
      $delete=DB::delete("update tblmission SET logo='' where id=$id");
      echo 1;
      exit();
     }

     public function MissiondeleteQuestionaireImages($id) {
      $user = SurveyQuestion::find($id);
        $user->questionImage='';
        $user->save();
      echo 1;
      exit();
     }

     public function deleteMilestoneImagesMission($id) {
      $delete=DB::delete("delete from tblmilestoneimages where id=$id");
      echo 1;
      exit();
     }

     public function postEditMission(MissionRequest $request, $id) {
            
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
              $mission->isreOpen=1;
              $mission->isActive=1;
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
     return redirect()->to('/businessuser/missionmanagement');
     }

     public function MissionMilestonepostEditMission(Request $request, $id,$milestoneId) {
          $common=new CommanController;

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

         flash()->success('Mission Milestone has updated successfully.');
         return redirect()->to('/businessuser/missionmanagement/milestones/'.$id);
     }

     public function deleteQuestionaireMission($id) {
             $user = MissionMilestone::find($id);
             $user->delete();
             echo 2;
             exit();
     }


     public function deleteQuestionaireQuestionMission($id) {
             $delete1=DB::delete("delete from tblsurveyquestionoption where questionId=$id");
             $user = SurveyQuestion::find($id);
             $user->delete();
             echo 2;
             exit();
     }


     /*public function MissioncompletedMission($id,$missionId,$customerId) {
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

       }   */

       public function MissioncompletedMission($id,$missionId,$customerId,$status) {
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

     public function MissionQuestionairepostCreateMission(Request $request,$id) {
            
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

               flash()->success('Mission Questionaire has added successfully.');
               return redirect()->to('/businessuser/missionmanagement/questionaire/edit/'.$id."/".$questionaire->id);

           } else {
             flash()->error('This Mission Questionaire title has been already taken. Please try with another title.');
             return redirect()->to('/businessuser/missionmanagement/questionaire/add/'.$id);
           }


    }

    
    public function addMissionQuestionaireMission($id) {
      $common=new CommanController;
      $mission=$common->MissionDetails($id);

        $businessId=isset($mission)?($mission['businessId']):0;
    $businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$businessId]])->get(['id','beaconName']);
    $businessusers = BusinessUsers::where([['isActive', '=','1']])->get(['id','companyName']);
      return view('admin.missionmanagement.addeditquestionairebusiness',compact('mission','businessusersbeacon','businessusers'));
    }


    public function MissiongetShow($id) {
       $common=new CommanController;
         $mission=$common->MissionDetails($id);
         $missionImageList=$common->missionImageList($id);
         $missionTargetLevelName=$common->missionTargetLevelName($id);
         $missionAreaofInterestName=$common->missionAreaofInterestName($id);
         return view('admin.missionmanagement.showbusiness',compact('mission','missionImageList','missionTargetLevelName','missionAreaofInterestName'));
     }

    public function getEditMission($id)
       {

        $user = Auth::guard('businessuser')->user();
        $loginUserId=$user->id;
        
        $common=new CommanController;
        $mission=$common->MissionDetails($id);
        $businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$loginUserId]])->get(['id','beaconName']);
        $businessusers = BusinessUsers::where([['isActive', '=','1'],['id', '=',$loginUserId]])->get(['id','companyName']);
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

         return view('admin.missionmanagement.addeditbusiness',compact('mission','idprooftype','areaofinterest','missionAreaofInterestList','missionTargetLevelList','levelmanagement','missionImageList','businessusers','businessusersbeacon','enrollcount','quote_validation','multiple_image_note','missionimagewidth','missionimageheight','missionlogowidth','missionlogoheight','customer','missionCustomerEmailAddressList'));
       }

    public function MissionDeleteall(Request $request) {
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
      return redirect()->to('/businessuser/missionmanagement');
      //exit;
     }

    public function deleteMilestoneMission($id) {
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

    public function missionenolldataMission(Request $request,$missionId,$status) {
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

                      $link=url('/').'/businessuser/missionmanagement/viewdetails/'.$custmissionId;
                      $unleave=url('/').'/businessuser/missionmanagement/unleavemission/'.$missionId."/".$custmissionId;
                      
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

    public function missionUnleaveMission($custmissionId) {
           $common=new CommanController;
           $delete1=DB::delete("delete from tblcustomermission where id=$custmissionId");
           $msg='You are successfully unleave this mission.';
           echo $msg;
           exit();
     }

    public function missionEnrollmentsMission(Request $request,$missionId) {
     
      $missionStatus=isset($request->status)?($request->status):0;
      $common=new CommanController;
      $mission=$common->MissionDetails($missionId);
      $missionenroll=DB::select( DB::raw("
Select customermission.*,customer.*,customermission.id as custmissionId from tblmission as mission  
 inner join tblcustomermission as customermission on  mission.id=customermission.missionId
 inner join tblcustomer as customer on  customermission.userId=customer.id
 where  mission.isActive=1 and  mission.id=".$missionId." and customer.isActive=1  order by mission.startDate"));
      return view('admin.missionmanagement.enrollmentbusiness',compact('mission','missionenroll','missionId','missionStatus'));    
     }

     public function MissionReOpen($status,$id) {
        $common=new CommanController;
      $mission = Mission::find($id);
      $mission->isreOpen=1;
          $mission->isActive=1;
          $mission->reopenDate=date('Y-m-d H:i:s');
      $mission->save();
      echo 2;
      exit();
     }

    public function MissionDelete($id) {
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

    public function MissionStatus($status,$id) {
        $common=new CommanController;
      $mission = Mission::find($id);
      $mission->isActive=$status;
      $mission->save();
      flash()->success('Mission status has updated successfully.');
     return redirect()->to('/businessuser/missionmanagement');
     }

    public function addMission() {
    $user = Auth::guard('businessuser')->user();
    $loginUserId=$user->id;

    $common=new CommanController;
    $businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$loginUserId]])->get(['id','beaconName']);
    $businessusers = BusinessUsers::where([['isActive', '=','1'],['id', '=',$loginUserId]])->get(['id','companyName']);
    $idprooftype=IdProofType::where([['isActive', '=','1']])->get();
    $areaofinterest=AreaOfInterest::where([['isActive', '=','1']])->get();
    $levelmanagement = LevelManagement::where([['isActive', '=','1']])->get(['id','name']);
    $multiple_image_note=$common->get_msg('multiple_image_note',1)?$common->get_msg('multiple_image_note',1):"Hold shift button for selecting multiple images.";
    $missionimagewidth=$common->getImageSizeValue('mission_image_width');
    $missionimageheight=$common->getImageSizeValue('mission_image_height');
    $missionlogowidth=$common->getImageSizeValue('mission_logo_width');
    $missionlogoheight=$common->getImageSizeValue('mission_logo_height');
    $customer = Customer::where('isActive',1)->orderby('email','asc')->get(['id','email']);
     return view('admin.missionmanagement.addeditbusiness',compact('businessusers','idprooftype','areaofinterest','businessusersbeacon','levelmanagement','multiple_image_note','missionimagewidth','missionimageheight','missionlogowidth','missionlogoheight','customer'));
  }

  public function postCreateMission(MissionRequest $request) {
     $user = Auth::guard('businessuser')->user();
     $loginUserId=$user->id;
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
       $email=isset($request->email)?(($request->email)):"";

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

       $mission->customNotificationMessage = isset($request->customNotificationMessage)?(ltrim($request->customNotificationMessage)):"";
       
       
       
      
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
         

         $data=DB::select( DB::raw("Select * from tblmissiontranscation  where businessId=".$loginUserId." order by id desc Limit 1"));
         $TranId=isset($data[0]->id)?($data[0]->id):0;
         $noOfRemainingMission=isset($data[0]->noOfRemainingMission)?($data[0]->noOfRemainingMission):0;
         $TotalRemainMission=$noOfRemainingMission - 1;
         $vendorpaymentType=DB::table('tblmissiontranscation')->where('id',$TranId)->update(
               ['noOfRemainingMission'=>$TotalRemainMission,'updatedDate'=>date('Y-m-d H:i:s')]);

         

             
                

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

           
                   
       
       
      
       flash()->success('Mission has  added successfully.');
       return redirect()->to('/businessuser/missionmanagement');
     } else {
         flash()->error('This Mission name has been already taken. Please try with another name.');
       return redirect()->to('/businessuser/missionmanagement/add');
     }
     //echo $request->name;
     //exit;
       }


        public function adsPlanList(Request $request) {
      

           $status=($request->status)?($request->status):"";
           $name=($request->name)?($request->name):"";
           $email=($request->email)?($request->email):"";
           $hash=($request->hash)?($request->hash):"";
           $order_id=($request->order_id)?($request->order_id):"";
           $transaction_id=($request->transaction_id)?($request->transaction_id):"";
           $reasonCode=($request->reasonCode)?($request->reasonCode):1;
           $reasonDescription=($request->reasonDescription)?($request->reasonDescription):"";

           $responseCode=($request->responseCode)?($request->responseCode):"";
           $total=($request->total)?($request->total):"";
           $D=($request->D)?($request->D):"";
           $date=($request->date)?($request->date):"";


           $endDate=date('Y-m-d');
           $planlist= AdsSubscriptionPlan::where('isActive',1)->get();
           $user = Auth::guard('businessuser')->user();
           $loginUserId=$user->id;
           $PersonName=(Auth::guard('businessuser')->user()->contactPersonName)?(Auth::guard('businessuser')->user()->contactPersonName):"Business User";
           $contactNo=(Auth::guard('businessuser')->user()->contactNo)?(Auth::guard('businessuser')->user()->contactNo):"999999999";
           $emailAddress=(Auth::guard('businessuser')->user()->emailAddress)?(Auth::guard('businessuser')->user()->emailAddress):"admin@admin.com";
        
            $PlanId=0;
            $PlanName='';
            $validatyMonths=1;
            $noOfAdsAllowed=0;
            $statusMessage='';
            $statusType=0;
        

          if ($total!='' && $total!=0) {
             $data=DB::select( DB::raw("Select id,planName,validatyMonths,noOfAdsAllowed from tbladssubscriptionplan  where price=".$total." Limit 1"));
             $PlanId=isset($data[0]->id)?($data[0]->id):0;
             $PlanName=isset($data[0]->planName)?($data[0]->planName):0;
             $validatyMonths=isset($data[0]->validatyMonths)?($data[0]->validatyMonths):1;
             $noOfAdsAllowed=isset($data[0]->noOfAdsAllowed)?($data[0]->noOfAdsAllowed):1;
          }

         if ($status!='' && $status=='success') {
            $statusMessage='You have successfully brought plan you can add ads.';
            $statusType=1;
            $endDate=date('Y-m-d',strtotime('+'.$validatyMonths.' months'));
            $transcationentry=DB::table('tbladstranscation')->insert(
                        ['businessId'=>$loginUserId,'createdDate'=>date('Y-m-d H:i:s'),'planId'=>$PlanId,'planName'=>$PlanName,'name'=>$PersonName,'email'=>$emailAddress,'transcationId'=>$transaction_id,'reasonCode'=>$reasonCode,'reasonDescription'=>$reasonDescription,'responseCode'=>$responseCode,'total'=>$total,'D'=>$D,'orderId'=>$order_id,'transcationDate'=>$date,'createdDate'=>date('Y-m-d H:i:s'),'isActive'=>1,'startDate'=>date('Y-m-d'),'endDate'=>$endDate,'hashCode'=>$hash,'status'=>$status,'noOfAdsPerDuration'=>$noOfAdsAllowed,'noOfRemainingAds'=>$noOfAdsAllowed]);

            flash()->success($statusMessage);
            return redirect()->to('/businessuser/adsmanagement');
         }

        if ($status!='' && $status=='failed') {
          $statusMessage='Please try again please enter valid Details.';
          $statusType=2;
          $endDate=date('Y-m-d',strtotime('+'.$validatyMonths.' months'));
          $transcationentry=DB::table('tbladstranscation')->insert(
                      ['businessId'=>$loginUserId,'createdDate'=>date('Y-m-d H:i:s'),'planId'=>$PlanId,'planName'=>$PlanName,'name'=>$PersonName,'email'=>$emailAddress,'transcationId'=>$transaction_id,'reasonCode'=>$reasonCode,'reasonDescription'=>$reasonDescription,'responseCode'=>$responseCode,'total'=>$total,'D'=>$D,'orderId'=>$order_id,'transcationDate'=>$date,'createdDate'=>date('Y-m-d H:i:s'),'isActive'=>0,'startDate'=>date('Y-m-d'),'endDate'=>$endDate,'hashCode'=>$hash,'status'=>$status,'noOfAdsPerDuration'=>$noOfAdsAllowed,'noOfRemainingAds'=>$noOfAdsAllowed]);


        }


       
       


       $checkAdsCount=DB::table('tbladstranscation')->where([['businessId', '=',$loginUserId],['isActive', '=',1],['endDate', '>=',$endDate],['noOfRemainingAds', '>',0]])->orderby('id','desc')->paginate(1)->count();
       return view('admin.adsmanagement.planlist',compact('loginUserId','checkAdsCount','planlist','PersonName','contactNo','emailAddress','statusMessage','statusType'));
   }

       public function adsList() {
           $user = Auth::guard('businessuser')->user();
           $loginUserId=$user->id;
           $endDate=date('Y-m-d');
           $ads= Ads::where('businessId',$loginUserId)->orderby('id','desc')->get();
           $checkAdsCount=DB::table('tbladstranscation')->where([['businessId', '=',$loginUserId],['isActive', '=',1],['endDate', '>=',$endDate],['noOfRemainingAds', '>',0]])->orderby('id','desc')->paginate(1)->count();
           $data=DB::select( DB::raw("Select * from tbladstranscation  where businessId=".$loginUserId." order by id desc Limit 1"));
           $TranId=isset($data[0]->id)?($data[0]->id):0;
           $noOfRemainingAds=isset($data[0]->noOfRemainingAds)?($data[0]->noOfRemainingAds):0;
           $endDate=isset($data[0]->endDate)?($data[0]->endDate):"";

           return view('admin.adsmanagement.indexbusiness',compact('ads','checkAdsCount','noOfRemainingAds','endDate'));
       }

       public function postCreateAds(AdsRequest $request) {

        $user = Auth::guard('businessuser')->user();
        $loginUserId=$user->id;

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
       //$mission->quotaLimit = isset($request->quotaLimit)?(ltrim($request->quotaLimit)):0;
             //$mission->durationOfMisson = isset($request->durationOfMisson)?(ltrim($request->durationOfMisson)):0;
             //$mission->title = isset($request->title)?(ltrim($request->title)):"";
             //$mission->description = isset($request->description)?(ltrim($request->description)):"";
             //$mission->estimationTime = isset($request->estimationTime)?(ltrim($request->estimationTime)):"";

             



       //$mission->rewardDescription = isset($request->rewardDescription)?(ltrim($request->rewardDescription)):"";
       //$mission->eligibiltyCriteria = isset($request->eligibiltyCriteria)?(ltrim($request->eligibiltyCriteria)):"";
       //$mission->cashReward = isset($request->cashReward)?(ltrim($request->cashReward)):0;
       //$mission->points = isset($request->points)?(ltrim($request->points)):0;

           $ads->pushAdsBeacon = isset($request->pushAdsBeacon)?(ltrim($request->pushAdsBeacon)):0;
           $ads->footfallCalcBeacon = isset($request->footfallCalcBeacon)?(ltrim($request->footfallCalcBeacon)):0;
           $ads->pushAdsGps = isset($request->pushAdsGps)?(ltrim($request->pushAdsGps)):0;

            $ads->ageFrom = isset($request->ageFrom)?(ltrim($request->ageFrom)):0;
            $ads->ageTo = isset($request->ageTo)?(ltrim($request->ageTo)):0;
            $ads->isMale = isset($request->isMale)?(ltrim($request->isMale)):0;
            $ads->isFemale = isset($request->isFemale)?(ltrim($request->isFemale)):0;
        //$mission->isVerified = isset($request->isVerified)?(ltrim($request->isVerified)):0;
        //$mission->isUnverified = isset($request->isUnverified)?(ltrim($request->isUnverified)):0;
       
       
       
      
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
                // $thumb_img->save($category_destinationPath.'/'.$category_image,80);
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

            $data=DB::select( DB::raw("Select * from tbladstranscation  where businessId=".$loginUserId." order by id desc Limit 1"));
            $TranId=isset($data[0]->id)?($data[0]->id):0;
            $noOfRemainingAds=isset($data[0]->noOfRemainingAds)?($data[0]->noOfRemainingAds):0;
            $TotalRemainAds=$noOfRemainingAds - 1;
            $vendorpaymentType=DB::table('tbladstranscation')->where('id',$TranId)->update(
                 ['noOfRemainingAds'=>$TotalRemainAds,'updatedDate'=>date('Y-m-d H:i:s')]);

       
      
       flash()->success('Ads has  added successfully.');
       return redirect()->to('/businessuser/adsmanagement');
     } else {
         flash()->error('This Ads name has been already taken. Please try with another name.');
       return redirect()->to('/businessuser/adsmanagement/add');
     }
     //echo $request->name;
     //exit;
       }

        public function getShowAds($id) {
       $common=new CommanController;
         $ads=$common->AdsDetails($id);
         $adsImageList=$common->adsImageList($id);
         $adsTargetLevelName=$common->adsTargetLevelName($id);
         $adsAreaofInterestName=$common->adsAreaofInterestName($id);
         return view('admin.adsmanagement.showbusiness',compact('ads','adsImageList','adsTargetLevelName','adsAreaofInterestName'));
     }

    
     
     public function deleteImagesAds($id) {
      $delete=DB::delete("delete from tbladsimages where id=$id");
      echo 1;
      exit();
     }

       public function AdsStatus($status,$id) {
        $common=new CommanController;
      $mission = Ads::find($id);
      $mission->isActive=$status;
      $mission->save();
      flash()->success('Ads status has updated successfully.');
     return redirect()->to('/businessuser/adsmanagement');
     }

       public function AdsDelete($id) {
          $common=new CommanController;
             $user = Ads::find($id);
                 $user->delete();
           echo 2;
             exit();
              
             
     }

     public function postEditAds(AdsRequest $request, $id) {
            
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
                 //$thumb_img = Image::make($files->getRealPath())->resize($width, $height);
                 //$thumb_img->save($category_destinationPath.'/'.$category_image,80);
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
     return redirect()->to('/businessuser/adsmanagement');
     }

     public function AdsDeleteall(Request $request) {
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
      
      return redirect()->to('/businessuser/adsmanagement');
      //exit;
     }

     public function getEditAds($id)
       {
        $common=new CommanController;
        $ads=$common->AdsDetails($id);
        $user = Auth::guard('businessuser')->user();
        $loginUserId=$user->id;
        $businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$loginUserId]])->get(['id','beaconName']);
    $businessusers = BusinessUsers::where([['isActive', '=','1'],['id', '=',$loginUserId]])->get(['id','companyName']);
    $idprooftype=IdProofType::where([['isActive', '=','1']])->get();
    $areaofinterest=AreaOfInterest::where([['isActive', '=','1']])->get();
    $levelmanagement = LevelManagement::where([['isActive', '=','1']])->get(['id','name']);
    $adsAreaofInterestList=$common->adsAreaofInterestList($id);
    $adsTargetLevelList=$common->adsTargetLevelList($id);
    $adsImageList=$common->adsImageList($id);
    
    $addsimagewidth=$common->getImageSizeValue('ads_image_width');
    $addsimageheight=$common->getImageSizeValue('ads_image_height');

         return view('admin.adsmanagement.addeditbusiness',compact('ads','idprooftype','areaofinterest','adsAreaofInterestList','adsTargetLevelList','levelmanagement','adsImageList','businessusers','businessusersbeacon','addsimagewidth','addsimageheight'));
       }

       public function addAds() {
        $common=new CommanController;
        $user = Auth::guard('businessuser')->user();
        $loginUserId=$user->id;
        $businessusersbeacon=BusinessUsersBeacon::where([['isActive', '=','1'],['businessId', '=',$loginUserId]])->get(['id','beaconName']);
        $businessusers = BusinessUsers::where([['isActive', '=','1'],['id', '=',$loginUserId]])->get(['id','companyName']);
        $idprooftype=IdProofType::where([['isActive', '=','1']])->get();
        $areaofinterest=AreaOfInterest::where([['isActive', '=','1']])->get();
        $levelmanagement = LevelManagement::where([['isActive', '=','1']])->get(['id','name']);
        $addsimagewidth=$common->getImageSizeValue('ads_image_width');
        $addsimageheight=$common->getImageSizeValue('ads_image_height');
     return view('admin.adsmanagement.addeditbusiness',compact('businessusers','idprooftype','areaofinterest','businessusersbeacon','levelmanagement','addsimagewidth','addsimageheight'));
  }

    public function missionList() {
       $user = Auth::guard('businessuser')->user();
       $loginUserId=$user->id;
       $endDate=date('Y-m-d');
       
       $data=DB::select( DB::raw("Select * from tblmissiontranscation  where businessId=".$loginUserId." order by id desc Limit 1"));
       $TranId=isset($data[0]->id)?($data[0]->id):0;
       $noOfRemainingMission=isset($data[0]->noOfRemainingMission)?($data[0]->noOfRemainingMission):0;
       $endDate=isset($data[0]->endDate)?($data[0]->endDate):"";


       $checkMissionCount=DB::table('tblmissiontranscation')->where([['businessId', '=',$loginUserId],['isActive', '=',1],['endDate', '>=',$endDate],['noOfRemainingMission', '>',0]])->orderby('id','desc')->paginate(1)->count();
       
       $missionmanagement = Mission::where('businessId',$loginUserId)->orderby('id','desc')->get();
       return view('admin.missionmanagement.indexbusiness',compact('missionmanagement','checkMissionCount','noOfRemainingMission','endDate'));
   }

   public function missionPlanList(Request $request) {
      

       $status=($request->status)?($request->status):"";
       $name=($request->name)?($request->name):"";
       $email=($request->email)?($request->email):"";
       $hash=($request->hash)?($request->hash):"";
       $order_id=($request->order_id)?($request->order_id):"";
       $transaction_id=($request->transaction_id)?($request->transaction_id):"";
       $reasonCode=($request->reasonCode)?($request->reasonCode):1;
       $reasonDescription=($request->reasonDescription)?($request->reasonDescription):"";

       $responseCode=($request->responseCode)?($request->responseCode):"";
       $total=($request->total)?($request->total):"";
       $D=($request->D)?($request->D):"";
       $date=($request->date)?($request->date):"";


       $endDate=date('Y-m-d');
       $planlist= MissionSubscriptionPlan::where('isActive',1)->get();
       $user = Auth::guard('businessuser')->user();
       $loginUserId=$user->id;
       $PersonName=(Auth::guard('businessuser')->user()->contactPersonName)?(Auth::guard('businessuser')->user()->contactPersonName):"Business User";
       $contactNo=(Auth::guard('businessuser')->user()->contactNo)?(Auth::guard('businessuser')->user()->contactNo):"999999999";
       $emailAddress=(Auth::guard('businessuser')->user()->emailAddress)?(Auth::guard('businessuser')->user()->emailAddress):"admin@admin.com";
        
        $PlanId=0;
        $PlanName='';
        $validatyMonths=1;
        $noOfMissionAllowed=0;
        $statusMessage='';
        $statusType=0;
        

        if ($total!='' && $total!=0) {
           
           $data=DB::select( DB::raw("Select id,planName,validatyMonths,noOfMissionAllowed from tblmissionsubscriptionplan  where price=".$total." Limit 1"));
           $PlanId=isset($data[0]->id)?($data[0]->id):0;
           $PlanName=isset($data[0]->planName)?($data[0]->planName):0;
           $validatyMonths=isset($data[0]->validatyMonths)?($data[0]->validatyMonths):1;
           $noOfMissionAllowed=isset($data[0]->noOfMissionAllowed)?($data[0]->noOfMissionAllowed):1;
           
           


        }

       if ($status!='' && $status=='success') {
          $statusMessage='You have successfully brought plan you can add mission now.';
          $statusType=1;
          $endDate=date('Y-m-d',strtotime('+'.$validatyMonths.' months'));

          $transcationentry=DB::table('tblmissiontranscation')->insert(
                      ['businessId'=>$loginUserId,'createdDate'=>date('Y-m-d H:i:s'),'planId'=>$PlanId,'planName'=>$PlanName,'name'=>$PersonName,'email'=>$emailAddress,'transcationId'=>$transaction_id,'reasonCode'=>$reasonCode,'reasonDescription'=>$reasonDescription,'responseCode'=>$responseCode,'total'=>$total,'D'=>$D,'orderId'=>$order_id,'transcationDate'=>$date,'createdDate'=>date('Y-m-d H:i:s'),'isActive'=>1,'startDate'=>date('Y-m-d'),'endDate'=>$endDate,'hashCode'=>$hash,'status'=>$status,'noOfMissionPerDuration'=>$noOfMissionAllowed,'noOfRemainingMission'=>$noOfMissionAllowed]);

           flash()->success($statusMessage);
            return redirect()->to('/businessuser/missionmanagement');
       }

       if ($status!='' && $status=='failed') {
          $statusMessage='Please try again please enter valid Details.';
          $statusType=2;

          $endDate=date('Y-m-d',strtotime('+'.$validatyMonths.' months'));

          $transcationentry=DB::table('tblmissiontranscation')->insert(
                      ['businessId'=>$loginUserId,'createdDate'=>date('Y-m-d H:i:s'),'planId'=>$PlanId,'planName'=>$PlanName,'name'=>$PersonName,'email'=>$emailAddress,'transcationId'=>$transaction_id,'reasonCode'=>$reasonCode,'reasonDescription'=>$reasonDescription,'responseCode'=>$responseCode,'total'=>$total,'D'=>$D,'orderId'=>$order_id,'transcationDate'=>$date,'createdDate'=>date('Y-m-d H:i:s'),'isActive'=>0,'startDate'=>date('Y-m-d'),'endDate'=>$endDate,'hashCode'=>$hash,'status'=>$status,'noOfMissionPerDuration'=>$noOfMissionAllowed,'noOfRemainingMission'=>$noOfMissionAllowed]);
       }


       
       


       $checkMissionCount=DB::table('tblmissiontranscation')->where([['businessId', '=',$loginUserId],['isActive', '=',1],['endDate', '>=',$endDate],['noOfRemainingMission', '>',0]])->orderby('id','desc')->paginate(1)->count();
       return view('admin.missionmanagement.planlist',compact('loginUserId','checkMissionCount','planlist','PersonName','contactNo','emailAddress','statusMessage','statusType'));
   }

    public function getShow($id) {

         $common=new CommanController;
         $businessusers=$common->BusinessUsersDetails($id);
         $beacon=BusinessUsersBeacon::where('businessId',$id)->get();
         $country = Country::where([['status', '=','1'],['id', '=','221']])->get();
         
         return view('admin.businessusers.businessshow',compact('businessusers','country','beacon'));

     }

     public function getEdit($id)
       {
           
         $common=new CommanController;
         $businessusers=$common->BusinessUsersDetails($id);
         $country = Country::where([['status', '=','1']])->get();
         $industrycategory=IndustryCategory::where([['isActive', '=','1']])->get();
         $businesswidth=$common->getImageSizeValue('business_image_width');
         $businessheight=$common->getImageSizeValue('business_image_height');
         return view('admin.businessusers.businessaddedit',compact('businessusers','country','industrycategory','businesswidth','businessheight'));
       }



       public function beaconList($businessId) {
       $beacon = BusinessUsersBeacon::where('businessId',$businessId)->get();
       $businessusers = BusinessUsers::find($businessId);
     
       return view('admin.beacon.businessindex',compact('beacon','businessId','businessusers'));
       }

       public function addBeacon($businessId) {
       $businessusers = BusinessUsers::find($businessId);
       return view('admin.beacon.businessaddedit',compact('businessId','businessusers'));
       }
  
   public function BeaconpostCreate(BusinessUsersBeaconRequest $request,$businessId) {
     
     $checkduplicate = DB::table('tblbusinessusersbeacon')->where([['beaconName', '=',$request->beaconName]])->count();
     $countemail = DB::table('tblbusinessusersbeacon')->where('nameSpaceId', '=', $request->nameSpaceId)->count();
     
    
           if ($checkduplicate==0 && $countemail==0) {
             $beacon = new BusinessUsersBeacon();
             $beacon->beaconName = isset($request->beaconName)?(ltrim($request->beaconName)):"";
             $beacon->nameSpaceId = isset($request->nameSpaceId)?(ltrim($request->nameSpaceId)):"";
             $beacon->instanceId = isset($request->instanceId)?(ltrim($request->instanceId)):"";
             $beacon->iBeaconUUID = isset($request->iBeaconUUID)?(ltrim($request->iBeaconUUID)):"";
             $beacon->iBeaconMinor = isset($request->iBeaconMinor)?(ltrim($request->iBeaconMinor)):"";
             $beacon->iBeaconMajor = isset($request->iBeaconMajor)?(ltrim($request->iBeaconMajor)):"";
             $beacon->building = isset($request->building)?(ltrim($request->building)):"";
             $beacon->floor = isset($request->floor)?(ltrim($request->floor)):"";
             $beacon->createdDate=date('Y-m-d H:i:s');
             $beacon->department = isset($request->department)?(ltrim($request->department)):0;
             $beacon->lattitude = isset($request->lattitude)?(ltrim($request->lattitude)):0;
             $beacon->longitude = isset($request->longitude)?(ltrim($request->longitude)):0;
             $beacon->beaconType = isset($request->beaconType)?(ltrim($request->beaconType)):0;
             $beacon->businessId = $businessId;
             $beacon->isActive=$request->status;
             $beacon->save();
             
             
             flash()->success('Beacon has  added successfully.');
             return redirect()->to('/businessuser/beacon/'.$businessId);
           } else {
               flash()->error('duplicate records please add another.');
             return redirect()->to('/businessuser/beacon/add/'.$businessId);
           }
     //echo $request->name;
     //exit;
       }
     
     public function BeaconStatus($status,$id) {
      $beacon = BusinessUsersBeacon::find($id);
      $beacon->isActive=$status;
      $beacon->save();
      flash()->success('Beacon status has updated successfully.');
     return redirect()->to('/businessuser/beacon/'.$id);
     }

     public function DeleteBeacon($id) {
       $beacon = BusinessUsersBeacon::find($id);
       $beacon->delete();
       $msg='';
       echo $msg; 
       exit();   
     }

     public function getEditBeacon($businessId,$id)
       {
    
         $businessusers = BusinessUsers::find($businessId);
         $common=new CommanController;
         $beacon = BusinessUsersBeacon::find($id);
         return view('admin.beacon.businessaddedit',compact('businessusers','beacon'));
       }

       public function postEditBeacon(BusinessUsersBeaconRequest $request,$businessId,$id) {
       $beacon = BusinessUsersBeacon::find($id);
       $beacon->beaconName = isset($request->beaconName)?(ltrim($request->beaconName)):"";
       $beacon->nameSpaceId = isset($request->nameSpaceId)?(ltrim($request->nameSpaceId)):"";
       $beacon->instanceId = isset($request->instanceId)?(ltrim($request->instanceId)):"";
       $beacon->iBeaconUUID = isset($request->iBeaconUUID)?(ltrim($request->iBeaconUUID)):"";
       $beacon->iBeaconMinor = isset($request->iBeaconMinor)?(ltrim($request->iBeaconMinor)):"";
       $beacon->iBeaconMajor = isset($request->iBeaconMajor)?(ltrim($request->iBeaconMajor)):"";
       $beacon->building = isset($request->building)?(ltrim($request->building)):"";
       $beacon->floor = isset($request->floor)?(ltrim($request->floor)):"";
       $beacon->department = isset($request->department)?(ltrim($request->department)):0;
       $beacon->lattitude = isset($request->lattitude)?(ltrim($request->lattitude)):0;
       $beacon->longitude = isset($request->longitude)?(ltrim($request->longitude)):0;
       $beacon->beaconType = isset($request->beaconType)?(ltrim($request->beaconType)):0;
       $beacon->businessId = $businessId;
       $beacon->updatedDate=date('Y-m-d H:i:s');
       $beacon->isActive=$request->status;
       $beacon->save();
       flash()->success('Beacon has updated successfully.');
       return redirect()->to('/businessuser/beacon/'.$businessId);
     }
     
     public function postEditBusiness(BusinessUsersRequest $request, $id) {
     
         $businessusers = BusinessUsers::find($id);
         $businessusers->companyName = isset($request->companyName)?(ltrim($request->companyName)):"";
         $businessusers->emailAddress = isset($request->emailAddress)?(ltrim($request->emailAddress)):"";
         $businessusers->contactPersonName = isset($request->contactPersonName)?(ltrim($request->contactPersonName)):"";
         $businessusers->contactNo = isset($request->contactNo)?(ltrim($request->contactNo)):"";
         
         $businessusers->address1 = isset($request->address1)?(ltrim($request->address1)):"";
         $businessusers->address2 = isset($request->address2)?(ltrim($request->address2)):"";

         if (isset($request->password) && $request->password!='') {
         $businessusers->password =(ltrim(bcrypt($request->password)));
         }
    
     
      if ($request->file('profilePicture')) {
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
         $check=in_array($extension,$allowedfileExtension);

         if($check) {
         $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
         $thumb_img->save($category_destinationPath.'/'.$category_image,80);
         $filename=$file->getClientOriginalName();
         $destinationPath = 'businessusers';
         $file->move($destinationPath,$file->getClientOriginalName());
         $businessusers->profilePicture=$category_image;
        }
       }

         $businessusers->updatedDate=date('Y-m-d H:i:s');

     $businessusers->countryId = isset($request->countryId)?(ltrim($request->countryId)):0;
     $businessusers->stateId = isset($request->stateId)?(ltrim($request->stateId)):0;
     $businessusers->cityId = isset($request->cityId)?(ltrim($request->cityId)):0;
     $businessusers->industryCategoryId = isset($request->industryCategoryId)?(ltrim($request->industryCategoryId)):0;

    
     $businessusers->isActive=$request->status;
     $businessusers->save();
     

    
     
     flash()->success('Your profile has updated successfully.');
     return redirect()->to('/businessuser/show/'.$id);
     }

     public function logOut()
     {
        Auth::logout();
        auth('businessuser')->logout();
        //Auth::guard('businessuser')->user()->logout();
        // Get the session key for this user
        //$sessionKey = $this->guard()->getName();

        // Logout current user by guard
        //$this->guard()->logout();

        // Delete single session key (just for this user)
        //$request->session()->forget($sessionKey);

        return redirect()->to('businessuser/login');
      }

    /*protected function guard()
    {
        return Auth::guard('employee');
    }*/



}
