<?php
namespace Laraspace\Http\Controllers\API;
use Illuminate\Support\Facades\Session;
use Laraspace\Http\Controllers\Controller; 
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; 
use Laraspace\Questions;
use Laraspace\Exam;
use Laraspace\ExamQueRel;
use Validator;
use Config;
use DB;
use Illuminate\Support\Facades\URL;


Class ExamApiController extends Controller 
{

	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Config $authenticate){
        $this->_authenticate = config('constant.authenticate');
    }

	/*Get Customer Id wise All exam*/
	public function getAllExam(Request $request)
	{
		$common = new CommanController;
		$authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        if (in_array($apiauthenticate, $authenticate)){
			$allQuestion = Exam::with('topics')->where('cust_id',$request->customer_id)->orderby('id','DESC')->get();
			if(count($allQuestion) != 0){
				$myarray['result'] = $allQuestion;
	            $myarray['message'] = $common->get_msg("get_exam", $langId) 
	                            ? $common->get_msg("get_exam", $langId) 
	                            : 'Get all exam successfully.';
	            $myarray['status'] = 1;
			} else {
				$myarray['result'] = (object)array();
	            $myarray['message'] = $common->get_msg("get_exam", $langId) 
	                            ? $common->get_msg("get_exam", $langId) 
	                            : 'Exam Not Found.';
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
	/*Create*/
	public function createExam(Request $request)
	{
		$common = new CommanController;
		$authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        if (in_array($apiauthenticate, $authenticate)){

			$validator = Validator::make($request->all() , 
	        [
	            'title' => 'required',
	            'question_total' => 'required',
	            'duration_minutes' => 'required',
	            'topics' => 'required',
	        ]);
	        
	        if ($validator->fails()){
	        	$errors = collect($validator->errors());
	            $error = $errors->first();
	            $myarray['result'] = (object)array();
	            $myarray['message'] = implode('', $error);
	            $myarray['status'] = 0;
	        } else {
	        	$create = new Exam;
				$create->title = $request->title;
				$create->duration_minutes = $request->duration_minutes;
				$create->topics_id = $request->topics;
				$create->cust_id = $request->cust_id;

				$topicIDs = explode(',',$request->topics);
				if($request->question_total == 0){
					$randomQuestionIDs = DB::table('tblquestion')
									    ->join('topicQueRel', 'tblquestion.questionId', '=', 'topicQueRel.questionId')
									    ->whereIn('topicQueRel.topicId', $topicIDs)
									    ->where('tblquestion.isActive', 1)
									    ->pluck('tblquestion.questionId')->toArray();
				} else {
					$randomQuestionIDs = DB::table('tblquestion')
										    ->join('topicQueRel', 'tblquestion.questionId', '=', 'topicQueRel.questionId')
										    ->whereIn('topicQueRel.topicId', $topicIDs)
										    ->where('tblquestion.isActive', 1)
										    ->inRandomOrder()
										    ->limit($request->question_total)
										    ->pluck('tblquestion.questionId')->toArray();

				}
				$message = '';
				if (count($randomQuestionIDs) < $request->question_total) {
					$message = "Only " . count($randomQuestionIDs) . " questions were found.";
				}
				$questionIDsString = implode(',', $randomQuestionIDs);
				$create->question_total = count($randomQuestionIDs);
				$create->question_id = $questionIDsString;
				$create->save();
				// Iterate over each topic ID
				foreach ($topicIDs as $topicID) {
				    // Create ExamQueRel entry for each topic ID
				    ExamQueRel::create([
				        'exam_id' => $create->id,
				        'topic_id' => $topicID,
				    ]);
				}
				$AllQuestion = array();
				$exam = Exam::findOrFail($create->id);

				// Include the exam and its related topics in $AllQuestion
				$AllQuestion['exam'] = [
				    'exam_details' => $exam,
				    'topics' => $exam->topics, // Assuming $exam->topics contains the related topics
				];
				if(!empty($randomQuestionIDs)){
					
					$allGetQuestion = $common->getQuestion($randomQuestionIDs);
				
					if(!empty($allGetQuestion))
					{
						foreach ($allGetQuestion as &$question) {
				            if ($question->video) {
				                $question->videoUrl = URL::to('/') . '/topicImages/' . $question->video;
				            } else {
				                $question->videoUrl = null;
				            }

				            foreach ($question->options as &$option) {
							    if ($option->questionImage) {
							        $option->questionImageUrl = URL::to('/') . '/optionImages/' . $option->questionImage;
							    } else {
							        $option->questionImageUrl = null;
							    }
							}

				        }
						$AllQuestion['question'] = $allGetQuestion->toArray();
					}				
				}
				
				if (count($randomQuestionIDs) < $request->question_total) {
					//$message = "Only " . count($randomQuestionIDs) . " questions were found and Create Exam successfully .";
					if(count($randomQuestionIDs) == 0){
						$message = "No questions found";
					} else {
						$message = "Only " . count($randomQuestionIDs) . " questions were found and Create Exam successfully .";
					}
					
				} else {
					$message = $common->get_msg("create_exam",$langId)?$common->get_msg("blank_customerId",$langId):"Create Exam successfully.";
				}
				/*Question object array*/
				$myarray['result']= $AllQuestion;
				$myarray['message']= $message;
				$myarray['status']=1;
	        }
	    }else{

            $myarray['result'] = (object)array();
            $myarray['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $myarray['status'] = 0;
        }
	    return response()->json($myarray);
	}

	/*Exam store*/
	public function custExamSubmit(Request $request)
	{
		
		$common = new CommanController;
		$authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        if (in_array($apiauthenticate, $authenticate))
        {
        	$validator = Validator::make($request->all() , 
	        [
	            'exam_id' => 'required',
	            'correct_answer_total' => 'required',
	           // 'exam_result' => 'required',
	            'cust_id' => 'required',
	        ]);
	        
	        if ($validator->fails()){
	        	$errors = collect($validator->errors());
	            $error = $errors->first();
	            $myarray['result'] = (object)array();
	            $myarray['message'] = implode('', $error);
	            $myarray['status'] = 0;
	        } else {
	        	$update = Exam::find($request->exam_id);
	        	if($update){
	        		$update->correct_answer_total = $request->correct_answer_total;
	        		$update->exam_result = $request->exam_result ?? Null;
	        		$update->save();
	        		$myarray['result'] = $update;
		            $myarray['message'] = $common->get_msg("add_exam", $langId) 
		                            ? $common->get_msg("add_exam", $langId) 
		                            : 'Add Exam successfully.';
		            $myarray['status'] = 1;
	        	} else {
	        		$myarray['result'] = (object)array();
		            $myarray['message'] = $common->get_msg("add_exam", $langId) 
		                            ? $common->get_msg("add_exam", $langId) 
		                            : 'Exam Not Found.';
		            $myarray['status'] = 0;
	        	}
	        }
	      }
	      return response()->json($myarray);
        
	}
	/* Get Exam ID*/
	/*public function show($id, Request $request){
		$common = new CommanController;
		$authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        if (in_array($apiauthenticate, $authenticate)){
        	$exam = Exam::find($id);
        	if(!empty($exam))
        	{
        		$AllQuestion['exam'] = [
					    'exam_details' => $exam,
					];
        		$questionId = explode(',',$exam->question_id);
        		$allGetQuestion = $common->getQuestion($questionId);
        		if(!empty($allGetQuestion)){
        			$AllQuestion['question'] = $allGetQuestion->toArray();
	        		$myarray['result'] = $AllQuestion;
		            $myarray['message'] = $common->get_msg("exam_show", $langId) 
		                            ? $common->get_msg("exam_show", $langId) 
		                            : 'Exam get successfully.';
		            $myarray['status'] = 1;
        		}
				
        	} else {
        		$myarray['result'] = (object)array();
	            $myarray['message'] = $common->get_msg("exam_show", $langId) 
	                            ? $common->get_msg("exam_show", $langId) 
	                            : 'Exam Not Found.';
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
	}*/

	/*add new code 20-11-2024*/
	public function show($id, Request $request)
	{
	    $common = new CommanController;
	    $authenticate = $this->_authenticate;
	    $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
	    $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
	    if (in_array($apiauthenticate, $authenticate)) {
	        $exam = Exam::find($id);
	        if (!empty($exam)) {
	            $AllQuestion['exam'] = [
	                'exam_details' => $exam,
	            ];
	            $questionId = explode(',', $exam->question_id);
	            $allGetQuestion = $common->getQuestion($questionId);
	            if (!empty($allGetQuestion)) {
	                foreach ($allGetQuestion as &$question) {
	                    $question->videoUrl = $question->video 
	                        ? URL::to('/') . '/topicImages/' . $question->video 
	                        : null;
	                    foreach ($question->options as &$option) {
	                        $option->questionImageUrl = $option->questionImage 
	                            ? URL::to('/') . '/optionImages/' . $option->questionImage 
	                            : null;
	                    }
	                }
	                $AllQuestion['question'] = $allGetQuestion->toArray();
	                $myarray['result'] = $AllQuestion;
	                $myarray['message'] = $common->get_msg("exam_show", $langId) 
	                    ? $common->get_msg("exam_show", $langId) 
	                    : 'Exam fetched successfully.';
	                $myarray['status'] = 1;
	            } else {
	                $myarray['result'] = (object)[];
	                $myarray['message'] = $common->get_msg("exam_show", $langId) 
	                    ? $common->get_msg("exam_show", $langId) 
	                    : 'Questions not found.';
	                $myarray['status'] = 0;
	            }
	        } else {
	            $myarray['result'] = (object)[];
	            $myarray['message'] = $common->get_msg("exam_show", $langId) 
	                ? $common->get_msg("exam_show", $langId) 
	                : 'Exam not found.';
	            $myarray['status'] = 0;
	        }
	    } else {
	        $myarray['result'] = (object)[];
	        $myarray['message'] = $common->get_msg("invalid_authentication", $langId) 
	            ? $common->get_msg("invalid_authentication", $langId) 
	            : 'Invalid Authentication.';
	        $myarray['status'] = 0;
	    }
	    return response()->json($myarray);
	}


	/*Customer Exam wise summery show */
	public function examSummary(Request $request) {
		$summary = array();
	    $exam_id = $request->exam_id; // Assuming you pass the exam ID in the request
	    $customer_id = $request->cust_id; // Assuming you pass the customer ID in the request
	    
	    // Check if the exam exists for the customer
	    $exam = Exam::where('id', $exam_id)
	        ->where('cust_id', $customer_id)
	        ->first();

	    if (!$exam) {
	        return response()->json(['message' => 'Exam not found', 'status' => 0]);
	    }

	    // Parse the exam_result JSON column
	    $exam_results = json_decode($exam->exam_result, true);
	  
	  	if(!empty($exam_results)){
	  		
		    // Create an associative array of given answers for quick lookup
		    $given_answers = [];
		    foreach ($exam_results as $result) {
		        $given_answers[$result['question_id']] = $result['answer_id'];
		    // Retrieve questions, options, and correct answers in a single query
		    $exam_summary = DB::table('tblquestion')
		        ->join('tblquestionoption', 'tblquestion.questionId', '=', 'tblquestionoption.questionId')
		        ->leftJoin('exam', function ($join) use ($exam_id, $customer_id) {
		            $join->on('tblquestion.questionId', '=', 'exam.question_id')
		                ->where('exam.id', $exam_id)
		                ->where('exam.cust_id', $customer_id);
		        })
		        ->select(
		            'tblquestion.questionId as question_id',
		            'tblquestion.question',
		            'tblquestionoption.id as option_id',
		            'tblquestionoption.questionImageText',
		            'tblquestionoption.isCorrectAnswer'
		        )
		        ->where('exam.id', $exam_id)
		        ->orWhereIn('tblquestion.questionId', array_keys($given_answers))
		        ->get();
		    }
		    // Group the results by question_id
		    $grouped_summary = $exam_summary->groupBy('question_id');
		   
		    // Format the data
		    $summary = $grouped_summary->map(function ($item) use ($given_answers) {
		        $question_text = $item->first()->question;
		        $options = $item->map(function($option) {
		            return [
		                'id' => $option->option_id,
		                'questionImageText' => $option->questionImageText,
		                'isCorrectAnswer' => $option->isCorrectAnswer
		            ];
		        });

		        $question_id = $item->first()->question_id;
		        $given_answer = $given_answers[$question_id] ?? null;
		        $correct_answer = optional($item->firstWhere('is_correct', 1))->option_id;

		        return [
		            'questionId' => $question_id,
		            'question' => $question_text,
		            'options' => $options,
		            'given_answer' => $given_answer,
		            'correct_answer' => $correct_answer
		        ];
		    });

	  	}
	  	if(!empty($summary)){
	  		$data = ['message' => 'Exam summary retrieved successfully', 'status' => 1, 'summary' => $summary->values()];
	  	} else {
	  		$data = ['message' => 'Exam summary not found', 'status' => 1, 'summary' => []];
	  	}
	  	return response()->json($data);
	}

	/**
	 * Get focus areas (weak topics) for customer
	 * POST /api/customer/focusareas
	 * Request: { customer_id: int }
	 */
	public function getFocusAreas(Request $request)
	{
		$common = new CommanController;
		$authenticate = $this->_authenticate;
		$apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
		$langId = ($request->header('langId')) ? ($request->header('langId')) : 1;

		if (in_array($apiauthenticate, $authenticate)) {
			$validator = Validator::make($request->all(), [
				'customer_id' => 'required'
			]);

			if ($validator->fails()) {
				$errors = collect($validator->errors());
				$error = $errors->first();
				$myarray['result'] = (object)array();
				$myarray['message'] = implode('', $error);
				$myarray['status'] = 0;
				return response()->json($myarray);
			}

			$customer_id = $request->customer_id;

			// Get all exams for customer
			$exams = Exam::where('cust_id', $customer_id)
						 ->whereNotNull('exam_result')
						 ->get();

			if ($exams->isEmpty()) {
				$myarray['result'] = [
					'focus_areas' => [],
					'total_exams_analyzed' => 0
				];
				$myarray['message'] = 'No exams found for this customer.';
				$myarray['status'] = 1;
				return response()->json($myarray);
			}

			$topicPerformance = [];

			foreach ($exams as $exam) {
				// Parse exam_result JSON
				$exam_result = json_decode($exam->exam_result, true);
				$given_answers = [];

				if (is_array($exam_result)) {
					foreach ($exam_result as $item) {
						if (isset($item['question_id']) && isset($item['answer_id'])) {
							$given_answers[$item['question_id']] = $item['answer_id'];
						}
					}
				}

				if (empty($given_answers)) {
					continue;
				}

				// Get questions with correct answers and topics
				$questions = DB::table('tblquestion')
					->join('tblquestionoption', 'tblquestion.questionId', '=', 'tblquestionoption.questionId')
					->join('topicQueRel', 'tblquestion.questionId', '=', 'topicQueRel.questionId')
					->join('topics', 'topicQueRel.topicId', '=', 'topics.id')
					->whereIn('tblquestion.questionId', array_keys($given_answers))
					->where('tblquestionoption.isCorrectAnswer', 1)
					->select(
						'tblquestion.questionId',
						'tblquestionoption.id as correct_option_id',
						'topics.id as topic_id',
						'topics.topicName'
					)
					->get();

				// Count incorrect answers per topic
				foreach ($questions as $question) {
					$topic_name = $question->topicName;
					$question_id = $question->questionId;
					$correct_option = $question->correct_option_id;
					$given_answer = $given_answers[$question_id] ?? null;

					if (!isset($topicPerformance[$topic_name])) {
						$topicPerformance[$topic_name] = [
							'topic_id' => $question->topic_id,
							'name' => $topic_name,
							'incorrect_count' => 0,
							'total_count' => 0
						];
					}

					$topicPerformance[$topic_name]['total_count']++;

					if ($given_answer != $correct_option) {
						$topicPerformance[$topic_name]['incorrect_count']++;
					}
				}
			}

			// Convert to array and sort by incorrect count (descending)
			$focusAreas = array_values($topicPerformance);
			usort($focusAreas, function($a, $b) {
				return $b['incorrect_count'] - $a['incorrect_count'];
			});

			// Calculate percentage for each topic
			foreach ($focusAreas as &$area) {
				$area['error_rate'] = $area['total_count'] > 0
					? round(($area['incorrect_count'] / $area['total_count']) * 100, 1)
					: 0;
			}

			$myarray['result'] = [
				'focus_areas' => $focusAreas,
				'total_exams_analyzed' => count($exams)
			];
			$myarray['message'] = 'Focus areas retrieved successfully.';
			$myarray['status'] = 1;
		} else {
			$myarray['result'] = (object)array();
			$myarray['message'] = $common->get_msg("invalid_authentication", $langId)
							? $common->get_msg("invalid_authentication", $langId)
							: 'Invalid Authentication.';
			$myarray['status'] = 0;
		}

		return response()->json($myarray);
	}

	/**
	 * Get performance statistics for customer
	 * POST /api/customer/performance
	 * Request: { customer_id: int }
	 */
	public function getPerformanceStats(Request $request)
	{
		$common = new CommanController;
		$authenticate = $this->_authenticate;
		$apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
		$langId = ($request->header('langId')) ? ($request->header('langId')) : 1;

		if (in_array($apiauthenticate, $authenticate)) {
			$validator = Validator::make($request->all(), [
				'customer_id' => 'required'
			]);

			if ($validator->fails()) {
				$errors = collect($validator->errors());
				$error = $errors->first();
				$myarray['result'] = (object)array();
				$myarray['message'] = implode('', $error);
				$myarray['status'] = 0;
				return response()->json($myarray);
			}

			$customer_id = $request->customer_id;

			// Get all completed exams
			$exams = Exam::where('cust_id', $customer_id)->get();

			if ($exams->isEmpty()) {
				$myarray['result'] = [
					'correct_answers' => 0,
					'wrong_answers' => 0,
					'skipped_questions' => 0,
					'total_time_spent_minutes' => 0,
					'total_exams' => 0
				];
				$myarray['message'] = 'No exams found for this customer.';
				$myarray['status'] = 1;
				return response()->json($myarray);
			}

			$totalCorrect = 0;
			$totalWrong = 0;
			$totalSkipped = 0;
			$totalTimeSpent = 0;

			foreach ($exams as $exam) {
				$exam_result = json_decode($exam->exam_result, true);
				$given_answers = [];

				if (is_array($exam_result)) {
					foreach ($exam_result as $item) {
						if (isset($item['question_id']) && isset($item['answer_id'])) {
							$given_answers[$item['question_id']] = $item['answer_id'];
						}
					}
				}

				$question_ids = explode(',', $exam->question_id);

				// Get correct answers for questions in this exam
				$correctAnswers = DB::table('tblquestion')
					->join('tblquestionoption', 'tblquestion.questionId', '=', 'tblquestionoption.questionId')
					->whereIn('tblquestion.questionId', $question_ids)
					->where('tblquestionoption.isCorrectAnswer', 1)
					->pluck('tblquestionoption.id', 'tblquestion.questionId')
					->toArray();

				foreach ($question_ids as $qid) {
					$qid = trim($qid);
					if (empty($qid)) continue;

					if (!isset($given_answers[$qid]) || $given_answers[$qid] == 0 || $given_answers[$qid] == null) {
						$totalSkipped++;
					} elseif (isset($correctAnswers[$qid]) && $given_answers[$qid] == $correctAnswers[$qid]) {
						$totalCorrect++;
					} else {
						$totalWrong++;
					}
				}

				$totalTimeSpent += $exam->duration_minutes ?? 0;
			}

			$myarray['result'] = [
				'correct_answers' => $totalCorrect,
				'wrong_answers' => $totalWrong,
				'skipped_questions' => $totalSkipped,
				'total_time_spent_minutes' => $totalTimeSpent,
				'total_exams' => count($exams)
			];
			$myarray['message'] = 'Performance statistics retrieved successfully.';
			$myarray['status'] = 1;
		} else {
			$myarray['result'] = (object)array();
			$myarray['message'] = $common->get_msg("invalid_authentication", $langId)
							? $common->get_msg("invalid_authentication", $langId)
							: 'Invalid Authentication.';
			$myarray['status'] = 0;
		}

		return response()->json($myarray);
	}
}