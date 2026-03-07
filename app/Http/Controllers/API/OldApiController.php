<?php 
namespace Laraspace\Http\Controllers\API;
use Illuminate\Support\Facades\Session;
use Laraspace\Http\Controllers\Controller; 
use Laraspace\Http\Controllers\CommanController;
use Laraspace\Mail\VendorForgotPassword;
use Laraspace\Mail\CustomerForgotPassword;
use Laraspace\Mail\GeneralMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Carbon;
use Illuminate\Http\Request; 
use Laraspace\UserSubscriptionPlan;
use Laraspace\CustomerRegister;
use Laraspace\LessionManagement;
use Laraspace\TranactionDetails;
use Laraspace\QuestionCommnent;
use Laraspace\UnnecessaryWords;
use Laraspace\LevelManagement;
use Laraspace\AppNoticeBoard;
use Laraspace\PlanPackage;
use Laraspace\Questions;
use Laraspace\Customer;
use Laraspace\Language;
use Laraspace\Country;
use Laraspace\Banner;
use Laraspace\Subject;
use Laraspace\Topics;
use Validator;
use Config;
use Hash;
use Mail;
use File;
/*Old Code*/

	/* Message List */
	
	/*public function getMessages(Request $request) 
	{
	    $common=new CommanController;
	    $lang_id=($request->lang_id)?($request->lang_id):1;
	    //$userId=($request->userId)?($request->userId):0;
	    //$isVendor=($request->isVendor)?($request->isVendor):0;
        $langId=($request->header('langId'))?($request->header('langId')):1; 
        $apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
        $authenticate=$this->_authenticate;
       
        if (in_array($apiauthenticate,$authenticate)) {
			$data=DB::select( DB::raw("SELECT tblgeneralmessage.title_key,tblgeneralmessagetranslation.title_value from tblgeneralmessagetranslation
			inner join tblgeneralmessage on tblgeneralmessage.id=tblgeneralmessagetranslation.general_message_id where tblgeneralmessagetranslation.lang_id=$lang_id and tblgeneralmessage.is_app_msg = '1'") ); 
			$vals='';
			if ($data) {
		      	foreach ($data as $values) {
				   	$title_value=$values->title_value;
				   	$title_key=$values->title_key;
				   	$messageList[]=array("msgKey"=>$title_key,"msgValue"=>$title_value);
				}
				$myarray['result']=$messageList;					
				$myarray['message']=$common->get_msg("message_list",$langId)?$common->get_msg("message_list",$langId):"Message List.";
				$myarray['status']=1;
			}else {
		       	$myarray['result']=array();					
				$myarray['message']=$common->get_msg("no_message_list",$langId)?$common->get_msg("no_message_list",$langId):"No App Messages Found.";
				$myarray['status']=1;
		   	}
		} else {
			$myarray['result']=(object)array();					
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
        }	   

	   return response()->json($myarray);
	}*/
	
	/*public function levelList(Request $request) 
	{
		$common=new CommanController;
		$langId=($request->header('langId'))?($request->header('langId')):1; 
        $url=url('/');
        $userId = ($request->userId) ? ($request->userId) : 0; 

        $Display_Level_Id=$common->get_msg('Display_Level_Id') ? $common->get_msg('Display_Level_Id') :1;        
        
        $Level = LevelManagement::where('isActive','=',1)->orderby('sortOrder','asc')->get(); 
             
		$myarray=array();
		$arrays=array();

		$url=url('/');

		if ($Level->count() > 0) {
		 	foreach($Level as  $value) {
	 			$levelId=isset($value->levelId)?($value->levelId):0;
		 		$levelsortOrder=isset($value->sortOrder)?($value->sortOrder):0;
		 		$levelName=isset($value->levelName)?($value->levelName):"";
		 		$levelDescription=isset($value->levelDescription)?($value->levelDescription):"";

				$assignlession=DB::table('tblassignlessionlevel')->where('levelId', '=',$levelId)->orderby('lessionsortOrder','asc')->get();

            	$qrrArray=array();
             	$lessonArray=array();

				if ($assignlession) {
                	foreach($assignlession as $ass) {
                    	$lessionId=isset($ass->lessionId)?($ass->lessionId):0;
						$Questions = Questions::where('lessionId','=',$lessionId)->where('isActive','=',1)->orderby('sortOrder','asc')->get();
						$QuestionsCount = Questions::where('lessionId','=',$lessionId)->where('isActive','=',1)->orderby('sortOrder','asc')->count();

                		$lessionmanagement = LessionManagement::where('isActive','=',1)->where('lessionId',$lessionId)->first();

	                    $lesName=isset($lessionmanagement->lessionName)?($lessionmanagement->lessionName):"";
	                    $lesDescription=isset($lessionmanagement->lessionDescription)?($lessionmanagement->lessionDescription):"";
	                    $lesSortOrder=isset($lessionmanagement->sortOrder)?($lessionmanagement->sortOrder):"";
                     
                    	$qrrArray=array();
                     
                    	if ($QuestionsCount > 0) {
							foreach($Questions as $querows) {
                            	$lessionName=isset($querows->lessionName)?($querows->lessionName):"";
	                            $question=isset($querows->question)?($querows->question):"";
	                            $questionType=isset($querows->questionType)?($querows->questionType):0;
	                            $sortOrder=isset($querows->sortOrder)?($querows->sortOrder):0;
	                            $createdDate=isset($querows->createdDate)?($querows->createdDate):"";
	                            $uploadVocals=isset($querows->uploadVocals)?($querows->uploadVocals):"";
	                            $video=isset($querows->video)?($querows->video):"";
	                            $audio=isset($querows->audio)?($querows->audio):"";
	                            $fillBlankWord=isset($querows->fillBlankWord)?($querows->fillBlankWord):"";
	                            $questionId=isset($querows->questionId)?($querows->questionId):0;
	                            $lessionId=isset($querows->lessionId)?($querows->lessionId):0;
	                            $questionHeader=isset($querows->questionHeader)?($querows->questionHeader):"";
	                            $listType=isset($querows->listType)?($querows->listType):1;

	                            if ($uploadVocals!='') {
	                               $uploadVocals=$url."/questionVocals/".$uploadVocals;
	                            }

	                            if ($video!='') {
	                               $video=$url."/questionVideos/".$video;
	                            }

	                            if ($audio!='') {
	                               $audio=$url."/questionAudios/".$audio;
	                            }

	                            $optionArr=array();
	                            $translatedArr=array();

                            	if ($questionType==1) {
                                	$questionImage=DB::table('tblquestionimage')->where('questionId', '=',$questionId)->get();
									if ($questionImage) {

	                                    foreach($questionImage as $opt) {

	                                         $optionId=isset($opt->id)?($opt->id):0;
	                                         $isCorrectAnswer=isset($opt->isCorrectAnswer)?($opt->isCorrectAnswer):0;
	                                         $questionImage=isset($opt->questionImage)?($opt->questionImage):"";
	                                         $questionImageText=isset($opt->questionImageText)?($opt->questionImageText):""; 
	                                         $questionAudioImageSelect=isset($opt->questionAudio)?($opt->questionAudio):"";

	                                         if ($questionImage!='') {
	                                         	$questionImage=$url."/questionImage/".$questionImage;
	                                         }

	                                         if ($questionAudioImageSelect!='') {
	                                         	$questionAudioImageSelect=$url."/questionAudioSelection/".$questionAudioImageSelect;
	                                         }

	                                         $optionArr[]=array('optionId'=>$optionId,'questionImage'=>$questionImage,'questionImageText'=>$questionImageText,'isCorrectAnswer'=>$isCorrectAnswer,'questionAudioImageSelect'=>$questionAudioImageSelect);  	
	                                    }
									}
                                }

	                            if ($questionType==2) {
	                                
	                                $wordone=DB::table('tblquestionwordone')->where('questionId', '=',$questionId)->orderby('wordSequence','asc')->get();

	                                if ($wordone) {

	                                    foreach($wordone as $opt) {
											$optionId=isset($opt->id)?($opt->id):0;
	                                        $wordText=isset($opt->wordText)?($opt->wordText):"";
	                                        $wordSequence=isset($opt->wordSequence)?($opt->wordSequence):"";

	                                        $optionArr[]=array('optionId'=>$optionId,'wordText'=>$wordText,'wordSequence'=>$wordSequence); 

	                                    }
	                                }    
	                            }

	                            if ($questionType==3) {
	                               	$wordtwo=DB::table('tblquestionwordtwo')->where('questionId', '=',$questionId)->orderby('wordSequence','asc')->get();

	                               	$wordtwoadditional=DB::table('tblquestionwordtwoadditional')->where('questionId', '=',$questionId)->get();

	                                if ($wordtwo) {
										foreach($wordtwo as $opt) {
											$optionId=isset($opt->id)?($opt->id):0;
	                                        $wordText=isset($opt->wordText)?($opt->wordText):"";
	                                        $wordSequence=isset($opt->wordSequence)?($opt->wordSequence):"";

	                                        $optionArr[]=array('optionId'=>$optionId,'wordText'=>$wordText,'wordSequence'=>$wordSequence);
	                                    }
	                                }

	                                if ($wordtwoadditional) {
										foreach($wordtwoadditional as $add) {
											$optionId=isset($add->id)?($add->id):0;
	                                        $originalText=isset($add->originalText)?($add->originalText):"";
	                                        $translatedText=isset($add->translatedText)?($add->translatedText):"";
	                                        $isHeightlight=isset($add->isHeightlight)?($add->isHeightlight):0;

	                                        $translatedArr[]=array('optionId'=>$optionId,'originalText'=>$originalText,'translatedText'=>$translatedText,'isHeightlight'=>$isHeightlight);
										}
	                                }        
	                            }

	                            if ($questionType==4) {
                            		$fillblank=DB::table('tblquestionfillblank')->where('questionId', '=',$questionId)->get();

	                                if ($fillblank) {
										foreach($fillblank as $opt) {
	                                        $optionId=isset($opt->id)?($opt->id):0;
	                                        $optionName=isset($opt->optionName)?($opt->optionName):"";
	                                         
	                                        $optionArr[]=array('optionId'=>$optionId,'optionName'=>$optionName);
	                                    }
	                                }    
	                            }

	                            if ($questionType==5) {
	                                
	                               	$match=DB::table('tblquestionmatchfollowing')->where('questionId', '=',$questionId)->get();

	                               	if ($match) {
										foreach($match as $opt) {
											$optionId=isset($opt->id)?($opt->id):0;
	                                        $originalWord=isset($opt->originalWord)?($opt->originalWord):"";
	                                        $translateWord=isset($opt->translateWord)?($opt->translateWord):"";

	                                        $optionArr[]=array('optionId'=>$optionId,'originalWord'=>$originalWord,'translateWord'=>$translateWord);
	                                    }
	                               }    
	                                	
	                            }

	                            if ($questionType==6) {
	                                
	                              	$alphabets=DB::table('tblquestionalphabetsnumbers')->where('questionId', '=',$questionId)->orderby('wordSequence','asc')->get();

	                               	if ($alphabets) {

	                                    foreach($alphabets as $opt) {
											$optionId=isset($opt->id)?($opt->id):0;
	                                        $optionName=isset($opt->optionName)?($opt->optionName):"";
	                                        $wordSequence=isset($opt->wordSequence)?($opt->wordSequence):"";
	                                        $pronounciationFile=isset($opt->pronounciationFile)?($opt->pronounciationFile):"";
	                                        $optionImages=isset($opt->optionImages)?($opt->optionImages):"";
	                                        if ($pronounciationFile!='') {
	                                         	$pronounciationFile=$url."/pronounciationFile/".$pronounciationFile;
	                                        }

	                                        if ($optionImages!='') {
	                                         	$optionImages=$url."/optionImages/".$optionImages;
	                                        }

	                                        $optionArr[]=array('optionId'=>$optionId,'optionName'=>$optionName,'wordSequence'=>$wordSequence,'pronounciationFile'=>$pronounciationFile,'optionImages'=>$optionImages);
										}
						            }    
	                            }
                            	$qrrArray[]=array('questionId'=>$questionId,'lessionId'=>$lessionId,'lessionName'=>$lesName,'question'=>$question,'questionType'=>$questionType,'sortOrder'=>$sortOrder,'createdDate'=>$createdDate,'questionVocals'=>$uploadVocals,'questionVideo'=>$video,'questionAudio'=>$audio,'questionHeader'=>$questionHeader,'listType'=>$listType,'fillBlankWord'=>$fillBlankWord,'options'=>$optionArr,'translate'=>$translatedArr);
                     		}
                    	}

	                    if(!empty($lessionmanagement)){
	                     	$lessonArray[]=array("lessionId"=>$lessionId,"lessionName"=>$lesName,"lessionDescription"=>$lesDescription,"sortOrder"=>$lesSortOrder,"lessionQuestionOption"=>$qrrArray); 
	                    }
                	}

				}
            	$arrays[]=array('levelId'=>(int)$levelId,"levelName"=>$levelName,"levelDescription"=>$levelDescription,"sortOrder"=>$levelsortOrder,"lessons"=>$lessonArray);
			}

			$myarray['result']=$arrays;
			$myarray['status']=1;
			$myarray['message']=$common->get_msg("level_list",$langId)?$common->get_msg("level_list",$langId):'Level List.';
		} else {
			$myarray['result']=$arrays;
			$myarray['status']=1;
			$myarray['message']=$common->get_msg("no_level_list",$langId)?$common->get_msg("no_level_list",$langId):'No Level Found.';
		}			 
		return response()->json($myarray); 
    }*/

    // public function deleteuser(Request $request) 
    // {
    // 	$deviceType=($request->deviceType)?($request->deviceType):0;
	//    	$deviceToken=($request->deviceToken)?($request->deviceToken):"";
	//    	$userId=($request->userId)?($request->userId):0;
	//    	$common=new CommanController;
    //    	$langId=($request->header('langId'))?($request->header('langId')):1; 

	//    	$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
    //    	$authenticate=$this->_authenticate;

	   	
    //     if (!$request->userId) {
	// 	   	$myarray['result']=array();					
	// 	   	$myarray['message']=$common->get_msg("blank_customerId",$langId)?$common->get_msg("blank_customerId",$langId):"Please select customerId.";
	// 	   	$myarray['status']=0;
		 
	//  	} elseif (!$request->deviceType) {
	// 	   	$myarray['result']=array();					
	// 	   	$myarray['message']=$common->get_msg("blank_deviceType",$langId)?$common->get_msg("blank_deviceType",$langId):"Please enter deviceType.";
	// 	   	$myarray['status']=0;
		 
	// 	} elseif (!$request->deviceToken) {
	// 	   	$myarray['result']=array();					
	// 	   	$myarray['message']=$common->get_msg("blank_deviceToken",$langId)?$common->get_msg("blank_deviceToken",$langId):"Please enter deviceToken.";
	// 	   	$myarray['status']=0;
		 
	// 	} else {
	// 		$checkDeviceTokenCount = DB::table('tbldevicetoken')->where([['customerId', '=',$userId],['deviceType', '=',$deviceType],['deviceToken', '=',$deviceToken]])->count();
             
    //         if ($checkDeviceTokenCount > 0) {
	// 			/*$deviceTokenUpdate=DB::table('tbldevicetoken')->where([['customerId', '=',$userId],['deviceType', '=',$deviceType],['deviceToken', '=',$deviceToken],['loginStatus', '=',1]])->update(
    //                ['loginStatus'=>0,'logoutDateTime'=>date('Y-m-d H:i:s')]);*/

    //             $deletecustomerpaymenttype=DB::delete("delete from tbldevicetoken where customerId='{$userId}' and deviceType='$deviceType' and deviceToken='{$deviceToken}'");
             	  
    //          	$customerDeviceTokenUpdate=DB::table('tblcustomer')->where([['id', '=',$userId]])->update(['email'=>'anonymous@gmail.com','loginStatus'=>0,'lastLogoutDate'=>date('Y-m-d H:i:s')]);

    //          	$myarray['result']=array();					
	// 	        $myarray['message']="Customer deleted successfully";
	// 	        $myarray['status']=1;  
	// 		} else {
    //      		$myarray['result']=array();					
	// 	        $myarray['message']="Customer Not deleted.";
	// 	        $myarray['status']=0;
    //      	}
	// 	}
	// 	return response()->json($myarray);
	// }
    /* Delete Notification */

	/*public function deleteNotification(Request $request) 
	{
    	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $notificationId=($request->notificationId)?($request->notificationId):0;

        //$langId=($request->header('langId'))?($request->header('langId')):1;
		$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
        $authenticate=$this->_authenticate; 

        if (in_array($apiauthenticate,$authenticate)) {
        	if (!$request->notificationId) {
			   $myarray['result']=array();					
			   $myarray['message']=$common->get_msg("blank_notification_id",$langId)?$common->get_msg("blank_notification_id",$langId):"Please Enter Notification Id.";
			   $myarray['status']=0; 
			} else {
		        $checkview = DB::table('tblnotification')->where([['id', '=', $notificationId]])->count();

		        if ($checkview > 0) {
			        $productview=DB::select( DB::raw("delete from tblnotification  where id={$notificationId}"));
			          $myarray['result']=array();					
					$myarray['message']=$common->get_msg("delete_notification",$langId)?$common->get_msg("delete_notification",$langId):"Your notification has been deleted.";
					$myarray['status']=1;
	          	} else {
		           
		         	$myarray['result']=array();					
					$myarray['message']=$common->get_msg("invalid_notification",$langId)?$common->get_msg("invalid_notification",$langId):"Invalid Notification Id.";
					$myarray['status']=0;
	          	}
	     	}

		} else {
            $myarray['result']=(object)array();					
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
		}           
		return response()->json($myarray);
    }*/

    /* notification list */

	/*public function notificationList(Request $request) 
	{
		$common=new CommanController;
		$langId=($request->header('langId'))?($request->header('langId')):1;
		$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
        $authenticate=$this->_authenticate; 

      	$pageLimit = isset($request->count)?(int)($request->count):5;
      	$lastId = isset($request->lastId)?(int)($request->lastId):0;			
	  	$pageNo = isset($request->page)?(int)($request->page):1;
	  	$startDate = isset($request->startDate)?($request->startDate):'';
	  	$endDate = isset($request->endDate)?($request->endDate):'';
      	$timezone=($request->timezone)?($request->timezone):"";
      	$userId=isset($request->userId)?(int)($request->userId):0;
      	$isCustomer=isset($request->isCustomer)?(int)($request->isCustomer):1;

      	if ($pageNo!=0) {
		  	$start = ($pageNo - 1) * $pageLimit;
	  	} else {
		  	$start=1; 
	  	}
              
      	$final='';
		if (strlen($timezone)==5) {
			$strsign=substr($timezone,0,1);
			$h=$strsign.substr($timezone,1,1)." hour "; 
			$m=$strsign.substr($timezone,3,2)." minutes";
			$final=$h.$m;	
		}
		if (strlen($timezone)==6) {
			$strsign=substr($timezone,0,1);
			$h=$strsign.substr($timezone,1,2)." hour "; 
			$m=$strsign.substr($timezone,4,2)." minutes";
			$final=$h.$m;	
		}
              
	    $notifications=array();
	    $notification = DB::table('tblnotification')->where('notifiedUserId', '=', $userId)->orderBy('id', 'desc')->skip($start)->take($pageLimit)->get();
	    $notificationcn = DB::table('tblnotification')->where('notifiedUserId', '=', $userId)->orderBy('id', 'desc')->get();
	    $totalRecordInPage=$notificationcn->count();

        $notificationCount=$common->NotificationCountCustomer($userId);

        if (in_array($apiauthenticate,$authenticate)) {
			if ($notification->count() > 0) {
				foreach ($notification as  $values) {
			    	$id=($values->id)?($values->id):0;
			    	$notifiedByUserId=($values->notifiedByUserId)?($values->notifiedByUserId):0;
			    	$notifiedUserId=($values->notifiedUserId)?($values->notifiedUserId):0;
			    	$flag=($values->flag)?($values->flag):0;
			    	$notificationType=($values->notificationType)?($values->notificationType):0;
			    	$createdDate=($values->createdDate)?($values->createdDate):0;
			    	$notification=($values->notification)?($values->notification):0;
			    	$SendBy=($values->SendBy)?($values->SendBy):0;
                    $missionId=($values->missionId)?($values->missionId):0;
                    //$productId=($values->productId)?($values->productId):0;

                    $isCustomerNotification=($values->isCustomerNotification)?($values->isCustomerNotification):0;


                    $isGeneralNotification=($values->isGeneralNotification)?($values->isGeneralNotification):0;

			        $lastAddedDate=$createdDate;  
                    
                    if($timezone!='') {
						$temp1= strtotime("$lastAddedDate $final");
			            $lastAddedDate= date("Y-m-d H:i:s", $temp1);
					}
					  
			    	if ($notificationType==2) {
			    		$notifiedByUserName='Admin';
			    		$notifiedByUserPicture='';
			    		
			    		$notifiedUserName=$common->customerName($notifiedUserId);
			    		$notifiedUserPicture=$common->customerProfilePic($notifiedUserId);
			    	    
			    	} elseif ($notificationType==1) {
                        $notifiedByUserName='Admin';
			    		$notifiedByUserPicture='';
			    		
                       $notifiedUserName=$common->customerName($notifiedUserId);
                       $notifiedUserPicture=$common->customerProfilePic($notifiedUserId);
                       
			    	} else {
			    	     $notifiedByUserName='Admin';
			    		$notifiedByUserPicture='';
			    		
                       $notifiedUserName=$common->customerName($notifiedUserId);
                       $notifiedUserPicture=$common->customerProfilePic($notifiedUserId);
			    	}
                      
                   	$missionName='';
                   	if ($missionId!=0) {
                   	 	$missionName=$common->getMissionName($missionId);
                   	}
				    $notifications[]=array("id"=>(int)$id,"notifiedByUserId"=>(int)$notifiedByUserId,"notifiedByUserName"=>$notifiedByUserName,"notifiedByUserPicture"=>$notifiedByUserPicture,"notifiedUserId"=>(int)$notifiedUserId,"notifiedUserName"=>$notifiedUserName,"notifiedUserPicture"=>$notifiedUserPicture,"flag"=>(int)$flag,"createdDate"=>$lastAddedDate,"notification"=>$notification,"notificationType"=>(int)$notificationType,"missionId"=>(int)$missionId,'missionName'=>$missionName,'isGeneralNotification'=>(int)$isGeneralNotification);
				}

			    $msg ='Notification List.';
		       	$myarray['result']=$notifications;
			    $myarray['totalUnreadCount']=$notificationCount;
               	$myarray['page'] = $pageNo;                				
			   	$myarray['totalRecord'] = (int)$totalRecordInPage;					
			   	$myarray['message']=$msg;
			   	$myarray['status']=1;
		 	} else {
                $msg='No Notification Found.';
                $myarray['result']=array();
                $myarray['totalUnreadCount']=$notificationCount;
                $myarray['page'] = $pageNo;                				
				$myarray['totalRecord'] = (int)$totalRecordInPage;					
				$myarray['message']=$msg;
				$myarray['status']=1;
		 	}
		} else {
			$myarray['result']=(object)array();					
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
		}	 

		return response()->json($myarray);    
	}*/

	/*public function noticeBoardList(Request $request)
	{
		$common=new CommanController;
		$langId=($request->header('langId'))?($request->header('langId')):1; 
        $url=url('/');
        
        $AppNoticeBoard = AppNoticeBoard::where('isActive','=',1)->orderby('sortOrder','asc')->get();
		$myarray=array();
		$arrays=array();

		$url=url('/');

		if ($AppNoticeBoard->count() > 0) {
		 	foreach($AppNoticeBoard as  $value) {
		 		$noticeId=isset($value->id)?($value->id):0;
			 	$sortOrder=isset($value->sortOrder)?($value->sortOrder):0;
			 	$photo=isset($value->photo)?($value->photo):"";
			 	$description=isset($value->description)?($value->description):"";

			 	if ($photo!='') {
			 		$photo=$url."/appnoticeboard/".$photo;
			 	}
				$arrays[]=array("noticeId"=>$noticeId,"noticeDescription"=>$description,"sortOrder"=>$sortOrder,"noticePhoto"=>$photo); 
	        }

			$myarray['result']=$arrays;
			$myarray['status']=1;
			$myarray['message']=$common->get_msg("app_noticeboard_list",$langId)?$common->get_msg("app_noticeboard_list",$langId):'Onboard Notice List.';
		} else {
			$myarray['result']=$arrays;
			$myarray['status']=1;
			$myarray['message']=$common->get_msg("no_noticeboard_list",$langId)?$common->get_msg("no_noticeboard_list",$langId):'No Onboard Notice Found.';
		}			 
		 
		return response()->json($myarray); 
    }*/

     /* Read Notification */

	/*public function readNotification(Request $request) 
	{
    	$common=new CommanController;
        $langId=($request->header('langId'))?($request->header('langId')):1;
        $notificationId=($request->notificationId)?($request->notificationId):0;

        //$langId=($request->header('langId'))?($request->header('langId')):1;
		$apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
        $authenticate=$this->_authenticate; 

        if (in_array($apiauthenticate,$authenticate)) {
        
	        if (!$request->notificationId) {
			   $myarray['result']=array();					
			   $myarray['message']=$common->get_msg("blank_notification_id",$langId)?$common->get_msg("blank_notification_id",$langId):"Please Enter Notification Id.";
			   $myarray['status']=0; 
			} else {
		        $checkview = DB::table('tblnotification')->where([['id', '=', $notificationId]])->count();

		        if ($checkview > 0) {
			        $productview=DB::select( DB::raw("update tblnotification SET `flag`=1 where id={$notificationId}"));
			          $myarray['result']=array();					
					$myarray['message']=$common->get_msg("update_notification_status",$langId)?$common->get_msg("update_notification_status",$langId):"Your notification status has been updated.";
					$myarray['status']=1;
	          	} else {
		           
		         	$myarray['result']=array();					
					$myarray['message']=$common->get_msg("invalid_notification",$langId)?$common->get_msg("invalid_notification",$langId):"Invalid Notification Id.";
					$myarray['status']=0;
	          	}
	     	}
		} else {
            $myarray['result']=(object)array();					
			$myarray['message']="Invalid Authentication.";
			$myarray['status']=0;
		}           
        return response()->json($myarray);
    }*/

    /*public function PlanList(Request $request)
    {
    	$common = new CommanController;
        $apiauthenticate=($request->header('AUTHENTICATE'))?($request->header('AUTHENTICATE')):1;
        $authenticate=$this->_authenticate;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $URL=url('/');

        if (in_array($apiauthenticate, $authenticate)){
                           
            $PlanPackageList = PlanPackage::where('isActive','1')->get();
            $PlanPackage=[];

	       	if (count($PlanPackageList) > 0) {
	            
	           	foreach ($PlanPackageList as $optionlist) {
	            
              		$packageId=isset($optionlist->packageId)?($optionlist->packageId):0;
	              	$packageName=isset($optionlist->packageName)?($optionlist->packageName):'';
	              	$packagePrice=isset($optionlist->packagePrice)?($optionlist->packagePrice):0;
	              	$packageDescription=isset($optionlist->packageDescription)?($optionlist->packageDescription):'';
	              	$planId=isset($optionlist->planId)?($optionlist->planId):0;
	              	$planName=isset($optionlist->planName)?($optionlist->planName):'';
	              	$packagePeriodInMonth=isset($optionlist->packagePeriodInMonth)?($optionlist->packagePeriodInMonth):0;
	              	$androidPlanKey=isset($optionlist->androidPlanKey)?($optionlist->androidPlanKey):'';
	              	$iosPlanKey=isset($optionlist->iosPlanKey)?($optionlist->iosPlanKey):'';
					
					$PlanPackage[]=array("packageId"=>(int)$packageId,"packageName"=>$packageName,"packagePrice"=>$packagePrice,"packageDescription"=>$packageDescription,"packagePeriodInMonth"=>(int)$packagePeriodInMonth,"androidPlanKey"=>$androidPlanKey,"iosPlanKey"=>$iosPlanKey);
	           	}

	           $arr['result']=$PlanPackage;                    
	           $arr['message']=$common->get_msg("plan_list",$langId)?$common->get_msg("plan_list",$langId):"Subscription Plan List.";
	           $arr['status']=1;  

            } else {
                $arr['result']=array();                  
                $arr['message']=$common->get_msg("no_plan_list",$langId)?$common->get_msg("no_plan_list",$langId):"No Subscription Plan Found.";
                $arr['status']=0;
            }
        }else{
            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }
        return response()->json($arr);
    }*/

    /*public function customerSelectPlan(Request $request)
    {
		$common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $userId = ($request->userId) ? ($request->userId) : 0;
        $Display_Level_Id=$common->get_msg('Display_Level_Id') ? $common->get_msg('Display_Level_Id') :1;
        $curDate=date("Y-m-d");

        if (in_array($apiauthenticate, $authenticate)){
			$validator = Validator::make($request->all() , 
                [ 
                    'userId' => ['required'],
                    'packageId' => ['required']
                ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            }else{
                $customerData = CustomerRegister::where('id',$userId)->first();
                $userId = ($request->userId) ? ($request->userId) : 0;
                $packageId = ($request->packageId) ? ($request->packageId) : '';
                $androidPlanKey = ($request->androidPlanKey) ? ($request->androidPlanKey) : '';
                $iosPlanKey = ($request->iosPlanKey) ? ($request->iosPlanKey) : '';
                $jsonresponseobject = isset($request->jsonresponseobject) ? ($request->jsonresponseobject) : '';
                $currentDate=date("Y-m-d");

                if ($customerData) {
                	$userSubscriptionData = UserSubscriptionPlan::where('userId',$userId)->where('packageId',$packageId)->where('isActive',1)->orderby('id','desc')->first();
                    $userSubscriptioncheckingId=isset($userSubscriptionData->id)?($userSubscriptionData->id):0;
                    
                    if ($userSubscriptionData) {
                        $usersubscriptionId=isset($userSubscriptionData->id)?($userSubscriptionData->id):0;
                        $createdDate=isset($userSubscriptionData->createdDate)?($userSubscriptionData->createdDate):"";
                        $expiryDate=isset($userSubscriptionData->expiryDate)?($userSubscriptionData->expiryDate):'';
                        $PlanPackageList = PlanPackage::where('packageId',$packageId)->where('isActive','1')->first();
                        $packageName=isset($PlanPackageList->packageName)?($PlanPackageList->packageName):'';
                        $packagePrice=isset($PlanPackageList->packagePrice)?($PlanPackageList->packagePrice):'';
                        $packagePeriodInMonth=isset($PlanPackageList->packagePeriodInMonth)?($PlanPackageList->packagePeriodInMonth):0;
                        $expiryDateExist=date("Y-m-d",strtotime($expiryDate. '+' .$packagePeriodInMonth. "months"));

					    $fromDate=date("Y-m-d",strtotime($expiryDate. '+1 days'));
					    $endDate=date("Y-m-d",strtotime($fromDate. '+' .$packagePeriodInMonth. "months"));

					    $userSubscription = UserSubscriptionPlan::create([
                            'userId' => $userId,
                            'androidPlanKey' => $androidPlanKey,
                            'iosPlanKey' => $iosPlanKey,
                            'isActive' => 0,
                            'packageId' => $packageId,
                            'packageName' => $packageName,
                            'packagePrice'=>$packagePrice,
                            'packagePeriodInMonth' => $packagePeriodInMonth,
                            'createdDate' => date('Y-m-d H:i:s'),
                            'expiryDate' => $endDate,
                            'fromDate' => $fromDate,
                            'jsonresponseobject' => $jsonresponseobject,
                        ]);

				      	$packageData=$common->getUserSubscriptionPlanPackage($userId);
                        
                        $custData=$customerData->toArray();
                        $CustomerSubscriptionArr=array_merge($custData,$packageData);

                        $arr['result'] = $CustomerSubscriptionArr;
                        $arr['message'] = $common->get_msg("plan_renew", $langId) ? $common->get_msg("plan_renew", $langId) : 'Your plan has been renewed successfully.';
                        $arr['status'] = 1; 
					} else {
                       	$PlanPackageList = PlanPackage::where('packageId',$packageId)->where('isActive','1')->first();
                       	$packageName=isset($PlanPackageList->packageName)?($PlanPackageList->packageName):'';
                       	$packagePrice=isset($PlanPackageList->packagePrice)?($PlanPackageList->packagePrice):'';
                       	$packagePeriodInMonth=isset($PlanPackageList->packagePeriodInMonth)?($PlanPackageList->packagePeriodInMonth):0;
                       	$expiryDate=date("Y-m-d",strtotime($currentDate. '+' .$packagePeriodInMonth. "months"));
                           
                        $userSubscriptionDataCount = UserSubscriptionPlan::where('userId',$userId)->where('packageId','!=',1)->where('isActive',1)->orderby('id','desc')->count();

                        if ($userSubscriptionDataCount > 0) {
                       	   	$isActive=0;
                       	   	$userSubscriptionPlanData= UserSubscriptionPlan::where('userId',$userId)->where('packageId','!=',1)->orderby('id','desc')->first();
                       	   	$userSubscriptionPlanExpiryDate=isset($userSubscriptionPlanData->expiryDate)?($userSubscriptionPlanData->expiryDate):'';
                       	   	$fromDate=date("Y-m-d",strtotime($userSubscriptionPlanExpiryDate. '+1 days'));
                       	   	$endDate=date("Y-m-d",strtotime($fromDate. '+' .$packagePeriodInMonth. "months"));
                       	} else {
                       	   	$fromDate=$currentDate;
                       	   	$endDate=$expiryDate;
                       	   	$isActive=1;
                        } 

                        if ($userSubscriptioncheckingId==0) {
							$userSubscription = UserSubscriptionPlan::create([
                                'userId' => $userId,
                                'androidPlanKey' => $androidPlanKey,
                                'iosPlanKey' => $iosPlanKey,
                                'isActive' => $isActive,
                                'packageId' => $packageId,
                                'packageName' => $packageName,
                                'packagePrice'=>$packagePrice,
                                'packagePeriodInMonth' => $packagePeriodInMonth,
                                'createdDate' => date('Y-m-d H:i:s'),
                                'expiryDate' => $endDate,
                                'fromDate' => $fromDate,
                                'jsonresponseobject' => $jsonresponseobject,
                         	]);
                             
                            $usersubscriptionId = $userSubscription->id;
                             
                           	if ($packageId!=1) {
                           	   	$updatePackage=DB::table('tblusersubscription')->where('userId',$userId)->where('packageId',1)->update(['isActive'=>2,'updatedDate'=>date('Y-m-d H:i:s')]);
                           	}  
                       	} else {
                        	$usersubscriptionId =$userSubscriptioncheckingId;
                        }

						$packageData=$common->getUserSubscriptionPlanPackage($userId);
						$custData=$customerData->toArray();
                        $CustomerSubscriptionArr=array_merge($custData,$packageData);
                            
                        $arr['result'] = $CustomerSubscriptionArr;
                        $arr['message'] = $common->get_msg("user_plan_subscription", $langId) ? $common->get_msg("user_plan_subscription", $langId) : 'You have successfully plan subscribed.';
                        $arr['status'] = 1;  
					} 
				} else {
					$arr['result'] = (object)array();
                  	$arr['message'] = $common->get_msg("something_went_wrong", $langId) 
                                                ? $common->get_msg("something_went_wrong", $langId) 
                                                : 'Oops, Something went wrong.';
                  	$arr['status'] = 0;
              	} 
			}   
		} else {
            $arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }

        $request1=json_encode($request->all());
        $response1=json_encode($arr);

        $time = "\n\n\n\n\n\n--------".date('Y-m-d H:i:s')."--------------\n\n";
        $ch = "Request:".$request1."\n";
        $ch2="\n";
        $ch1= "Response:".$response1."\n";
        $data=$time.$ch.$ch2.$ch1;
        $file = time() .rand(). '_paymentrequestresponse.txt';
        $destinationPath=public_path()."/requestresponse/";
        
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath,0777,true);  
        }
        File::put($destinationPath.$file,$data);
        return response()->json($arr);  
    }*/


   /* public function customerPlanHistory(Request $request)
    {
		$common = new CommanController;
        $authenticate = $this->_authenticate;
        $apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
        $langId = ($request->header('langId')) ? ($request->header('langId')) : 1;
        $userId = ($request->userId) ? ($request->userId) : 0;
        $Display_Level_Id=$common->get_msg('Display_Level_Id') ? $common->get_msg('Display_Level_Id') :1;
        $curDate=date("Y-m-d");

        if (in_array($apiauthenticate, $authenticate)){
			$validator = Validator::make($request->all() , 
                [ 
                    'userId' => ['required']
                ]);

            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->first();

                $arr['result'] = (object)array();
                $arr['message'] = implode('', $error);
                $arr['status'] = 0;
            }else{
            	$userSubscriptionData = UserSubscriptionPlan::where('userId',$userId)->orderby('expiryDate','desc')->get();
            	$userSubscriptionArr=[];
            	if(count($userSubscriptionData) > 0) {
                    foreach($userSubscriptionData as $subData) {
						$usersubscriptionId=isset($subData->id)?($subData->id):0;
                        $userpackageId=isset($subData->packageId)?($subData->packageId):0;
                        $packageName=isset($subData->packageName)?($subData->packageName):"";
                        $createdDate=isset($subData->createdDate)?($subData->createdDate):"";
                        $packagePrice=isset($subData->packagePrice)?($subData->packagePrice):0;
                        $packagePeriodInMonth=isset($subData->packagePeriodInMonth)?($subData->packagePeriodInMonth):0;
                        $expiryDate=isset($subData->expiryDate)?($subData->expiryDate):'';
                        $androidPlanKey=isset($subData->androidPlanKey)?($subData->androidPlanKey):'';
                        $iosPlanKey=isset($subData->iosPlanKey)?($subData->iosPlanKey):'';
                        $createdDate=isset($subData->createdDate)?($subData->createdDate):"";
                        $cancelDate=isset($subData->cancelDate)?($subData->cancelDate):"";
                        $isActive=isset($subData->isActive)?($subData->isActive):0;
                        $fromDate=isset($subData->fromDate)?($subData->fromDate):"";
                          
                        if ($isActive==1 && $expiryDate >= $curDate ) {
                          	$statusName='Active';
                        } elseif($isActive==0) {
                          	$statusName='InActive';
                        } elseif($isActive==2) {
                            $statusName='Expired';
                        } else {
                        	$statusName='InActive';
                        }  
                        $userSubscriptionArr[]=array("usersubscriptionId"=>$usersubscriptionId,"packageId"=>(int)$userpackageId,"packageName"=>$packageName,"packagePrice"=>$packagePrice,"packagePeriodInMonth"=>$packagePeriodInMonth,"androidPlanKey"=>$androidPlanKey,"iosPlanKey"=>$iosPlanKey,"createdDate"=>$createdDate,"cancelDate"=>$cancelDate,"expiryDate"=>$expiryDate,"packageStatus"=>(int)$isActive,"packageStatusName"=>$statusName,"fromDate"=>$fromDate);
                    }
					$arr['result'] = $userSubscriptionArr;
                   	$arr['message'] = $common->get_msg("purchased_plan_list", $langId) ? $common->get_msg("purchased_plan_list", $langId) : 'Purchased Plan List.';
                   	$arr['status'] = 1;  
				} else {
					$arr['result'] = (object)array();
                   $arr['message'] = $common->get_msg("no_purchased_plan_list", $langId) ? $common->get_msg("purchased_plan_list", $langId) : "You haven't purchased any plan.";
                   $arr['status'] = 1;	
				} 
			}

        } else {
        	$arr['result'] = (object)array();
            $arr['message'] = $common->get_msg("invalid_authentication", $langId) 
                            ? $common->get_msg("invalid_authentication", $langId) 
                            : 'Invalid Authentication.';
            $arr['status'] = 0;
        }
        return response()->json($arr);
    } */