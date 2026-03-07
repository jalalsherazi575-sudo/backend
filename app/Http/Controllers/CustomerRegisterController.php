<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Laraspace\Exports\CustomerRegisterExport;
use Laraspace\Exports\CustomerPlanExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laraspace\UserSubscriptionPlan;
use Laraspace\CustomerRegister;
use Laraspace\PlanPackage;
use Laraspace\planSubject;
use Laraspace\Country;
use Laraspace\Exam;
use Laraspace\Questions;
use Laraspace\TransactionMaster;
use Laraspace\TransactionDetails;
use Laraspace\LevelManagement;
use Laraspace\Subject;
use Auth;
use Image;

use Laraspace\Helpers\Helper;

//use Laraspace\CustomerAddress;
class CustomerRegisterController extends Controller
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
     * Generate referral code
     *
     * @return string
     */
    public function generateRefferalCode()
    {
        $referralCode = Str::random(8);
        return $referralCode ;
    }
      /*
     * Generate OTP using random function
     */
    public function generateOTP()
    {
        $fourRandomDigit = mt_rand(1000,9999);
        return $fourRandomDigit;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $customerData = CustomerRegister::orderBy('id', 'DESC')->get();
        return view('admin.customerregister.index',['customerData'=>$customerData]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    	$country = Country::where('status', '=','1')->get();
        return view('admin.customerregister.addedit',['country'=>$country]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'name'      => 'required',
            'email'     => 'required', 'email','unique:tblcustomerregister,email', 
            'password'  => 'required',
            'phone'     => 'required', 'digits_between:10,13','numeric','unique:tblcustomerregister,phone',
            'photo'     =>'image|mimes:jpeg,png,jpg',
        ]);

        $filename='';
        $customer_image='';
        if ($request->file('photo')) 
        {
            $file = $request->file('photo');
            $size = getimagesize($file);
            $ratio = $size[0]/$size[1];
            $common=new CommanController;
            $categorywidth=100;
            $categoryheight=100;

            if( $ratio > 1) {
                $width = $categorywidth;
                $height = $categoryheight/$ratio;
            } else {
                $width = $categorywidth;
                $height = $categoryheight;
            }
             
            $extension = $file->getClientOriginalExtension();
            $customer_image=time().$file->getClientOriginalName();
            $category_destinationPath = public_path('/customerregisterphoto/thumbnail_images');
            $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
            $thumb_img->save($category_destinationPath.'/'.$customer_image,80);
             
            $filename=$file->getClientOriginalName();
            $destinationPath = 'customerregisterphoto';
            $file->move($destinationPath,$file->getClientOriginalName());
        }

        CustomerRegister::create([
            'name' => (ltrim($request->name)),
            'email' => (ltrim($request->email)),
            'password' => (ltrim(bcrypt($request->password))),
            'gender' => isset($request->gender)?(ltrim($request->gender)):0,
            'birthDate' => isset($request->birthDate)?(ltrim(date("Y-m-d",strtotime($request->birthDate)))):"",
            'phone' => isset($request->phone)?(ltrim($request->phone)):"",
            'countryId' => isset($request->country)?(ltrim($request->country)):0,
            'isActive' => $request->isActive,
            'photo' => $customer_image,
            'OTP' => $this->generateOTP(),
            'remember_token' => (ltrim($request->_token)),
            'referralCode' => $this->generateRefferalCode(),
            'phoneVerificationSentRequestTime' => date('Y-m-d H:i:s'),
            'createdDate' => date('Y-m-d H:i:s'),
            'updatedDate' => date('Y-m-d H:i:s'),
        ]);

        // $sid    = env('TWILIO_ACCOUNT_SID');
        // $token  = env('TWILIO_AUTH_TOKEN');
        // $client = new Client( $sid, $token );

        // $client->messages->create(
        //            $request->phone,
        //            [
        //                'from' => env('TWILIO_PHONE_NUMBER'),
        //                'body' => "Here i m sending you OTP. Please verify. Your otp is " .$this->generateOTP(),
        //            ]
        //        );

        session()->flash('success','Customer has been registered successfully.');                   
        return redirect()->to('/admin/customers');
    }

     /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $country = Country::where('status', '=','1')->get();
        $common=new CommanController;
        $customerimagewidth=$common->getImageSizeValue('customer_register_image_width');
        $customerimageheight=$common->getImageSizeValue('customer_register_image_height');
        $customer = CustomerRegister::find($id);
        return view('admin.customerregister.addedit',compact('customer','customerimagewidth','customerimageheight','country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = $request->validate([
            'name'      => 'required',
            'email'     => 'required|email|unique:tblcustomerregister,email,' . $id,
            //'password'  => 'required',
            'phone'     => 'required|digits_between:10,13|numeric|unique:tblcustomerregister,phone,' . $id,
            'photo'     => 'image|mimes:jpeg,png,jpg'
        ]);

        $update=CustomerRegister::find($id);
        $update->name   = (ltrim($request->name));
        $update->email  = (ltrim($request->email)); 
        $update->phone  = isset($request->phone)?(ltrim($request->phone)) : "";
        $update->isActive = $request->isActive;
        $update->createdDate = date('Y-m-d H:i:s');
        $update->updatedDate = date('Y-m-d H:i:s'); 
        if ($request->file('photo')) 
        {
            $file = $request->file('photo');
            $size = getimagesize($file);
            $ratio = $size[0]/$size[1];
            $common=new CommanController;
            $customerimagewidth=100;
            $customerimageheight=100;
            
            if( $ratio > 1) {
                $width = $customerimagewidth;
                $height = $customerimageheight/$ratio;
            }
            else {
                $width = $customerimagewidth;
                $height = $customerimageheight;
            }

            $extension = $file->getClientOriginalExtension();
            $customer_image=time().$file->getClientOriginalName();
            $customer_destinationPath = public_path('/customerregisterphoto/thumbnail_images');
            $thumb_img = Image::make($file->getRealPath())->resize($width, $height);
            $thumb_img->save($customer_destinationPath.'/'.$customer_image,80);

            $filename=$file->getClientOriginalName();
            $destinationPath = 'customerregisterphoto';
            $file->move($destinationPath,$file->getClientOriginalName());
            $update->photo = $customer_image;
        }
        if (isset($request->password) && $request->password!='') {
            $update->password = (ltrim(bcrypt($request->password)));
        } 
        $update->save();
        session()->flash('success','Customer has been updated successfully.');         
        return redirect()->to('/admin/customers');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = CustomerRegister::findOrFail($id);
        // Delete the user's image file if it exists
        if (!empty($user->photo)) {
            $imagePath = public_path('customerregisterphoto/thumbnail_images/' . $user->photo);
            if (file_exists($imagePath)) {
                unlink($imagePath); // Delete the image file from the server
            }
        }
        $user->delete();
        session()->flash('success','Customer has been deleted successfully.');                   
        return redirect()->to('/admin/customers');
    }

    /*
     * Status Update either active or inactive
     * @param $status, $id
     */
    public function statusUpdate($status,$id)
    {
        CustomerRegister::where('id',$id)->update(['isActive'=>$status]);
        session()->flash('success','Customer status has been updated successfully.');                   
        return redirect()->to('/admin/customers');
    }

    /*Export CSV*/
    public function exportCSV()
    {
     
        $data = CustomerRegister::orderBy('id', 'DESC')->get()->toArray();
        $common = new CommanController();
        $fileName = time() . "_CustomerData.csv";
        return Excel::download(new CustomerRegisterExport, $fileName);
    }

    /*User Subscription Plan start */

    public function UserSubscriptionPlanPackageList($customerId) {

        $common=new CommanController;
        $customer = CustomerRegister::findOrFail($customerId);
        $transactiondetail = TransactionMaster::where('customer_id',$customerId)->orderby('id','DESC')->get();


        /*$customer = CustomerRegister::find(84);
        $customerdata = array();
        if(!empty($customer)){
         $customerdata = $customer;   
        }
        $amount = 100;
        
        $plan = PlanPackage::find(7);
        $plandata = array();
        if(!empty($plan)){
         $plandata = $plan;   
        }



        $invoice = Helper::infakt_integration_get_invoice_data($customerdata,$amount,$plandata);

          echo "<pre>";print_r($invoice);
         
        exit();*/

        return view('admin.customerregister.planpackage',compact('customer','transactiondetail','customerId'));
    }

    public function allUserSubscriptionPlanPackageList() {

        $common=new CommanController;
        $customer = CustomerRegister::get();
        /*$transactiondetail = TransactionMaster::join('transaction_details', 'transaction_master.customer_id', '=', 'transaction_details.customer_id')
            ->join('tblplanpackage', 'transaction_details.plan_id', '=', 'tblplanpackage.packageId')
            ->join('tblcustomerregister', 'transaction_details.customer_id', '=', 'tblcustomerregister.id')
            ->orderBy('transaction_master.id', 'DESC')
            ->select('transaction_master.*', 'transaction_details.plan_id','tblplanpackage.packageName','tblcustomerregister.name')
            ->get();*/

        $transactiondetail = TransactionMaster::join('tblcustomerregister', 'transaction_master.customer_id', '=', 'tblcustomerregister.id')
            ->orderBy('transaction_master.id', 'DESC')
            ->select('transaction_master.*','tblcustomerregister.name')
            ->get();

        return view('admin.customerregister.allusersplanpackage',compact('customer','transactiondetail'));
    }

    /*Plan Pacakge Detail*/
    public function getPackageDetail($id) {
        $transaction = TransactionMaster::find($id);
        $customer = CustomerRegister::find($transaction->customer_id);
        $plandetail = TransactionDetails::with('subject','category','planpackage','transaction')->where('transaction_id',$id)->orderby('id','DESC')->get();
        return view('admin.customerregister.planpackagedetail',compact('customer','transaction','plandetail'));
    }
    /*Custmer Subscriptions*/
    public function AssignPlanPackage($customerId) {
        $common=new CommanController;
        $customer = CustomerRegister::find($customerId);
        $category = LevelManagement::where('isActive',1)->get();

        return view('admin.customerregister.assignplanpackage',compact('customer','category'));
           
    }
    public function getSubjects($categoryId)
    {
        $subjects = Subject::where('categoryId', $categoryId)->where('isActive','1')->pluck('subjectName', 'id');
        return response()->json($subjects);
    }
   
    public function getPlans($subjectId)
    {
        
        /*$plans = PlanPackage::where('subjectId', $subjectId)->where('isActive','1')->select('packageId', 'packageName', 'packagePrice', 'packagePeriodInMonth')
                    ->get();*/
         $plans = PlanSubject::join('tblplanpackage', 'plan_subject.plan_id', '=', 'tblplanpackage.packageId')
                            ->select('plan_subject.subject_id','tblplanpackage.*')
                            ->where('plan_subject.subject_id', $subjectId)
                            ->get();
        return response()->json($plans);
    } 
    /*Add Plan*/
    public function PostAssignPlanPackage(Request $request, $customerId) 
    {
        $validator = $request->validate([
            'category_id'      => 'required',
            'subject_id'     => 'required',
            'plan_id'      => 'required',
            'description'     => 'required',
         ],[
            'category_id.required' => "Please select the category.",
            'subject_id.required' => "Please select the subject.",
            'plan_id.required' => "Please select the plan.",
            'description.required' => "Please enter the description.",
         ]);
        $plans = PlanPackage::where('packageId',$request->plan_id)->first();
        $currentDate=date("Y-m-d");
        $endDate = now()->addMonths($plans->packagePeriodInMonth); 
        $loginUserId=Auth::user()->id;
        $orderid = $request->customerid.'_'.now(); 
        $transaction_id = $this->generateRefferalCode();
        
        $common=new CommanController;
        $createtransactonmaster  = new TransactionMaster;
        $createtransactonmaster->transaction_id = 'TR-'.$transaction_id;
        $createtransactonmaster->customer_id = $request->customerid;
        $createtransactonmaster->transaction_order_id = $orderid;
        $createtransactonmaster->total_amount = $plans->packagePrice;
        $createtransactonmaster->payment_status = $request->category_id;
        $createtransactonmaster->paymentDate = now();
        $createtransactonmaster->isAssignedByAdmin = 1;
        $createtransactonmaster->assignAdminId = $loginUserId;
        $createtransactonmaster->save();

        $create  = new TransactionDetails;
        $create->transaction_id = $createtransactonmaster->id;
        $create->transaction_order_id = $createtransactonmaster->transaction_order_id;
        $create->customer_id = $request->customerid;
        $create->subject_id = $request->subject_id;
        $create->category_id = $request->category_id;
        $create->plan_id = $request->plan_id;
        $create->plan_month = $plans->packagePeriodInMonth;
        $create->start_date = now();
        $create->end_date = $endDate;
        $create->plan_amount = $plans->packagePrice;
        $create->status = '1';
        $create->save();

        session()->flash('success','Customer Plan Package has been assigned successfully.');                   
        return redirect()->to('/admin/customer/planpackage/'.$customerId);

    }

    public function deletePlanPackage($customerId,$id)
    {
        $user = UserSubscriptionPlan::find($id);
        $user->delete();
        session()->flash('success','Customer Plan Package has been deleted successfully.');                   
        return redirect()->to('/admin/customer/planpackage/'.$customerId);
    }


    //delete plan transaction package
    public function deleteTransactionPackage($customerId,$id)
    {

        $TransactionMaster = TransactionMaster::where('customer_id',$customerId)->where('id',$id);
        $TransactionMaster->delete();
        $Transactiondetails = TransactionDetails::where('customer_id',$customerId)->where('transaction_id',$id);
        $Transactiondetails->delete();
        session()->flash('success','Customer Plan Package transaction detail has been deleted successfully.');                   
        return redirect()->to('/admin/customer/planpackage/'.$customerId);
        
    }


    /*Export Plan*/
    public function exportUserSubscriptionPlanPackageList($customerId)
    {
        $common=new CommanController;
        $customer = CustomerRegister::find($customerId);

        if ($customer) {
            $customerName=isset($customer->name)?($customer->name):"";
            $fileName=time()."_".$customerName."_"."SubscriptionPlanPackage.csv";
        } else {
            $fileName=time()."_SubscriptionPlanPackage.csv";
            $customerName='';
        } 
         return Excel::download(new CustomerPlanExport($customerId), $fileName);
        
    }
    /*User Subscription Plan End*/
    
    /*Customer Id Wise Exam Get*/
    public function getExam($id)
    {
        $exam = Exam::orderby('id','DESC')->where('cust_id',$id)->get();
        return view('admin.customerregister.exam',compact('exam'));
    }
    /*Exam Delete*/
    public function deleteExam($id)
    {

        $examdelete = Exam::findOrFail($id);
        if($examdelete->delete()){
           return Response(['status'=>'success','message'=> 'Exam deleted successfully']);  
        } else {
           return Response(['status'=>'error','message'=>  'Something went wrong!']); 
        }
    }
    /*Exam Id Wise question Get*/
    public function examQuestion($id)
    {
        $exam = Exam::find($id);
        $questionId = explode(',',$exam->question_id);
        $question = Questions::with('topics')->whereIn('questionId',$questionId)->get();
        $id = $exam->cust_id;
        return view('admin.customerregister.question',compact('question','id'));
    }
    /*End*/
}
