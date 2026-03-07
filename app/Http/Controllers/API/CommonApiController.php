<?php
namespace Laraspace\Http\Controllers\API;
use Illuminate\Support\Facades\Session;
use Laraspace\Http\Controllers\Controller; 
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; 
use Laraspace\LevelManagement;
use Laraspace\PlanPackage;
use Laraspace\planSubject;
use Laraspace\Subject;
use Laraspace\Topics;
use Laraspace\TransactionDetails;
use Laraspace\CustomerRegister;
use Laraspace\Questions;
use Validator;
use Config;
use DB;
use Illuminate\Support\Facades\URL;


Class CommonApiController extends Controller 
{
	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Config $authenticate){
        $this->_authenticate = config('constant.authenticate');
    }

    /*Get Category*/
	public function getAllCategory(Request $request)
	{
		
		$common = new CommanController;
		$authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $CDT = now()->format('Y-m-d H:i:s');
        if (in_array($apiauthenticate, $authenticate)){
			$category = LevelManagement::Join('transaction_details', function ($join) use ($request,$CDT) {
	            $join->on('tbllevelmanagement.levelId', '=', 'transaction_details.category_id')
	                 ->where('transaction_details.customer_id', '=', $request->userId)
	                 ->where('transaction_details.start_date', '<', $CDT)
					 ->where('transaction_details.end_date', '>=', $CDT)
	                 ->where('transaction_details.status', '=','1');
	        })
	        ->where('tbllevelmanagement.isActive', '=', 1)
	         ->groupby('tbllevelmanagement.levelId')
	        ->get(['tbllevelmanagement.*']);
			if(count($category) != 0){
				$myarray['result'] = $category;
	            $myarray['message'] = $common->get_msg("get_category", $langId) 
	                            ? $common->get_msg("get_category", $langId) 
	                            : 'Get the all category sucessfully.';
	            $myarray['status'] = 1;
			} else {
				
            $myarray['result'] = (object)array();
            $myarray['message'] = $common->get_msg("get_category", $langId) 
                            ? $common->get_msg("get_category", $langId) 
                            : 'Category Not Found.';
            $myarray['status'] = 0;
			}
		} else {
            $myarray['result'] = (object)array();
            $myarray['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $myarray['status'] = 0;
		}

		return response()->json($myarray);
	}
	/*Category Id Wise Subject Get*/
	public function categorySubject(Request $request)
	{
		$common = new CommanController;
		$authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $CDT = now()->format('Y-m-d H:i:s');
        if (in_array($apiauthenticate, $authenticate)){
			$subject = Subject::Join('transaction_details', function ($join) use ($request,$CDT) {
                $join->on('subject.id', '=', 'transaction_details.subject_id')
                     ->where('transaction_details.customer_id', '=', $request->userId)
                     ->where('transaction_details.category_id', '=', $request->catId)
                     ->where('transaction_details.start_date', '<', $CDT)
					 ->where('transaction_details.end_date', '>=', $CDT)
                     ->where('transaction_details.status', '=', '1');
            })
            ->where('subject.categoryId', $request->catId)
             ->where('subject.isActive','1')
            ->groupBy('subject.id')
            ->get(['subject.*']);
			if(count($subject) != 0){
				$myarray['result'] = $subject;
	            $myarray['message'] = $common->get_msg("get_subject", $langId) 
	                            ? $common->get_msg("get_subject", $langId) 
	                            : 'Get the all subject sucessfully.';
	            $myarray['status'] = 1;
			} else {
				
            $myarray['result'] = (object)array();
            $myarray['message'] = $common->get_msg("get_subject", $langId) 
                            ? $common->get_msg("get_subject", $langId) 
                            : 'Subject Not Found.';
            $myarray['status'] = 0;
			}
		} else {
            $myarray['result'] = (object)array();
            $myarray['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $myarray['status'] = 0;
		}

		return response()->json($myarray);
	}

	/*Subject ID Wise Topics Get */
	public function subjectTopics(Request $request)
	{

		$common = new CommanController;
		$authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        if (in_array($apiauthenticate, $authenticate)){
        	$subId = explode(',',$request->subjectId);
			$topics =Topics::whereIn('subjectId',$subId)->get();
			if(count($topics) != 0){
				$myarray['result'] = $topics;
	            $myarray['message'] = $common->get_msg("get_topics", $langId) 
	                            ? $common->get_msg("get_topics", $langId) 
	                            : 'Get the all topics sucessfully.';
	            $myarray['status'] = 1;
			} else {
				
            $myarray['result'] = (object)array();
            $myarray['message'] = $common->get_msg("get_topics", $langId) 
                            ? $common->get_msg("get_topics", $langId) 
                            : 'Topics Not Found.';
            $myarray['status'] = 0;
			}
		} else {
            $myarray['result'] = (object)array();
            $myarray['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $myarray['status'] = 0;
		}

		return response()->json($myarray);
	}
	/* SubjectPlan */
	public function subjectPlan($id, Request $request)
	{
		
		$customer_id = "";
		if ($request->customerId) {
			$customer_id = $request->customerId;
		}
		
		$userSubscriptionData = TransactionDetails::where('customer_id', $customer_id)->where('subject_id',$id)->count();
		$common = new CommanController;
		$authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        if (in_array($apiauthenticate, $authenticate)){

        /*Note: If user has purchased free/any plan before then next time user wouldn't be able to access free plan*/
		if ($userSubscriptionData == 0) {
			/*Note: Plan crud module some query changes */
			$plan =  PlanSubject::with(['subjects' => function ($query) {
						        $query->select('id', 'subjectName', 'categoryId');
						    }, 'subjects.category' => function ($query) {
						        $query->select('levelId', 'levelId', 'levelName');
						    }])
						    ->join('tblplanpackage', 'plan_subject.plan_id', '=', 'tblplanpackage.packageId')
							->select('plan_subject.subject_id','tblplanpackage.*')
						    ->where('plan_subject.subject_id', $id)
						    ->orderBy('tblplanpackage.packagePrice', 'asc')
						    ->get();
		}else{
			$plan =  PlanSubject::with(['subjects' => function ($query) {
						        $query->select('id', 'subjectName', 'categoryId');
						    }, 'subjects.category' => function ($query) {
						        $query->select('levelId', 'levelId', 'levelName');
						    }])
						    ->join('tblplanpackage', 'plan_subject.plan_id', '=', 'tblplanpackage.packageId')
							->select('plan_subject.subject_id','tblplanpackage.*')
						    ->where('plan_subject.subject_id', $id)
						    ->where('tblplanpackage.packagePrice', '!=', '0.00')
						    ->orderBy('tblplanpackage.packagePrice', 'asc')
						    ->get();
		}


			//echo "<pre>";print_r($plan);exit;
			

			if(count($plan) != 0){
				$myarray['result'] = $plan;
	            $myarray['message'] = $common->get_msg("get_plan", $langId) 
	                            ? $common->get_msg("get_plan", $langId) 
	                            : 'Get the all plan sucessfully.';
	            $myarray['status'] = 1;
			} else {
				
            $myarray['result'] = (object)array();
            $myarray['message'] = $common->get_msg("get_plan", $langId) 
                            ? $common->get_msg("get_plan", $langId) 
                            : 'Plan Not Found.';
            $myarray['status'] = 0;
			}
		} else {
            $myarray['result'] = (object)array();
            $myarray['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $myarray['status'] = 0;
		}

		return response()->json($myarray);
	}
	/*All common search customer id wise subject id wise question search */
	public function searchQuestions(Request $request){
		$common = new CommanController;
		$authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
		$customer = CustomerRegister::find($request->cust_id);
	    if (!$customer) {
	    	$myarray['result'] = (object)array();
            $myarray['message'] = $common->get_msg("customer_user_not_found", $langId) 
                            ? $common->get_msg("customer_user_not_found", $langId) 
                            : 'Customer not found.';
            $myarray['status'] = 0;
	       return response()->json($myarray);
	    }
	    $customerId =  $request->cust_id ?? '';
	    $subjectId = $request->subject_id ?? '';
	    $searchKeyword = $request->search ?? '';
	  	$questionsIDs = DB::table('tblquestion as q')
			    ->leftJoin('topicQueRel as tq', 'q.questionId', '=', 'tq.questionId')
			    ->leftJoin('topics as t', 'tq.topicId', '=', 't.id')
			    ->leftJoin('subject as s', 't.subjectId', '=', 's.id')
			    ->leftJoin('transaction_details as td', 's.id', '=', 'td.subject_id')
			    ->leftJoin('tblcustomerregister as c', 'td.customer_id', '=', 'c.id')
			    ->Join('tbllevelmanagement as cat', 's.categoryId', '=', 'cat.levelId')
			    ->where('s.id', $subjectId)
			    ->where('c.id', $customerId)
			    ->where('q.isActive', 1)
			    ->where('q.question', 'like', '%' . $searchKeyword . '%')
			     ->pluck('q.questionId')->toArray();
		if(!empty($questionsIDs)){
			$allGetQuestion = $common->getQuestion($questionsIDs);
			if(count($allGetQuestion) != 0){
				foreach ($allGetQuestion as &$question) {
	                if ($question->video) {
	                    $question->videoUrl = URL::to('/') . '/topicImages/' . $question->video;
	                }else{
	                	$question->videoUrl = null;
	                }

	                foreach ($question->options as &$option) {
	                    if ($option->questionImage) {
	                        $option->questionImageUrl = URL::to('/') . '/optionImages/' . $option->questionImage;
	                    }else{
	                    	$option->questionImageUrl = null;
	                    }
	                }
	            }
				$myarray['result'] = $allGetQuestion;
		        $myarray['message'] = $common->get_msg("search", $langId) 
		                        ? $common->get_msg("search", $langId) 
		                        : 'Question Listing.';
		        $myarray['status'] = 1;

			} else {
				$myarray['result'] = (object)array();
		        $myarray['message'] = $common->get_msg("search_not_found", $langId) 
		                        ? $common->get_msg("search_found", $langId) 
		                        : 'Question not found.';
		        $myarray['status'] = 0;
			}
		} else {
			$myarray['result'] = (object)array();
	        $myarray['message'] = $common->get_msg("search_not_found", $langId) 
	                        ? $common->get_msg("search_not_found", $langId) 
	                        : 'Question not found.';
	        $myarray['status'] = 0;
		}
		
      return response()->json($myarray);
	}
}