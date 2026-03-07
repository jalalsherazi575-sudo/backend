<?php
namespace Laraspace\Http\Controllers\API;

use Illuminate\Support\Facades\Session;
use Laraspace\Http\Controllers\Controller;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Validator;
use Config;
use DB;

Class BookmarkApiController extends Controller
{
	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Config $authenticate){
        $this->_authenticate = config('constant.authenticate');
    }

	/**
	 * Get all bookmarks for customer
	 * POST /api/customer/bookmarks
	 * Request: { customer_id: int }
	 */
	public function getBookmarks(Request $request)
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

			// Get bookmarks for customer with question details
			$bookmarks = DB::table('customer_bookmarks')
				->join('tblquestion', 'customer_bookmarks.question_id', '=', 'tblquestion.questionId')
				->where('customer_bookmarks.cust_id', $customer_id)
				->select(
					'customer_bookmarks.id',
					'customer_bookmarks.question_id',
					'customer_bookmarks.created_at',
					'tblquestion.question',
					'tblquestion.questionExplanation'
				)
				->orderBy('customer_bookmarks.created_at', 'DESC')
				->get();

			// Get options for each bookmarked question
			$result = [];
			foreach ($bookmarks as $bookmark) {
				$options = DB::table('tblquestionoption')
					->where('questionId', $bookmark->question_id)
					->select('id', 'questionImageText', 'isCorrectAnswer')
					->get();

				$result[] = [
					'bookmark_id' => $bookmark->id,
					'question_id' => $bookmark->question_id,
					'question' => $bookmark->question,
					'explanation' => $bookmark->questionExplanation,
					'options' => $options,
					'bookmarked_at' => $bookmark->created_at
				];
			}

			$myarray['result'] = [
				'bookmarks' => $result,
				'total_bookmarks' => count($result)
			];
			$myarray['message'] = 'Bookmarks retrieved successfully.';
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
	 * Add bookmark for customer
	 * POST /api/customer/bookmarks/add
	 * Request: { customer_id: int, question_id: int }
	 */
	public function addBookmark(Request $request)
	{
		$common = new CommanController;
		$authenticate = $this->_authenticate;
		$apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
		$langId = ($request->header('langId')) ? ($request->header('langId')) : 1;

		if (in_array($apiauthenticate, $authenticate)) {
			$validator = Validator::make($request->all(), [
				'customer_id' => 'required',
				'question_id' => 'required'
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
			$question_id = $request->question_id;

			// Check if bookmark already exists
			$existing = DB::table('customer_bookmarks')
				->where('cust_id', $customer_id)
				->where('question_id', $question_id)
				->first();

			if ($existing) {
				$myarray['result'] = ['bookmark_id' => $existing->id];
				$myarray['message'] = 'Question already bookmarked.';
				$myarray['status'] = 1;
			} else {
				// Insert new bookmark
				$bookmark_id = DB::table('customer_bookmarks')->insertGetId([
					'cust_id' => $customer_id,
					'question_id' => $question_id,
					'created_at' => now(),
					'updated_at' => now()
				]);

				$myarray['result'] = ['bookmark_id' => $bookmark_id];
				$myarray['message'] = 'Bookmark added successfully.';
				$myarray['status'] = 1;
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

	/**
	 * Remove bookmark for customer
	 * POST /api/customer/bookmarks/remove
	 * Request: { customer_id: int, question_id: int }
	 */
	public function removeBookmark(Request $request)
	{
		$common = new CommanController;
		$authenticate = $this->_authenticate;
		$apiauthenticate = ($request->header('AUTHENTICATE')) ? ($request->header('AUTHENTICATE')) : 1;
		$langId = ($request->header('langId')) ? ($request->header('langId')) : 1;

		if (in_array($apiauthenticate, $authenticate)) {
			$validator = Validator::make($request->all(), [
				'customer_id' => 'required',
				'question_id' => 'required'
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
			$question_id = $request->question_id;

			// Delete bookmark
			$deleted = DB::table('customer_bookmarks')
				->where('cust_id', $customer_id)
				->where('question_id', $question_id)
				->delete();

			if ($deleted > 0) {
				$myarray['result'] = ['deleted' => true];
				$myarray['message'] = 'Bookmark removed successfully.';
				$myarray['status'] = 1;
			} else {
				$myarray['result'] = ['deleted' => false];
				$myarray['message'] = 'Bookmark not found.';
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
}
