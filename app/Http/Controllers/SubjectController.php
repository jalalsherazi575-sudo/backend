<?php

namespace Laraspace\Http\Controllers;


use Laraspace\Http\Requests\BankRequest;
use Laraspace\Http\Requests\SubjectRequest;
use Laraspace\Http\Requests\PlanPackageRequest;
use Intervention\Image\Facades\Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Laraspace\LevelManagement;
use Laraspace\Subject;
use Laraspace\PlanPackage;
use Laraspace\Language;
use Laraspace\planSubject;
use Laraspace\Topics;

class SubjectController extends Controller
{

	public function __construct() {
    	$this->middleware('auth');
  	}
    
  	public function index() 
  	{
		$subject = Subject::with('category')->get();
		return view('admin.subject.index',compact('subject'));
	}

	public function add()
	{
		$category = LevelManagement::all();
		return view('admin.subject.addedit',compact('category'));
	}

	public function postCreate(SubjectRequest $request)
	{
		$common=new CommanController;
	
		$subject = new Subject();
		$subject->categoryId=$request->category;
		$subject->subjectName=$request->subjectName;
		$subject->isActive=$request->status;
		$subject->createdDate=date('Y-m-d H:i:s');
		if(!empty($request->file('subImage'))){
            $imageGellary    = $request->file('subImage');
            $imagesize = getimagesize($imageGellary);
            $width = $imagesize[0];
            $height = $imagesize[1];
            /*set image size*/
            $imagew = 300;  
            $imageh = 300;  
            $imageheight_width = array();
            $setheight = 300;
            $setwidth = 300;  
            $size = $common->proposnallyimage($height,$width,$setheight,$setwidth);
            $new_name = md5(uniqid(rand(), true)).'.'.$imageGellary->getClientOriginalExtension();

            Image::make($imageGellary)->resize($size['NewWidth'],$size['Newheight'])->save(public_path('images/subject'.'/'.$new_name));
            $subject->subImage = $new_name;
        }
		$subject->save();
		session()->flash('success','Subject has added successfully.');
		return redirect()->to('/admin/subject'); 
	}

	public function getEdit($id){
		$category = LevelManagement::all();
		$subject = Subject::find($id);
     	return view('admin.subject.addedit',compact('subject','category'));
	}

	public function postEdit(SubjectRequest $request, $id) 
	{
		$common=new CommanController;
		$subject = Subject::find($id);
		$subject->categoryId=$request->category;
		$subject->subjectName=$request->subjectName;
		$subject->isActive=$request->status;
		$oldimage = $subject->subImage;
		if(!empty($request->file('subImage'))){
			$imagePath = public_path('images/subject/' . $oldimage);
           	if ( !empty($oldimage) && file_exists($imagePath)) {
               unlink($imagePath);
            }
            $imageGellary    = $request->file('subImage');
            $imagesize = getimagesize($imageGellary);
            $width = $imagesize[0];
            $height = $imagesize[1];
            /*set image size*/
            $imagew = 300;  
            $imageh = 300;  
            $imageheight_width = array();
            $setheight = 300;
            $setwidth = 300;  
            $size = $common->proposnallyimage($height,$width,$setheight,$setwidth);
            $new_name = md5(uniqid(rand(), true)).'.'.$imageGellary->getClientOriginalExtension();

            Image::make($imageGellary)->resize($size['NewWidth'],$size['Newheight'])->save(public_path('images/subject'.'/'.$new_name));
            $subject->subImage = $new_name;
        }
		$subject->save();
        session()->flash('success','Subject has updated successfully.');
		return redirect()->to('/admin/subject');
   	}

