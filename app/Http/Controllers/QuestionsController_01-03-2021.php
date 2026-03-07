<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\QuestionsRequest;
use Illuminate\Http\Request;
use Laraspace\LevelManagement;
use Laraspace\LessionManagement;
use Laraspace\Questions;
use Image;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Laraspace\Language;


class QuestionsController extends Controller
{

	public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
	   
	   $questions = Questions::all();
       return view('admin.questions.index',compact('questions'));
	}
	
	public function add() {
	   $common=new CommanController;
	   $rank=$common->getQuestionMaxRank();
	   $lessionmanagement = LessionManagement::where('isActive',1)->orderby('sortOrder','asc')->get();	
	   return view('admin.questions.addedit',compact('rank','lessionmanagement'));
	}

	
	 public function postCreate(QuestionsRequest $request) {
		 
		 
		 
		 $lessionId=isset($request->lessionId)?($request->lessionId):0;
		 $questionType=isset($request->questionType)?($request->questionType):0;

		 $question=isset($request->question)?($request->question):"";
		 $sortOrder=isset($request->sortOrder)?($request->sortOrder):0;
		 $status=isset($request->status)?($request->status):0;
          
         $lession = LessionManagement::where('lessionId',$lessionId)->first();

         $lessionName=isset($lession->lessionName)?($lession->lessionName):"";

         $fillBlankWord=isset($request->fillBlankWord)?($request->fillBlankWord):"";	

         $checkduplicate = DB::table('tblquestion')->where([['question', '=',$question],['questionType', '=',$questionType]])->count();

         $isNotValidimage=0;
        
		 /*$questionType=isset($request->questionType)?($request->questionType):0;
		 $questionType=isset($request->questionType)?($request->questionType):0;*/
		

		 if ($checkduplicate==0) {

			 $questions = new Questions();
			 
			 
             
             $questions->lessionId = $lessionId;
             $questions->lessionName = $lessionName;
             $questions->question = $question;
			 $questions->isActive=$status;
			 $questions->questionType=$questionType;
			 $questions->sortOrder=$sortOrder;
			 $questions->fillBlankWord=$fillBlankWord;

			 if($request->hasFile('uploadVocals')) {

				 	$file = $request->file('uploadVocals');
				 	$extension = $file->getClientOriginalExtension();
				 	$filename = time().$file->getClientOriginalName();
				 	$destinationPath = 'questionVocals';
	                $file->move($destinationPath,$filename);
	                $questions->uploadVocals=$filename;
				 
			 }

			 /*if($request->hasFile('video')) {

				 	$file = $request->file('video');
				 	$extension = $file->getClientOriginalExtension();
				 	$filename = time().$file->getClientOriginalName();
				 	$destinationPath = 'questionVideos';
	                $file->move($destinationPath,$filename);
	                $questions->video=$filename;
				 
			 }*/

			 if($request->hasFile('video')) {

				 $allowedfileExtension=['mp4','mov','wmv','avi'];
				 $file = $request->file('video');
				 //$filename = time().$file->getClientOriginalName();
				 
				 $extension = strtolower($file->getClientOriginalExtension());

				 $filename=time().".".$extension;
				 
				 $check=in_array($extension,$allowedfileExtension);
				 
				 if($check) {
				   $destinationPath = 'questionVideos';
				   $file->move($destinationPath,$filename);
				   $questions->video=$filename;						   
				 } else {
				 	$isNotValidimage=1;
				 }

			 }

			 if ($isNotValidimage==1) {
                   flash()->error('Please upload valid Question Video File.');
			       return redirect()->to('/admin/questions/add');
			 }

			 $questions->createdDate=date('Y-m-d H:i:s');
			 
			 $questions->save();

			 $questionId=$questions->questionId;

			 if ($questionType==1) {

			 	 
		         
		          if($request->hasFile('questionImage')) {
					   
					  // $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

					   $files = $request->file('questionImage');

					    $i=0; 

					    foreach($files as $key => $file) {

						 $filename = time().$file->getClientOriginalName();
                         $extension = $file->getClientOriginalExtension();
                         //$check=in_array($extension,$allowedfileExtension);
						 
							// if($check) {

							   $destinationPath = 'questionImage';
	                           $file->move($destinationPath,$filename);
                               
                               $questionImageText=isset($request->questionImageText[$key])?($request->questionImageText[$key]):"";
		                       $isCorrectAnswer=isset($request->isCorrectAnswer[$key])?($request->isCorrectAnswer[$key]):0;

		                       $ImageData=DB::table('tblquestionimage')->insert(
                               ['questionId'=>$questionId,'questionImage'=>$filename,'questionImageText'=>$questionImageText,'isCorrectAnswer'=>$isCorrectAnswer]);

							   /*$vendorproof=new VendorProof;
							   $vendorproof->venderId=$vendor->id;
							   $vendorproof->photo=$filename;
							   $vendorproof->createdDate=date('Y-m-d H:i:s');
							   $vendorproof->save();*/

							 
							 //}

                          $i++;

					    }

                    }

			 }

			 if ($questionType==2) {

			 	  if (isset($request->wordText)) {

                    	  $quetext=$request->wordText;

                    	  foreach($quetext as $keys => $vals) {

                    	  	  
		                      $wordSequence=isset($request->wordSequence[$keys])?($request->wordSequence[$keys]):0;

                                $ImageData=DB::table('tblquestionwordone')->insert(
                               ['questionId'=>$questionId,'wordText'=>$vals,'wordSequence'=>$wordSequence]);

             

                    	  }
                    }

			 }

			 if ($questionType==3) {

			 	  if (isset($request->wordTextTwo)) {

                    	  $quetext=$request->wordTextTwo;

                    	  foreach($quetext as $keys => $vals) {

                    	  	  
		                      $wordSequence=isset($request->wordSequenceTwo[$keys])?($request->wordSequenceTwo[$keys]):0;

                                $ImageData=DB::table('tblquestionwordtwo')->insert(
                               ['questionId'=>$questionId,'wordText'=>$vals,'wordSequence'=>$wordSequence]);

             

                    	  }
                    }

                   if (isset($request->originaltext)) {

                    	  $originaltext=$request->originaltext;

                    	  foreach($originaltext as $keys => $vals) {

                    	  	  
		                      $heightlight=isset($request->heightlight[$keys])?($request->heightlight[$keys]):0;
		                      $translation=isset($request->translation[$keys])?($request->translation[$keys]):"";

                                $ImageData=DB::table('tblquestionwordtwoadditional')->insert(
                               ['questionId'=>$questionId,'originalText'=>$vals,'isHeightlight'=>$heightlight,'translatedText'=>$translation]);

             

                    	  }
                    } 



			 }

			 if ($questionType==4) {

			 	  if (isset($request->optionName)) {

                    	  $quetext=$request->optionName;

                    	  foreach($quetext as $keys => $vals) {

                    	  	  
		                      //$wordSequence=isset($request->wordSequenceTwo[$keys])?($request->wordSequenceTwo[$keys]):0;

                                $ImageData=DB::table('tblquestionfillblank')->insert(
                               ['questionId'=>$questionId,'optionName'=>$vals]);

             

                    	  }
                    }

			 }

			 if ($questionType==5) {

			 	  if (isset($request->originalWord)) {

                    	  $quetext=$request->originalWord;

                    	  foreach($quetext as $keys => $vals) {

                    	  	  
		                      $wordSequence=isset($request->translateWord[$keys])?($request->translateWord[$keys]):0;

                                $ImageData=DB::table('tblquestionmatchfollowing')->insert(
                               ['questionId'=>$questionId,'originalWord'=>$vals,'translateWord'=>$wordSequence]);

             

                    	  }
                    }

			 }

			 if ($questionType==6) {

			 	   $wordSequenceAlS=isset($request->wordSequenceAl)?($request->wordSequenceAl):0;
		            
		            if ($wordSequenceAlS) {
                        
                        foreach($wordSequenceAlS as $key => $vals) {

                        	   $questionImageText=isset($request->optionNameAl[$key])?($request->optionNameAl[$key]):"";
		                       $isCorrectAnswer=isset($request->wordSequenceAl[$key])?($request->wordSequenceAl[$key]):0;

                               $filename='';
                               $optionImage='';

		                       if ($request->hasFile('pronounciationFile')) {
                                 
	                                 if (isset($request->file('pronounciationFile')[$key])) {

			                       	   $files = $request->file('pronounciationFile')[$key];

			                       	   $filename = time().$files->getClientOriginalName();
	                                   $extension = $files->getClientOriginalExtension();

	                                   $destinationPath = 'pronounciationFile';
		                               $files->move($destinationPath,$filename);

		                             }  

		                       } 

		                       if ($request->hasFile('optionImages')) {
                                 
	                                 if (isset($request->file('optionImages')[$key])) {
	                                       
	                                       $files = $request->file('optionImages')[$key];

			                       	       $optionImage = time().$files->getClientOriginalName();
		                                   $extension = $files->getClientOriginalExtension();

		                                   $destinationPath = 'optionImages';
			                               $files->move($destinationPath,$optionImage);

	                                 }
		                       	   
		                       } 

		                      $ImageData=DB::table('tblquestionalphabetsnumbers')->insert(
                               ['questionId'=>$questionId,'pronounciationFile'=>$filename,'optionName'=>$questionImageText,'wordSequence'=>$isCorrectAnswer,'optionImages'=>$optionImage]);  

                        }

		            }

		          /*if($request->hasFile('pronounciationFile')) {
					   
					  // $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

					   $files = $request->file('pronounciationFile');

					    $i=0; 

					    foreach($files as $key => $file) {

						 $filename = time().$file->getClientOriginalName();
                         $extension = $file->getClientOriginalExtension();
                         //$check=in_array($extension,$allowedfileExtension);
						 
							// if($check) {

							   $destinationPath = 'pronounciationFile';
	                           $file->move($destinationPath,$filename);
                               
                               $questionImageText=isset($request->optionNameAl[$key])?($request->optionNameAl[$key]):"";
		                       $isCorrectAnswer=isset($request->wordSequenceAl[$key])?($request->wordSequenceAl[$key]):0;

		                      $ImageData=DB::table('tblquestionalphabetsnumbers')->insert(
                               ['questionId'=>$questionId,'pronounciationFile'=>$filename,'optionName'=>$questionImageText,'wordSequence'=>$isCorrectAnswer]);

							

							 
							 

                          $i++;

					    }

                    }*/

			 }

			 

			 flash()->success('Question has  added successfully.');
			 return redirect()->to('/admin/questions');

		 } else {

		     flash()->error('This Question name has been already taken. Please try with another name.');
			 return redirect()->to('/admin/questions/add');
		 }
		 //echo $request->name;
		 //exit;
       }
	   
	   public function Status($status,$id) {
	      
		  $questions = Questions::find($id);
		  $questions->isActive=$status;
		  $questions->save();
		  flash()->success('Question status has updated successfully.');
		  return redirect()->to('/admin/questions');
		  
	   }
	   
	   public function Delete($id) {
		   
		      $user = Questions::find($id);
              $user->delete();
			  echo 2;
		  
		   exit();
	   }
	   
	   	public function Deleteall(Request $request) {
			$error='';
			$err=0;
			$section='';
			$section2='';
			$common=new CommanController;

			$delete_question=$common->get_msg("delete_question",1)?$common->get_msg("delete_question",1):"This question has been successfully deleted.";

			if(!empty($request->del)){
				foreach ($request->del as $val) {
					$lesson = Questions::find($val);
					$lesson->delete();
				}
				$msg=$delete_question;
				flash()->success($msg);
				return redirect()->to('/admin/questions');
			}		 
	   	}
	   
	   	public function getEdit($id) {

       	 $common=new CommanController;
       	 $rank=$common->getQuestionMaxRank();
         $questions = Questions::find($id);
         $lessionmanagement = LessionManagement::where('isActive',1)->orderby('sortOrder','asc')->get();
         $questionimage = DB::table('tblquestionimage')->where([['questionId', '=',$id]])->get();
         $questionwordone = DB::table('tblquestionwordone')->where([['questionId', '=',$id]])->get();
         $questionwordtwo = DB::table('tblquestionwordtwo')->where([['questionId', '=',$id]])->get();
         $questionfillblank = DB::table('tblquestionfillblank')->where([['questionId', '=',$id]])->get();
         $pronounciationFile = DB::table('tblquestionalphabetsnumbers')->where([['questionId', '=',$id]])->get();
         $questionwordmatch = DB::table('tblquestionmatchfollowing')->where([['questionId', '=',$id]])->get();
         $additional = DB::table('tblquestionwordtwoadditional')->where([['questionId', '=',$id]])->get();
         
         return view('admin.questions.addedit',compact('questions','rank','lessionmanagement','questionimage','questionwordone','questionwordtwo','questionfillblank','pronounciationFile','questionwordmatch','additional'));

       }

       
       public function deleteQuestionLogo($id) {
	   	$delete=DB::delete("update tblquestionimage SET questionImage='' where id=$id");
	   	echo 1;
	   	exit();
	   }

	   public function deleteQuestion($id) {
	   	$delete=DB::delete("delete from tblquestionimage  where id=$id");
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
	   
	   
       
	   
	   
	   public function postEdit(QuestionsRequest $request,$id) {
            
             $lessionId=isset($request->lessionId)?($request->lessionId):0;
			 $questionType=isset($request->questionType)?($request->questionType):0;

			 $question=isset($request->question)?($request->question):"";
			 $sortOrder=isset($request->sortOrder)?($request->sortOrder):0;
			 $status=isset($request->status)?($request->status):0;
	          
	         $lession = LessionManagement::where('lessionId',$lessionId)->first();

	         $lessionName=isset($lession->lessionName)?($lession->lessionName):"";	
	         $fillBlankWord=isset($request->fillBlankWord)?($request->fillBlankWord):"";	

	         //print_r($request->all());
	         //exit;

         //$checkduplicate = DB::table('tblquestion')->where([['question', '=',$question]])->count();

	   	   $checkduplicate = DB::table('tblquestion')->where([['question', '=',$question],['questionId', '!=',$id],['questionType', '=',$questionType]])->count();

	   	   $isNotValidimage=0;
	   	  
          if ($checkduplicate==0) {

		         $questions = Questions::find($id);
			  
	             $questions->lessionId = $lessionId;
	             $questions->lessionName = $lessionName;
	             $questions->question = $question;
				 $questions->isActive=$status;
				 $questions->questionType=$questionType;
				 $questions->sortOrder=$sortOrder;
				 $questions->fillBlankWord=$fillBlankWord;

				 if($request->hasFile('uploadVocals')) {

				 	$file = $request->file('uploadVocals');
				 	$extension = $file->getClientOriginalExtension();
				 	$filename = time().$file->getClientOriginalName();
				 	$destinationPath = 'questionVocals';
	                $file->move($destinationPath,$filename);
	                $questions->uploadVocals=$filename;
				 
				 }

				if ($request->hasFile('video')) {

					 $allowedfileExtension=['mp4','mov','wmv','avi','webm'];
					 $file = $request->file('video');
					 
					 $extension = strtolower($file->getClientOriginalExtension());

					 $filename=time().".".$extension;
					 
					 $check=in_array($extension,$allowedfileExtension);
					 
					 if($check) {
					   $destinationPath = 'questionVideos';
					   $file->move($destinationPath,$filename);
					   $questions->video=$filename;						   
					 } else {
					 	$isNotValidimage=1;
					 }

			   }

				 if ($isNotValidimage==1) {
	                   flash()->error('Please upload valid Question Video File.');
				       return redirect()->to('/admin/questions/edit/'.$id);
				 }

				 $questions->createdDate=date('Y-m-d H:i:s');
				 
				 $questions->save();

				 if ($questionType==1) {

			 	 
		         
		          if($request->hasFile('questionImage')) {
					   
					  // $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

					   $files = $request->file('questionImage');

					    $i=0; 

					    foreach($files as $key => $file) {

						 $filename = time().$file->getClientOriginalName();
                         $extension = $file->getClientOriginalExtension();
                         //$check=in_array($extension,$allowedfileExtension);
						 
							// if($check) {

							   $destinationPath = 'questionImage';
	                           $file->move($destinationPath,$filename);
                               
                               $questionImageText=isset($request->questionImageText[$key])?($request->questionImageText[$key]):"";
		                       $isCorrectAnswer=isset($request->isCorrectAnswer[$key])?($request->isCorrectAnswer[$key]):0;

		                       
                              $checkrecord = DB::table('tblquestionimage')->where([['id', '=',$key]])->count();
                            
                            if ($checkrecord==0) {
                                
                                $ImageData=DB::table('tblquestionimage')->insert(
                               ['questionId'=>$id,'questionImage'=>$filename,'questionImageText'=>$questionImageText,'isCorrectAnswer'=>$isCorrectAnswer]);


                            } else {
                            	$delete=DB::delete("update tblquestionimage SET questionId=$id,questionImage='".$filename."',questionImageText='".$questionImageText."',isCorrectAnswer=".$isCorrectAnswer." where id=$key");

                            } 

		                       
							 

                          $i++;

					    }

                    }


                    if (isset($request->questionImageText) && !$request->hasFile('questionImage')) {

                    	  $quetext=$request->questionImageText;

                    	  foreach($quetext as $keys => $vals) {

                    	  	  
		                      $isCorrectAnswer=isset($request->isCorrectAnswer[$keys])?($request->isCorrectAnswer[$keys]):0;

		                      $checkrecord = DB::table('tblquestionimage')->where([['id', '=',$keys]])->count();

		                        if ($checkrecord==0) {
                                
                                $ImageData=DB::table('tblquestionimage')->insert(
                               ['questionId'=>$id,'questionImageText'=>$questionImageText,'isCorrectAnswer'=>$isCorrectAnswer]);


	                            } else {
	                            	$delete=DB::delete("update tblquestionimage SET questionId=$id,questionImageText='".$vals."',isCorrectAnswer=".$isCorrectAnswer." where id=$keys");

	                            } 

                    	  }
                    }

                    

			 }

			 if ($questionType==2) {

			 	  if (isset($request->wordText)) {

                    	  $quetext=$request->wordText;

                    	  foreach($quetext as $keys => $vals) {

                    	  	  
		                      $wordSequence=isset($request->wordSequence[$keys])?($request->wordSequence[$keys]):0;

		                      $checkrecord = DB::table('tblquestionwordone')->where([['id', '=',$keys]])->count();

		                        if ($checkrecord==0) {
                                
                                $ImageData=DB::table('tblquestionwordone')->insert(
                               ['questionId'=>$id,'wordText'=>$vals,'wordSequence'=>$wordSequence]);


	                            } else {
	                            	$delete=DB::delete("update tblquestionwordone SET questionId=$id,wordText='".$vals."',wordSequence=".$wordSequence." where id=$keys");

	                            } 

                    	  }
                    }

			 }

			 if ($questionType==3) {

			 	  if (isset($request->wordTextTwo)) {

                    	  $quetext=$request->wordTextTwo;

                    	  foreach($quetext as $keys => $vals) {

                    	  	  
		                      $wordSequence=isset($request->wordSequenceTwo[$keys])?($request->wordSequenceTwo[$keys]):0;

		                      $checkrecord = DB::table('tblquestionwordtwo')->where([['id', '=',$keys]])->count();

		                        if ($checkrecord==0) {
                                
                                $ImageData=DB::table('tblquestionwordtwo')->insert(
                               ['questionId'=>$id,'wordText'=>$vals,'wordSequence'=>$wordSequence]);


	                            } else {
	                            	$delete=DB::delete("update tblquestionwordtwo SET questionId=$id,wordText='".$vals."',wordSequence=".$wordSequence." where id=$keys");

	                            } 

                    	  }
                    }

                    if (isset($request->originaltext)) {

                    	  $originaltext=$request->originaltext;
                           
                           $delete=DB::delete("delete from tblquestionwordtwoadditional where questionId=$id");

                    	  foreach($originaltext as $keys => $vals) {

                    	  	  
		                      $heightlight=isset($request->heightlight[$keys])?($request->heightlight[$keys]):0;
		                      $translation=isset($request->translation[$keys])?($request->translation[$keys]):"";

		                      //$checkrecord = DB::table('tblquestionwordtwoadditional')->where([['id', '=',$keys]])->count();

		                        /*if ($checkrecord==0) {
                                
                                $ImageData=DB::table('tblquestionwordtwoadditional')->insert(
                               ['questionId'=>$id,'originalText'=>$vals,'isHeightlight'=>$heightlight,'translatedText'=>$translation]);


	                            } else {
	                            	$delete=DB::delete("update tblquestionwordtwoadditional SET questionId=$id,originalText='".$vals."',isHeightlight=".$heightlight.",translatedText='".$translation."' where id=$keys");

	                            } */

                                $ImageData=DB::table('tblquestionwordtwoadditional')->insert(
                               ['questionId'=>$id,'originalText'=>$vals,'isHeightlight'=>$heightlight,'translatedText'=>$translation]);

             

                    	  }
                    } 

			 }

			 if ($questionType==4) {

			 	  if (isset($request->optionName)) {

                    	  $quetext=$request->optionName;

                    	  foreach($quetext as $keys => $vals) {

                    	  	  
		                      //$wordSequence=isset($request->wordSequenceTwo[$keys])?($request->wordSequenceTwo[$keys]):0;

		                      $checkrecord = DB::table('tblquestionfillblank')->where([['id', '=',$keys]])->count();

		                        if ($checkrecord==0) {
                                
                                $ImageData=DB::table('tblquestionfillblank')->insert(
                               ['questionId'=>$id,'optionName'=>$vals]);


	                            } else {
	                            	$delete=DB::delete("update tblquestionfillblank SET questionId=$id,optionName='".$vals."' where id=$keys");

	                            } 

                    	  }
                    }

			 }

			 if ($questionType==5) {

			 	  if (isset($request->originalWord)) {

                    	  $quetext=$request->originalWord;

                    	  foreach($quetext as $keys => $vals) {

                    	  	  
		                      $wordSequence=isset($request->translateWord[$keys])?($request->translateWord[$keys]):0;

		                      $checkrecord = DB::table('tblquestionmatchfollowing')->where([['id', '=',$keys]])->count();

		                        if ($checkrecord==0) {
                                
                                $ImageData=DB::table('tblquestionmatchfollowing')->insert(
                               ['questionId'=>$id,'originalWord'=>$vals,'translateWord'=>$wordSequence]);


	                            } else {
	                            	$delete=DB::delete("update tblquestionmatchfollowing SET questionId=$id,originalWord='".$vals."',translateWord='".$wordSequence."' where id=$keys");

	                            } 

                    	  }
                    }

			 }

			 if ($questionType==6) {

			 	 
		         
		          /*if($request->hasFile('pronounciationFile')) {
					   
					  // $allowedfileExtension=['pdf','jpg','png','docx','jpeg','doc','gif'];

					   $files = $request->file('pronounciationFile');

					    $i=0; 

					    foreach($files as $key => $file) {

						 $filename = time().$file->getClientOriginalName();
                         $extension = $file->getClientOriginalExtension();
                         //$check=in_array($extension,$allowedfileExtension);
						 
							// if($check) {

							   $destinationPath = 'pronounciationFile';
	                           $file->move($destinationPath,$filename);
                               
                               $questionImageText=isset($request->optionNameAl[$key])?($request->optionNameAl[$key]):"";
		                       $isCorrectAnswer=isset($request->wordSequenceAl[$key])?($request->wordSequenceAl[$key]):0;

		                       
                              $checkrecord = DB::table('tblquestionalphabetsnumbers')->where([['id', '=',$key]])->count();
                            
                            if ($checkrecord==0) {
                                
                                $ImageData=DB::table('tblquestionalphabetsnumbers')->insert(
                               ['questionId'=>$id,'pronounciationFile'=>$filename,'optionName'=>$questionImageText,'wordSequence'=>$isCorrectAnswer]);


                            } else {
                            	$delete=DB::delete("update tblquestionalphabetsnumbers SET questionId=$id,pronounciationFile='".$filename."',optionName='".$questionImageText."',wordSequence=".$isCorrectAnswer." where id=$key");

                            } 

		                       
							 

                          $i++;

					    }

                    }*/

                    $wordSequenceAlS=isset($request->wordSequenceAl)?($request->wordSequenceAl):0;

                    if ($wordSequenceAlS) {

                    	  //$quetext=$request->optionNameAl;

                    	  foreach($wordSequenceAlS as $key => $vals) {
                               
                               $filename='';
                               $optionImage='';

		                       if ($request->hasFile('pronounciationFile')) {
                                 
	                                 if (isset($request->file('pronounciationFile')[$key])) {

			                       	   $files = $request->file('pronounciationFile')[$key];

			                       	   $filename = time().$files->getClientOriginalName();
	                                   $extension = $files->getClientOriginalExtension();

	                                   $destinationPath = 'pronounciationFile';
		                               $files->move($destinationPath,$filename);
                                       
		                             }  

		                       } 

		                       if ($request->hasFile('optionImages')) {
                                 
	                                 if (isset($request->file('optionImages')[$key])) {
	                                       
	                                       $files = $request->file('optionImages')[$key];

			                       	       $optionImage = time().$files->getClientOriginalName();
		                                   $extension = $files->getClientOriginalExtension();

		                                   $destinationPath = 'optionImages';
			                               $files->move($destinationPath,$optionImage);



	                                 }
		                       	   
		                       }
                    	  	  
		                      
								$questionImageText=isset($request->optionNameAl[$key])?($request->optionNameAl[$key]):"";
								$isCorrectAnswer=isset($request->wordSequenceAl[$key])?($request->wordSequenceAl[$key]):0;
                                 

		                        $checkrecord = DB::table('tblquestionalphabetsnumbers')->where([['id', '=',$key]])->count();

		                        if ($checkrecord==0) {
                                
	                               if ($filename!='' && $optionImage!='') {
                                       
                                       $ImageData=DB::table('tblquestionalphabetsnumbers')->insert(['questionId'=>$id,'optionName'=>$questionImageText,'wordSequence'=>$isCorrectAnswer,'pronounciationFile'=>$filename,'optionImages'=>$optionImage]);
                                   } elseif ($filename!='' && $optionImage=='') {

                                   	   $ImageData=DB::table('tblquestionalphabetsnumbers')->insert(['questionId'=>$id,'optionName'=>$questionImageText,'wordSequence'=>$isCorrectAnswer,'pronounciationFile'=>$filename]);

                                   } elseif ($filename=='' && $optionImage!='') {
                                       
                                       $ImageData=DB::table('tblquestionalphabetsnumbers')->insert(['questionId'=>$id,'optionName'=>$questionImageText,'wordSequence'=>$isCorrectAnswer,'optionImages'=>$optionImage]);
                                   } else {

                                   	  $ImageData=DB::table('tblquestionalphabetsnumbers')->insert(['questionId'=>$id,'optionName'=>$questionImageText,'wordSequence'=>$isCorrectAnswer]);
                                   }
                                

	                            } else {

                                    if ($filename!='' && $optionImage!='') {

                                       $delete=DB::select( DB::raw('update tblquestionalphabetsnumbers SET questionId='.$id.',optionName="'.$questionImageText.'",wordSequence='.$isCorrectAnswer.',pronounciationFile="'.$filename.'",optionName="'.$optionImage.'" where id='.$key.''));

                                    } elseif ($filename!='' && $optionImage=='') {

                                   	  $delete=DB::select( DB::raw('update tblquestionalphabetsnumbers SET questionId='.$id.',optionName="'.$questionImageText.'",wordSequence='.$isCorrectAnswer.',pronounciationFile="'.$filename.'" where id='.$key.''));

                                    } elseif ($filename=='' && $optionImage!='') {
                                       
                                       $delete=DB::select( DB::raw('update tblquestionalphabetsnumbers SET questionId='.$id.',optionName="'.$questionImageText.'",wordSequence='.$isCorrectAnswer.',optionImages="'.$optionImage.'" where id='.$key.''));
                                    
                                    } else {

	                            	   $delete=DB::select( DB::raw('update tblquestionalphabetsnumbers SET questionId='.$id.',optionName="'.$questionImageText.'",wordSequence='.$isCorrectAnswer.' where id='.$key.''));
	                            	}

	                            } 

                    	  }

                    }

                    

			 }

			  
			  //$levelmanagement->save();
	 
			  flash()->success('Question has updated successfully.');
			  return redirect()->to('/admin/questions');
			
			} else {

				 flash()->error('This Question name has been already taken. Please try with another name.');
			     return redirect()->to('/admin/questions/edit/'.$id);
			
			} 
	   }
}
