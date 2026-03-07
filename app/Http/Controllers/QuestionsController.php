<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\Log;
use Laraspace\Http\Requests\QuestionsRequest;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;
use Laraspace\TopicQueRel;
use Laraspace\QuestionCommnent;
use Laraspace\Imports\QuestionsImport;
use Laraspace\QuestionOption;
use Laracasts\Flash\Flash;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Laraspace\LevelManagement;
use Laraspace\LessionManagement;
use Laraspace\Questions;
use Laraspace\Subject;
use Laraspace\Topics;
use Image;
use Auth;
use URL;
use Laraspace\Helpers\Helper;

class QuestionsController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
   		//$questions = Questions::orderBy('questionId', 'DESC')->get();
   		
   		$topics = Topics::select('topics.id','topics.topicName','subject.subjectName')->join('subject', 'topics.subjectId', '=', 'subject.id')->where('topics.isActive','1')->get();	
   		$categories = LevelManagement::where('isActive',1)->get();
       	return view('admin.questions.index',compact('topics','categories'));
	}
	public function getSubjects($category_id) {
	    $subjects = Subject::where('categoryId', $category_id)->where('isActive','1')->get();
	    return response()->json($subjects);
	}

	public function getTopics($subject_id) {
	    $topics = Topics::where('subjectId', $subject_id)->where('isActive','1')->get();
	    return response()->json($topics);
	}

	public function searchQuestions(Request $request) {
	    $questions = Question::where('topic_id', $request->topic_id)->get();
	    return response()->json($questions);
	}
	public function getQuestionsData(Request $request)
	{
		 $questions = DB::table('tblquestion')
		        ->join('topicQueRel', 'tblquestion.questionId', '=', 'topicQueRel.questionId')
		        ->join('topics', 'topicQueRel.topicId', '=', 'topics.id')
		        ->select('tblquestion.*');

		    if (!empty($request->topic)) {
		        $questions->where('topicQueRel.topicId', $request->topic);
		    }

    	return DataTables::of($questions)
        ->addColumn('action', function($question){
        	$button = '';
          	if(checkPermission(Auth::user()->id,'update',47)){
          		$button .= '<a href="'. URL::to('admin/questions/edit/'.$question->questionId.'') .'" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i>Edit</a>';
          		if($question->isActive==1){
					$button .= ' <a href="'.URL::to('admin/questions/status/0/'.$question->questionId.'') .'" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-lock"></i>Inactive</a>';
			    } else {
					$button .= ' <a href="'.URL::to('admin/questions/status/1/'.$question->questionId.'') .'" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-unlock-alt"></i>Active</a>';	 
			   }
          	}
          	if(checkPermission(Auth::user()->id,'delete',47)){
          		$button .= ' <a onclick="check_delete('.$question->questionId.');" class="btn btn-default btn-sm" data-token="'.csrf_token().'" data-delete data-confirmation="notie"> <i class="icon-fa icon-fa-trash"></i> Delete</a>';

          	}
          	$button .=' <a href="'.URL::to('admin/questions/comments/'.$question->questionId.'').'" class="btn btn-default btn-sm"><i class="icon-fa icon-fa-search"></i> Comments</a>';
          	return $button;
        })
        ->addColumn('select', function($question){
            return '<input type="checkbox" class="uniquechk" name="del[]" value="'.$question->questionId.'">';
        })
        ->editColumn('isActive', function($question){
            return $question->isActive ? 'Active' : 'Inactive';
        })
        ->rawColumns(['action', 'select'])
        ->make(true);
	}
	public function add() {
	   $common=new CommanController;
	   $rank=$common->getQuestionMaxRank();
	   $cat = LevelManagement::where('isActive',1)->get();
	   $topics = Topics::select('topics.id','topics.topicName','subject.subjectName')->join('subject', 'topics.subjectId', '=', 'subject.id')->where('topics.isActive','1')->get();	
	   return view('admin.questions.addedit',compact('rank','topics','cat'));
	}

   	/*public function postCreate(QuestionsRequest $request) {
		
		try{
			
			$questionType=isset($request->questionType)?($request->questionType):0;
			$question=isset($request->question)?($request->question):"";
			$status=isset($request->status)?($request->status):0;
	    	$fillBlankWord=isset($request->fillBlankWord)?($request->fillBlankWord):"";	
	    	$topicId=isset($request->topicId)?($request->topicId):"";	
	    	$description=isset($request->description)?($request->description):"";	
	      $checkduplicate = DB::table('tblquestion')->where([['question', '=',$question],['questionType', '=',$questionType]])->count();
	      $categoryId=isset($request->categoryId)?($request->categoryId):0;
	      $subjectId=isset($request->subjectId)?($request->subjectId):0;
	        $isNotValidimage=0;
	        $isNotValidaudio=0;
	        
			if ($checkduplicate==0) {
				$questions = new Questions();
				if ($request->hasFile("topicImage")) {
					        $file = $request->file("topicImage");
					        if ($file->isValid()) {
					            $filename = time() . '_' . $file->getClientOriginalName();
					            $destinationPath = 'topicImages';
					            $file->move(public_path($destinationPath), $filename);
					        }
					    } else {
					        $filename = ''; 
					    }
						
						$topicImage = '';
						$questions->video = $filename;
				$questions->question = $question;
	         
	         $questions->description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
			 	$questions->isActive=$status;
			 	$questions->questionType=$questionType;
			 	$questions->fillBlankWord=$fillBlankWord;
				$questions->createdDate=date('Y-m-d H:i:s');
				$questions->questionHeader=($request->questionHeader) ? $request->questionHeader : "";
				$questions->listType=($request->listType) ? $request->listType : 1;
				$questions->save();
				
				$questionId=$questions->questionId;

				
				if ($questionType==1) {
				 	$questionAsk=isset($request->questionImageText)?($request->questionImageText):0;


					foreach ($questionAsk as $key => $value) {
						$questionImageText=isset($request->questionImageText[$key])?($request->questionImageText[$key]):"";
						$isCorrectAnswer=isset($request->isCorrectAnswer[$key])?($request->isCorrectAnswer[$key]):0;
						if ($request->hasFile("optionImage.$key")) {
					        $file = $request->file("optionImage.$key");
					        if ($file->isValid()) {
					            $filename = time() . '_' . $file->getClientOriginalName();
					            $destinationPath = 'optionImages';
					            $file->move(public_path($destinationPath), $filename);
					        }
					    } else {
					        $filename = ''; 
					    }
						
						$optionImage = '';
						$imageData = new QuestionOption();
						$imageData->questionId = $questionId;
						$imageData->questionImageText = $questionImageText;
						$imageData->questionImage = $filename;
						$imageData->isCorrectAnswer = $isCorrectAnswer;
						$imageData->save();

					}
				 }
                for($i=0;$i<count($topicId);$i++){
                	$subquerel = new TopicQueRel();
					$subquerel->topicId = $topicId[$i];
		         	$subquerel->questionId = $questionId;
		         	$subquerel->creation_time = date('Y-m-d-His');
		         	$subquerel->save();
                }

				flash()->success('Question has  added successfully.');
				return redirect()->to('/admin/questions');
			} else {
				flash()->error('This Question name has been already taken. Please try with another name.');
				return redirect()->to('/admin/questions/add');
			}
		} catch (Exception $e) {
        	echo $e->getMessage();
		}

   	}*/
   	/*updated on 20-06-2025 for the duplicate title issue resolve*/
   	public function postCreate(QuestionsRequest $request) {
		try{
			$questionType=isset($request->questionType)?($request->questionType):0;
			$question=isset($request->question)?($request->question):"";
			$status=isset($request->status)?($request->status):0;
	    	$fillBlankWord=isset($request->fillBlankWord)?($request->fillBlankWord):"";	
	    	$topicId=isset($request->topicId)?($request->topicId):"";	
	    	$description=isset($request->description)?($request->description):"";
	    	sort($topicId);
	      	$questions = DB::table('tblquestion')
			    ->where('question', $question)
			    ->where('questionType', $questionType)
			    ->get();
			$isDuplicate = 0;
			foreach ($questions as $q) {
			    $existingTopics = DB::table('topicQueRel')
			        ->where('questionId', $q->questionId)
			        ->pluck('topicId')
			        ->toArray();
			        sort($existingTopics); // Sort for comparison

				    $existingTopics = array_map('intval', $existingTopics);
					$topicId = array_map('intval', $topicId);

					sort($existingTopics);
					sort($topicId);
		     

			    if ($existingTopics === $topicId) {
			        $isDuplicate = 1;
			        break;
			    }
			}
	      	$categoryId=isset($request->categoryId)?($request->categoryId):0;
	      	$subjectId=isset($request->subjectId)?($request->subjectId):0;
	        $isNotValidimage=0;
	        $isNotValidaudio=0;	        
			if ($isDuplicate == 0) {
				$questions = new Questions();
				if ($request->hasFile("topicImage")) {
					        $file = $request->file("topicImage");
					        if ($file->isValid()) {
					            $filename = time() . '_' . $file->getClientOriginalName();
					            $destinationPath = 'topicImages';
					            $file->move(public_path($destinationPath), $filename);
					        }
					    } else {
					        $filename = '';
					    }
						$topicImage = '';
						$questions->video = $filename;
						$questions->question = $question;
	         
			         	$questions->description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
					 	$questions->isActive=$status;
					 	$questions->questionType=$questionType;
					 	$questions->fillBlankWord=$fillBlankWord;
						$questions->createdDate=date('Y-m-d H:i:s');
						$questions->questionHeader=($request->questionHeader) ? $request->questionHeader : "";
						$questions->listType=($request->listType) ? $request->listType : 1;
						$questions->save();
						
						$questionId=$questions->questionId;
				if ($questionType==1) {
				 	$questionAsk=isset($request->questionImageText)?($request->questionImageText):0;
					foreach ($questionAsk as $key => $value) {
						$questionImageText=isset($request->questionImageText[$key])?($request->questionImageText[$key]):"";
						$isCorrectAnswer=isset($request->isCorrectAnswer[$key])?($request->isCorrectAnswer[$key]):0;
						if ($request->hasFile("optionImage.$key")) {
					        $file = $request->file("optionImage.$key");
					        if ($file->isValid()) {
					            $filename = time() . '_' . $file->getClientOriginalName();
					            $destinationPath = 'optionImages';
					            $file->move(public_path($destinationPath), $filename);
					        }
					    } else {
					        $filename = ''; // No image uploaded for this option
					    }
						$optionImage = '';
						$imageData = new QuestionOption();
						$imageData->questionId = $questionId;
						$imageData->questionImageText = $questionImageText;
						$imageData->questionImage = $filename;
						$imageData->isCorrectAnswer = $isCorrectAnswer;
						$imageData->save();
					}
				 }
                for($i=0;$i<count($topicId);$i++){
                	$subquerel = new TopicQueRel();
					$subquerel->topicId = $topicId[$i];
		         	$subquerel->questionId = $questionId;
		         	$subquerel->creation_time = date('Y-m-d-His');
		         	$subquerel->save();
                }
				flash()->success('Question has  added successfully.');
				return redirect()->to('/admin/questions');
			} else {
				flash()->error('This Question name has been already taken. Please try with another name.');
				return redirect()->to('/admin/questions/add');
			}
		} catch (Exception $e) {
        	echo $e->getMessage();
		}

   	}
	   
   	public function Status($status,$id) {
      
	  $questions = Questions::find($id);
	  $questions->isActive=$status;
	  $questions->save();
	  flash()->success('Question status has updated successfully.');
	  return redirect()->to('/admin/questions');
	  
   	}
   	/*Note: Question Related Delete All table*/
   	public function Delete(Request $request,$id) {
   		$questions = Questions::find($id);
        if ($questions) {
        	if(!empty($request->topic)){
	        	$relatedQuestionTopics = DB::table('topicQueRel')->where('questionId', $id)->pluck('topicId')->toArray();
	        	// Check if the question is related to the selected topic
	            if (count($relatedQuestionTopics) != 1) {
	               DB::table('topicQueRel')->where('questionId', $id)->where('topicId',$request->topic)->delete();
	            } else {
	            	$relatedQuestionTopics = DB::table('topicQueRel')->where('questionId', $id)->delete();
			    	$relatedQuestionOption = DB::table('tblquestionoption')->where('questionId', $id)->delete();
			    	$relatedQuestionComment = DB::table('question_commnent')->where('questionId', $id)->delete();
		            $questions->delete();
	            }
        	} else {
		    	$relatedQuestionTopics = DB::table('topicQueRel')->where('questionId', $id)->delete();
		    	$relatedQuestionOption = DB::table('tblquestionoption')->where('questionId', $id)->delete();
		    	$relatedQuestionComment = DB::table('question_commnent')->where('questionId', $id)->delete();
	            $questions->delete();

        	}
            return response()->json(['status' => 'success', 'message' => 'Question deleted successfully.']);
        }
        return response()->json(['status' => 'error', 'message' => 'Question not found.']);
	}
   
   	public function Deleteall(Request $request) {
   		
   		if(count($request->del) != 0){
			foreach ($request->del as $val) {
				$question = Questions::find($val);
				if (empty($request->selecttopic)) {
                    DB::table('topicQueRel')->where('questionId', $val)->delete();
				    DB::table('tblquestionoption')->where('questionId', $val)->delete();
				    DB::table('question_commnent')->where('questionId', $val)->delete();
				    $question->delete();
                } else {
                	$relatedQuestionTopics = DB::table('topicQueRel')->where('questionId', $val)->pluck('topicId')->toArray();
                	// Check if the question is related to the selected topic
                    if (count($relatedQuestionTopics) != 1) {
                       DB::table('topicQueRel')->where('questionId', $val)->where('topicId',$request->selecttopic)->delete();
                    } else {
                    	DB::table('topicQueRel')->where('questionId', $val)->delete();
					    DB::table('tblquestionoption')->where('questionId', $val)->delete();
					    DB::table('question_commnent')->where('questionId', $val)->delete();
					    $question->delete();
                    }
                }
			}
			return response()->json(['status' => 'success', 'message' => 'Question deleted successfully.']);
		}	
		return response()->json(['status' => 'error', 'message' => 'Question not found.']);	 
   	}
   	/*End*/

	public function getEdit($id) {
   	 	$common=new CommanController;
   	 	$questions = Questions::findOrFail($id);
   	 	$categories = LevelManagement::where('isActive',1)->orderby('sortOrder','asc')->get();	
   	 	$subjects = Subject::where('categoryId',$questions->categoryId)->get(["subjectName", "id"]);
   	 	
   	 	$questionimage = QuestionOption::where([['questionId', '=',$id]])->get();
   	 	
   	 	$topics = Topics::select('topics.id','topics.topicName','subject.subjectName')->join('subject', 'topics.subjectId', '=', 'subject.id')->where('topics.isActive','1')->get();	
   	 	$queTopic = TopicQueRel::where('questionId',$id)->pluck('topicId')->toArray();
   	 	$cat = LevelManagement::where('isActive',1)->get();
   	 	return view('admin.questions.addedit',compact('questions','questionimage','categories','subjects','topics','queTopic','cat'));
   	}

   	public function deleteQuestionLogo($id) {
	   	$delete=DB::delete("update tblquestionoption SET questionImage='' where id=$id");
	   	echo 1;
	   	exit();
   	}

   	public function deleteTopicLogo($id) {
	   	$delete=DB::delete("update tblquestion SET video='' where questionId=$id");
	   	echo 1;
	   	exit();
   	}

   	public function deleteAudioSelect($id) {
   		$delete=DB::delete("update tblquestionoption SET questionAudio='' where id=$id");
   		echo 1;
   		exit();
   	}

   	public function deleteQuestion($id) {
   		$delete=DB::delete("delete from tblquestionoption  where id=$id");
   		echo 1;
   		exit();
   	}

   	public function deleteQuestionWordOne($id) {
   		$delete=DB::delete("delete from tblquestionwordone  where id=$id");
   		echo 1;
   		exit();
   	}

   	public function deleteQuestionWordTwo($id) {
   		$delete=DB::delete("delete from tblquestionwordtwo  where id=$id");
   		echo 1;
   		exit();
   	}

    public function deleteQuestionVocal($id) {
   		$delete=DB::delete("update tblquestion SET uploadVocals=''  where questionId=$id");
   		echo 1;
   		exit();
   	}

   	public function deleteQuestionVideo($id) {
   		$delete=DB::delete("update tblquestion SET video=''  where questionId=$id");
   		echo 1;
   		exit();
   	}

   	public function deleteQuestionAudio($id) {
   		$delete=DB::delete("update tblquestion SET audio=''  where questionId=$id");
   		echo 1;
   		exit();
   	}

   	public function deleteOptionFillBlank($id) {
   		$delete=DB::delete("delete from tblquestionfillblank  where id=$id");
   		echo 1;
   		exit();
   	}

   	public function deleteremoveOptionAl($id) {
   		$delete=DB::delete("delete from tblquestionalphabetsnumbers  where id=$id");
   		echo 1;
   		exit();
   	}

   	public function deleteQuestionPronounciationFile($id) {
	   	$delete=DB::delete("update tblquestionalphabetsnumbers SET pronounciationFile='' where id=$id");
	   	echo 1;
	   	exit();
   	}

   	public function deleteQuestionImageFile($id) {
   	$delete=DB::delete("update tblquestionalphabetsnumbers SET optionImages='' where id=$id");
   	echo 1;
   	exit();
   	}
   
   	public function deleteremoveOptionWordMatch($id) {
   	$delete=DB::delete("delete from tblquestionmatchfollowing  where id=$id");
   	echo 1;
   	exit();
   	}
	    
   
   	/*public function postEdit(QuestionsRequest $request,$id) {
   		
        $lessionId=isset($request->lessionId)?($request->lessionId):0;
		$questionType=isset($request->questionType)?($request->questionType):0;

		$question=isset($request->question)?($request->question):"";
		$description =isset($request->description)?($request->description):"";
		$sortOrder=isset($request->sortOrder)?($request->sortOrder):0;
		$status=isset($request->status)?($request->status):0;
      	$topicId=isset($request->topicId)?($request->topicId):"";	
        $lession = LessionManagement::where('lessionId',$lessionId)->first();

        $lessionName=isset($lession->lessionName)?($lession->lessionName):"";	
        $fillBlankWord=isset($request->fillBlankWord)?($request->fillBlankWord):"";	

		

		$checkduplicate = DB::table('tblquestion')
		       ->join('topicQueRel', 'tblquestion.questionId', '=', 'topicQueRel.questionId')
		        ->join('topics', 'topicQueRel.topicId', '=', 'topics.id')
		        ->where([['tblquestion.question', '=',$question],['tblquestion.questionId', '!=',$id],['tblquestion.questionType', '=',$questionType]])->count();

		  
		
		$categoryId=isset($request->categoryId)?($request->categoryId):0;
      	$subjectId=isset($request->subjectId)?($request->subjectId):0;

   	   	$isNotValidimage=0;
   	   	$isNotValidaudio=0;
   	  
      	if ($checkduplicate==0) {
      		$questions = Questions::find($id);
      			$filename = $questions->video;
				if ($request->hasFile("topicImage")) {
							$temporaryPath = $request->file('topicImage')->store('temp');
							session()->flash('topicImage', $temporaryPath);
					        $file = $request->file("topicImage");
					        if ($file->isValid()) {
					            $filename = time() . '_' . $file->getClientOriginalName();
					            $destinationPath = 'topicImages';
					            $file->move(public_path($destinationPath), $filename);
					        }
					    }
						
						$questions->video = $filename;
				$questions->question = $question;
            $questions->lessionId = $lessionId;
            $questions->lessionName = $lessionName;
            $questions->question = $question;
            
            $questions->description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
           
				$questions->isActive=$status;
				$questions->questionType=$questionType;
				$questions->sortOrder=$sortOrder;
				$questions->fillBlankWord=$fillBlankWord;

			$questions->createdDate=date('Y-m-d H:i:s');
			$questions->questionHeader=($request->questionHeader) ? $request->questionHeader : "";
			$questions->listType=($request->listType) ? $request->listType : 1;
			$questions->save();

			 
			if ($questionType==1) {
				if (isset($request->questionImageText)) {
					$quetext=$request->questionImageText;
					
                    foreach($quetext as $keys => $vals) {
						$isCorrectAnswer=isset($request->isCorrectAnswer[$keys])?($request->isCorrectAnswer[$keys]):0;
						$checkrecord = QuestionOption::where([['id', '=',$keys]])->count();
						

		                if ($checkrecord==0) {
		                	
		                	$imageData = new QuestionOption();
							$imageData->questionId = $id;
							$imageData->questionImageText = $vals;
							
							$imageData->isCorrectAnswer = $isCorrectAnswer;
							$imageData->save();
                            
						} else {
							
	                        $delete=DB::delete("update tblquestionoption SET questionId=$id,questionImageText='".$vals."',isCorrectAnswer=".$isCorrectAnswer." where id=$keys");
						}

					}

					if($request->hasFile('optionImage')) {
					   $files = $request->file('optionImage');
					    $i=0; 
					    foreach($files as $key => $file) {
						 $filename = time().$file->getClientOriginalName();
                         $extension = $file->getClientOriginalExtension();
							   $destinationPath = 'optionImages';
	                           $file->move(public_path($destinationPath),$filename);
		                       
                            $checkrecord = DB::table('tblquestionoption')->where([['id', '=',$key]])->count();
                            
                            if ($checkrecord==0) {
                            
                                $ImageData = DB::table('tblquestionoption')
					               ->where('questionImageText', $vals)
					               ->update(['questionImage' => $filename]);


                            } else {
                            	
                            	$delete=DB::delete("update tblquestionoption SET questionId=$id,questionImage='".$filename."' where id=$key");

                            } 

                          $i++;

					    }

                    }
				}

                }
                

			$queTopic = TopicQueRel::where('questionId',$id)->get();
			if(count($queTopic)>0){
				$deleterel = DB::table('topicQueRel')->where('questionId', $id)->delete();
			}

			for($i=0;$i<count($topicId);$i++){
            	$subquerel = new TopicQueRel();
				$subquerel->topicId = $topicId[$i];
	         	$subquerel->questionId = $id;
	         	$subquerel->creation_time = date('Y-m-d-His');
	         	$subquerel->save();
            } 
			
			flash()->success('Question has updated successfully.');
		  	return redirect()->to('/admin/questions');
		
		} else {
			flash()->error('This Question name has been already taken. Please try with another name.');
		    return redirect()->to('/admin/questions/edit/'.$id);
		
		} 
   	}*/

   	/*updated on 20-06-2025 for the duplicate title issue resolve*/
   	public function postEdit(QuestionsRequest $request,$id) {
        $lessionId=isset($request->lessionId)?($request->lessionId):0;
		$questionType=isset($request->questionType)?($request->questionType):0;
		$question=isset($request->question)?($request->question):"";
		$description =isset($request->description)?($request->description):"";
		$sortOrder=isset($request->sortOrder)?($request->sortOrder):0;
		$status=isset($request->status)?($request->status):0;
      	//$topicId=isset($request->topicId)?($request->topicId):"";	
      	$topicId = $request->topicId ?? [];
      	$newTopics = array_map('intval', $topicId);
      	sort($newTopics);
        $lession = LessionManagement::where('lessionId',$lessionId)->first();
        $lessionName=isset($lession->lessionName)?($lession->lessionName):"";	
        $fillBlankWord=isset($request->fillBlankWord)?($request->fillBlankWord):"";	
		$similarQuestions = DB::table('tblquestion')
		    ->where('question', $question)
		    ->where('questionType', $questionType)
		    ->where('questionId', '!=', $id)
		    ->get();
		$checkduplicate = 0;
		foreach ($similarQuestions as $q) {
	    	$existingTopics = DB::table('topicQueRel')
	        ->where('questionId', $q->questionId)
	        ->pluck('topicId')
	        ->map(fn($t) => intval($t)) // Cast to int
	        ->toArray();
	    	sort($existingTopics);
	    	if ($existingTopics === $newTopics) {
	        	$checkduplicate = 1;
	        	break;
	    	}
		}		
		$categoryId=isset($request->categoryId)?($request->categoryId):0;
      	$subjectId=isset($request->subjectId)?($request->subjectId):0;
   	   	$isNotValidimage=0;
   	   	$isNotValidaudio=0;   	  
      	if ($checkduplicate == 0) {
      		$questions = Questions::find($id);
      			$filename = $questions->video;
				if ($request->hasFile("topicImage")) {
							$temporaryPath = $request->file('topicImage')->store('temp');
							session()->flash('topicImage', $temporaryPath);
					        $file = $request->file("topicImage");
					        if ($file->isValid()) {
					            $filename = time() . '_' . $file->getClientOriginalName();
					            $destinationPath = 'topicImages';
					            $file->move(public_path($destinationPath), $filename);
					        }
					    }
						$questions->video = $filename;
						$questions->question = $question;
						$questions->lessionId = $lessionId;
						$questions->lessionName = $lessionName;
						$questions->question = $question;
						$questions->description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
						$questions->isActive=$status;
						$questions->questionType=$questionType;
						$questions->sortOrder=$sortOrder;
						$questions->fillBlankWord=$fillBlankWord;
						$questions->createdDate=date('Y-m-d H:i:s');
						$questions->questionHeader=($request->questionHeader) ? $request->questionHeader : "";
						$questions->listType=($request->listType) ? $request->listType : 1;
						$questions->save();			 
			if ($questionType==1) {
				if (isset($request->questionImageText)) {
					$quetext=$request->questionImageText;
                    foreach($quetext as $keys => $vals) {
						$isCorrectAnswer=isset($request->isCorrectAnswer[$keys])?($request->isCorrectAnswer[$keys]):0;
						$checkrecord = QuestionOption::where([['id', '=',$keys]])->count();
		                if ($checkrecord==0) {
		                	$imageData = new QuestionOption();
							$imageData->questionId = $id;
							$imageData->questionImageText = $vals;
							$imageData->isCorrectAnswer = $isCorrectAnswer;
							$imageData->save();
						} else {
	                        $delete=DB::delete("update tblquestionoption SET questionId=$id,questionImageText='".$vals."',isCorrectAnswer=".$isCorrectAnswer." where id=$keys");
						}
					}
					if($request->hasFile('optionImage')) {
					   $files = $request->file('optionImage');
					    $i=0; 
					    foreach($files as $key => $file) {
						 $filename = time().$file->getClientOriginalName();
                         $extension = $file->getClientOriginalExtension();
							   $destinationPath = 'optionImages';
	                           $file->move(public_path($destinationPath),$filename);
	                           $checkrecord = DB::table('tblquestionoption')->where([['id', '=',$key]])->count();                            
                            if ($checkrecord==0) {
                                $ImageData = DB::table('tblquestionoption')
					               ->where('questionImageText', $vals)
					               ->update(['questionImage' => $filename]);
                            } else {
                            	$delete=DB::delete("update tblquestionoption SET questionId=$id,questionImage='".$filename."' where id=$key");
                            } 
                          $i++;
					    }
                    }
				}
			}
			$queTopic = TopicQueRel::where('questionId',$id)->get();
			if(count($queTopic)>0){
				$deleterel = DB::table('topicQueRel')->where('questionId', $id)->delete();
			}
			for($i=0;$i<count($topicId);$i++){
            	$subquerel = new TopicQueRel();
				$subquerel->topicId = $topicId[$i];
	         	$subquerel->questionId = $id;
	         	$subquerel->creation_time = date('Y-m-d-His');
	         	$subquerel->save();
            }
            flash()->success('Question has updated successfully.');
		  	return redirect()->to('/admin/questions');		
		} else {
			flash()->error('This Question name has been already taken. Please try with another name.');
		    return redirect()->to('/admin/questions/edit/'.$id);		
		} 
   	}

	public function getSubject($catId){
		$subjects = Subject::where('categoryId',$catId)->get(["subjectName", "id"]);
		return response()->json($subjects);
	}

	public function getTopic(Request $request){
		 $term = $request->input('term');
		//$topics = Topics::where('subjectId',$subId)->get(["topicName", "id"]);

		$topics = Topics::select('topics.id','topics.topicName','subject.subjectName','tbllevelmanagement.levelName')
										->join('subject', 'topics.subjectId', '=', 'subject.id')
										->join('tbllevelmanagement', 'subject.categoryId', '=', 'tbllevelmanagement.levelId')
										->where('topics.isActive','1')
										->where('topicName', 'LIKE', '%' . $term . '%')
										->get();
	   $data = [];
	    foreach ($topics as $topic) {
	        $data[] = [
	            'id' => $topic->id,
	            'text' => $topic->topicName .' - '. $topic->subjectName .' - '. $topic->levelName ,
	        ];
	    }

	    return response()->json($data);
	}
	public function getSelectedTopic($topicId)
    {
    	
    	$topicId = explode(',',$topicId);
        // Fetch selected values from the database or any other source
        $selectedValues = Topics::select('topics.id','topics.topicName','subject.subjectName','tbllevelmanagement.levelName')
										->join('subject', 'topics.subjectId', '=', 'subject.id')
										->join('tbllevelmanagement', 'subject.categoryId', '=', 'tbllevelmanagement.levelId')
										->where('topics.isActive','1')
										->whereIn('topics.id',$topicId)->get(); // Example selected values

        // Format the data in the required format for Select2
        $data = [];

        foreach ($selectedValues as $value) {
            $data[] = [
                'id' => $value->id,
               'text' => $value->topicName .' - '. $value->subjectName .' - '. $value->levelName ,
            ];
        }

        return response()->json($data);
    }
	/*Get Comment in Question Id wise*/
	public function getComments($questionId){
		$comments = QuestionCommnent::query()->with(['customer' => function ($query) {
		    $query->select('id','name','photo');
		}])->orderby('id','DESC')->where('questionId',$questionId)->where('parentId','=','0')->get();
		
		return view('admin.questions.comment',compact('comments'));
	}
	/*Child Comment*/
	public function getChildComments($parentId){
		try {
	        $childComments = QuestionCommnent::query()
	            ->with(['customer' => function ($query) {
	                $query->select('id','name','photo');
	            }])
	            ->orderBy('id','DESC')
	            ->where('parentId', $parentId)
	            ->get();

	        return response()->json($childComments);
	    } catch (\Exception $e) {
	        // Log the error
	        \Log::error('Error retrieving child comments: ' . $e->getMessage());

	        // Return a JSON response with an error message
	        return response()->json(['error' => 'An error occurred while retrieving child comments.'], 500);
	    }
	}
	/*Comment Delete*/
	public function commentsDelete($id) {
		   
      $cdelete = QuestionCommnent::findOrFail($id);
      	if($cdelete->delete()){
           return Response(['status'=>'success','message'=> 'Comment deleted successfully']);  
        } else {
           return Response(['status'=>'error','message'=>  'Something went wrong!']); 
        }
  	}
  	/*Question Import*/
  	 public function importQuestions(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'question_import' => 'required|mimes:xlsx,csv',
        ]);

        // Get the file from the request
        $file = $request->file('question_import');

        try {
            // Read data from the file using Laravel Excel
            Excel::import(new QuestionsImport, $file->store('temp'));
            
            // Flash success message
            session()->flash('success', 'Questions have been imported successfully.');
        } catch (\Exception $e) {
            // Log error or handle as needed
            \Log::error('Error importing questions: ' . $e->getMessage());
            
            // Flash error message
            session()->flash('error', 'Failed to import questions. Please check the file format and try again.');
        }

        // Redirect back to the questions page
        return redirect()->away('http://medfellowsadminpanel.loc/admin/questions');
    }
  	
}
