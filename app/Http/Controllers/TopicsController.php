<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\BankRequest;
use Illuminate\Http\Request;
use Laraspace\Topics;
use Laraspace\Subject;
use Laraspace\LevelManagement;
use Laraspace\Http\Requests\TopicRequest;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;

class TopicsController extends Controller{

	public function __construct() {
    	$this->middleware('auth');
  	}
    
  	public function index() {
		$topics = Topics::join('subject', 'topics.subjectId', '=', 'subject.id')
			->where('topics.isActive','1')
			->select('subject.subjectName','topics.id','topics.topicName','topics.topicDescription','topics.subjectId')
			->orderBy('id', 'DESC')
			->get();
		
		return view('admin.topics.index',compact('topics'));
	}

	public function add(){
		$subjects = Subject::all();
		$category = LevelManagement::all();
		return view('admin.topics.addedit',compact('subjects','category'));
	}

	public function postCreate(TopicRequest $request)
	{


		$highestShortOrderId = Topics::where('subjectId',$request->subject)->max('short_order_id');

		$short_order_id = 1;

		if(!empty($highestShortOrderId)){
			$short_order_id = $highestShortOrderId + 1;
		}


		$topic = new Topics();
		$topic->subjectId=$request->subject;
		$topic->topicName=$request->topicName;
		$topic->short_order_id=$short_order_id;
		//$topic->topicDescription=$request->topicDescription;
		$topic->isActive=$request->status;
		$topic->createdDate=date('Y-m-d H:i:s');
		$topic->updatedDate=date('Y-m-d H:i:s');
		$topic->save();
		session()->flash('success','Topic has added successfully.');
		return redirect()->to('/admin/topics');
	}

	public function getEdit($id){
		$subjects = Subject::all();
		$topic = Topics::find($id);
		$selcat = Subject::find($topic->subjectId);
		$category = LevelManagement::all();
		return view('admin.topics.addedit',compact('subjects','topic','category','selcat'));
	}

	public function postEdit(Request $request, $id) {
		$topic = Topics::find($id);
		$topic->subjectId=$request->subject;
		$topic->topicName=$request->topicName;
		//$topic->topicDescription=$request->topicDescription;
		$topic->isActive=$request->status;
		$topic->updatedDate=date('Y-m-d H:i:s');
		$topic->save();
        session()->flash('success','Topic has updated successfully.');
		return redirect()->to('/admin/topics');
   	}

   	public function Delete($id) {
	    $relatedTopicCount = DB::table('topicQueRel')
            ->where('topicId', $id)
            ->count();

        if ($relatedTopicCount > 0) {
            return response()->json(['status' => 'error', 'message' => 'This Topic cannot be deleted because it has assigned Questions.']);
        }

        $topics = Topics::find($id);

        if ($topics) {
            $topics->delete();
            return response()->json(['status' => 'success', 'message' => 'Topic deleted successfully.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Topic not found.']);
   }
   // Get Subject as per category id 
   public function getSubjects($categoryId)
   {
	   // Fetch subjects based on the category ID
	   $subjects = Subject::where('categoryId', $categoryId)->pluck('subjectName', 'id');
	   return response()->json($subjects);
   }
}
