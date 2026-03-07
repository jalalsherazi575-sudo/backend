<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('logout', 'API\ApiController@customerLogout');

Route::post('getMessages', 'API\ApiController@getMessages');
Route::post('dashboardSubject', 'API\ApiController@dashboardSubject');
Route::post('trainingSubjectsList', 'API\ApiController@trainingSubjectsList');
Route::post('dashboardCategory', 'API\ApiController@dashboardCategory');
Route::post('trainingCategoryList', 'API\ApiController@trainingCategoryList');
Route::post('dashboardCategoryV1', 'API\ApiController@dashboardCategoryV1');
Route::post('topics', 'API\ApiController@topics');
Route::post('questions', 'API\ApiController@questions');
Route::post('learningexam', 'API\ApiController@learningExam');
Route::post('bannerList', 'API\ApiController@bannerList');
Route::post('checkversion', 'API\ApiController@checkVersion');
Route::post('insertDeviceToken', 'API\ApiController@InsertDeviceToken');
Route::post('listQuestion', 'API\ApiController@listQuestion');


/*Comment Module*/
Route::post('getComments','API\ApiController@getComments');
Route::post('createComments', 'API\ApiController@createComments');
Route::post('deleteComment','API\ApiController@deleteComment');
Route::post('getAllComments','API\ApiController@getAllComments');
Route::post('getCommentsCount','API\ApiController@getAllCommentsCount');


/*Customer Prefix*/
Route::group(['prefix' => 'customer',], function(){
	Route::post('/login', 'API\CustomerApiController@login');
	Route::post('/logout', 'API\CustomerApiController@logout');
	Route::post('/register', 'API\CustomerApiController@register');
	Route::post('/profileUpdate', 'API\CustomerApiController@profileUpdate');
	Route::post('/deleteUser', 'API\CustomerApiController@deleteUser');
	Route::post('/forgotPassword', 'API\CustomerApiController@forgotPassword');
	Route::post('/resetPassword', 'API\CustomerApiController@resetPassword');
	Route::post('/changePassword', 'API\CustomerApiController@changePassword');
	Route::post('customerPlanHistory', 'API\CustomerApiController@customerPlanHistory');
	Route::post('customerPurchase', 'API\CustomerApiController@customerPurchase');
	Route::get('successfully_payment','API\CustomerApiController@successfully_payment');
	Route::post('search', 'API\CustomerApiController@search');
	/*Exam*/
	Route::post('getexam','API\ExamApiController@getAllExam');
	Route::post('createexam','API\ExamApiController@createExam');
	Route::post('examsubmit','API\ExamApiController@custExamSubmit');
	Route::get('exam/{id}','API\ExamApiController@show');
	Route::post('examsummary','API\ExamApiController@examSummary');
	Route::post('focusareas','API\ExamApiController@getFocusAreas');
	Route::post('performance','API\ExamApiController@getPerformanceStats');

	/*Bookmarks*/
	Route::post('bookmarks','API\BookmarkApiController@getBookmarks');
	Route::post('bookmarks/add','API\BookmarkApiController@addBookmark');
	Route::post('bookmarks/remove','API\BookmarkApiController@removeBookmark');

	Route::post('getcategory','API\CommonApiController@getAllCategory');
	Route::post('category','API\CommonApiController@categorySubject');
	Route::post('category/subject','API\CommonApiController@subjectTopics');

	Route::get('subject/{id}','API\CommonApiController@subjectPlan');
	Route::post('allsearch','API\CommonApiController@searchQuestions');

});




/*Old */
/*Route::post('customerDetails', 'API\ApiController@customerDetails');
Route::post('levelList', 'API\ApiController@levelList');
Route::post('deleteNotification', 'API\ApiController@deleteNotification');
Route::post('notificationList', 'API\ApiController@notificationList');
Route::post('noticeBoardList', 'API\ApiController@noticeBoardList');
Route::post('readNotification', 'API\ApiController@readNotification');
Route::post('SubscriptionPlanList', 'API\ApiController@PlanList');
Route::post('customerSelectPlan', 'API\ApiController@customerSelectPlan');
Route::post('customerPlanHistory', 'API\ApiController@customerPlanHistory');*/
/*Route::group(['middleware' => 'auth:api'], function(){
Route::post('details', 'API\ApiController@details');
});*/

/*Route::post('/resendVerificationCode', 'API\CustomerApiController@resendVerificationCode');
Route::post('/verifyCustomerCode', 'API\CustomerApiController@verifyCustomerCode');
Route::post('/forgotPassword', 'API\CustomerApiController@forgotPassword');
Route::post('/resetPassword', 'API\CustomerApiController@resetPassword');
Route::post('/verifyNumber', 'API\CustomerApiController@verifyNumber');
Route::post('/confirmOTP', 'API\CustomerApiController@confirmOTP');
Route::post('/chooseProfileImage', 'API\CustomerApiController@chooseProfileImage');
Route::post('/getUserDetails', 'API\CustomerApiController@getUserDetails'); */
