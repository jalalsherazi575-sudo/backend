<?php
namespace Laraspace\Http\Controllers;

use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 
use Laraspace\PlanPackage;
use Laraspace\Subject;
use Laraspace\planSubject;
use Laraspace\LevelManagement;

use Auth;
use Image;

class PlanPackageController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $common=new CommanController;
        $packageData = PlanPackage::orderby('packageId','desc')->get();
        return view('admin.planpackage.index',compact('packageData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $common=new CommanController;
        return view('admin.planpackage.addedit');
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    // Retrieve data from request
    $packagePrice = $request->input('packagePrice', '0.00');
    $packageName = $request->input('packageName', '');
    $packageDescription = $request->input('packageDescription', '');
    $isActive = $request->input('isActive', 0);
    $packagePeriodInMonth = $request->input('packagePeriodInMonth', 0);
    $androidPlanKey = $request->input('androidPlanKey', '');
    $iosPlanKey = $request->input('iosPlanKey', '');

    // Validate the request
    $request->validate([
        'packageName' => [
            'required',
            Rule::unique('tblplanpackage')->where(function ($query) use ($packagePeriodInMonth) {
                return $query->where('packagePeriodInMonth', $packagePeriodInMonth);
            })
        ],
        'packagePrice' => [
            'required',
            'numeric',
            Rule::unique('tblplanpackage')->where(function ($query) use ($request) {
                return $query->where([
                    ['packagePeriodInMonth', $request->input('packagePeriodInMonth')],
                    ['packagePrice', $request->input('packagePrice')]
                ]);
            })
        ],
        'packagePeriodInMonth' => 'required|integer|min:1',
        // Add other validation rules as needed
    ], [
        'packageName.unique' => 'This plan name is already in use for the selected month.',
        'packageName.required' => 'The plan name is required.',
        'packagePrice.required' => 'The plan price is required.',
        'packagePrice.numeric' => 'The plan price must be a numeric value.',
        'packagePrice.unique' => 'A plan with this price already exists for the selected month.',
        'packagePeriodInMonth.required' => 'The plan period in months is required.',
        'packagePeriodInMonth.integer' => 'The plan period in months must be an integer.',
        'packagePeriodInMonth.min' => 'The plan period in months must be at least 1.',
    ]);

    // Create the package
    $packageId = PlanPackage::create([
        'packageName' => $packageName,
        'packagePrice' => $packagePrice,
        'packageDescription' => $packageDescription,
        'isActive' => $isActive,
        'packagePeriodInMonth' => $packagePeriodInMonth,
        'androidPlanKey' => $androidPlanKey,
        'iosPlanKey' => $iosPlanKey,
        'createdDate' => now(),
    ])->packageId;

    // Flash success message
    flash()->success('Package has been added successfully.');

    // Redirect to the plan package list
    return redirect()->to('/admin/planpackage/');
}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $common=new CommanController;
        $planpackage = PlanPackage::find($id);
        return view('admin.planpackage.addedit',compact('planpackage'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $packagePrice=($request->packagePrice)?($request->packagePrice):"0.00";
        $packageName=($request->packageName)?($request->packageName):"";
        $packageDescription=($request->packageDescription)?($request->packageDescription):"";
        $isActive=($request->isActive)?($request->isActive):0;
        $packagePeriodInMonth=isset($request->packagePeriodInMonth)?($request->packagePeriodInMonth):0;
        $androidPlanKey=isset($request->androidPlanKey)?($request->androidPlanKey):"";
        $iosPlanKey=isset($request->iosPlanKey)?($request->iosPlanKey):"";
        $request->validate([
            'packageName' => [
                'required',
                Rule::unique('tblplanpackage')->where(function ($query) use ($request, $id) {
                    return $query->where('packagePeriodInMonth', $request->input('packagePeriodInMonth'))
                                 ->where('packageId', '!=', $id);
                })
            ],
            'packagePrice' => [
                'required',
                'numeric',
                Rule::unique('tblplanpackage')->where(function ($query) use ($request, $id) {
                    return $query->where([
                        ['packagePeriodInMonth', $request->input('packagePeriodInMonth')],
                        ['packagePrice', $request->input('packagePrice')]
                    ])->where('packageId', '!=', $id);
                })
            ],
            'packagePeriodInMonth' => 'required|integer|min:1',
            // Add other validation rules as needed
        ], [
            'packageName.unique' => 'This plan name is already in use for the selected month.',
            'packageName.required' => 'The plan name is required.',
            'packagePrice.required' => 'The plan price is required.',
            'packagePrice.numeric' => 'The plan price must be a numeric value.',
            'packagePrice.unique' => 'A plan with this price already exists for the selected month.',
            'packagePeriodInMonth.required' => 'The plan period in months is required.',
            'packagePeriodInMonth.integer' => 'The plan period in months must be an integer.',
            'packagePeriodInMonth.min' => 'The plan period in months must be at least 1.',
        ]);
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
        flash()->success('Package has been updated successfully.');
        return redirect()->to('/admin/planpackage/');
    }

    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function destroy(Request $request)
    {
        $common = new CommanController();
         $relatedSubjectsCount = DB::table('plan_subject')
            ->where('plan_id', $request->id)
            ->count();

        if ($relatedSubjectsCount > 0) {
            return response()->json(['status' => 'error', 'message' => 'This plan cannot be deleted because it has assigned subjects.']);
        }

        $planPackage = PlanPackage::find($request->id);

        if ($planPackage) {
            $planPackage->delete();
            return response()->json(['status' => 'success', 'message' => 'Plan deleted successfully.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Plan not found.']);
   }

    /*
     * Status Update either active or inactive
     * @param $status, $id
     */
    public function statusUpdate($status,$id)
    {
        PlanPackage::where('packageId',$id)->update(['isActive'=>$status]);
        flash()->success('Plan Package status has been updated successfully.');
        return redirect()->to('/admin/planpackage/');
    }
    /*Assign subject */
    public function assingsubject($id){

        $common=new CommanController;
        $planpackage = PlanPackage::find($id);
        $subject = Subject::with('category')->where('isActive','1')->get();
        $levels = LevelManagement::where('isActive','1')->get();
        $selectedsubject = planSubject::where('plan_id',$id)->with('subjects','planpackage')->get();
        $selectedcategory = $selectedsubject->pluck('subjects.categoryId')->unique()->values()->all();
       return view('admin.planpackage.assignplanpackage',compact('planpackage','subject','selectedsubject','levels','selectedcategory'));
    }
    public function assingsubjectAdd(Request $request)
    {
        // Get the plan ID from the request
        $planId = $request['plan_id'];
        $remove_all_subject = $request['remove_all_subject'];

        if (!empty($planId) && $remove_all_subject) {            
            $existingRecords = planSubject::where('plan_id', $planId)->delete();
            return redirect()->to('/admin/planpackage/');
        }

        // If no plan_id exists, delete the existing records
        if (empty($planId)) {
            $existingRecords = planSubject::where('plan_id', $planId)->delete();
        }

        // Check if the request has a plan_id and either select_subject or select_all_subject
        if ($request->has('plan_id') && ($request->has('select_subject') || isset($request['select_all_subject']))) {

            // Handle the case when all subjects are selected based on category
            if (isset($request['select_all_subject']) && isset($request['select_category'])) {

                // Fetch subjects based on the selected categories
                $subjects = Subject::select('id', 'categoryId')
                    ->whereIn('categoryId', $request['select_category'])
                    ->where('isActive', '1')
                    ->get()
                    ->toArray();

                foreach ($subjects as $subject) {
                    // Check if the subject already exists in the plan
                    $planSubject = planSubject::where('plan_id', $planId)
                        ->where('subject_id', $subject['id'])
                        ->where('categoryId', $subject['categoryId'])
                        ->first();

                    if ($planSubject !== null) {
                        // If it exists, update the record
                        $planSubject->update([
                            'subject_id' => $subject['id'],
                            'categoryId' => $subject['categoryId']
                        ]);
                    } else {
                        // If it doesn't exist, create a new record
                        planSubject::create([
                            'plan_id' => $planId,
                            'subject_id' => $subject['id'],
                            'categoryId' => $subject['categoryId']
                        ]);
                    }
                }

            } else {
                // Handle the case when specific subjects are selected
                $subjectIds = $request->select_subject;

                foreach ($subjectIds as $subjectId) {
                    // Check if the subject already exists in the plan
                    $planSubject = planSubject::where('plan_id', $planId)
                        ->where('subject_id', $subjectId)
                        ->first();

                    if ($planSubject !== null) {
                        // If it exists, update the record (though no new categoryId is provided here)
                        $planSubject->update([
                            'subject_id' => $subjectId
                        ]);
                    } else {
                        // If it doesn't exist, create a new record
                        planSubject::create([
                            'plan_id' => $planId,
                            'subject_id' => $subjectId
                        ]);
                    }
                }
            }
        }
      
        session()->flash('success','Subject has been updated successfully.');
        // Redirect to the desired route after processing
        return redirect()->to('/admin/planpackage/');
    }
     public function remove_subject_plan(Request $request)
    {
        $delete=planSubject::findOrFail($request->id);

        if($delete->delete()){
            return Response(['status'=>'success','message'=>"Subject deleted successfully."]);  
        } else {
            /*Message */
            return Response(['status'=>'error','message'=> "Something went wrong!"]); 
        }
    }
}