   	public function Delete($id) {
        $subject = Subject::find($id);
        if (!$subject) {
            return response()->json(['status' => 'error', 'message' => 'Subject not found.'], 404);
        }
        // Check if the subject has any related data
        $topicCount = Topics::where('subjectId', $subject->id)->count();
        $planSubjectCount = planSubject::where('subject_id', $subject->id)->count();

        // If all related data are empty, delete the subject and associated records
        if ($topicCount == 0 && $planSubjectCount == 0) {
            // Delete the subject's image if exists
            if (!empty($subject->subImage)) {
                $imagePath = public_path('images/banner/' . $subject->subImage);
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Delete the image file from the server
                }
            }
            // Delete the subject
            $subject->delete();

            return response()->json(['status' => 'success', 'message' => 'Subject deleted successfully.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Kindly remove related topic(s) and assigned plan(s) before removing the subject.']);
        }
       
    }
    /*Start Plan module */
   	public function getPlan($planid){
		//$plans = PlanPackage::where('subjectId',$planid)->get();
        $plans = PlanSubject::leftJoin('tblplanpackage', 'tblplanpackage.packageId', '=', 'plan_subject.plan_id')
            ->where('plan_subject.subject_id', $planid)
            ->select('plan_subject.*', 'tblplanpackage.packageName', 'tblplanpackage.packagePrice', 'tblplanpackage.packagePeriodInMonth')
            ->get();
		return view('admin.subject.plan',compact('plans','planid'));
	}

	public function addplan($subId){
		$subjects = Subject::all();
		return view('admin.subject.addEditplan',compact('subjects','subId'));	
	}

	public function postPlan(PlanPackageRequest $request)
    {
		$packagePrice=isset($request->packagePrice)?($request->packagePrice):"0.00";
        $packageName=isset($request->packageName)?($request->packageName):"";
        $packageDescription=isset($request->packageDescription)?($request->packageDescription):"";
        $isActive=isset($request->isActive)?($request->isActive):0;
        $packagePeriodInMonth=isset($request->packagePeriodInMonth)?($request->packagePeriodInMonth):0;
        $androidPlanKey=isset($request->androidPlanKey)?($request->androidPlanKey):"";
        $iosPlanKey=isset($request->iosPlanKey)?($request->iosPlanKey):"";
        $subjectId = isset($request->subId)?($request->subId):"";
        $packageId=PlanPackage::create([
        	'subjectId' => $subjectId,
            'packageName' => $packageName,
            'packagePrice' => $packagePrice,
            'packageDescription' => $packageDescription,
            'isActive' => $isActive,
            'packagePeriodInMonth' => $packagePeriodInMonth,
            'createdDate' =>date('Y-m-d H:i:s'),
        ])->packageId;
               
        session()->flash('success',"Plan has been added successfully");
	 	return redirect()->to('/admin/subject/plans/'.$subjectId);
	}

    public function getEditPlan($id)
    {
        $common=new CommanController;
        $planpackage = PlanPackage::find($id);
        $subId = $planpackage->subjectId;
        return view('admin.subject.addEditplan',compact('planpackage','subId'));
    }

    public function postUpdatePlan(PlanPackageRequest $request,$id)
    {
        
        $planpackage = PlanPackage::find($id);

       // $defContentTitle=($request->defContentTitle)?($request->defContentTitle):"";
        $packagePrice=($request->packagePrice)?($request->packagePrice):"0.00";
        $packageName=($request->packageName)?($request->packageName):"";
        $packageDescription=($request->packageDescription)?($request->packageDescription):"";
        $isActive=($request->isActive)?($request->isActive):0;
        $packagePeriodInMonth=isset($request->packagePeriodInMonth)?($request->packagePeriodInMonth):0;
        $androidPlanKey=isset($request->androidPlanKey)?($request->androidPlanKey):"";
        $iosPlanKey=isset($request->iosPlanKey)?($request->iosPlanKey):"";
        $subjectId = isset($request->subId)?($request->subId):"";


        $updateArray = [
            'packageName' => $packageName,
            'packagePrice' => $packagePrice,
            'packageDescription' => $packageDescription,
            'isActive' => $isActive,
            'packagePeriodInMonth' => $packagePeriodInMonth,
            'androidPlanKey' => $androidPlanKey,
            'iosPlanKey' => $iosPlanKey,
            'updatedDate' =>date('Y-m-d H:i:s'),
            
        ];

        PlanPackage::where('packageId',$id)->update($updateArray);

        session()->flash('success',"Plan has been updated successfully");
         return redirect()->to('/admin/subject/plans/'.$subjectId);
    }
    
	public function DeletePlan($id)
    {
        $common = new CommanController();
        $SubscriptionPlan = PlanSubject::find($id);
        $SubscriptionPlan->delete();
        $msg="Package has been deleted successfully.";
        session()->flash('success',"Plan has been deleted successfully");
         return Response(['status'=>'success','message'=> $msg]);
	 	//return redirect()->to('/admin/subject');
        
    }


}
