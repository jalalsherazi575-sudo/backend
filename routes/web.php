<?php
/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
| Define the routes for your Frontend pages here
|
*/

#cache clear
Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
});

Route::get('/config-cache', function() {
    Artisan::call('config:cache');
});

Route::get('/', [
    'as' => 'home', 'uses' => 'FrontendController@home'
]);

Route::get('cronPlan','CommanController@cronPlan');
Route::get('cronMissionExpired','CommanController@cronMissionExpired');

Route::get('comman/get-state-list','CommanController@getStateList');
Route::get('comman/get-city-list','CommanController@getCityList');

Route::get('comman/get-beacon-list','CommanController@getBeaconList');

Route::get('privacy_policy', 'FrontendController@privacy_policypage');
Route::get('delete_account', 'FrontendController@delete_account');


Route::get('about_us', 'FrontendController@about_us');
Route::get('terms', 'FrontendController@terms');
Route::get('about_us_vendor', 'FrontendController@about_us_vendor');
Route::get('terms', 'FrontendController@terms');
Route::get('about_us_customer', 'FrontendController@about_us_customer');
Route::get('terms_customer', 'FrontendController@terms_customer');

Route::get('privacy_policy_vendor', 'FrontendController@privacy_policy_vendor');
Route::get('faqs_vendor', 'FrontendController@faq_vendor');
Route::get('privacypolicy', 'FrontendController@privacy_policy');
Route::get('faqs_customer', 'FrontendController@faq_customer');

Route::get('contact_us_customer', 'FrontendController@contact_us_customer');
Route::post('contact_us_customer', 'FrontendController@submit_contact_us');

Route::get('feedback_customer', 'FrontendController@feedback_customer');
Route::post('feedback_customer', 'FrontendController@submit_feedback_customer');




/* Vendor Reset Password */
Route::get('resetpassword/{id}', 'FrontendController@getReset');
Route::post('resetpassword', 'FrontendController@postReset');
Route::get('resetpassword', 'FrontendController@sucess');


/* Customer Reset Password */
Route::get('resetpasswordcustomer/{id}', 'FrontendController@getResetCustomer');
Route::post('resetpasswordcustomer', 'FrontendController@postResetCustomer');

Route::get('comman/get-questionoption-list','CommanController@getQuestionOptions');

Route::get('comman/get-questiontype-list','CommanController@getQuestionType');

Route::get('exportconsumermanagerdata', 'ConsumerManagerController@exportconsumermanagerreport');
Route::get('exportconsumerforumdata', 'ConsumerForumController@exportconsumerforumreport');


/* App Customer Reset Password */
Route::get('changePassword/{userid}', 'FrontendController@changePassword');
Route::post('changePassword', 'FrontendController@postChangePassword');
Route::get('appresetpasswordcustomer/{token}', 'FrontendController@getAppResetCustomer');
Route::post('appresetpasswordcustomer', 'FrontendController@postAppResetCustomer');

/*Payment before customer*/
Route::get('customerupdatepayment/{userid}/{url}', 'FrontendController@customerupdatepayment');
route::post('customerupdatepayment', 'FrontendController@addCustomerUpdatePayment');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Route group for Backend prefixed with "admin".
| To Enable Authentication just remove the comment from Admin Middleware
|
*/

Route::group([
    'prefix' => 'admin',
 // 'middleware' => 'admin'
], function(){

// Auth::routes(); // Commented out - causes conflict with custom login at /login


    // Dashboard
    //----------------------------------
    Route::get('/', [
        'as' => 'admin.dashboard', 'uses' => 'DashboardController@index'
    ]);

    Route::resource('users', 'UsersController');
    Route::get('users/create', 'UsersController@create');
    Route::post('users/create', 'UsersController@postCreate');
    Route::get('users/edit/{id}', 'UsersController@getEdit');
    Route::post('users/edit/{id}', 'UsersController@postEdit');
    Route::get('users/delete/{id}', 'UsersController@Delete');

    Route::resource('usersroles', 'UsersRolesController');
	Route::get('usersroles/create', 'UsersRolesController@create');
	Route::post('usersroles/create', 'UsersRolesController@postCreate');
	Route::get('usersroles/edit/{id}', 'UsersRolesController@getEdit');
	Route::get('usersroles/view/{id}', 'UsersRolesController@getView');
	Route::post('usersroles/edit/{id}', 'UsersRolesController@postEdit');
	Route::get('usersroles/delete/{id}', 'UsersRolesController@Delete');

	/* Level Management */
	
	Route::get('levelmanagement', [
        'as' => 'admin.levelmanagement', 'uses' => 'LevelManagementController@index'
    ]);
	
	Route::get('levelmanagement/add', [
        'as' => 'admin.levelmanagement', 'uses' => 'LevelManagementController@add'
    ]);
	
	Route::post('levelmanagement/add', [
        'as' => 'admin.levelmanagement', 'uses' => 'LevelManagementController@postCreate'
    ]);
	
	Route::get('levelmanagement/edit/{id}', 'LevelManagementController@getEdit');
	Route::put('levelmanagement/edit/{id}', 'LevelManagementController@postEdit');
	Route::get('levelmanagement/status/{status}/{id}', 'LevelManagementController@Status');
	Route::get('levelmanagement/delete/{id}', 'LevelManagementController@Delete');
	Route::post('levelmanagement/deleteall', 'LevelManagementController@Deleteall');
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
	Route::get('levelmanagement/assignlession/{id}', 'LevelManagementController@assignLession');
	Route::post('levelmanagement/assignlession/{id}', 'LevelManagementController@postAssignLession');

	/* Lession Management */
	
	Route::get('lessionmanagement', [
        'as' => 'admin.lessionmanagement', 'uses' => 'LessionManagementController@index'
    ]);
	
	Route::get('lessionmanagement/add', [
        'as' => 'admin.lessionmanagement', 'uses' => 'LessionManagementController@add'
    ]);
	
	Route::post('lessionmanagement/add', [
        'as' => 'admin.lessionmanagement', 'uses' => 'LessionManagementController@postCreate'
    ]);
	
	Route::get('lessionmanagement/edit/{id}', 'LessionManagementController@getEdit');
	Route::post('lessionmanagement/edit/{id}', 'LessionManagementController@postEdit');
	Route::get('lessionmanagement/status/{status}/{id}', 'LessionManagementController@Status');
	Route::get('lessionmanagement/delete/{id}', 'LessionManagementController@Delete');
	Route::post('lessionmanagement/deleteall', 'LessionManagementController@Deleteall');

	//subscription Plan Package  routes
    Route::get('planpackage', 'PlanPackageController@index');
    Route::get('planpackage/create', 'PlanPackageController@create');
    Route::post('planpackage/store', 'PlanPackageController@store');
    Route::get('planpackage/edit/{id}', 'PlanPackageController@edit');
    Route::post('planpackage/update/{id}', 'PlanPackageController@update');
     Route::post('planpackage_delete', 'PlanPackageController@destroy');
    Route::get('planpackage/status/{status}/{id}', 'PlanPackageController@statusUpdate');
    Route::get('planpackage/assingsubject/{id}', 'PlanPackageController@assingsubject');
    Route::post('planpackage/assingsubjectAdd', 'PlanPackageController@assingsubjectAdd');
    Route::post('remove_subject_plan', 'PlanPackageController@remove_subject_plan');

	/* OnBoard Management */
	
	Route::get('appnoticeboard', [
        'as' => 'admin.appnoticeboard', 'uses' => 'AppNoticeBoardController@index'
    ]);
	
	Route::get('appnoticeboard/add', [
        'as' => 'admin.appnoticeboard', 'uses' => 'AppNoticeBoardController@add'
    ]);
	
	Route::post('appnoticeboard/add', [
        'as' => 'admin.appnoticeboard', 'uses' => 'AppNoticeBoardController@postCreate'
    ]);
	
	Route::get('appnoticeboard/edit/{id}', 'AppNoticeBoardController@getEdit');
	Route::post('appnoticeboard/edit/{id}', 'AppNoticeBoardController@postEdit');
	Route::get('appnoticeboard/status/{status}/{id}', 'AppNoticeBoardController@Status');
	Route::get('appnoticeboard/delete/{id}', 'AppNoticeBoardController@Delete');
	Route::post('appnoticeboard/deleteall', 'AppNoticeBoardController@Deleteall');
	Route::get('appnoticeboard/deleteImages/{id}', 'AppNoticeBoardController@updatePhoto');

    /* Questions */
	
	Route::get('questions', [
        'as' => 'admin.questions', 'uses' => 'QuestionsController@index'
    ]);
	
	Route::get('questions/add', [
        'as' => 'admin.questions', 'uses' => 'QuestionsController@add'
    ]);
	
	Route::post('questions/add', [
        'as' => 'admin.questions', 'uses' => 'QuestionsController@postCreate'
    ]);
	
	Route::get('get-subjects/{category_id}', 'QuestionsController@getSubjects');
	Route::get('get-topics/{subject_id}', 'QuestionsController@getTopics');


	Route::get('questions/edit/{id}', 'QuestionsController@getEdit');
	Route::post('questions/edit/{id}', 'QuestionsController@postEdit');
	Route::get('questions/status/{status}/{id}', 'QuestionsController@Status');
	Route::get('questions/delete/{id}', 'QuestionsController@Delete');
	Route::post('questions/deleteall', 'QuestionsController@Deleteall');
	Route::get('questions/deleteImages/{id}', 'QuestionsController@deleteQuestionLogo');
	Route::get('questions/topicImage/{id}', 'QuestionsController@deleteTopicLogo');
	Route::get('questions/deleteAudioSelect/{id}', 'QuestionsController@deleteAudioSelect');
	Route::get('questions/removeOption/{id}', 'QuestionsController@deleteQuestion');
	Route::get('questions/removeOptionWord/{id}', 'QuestionsController@deleteQuestionWordOne');
	Route::get('questions/removeOptionWordTwo/{id}', 'QuestionsController@deleteQuestionWordTwo');
	Route::get('questions/deleteVocal/{id}', 'QuestionsController@deleteQuestionVocal');
	Route::get('questions/deleteVideo/{id}', 'QuestionsController@deleteQuestionVideo');
	Route::get('questions/deleteAudio/{id}', 'QuestionsController@deleteQuestionAudio');
	Route::get('questions/removeOptionFillBlank/{id}', 'QuestionsController@deleteOptionFillBlank');
	Route::get('questions/removeOptionAl/{id}', 'QuestionsController@deleteremoveOptionAl');
	Route::get('questions/deletePronounciationFile/{id}', 'QuestionsController@deleteQuestionPronounciationFile');
	Route::get('questions/removeOptionWordMatch/{id}', 'QuestionsController@deleteremoveOptionWordMatch');
	Route::get('questions/removeQuestionImageFile/{id}', 'QuestionsController@deleteQuestionImageFile');
	Route::get('questions/getSubject/{id}', 'QuestionsController@getSubject');
	/*Route::get('questions/getTopic/{id}', 'QuestionsController@getTopic');*/
	Route::get('questions/getTopic', 'QuestionsController@getTopic');
	Route::get('questions/getselectedtopic/{ids}', 'QuestionsController@getSelectedTopic');
	
	Route::get('questions/comments/{questionId}', 'QuestionsController@getComments');
	Route::get('questions/get-child-comments/{parentId}', 'QuestionsController@getChildComments')->name('childcomment');
	Route::get('comments/delete/{id}', 'QuestionsController@commentsDelete');

	Route::post('importQuestions', 'QuestionsController@importQuestions')->name('importQuestions');
	Route::get('admin/questions/data', 'QuestionsController@getQuestionsData')->name('admin.questions.data');

	/* GeneralMessage */
	
	Route::get('generalmessage', [
        'as' => 'admin.generalmessage', 'uses' => 'GeneralMessageController@index'
    ]);
	
	Route::get('generalmessage/add', [
        'as' => 'admin.generalmessage', 'uses' => 'GeneralMessageController@add'
    ]);
	
	Route::post('generalmessage/add', [
        'as' => 'admin.generalmessage', 'uses' => 'GeneralMessageController@postCreate'
    ]);
	
	Route::get('generalmessage/edit/{id}', 'GeneralMessageController@getEdit');
	Route::post('generalmessage/edit/{id}', 'GeneralMessageController@postEdit');
	Route::get('generalmessage/status/{status}/{id}', 'GeneralMessageController@Status');
	Route::get('generalmessage/delete/{id}', 'GeneralMessageController@Delete');
	Route::post('generalmessage/deleteall', 'GeneralMessageController@Deleteall');
	
	/*Email Template*/
	/*Email */
      Route::get('email','EmailTemplateController@index')->name('email');
      Route::get('email/create','EmailTemplateController@create')->name('email.create');
      Route::Post('email/store', 'EmailTemplateController@store')->name('email.store');
      Route::get('email/edit/{id}', 'EmailTemplateController@edit')->name('email.edit');
      Route::put('email/update/{id}','EmailTemplateController@update')->name('email.update');
      Route::get('email/{id}','EmailTemplateController@show')->name('email.show');
     Route::get('email/delete/{id}', 'EmailTemplateController@destroy')->name('email.delete');

     /*Default Configuration */
      Route::get('defaultconfiguration','DefaultConfigurationController@index')->name('defaultconfiguration');
      Route::Post('defaultconfiguration/store','DefaultConfigurationController@store')->name('defaultconfiguration.store');
	
	/* Version */
	
	Route::get('version', [
        'as' => 'admin.version', 'uses' => 'VersionController@index'
    ]);
	
	Route::get('version/add', [
        'as' => 'admin.version', 'uses' => 'VersionController@add'
    ]);
	
	Route::post('version/add', [
        'as' => 'admin.version', 'uses' => 'VersionController@postCreate'
    ]);
	
	Route::get('version/edit/{id}', 'VersionController@getEdit');
	Route::post('version/edit/{id}', 'VersionController@postEdit');
	Route::get('version/status/{status}/{id}', 'VersionController@Status');
	Route::get('version/delete/{id}', 'VersionController@Delete');
	Route::post('version/deleteall', 'VersionController@Deleteall');
	
	/* Customer */

	//customers routes
	Route::get('customers', 'CustomerRegisterController@index');
	Route::get('customer/create', 'CustomerRegisterController@create');
	Route::post('customer/store', 'CustomerRegisterController@store');
	Route::get('customer/editr/{id}', 'CustomerRegisterController@edit');
	Route::post('customer/update/{id}', 'CustomerRegisterController@update');
	Route::get('customer/deleter/{id}', 'CustomerRegisterController@destroy');
    Route::get('customer/statusr/{status}/{id}', 'CustomerRegisterController@statusUpdate');
    Route::get('customer/addresses/{id}', 'CustomerRegisterController@getAddressList');
	Route::get('customer/exportCSV', 'CustomerRegisterController@exportCSV');
	Route::get('customer/planpackage/{id}', 'CustomerRegisterController@UserSubscriptionPlanPackageList');

	Route::get('/reports', 'CustomerRegisterController@allUserSubscriptionPlanPackageList');
	
    Route::get('customer/planpackage/delete/{customerId}/{id}', 'CustomerRegisterController@deletePlanPackage');

    //delete plan transaction package
    Route::get('customer/planpackage/deleteTransactionPackage/{customerId}/{id}','CustomerRegisterController@deleteTransactionPackage');

    Route::get('customer/planpackage/exportCSV/{id}', 'CustomerRegisterController@exportUserSubscriptionPlanPackageList');
    Route::get('customer/assignplanpackage/{id}', 'CustomerRegisterController@AssignPlanPackage');
    Route::post('customer/assignplanpackage/{id}', 'CustomerRegisterController@PostAssignPlanPackage');
    Route::get('customer/planpackagedetail/{id}', 'CustomerRegisterController@getPackageDetail');
    Route::get('customer/exam/{customerId}', 'CustomerRegisterController@getExam');
    Route::get('customer/exam/delete/{customerId}', 'CustomerRegisterController@deleteExam');
    Route::get('customer/exam/questions/{examid}', 'CustomerRegisterController@examQuestion');
	Route::get('/customer/assignplanpackage/getSubjects/{categoryId}', 'CustomerRegisterController@getSubjects')->name('assignplanpackage.getSubjects');
	 Route::get('/customer/assignplanpackage/getPlans/{subjectId}', 'CustomerRegisterController@getPlans')->name('assignplanpackage.getPlans');
	/*Route::get('customer', [
        'as' => 'admin.customer', 'uses' => 'CustomerController@index'
    ]);
	
	Route::get('customer/add', [
        'as' => 'admin.customer', 'uses' => 'CustomerController@add'
    ]);
	
	Route::post('customer/add', [
        'as' => 'admin.customer', 'uses' => 'CustomerController@postCreate'
    ]);
	
	Route::get('customer/edit/{id}', 'CustomerController@getEdit');
	Route::post('customer/edit/{id}', 'CustomerController@postEdit');
	Route::get('customer/status/{status}/{id}', 'CustomerController@Status');
	Route::get('customer/delete/{id}', 'CustomerController@Delete');
	Route::post('customer/deleteall', 'CustomerController@Deleteall');

	Route::get('customer/product/{id}', 'CustomerController@getCustomerProduct');
	Route::get('customer/product/edit/{id}', 'CustomerController@getEdit');
	Route::post('customer/product/edit/{id}', 'CustomerController@postEdit');

	Route::get('customer/deleteIdProof/{id}', 'CustomerController@deleteIdProof');
    
    Route::get('customer/verify/{verify}/{id}', 'CustomerController@Verify');*/
    
	

	
	
	
	/* Setting */
	
	Route::get('setting', [
        'as' => 'admin.setting', 'uses' => 'SettingController@index'
    ]);
	
	Route::get('setting/add', [
        'as' => 'admin.setting', 'uses' => 'SettingController@add'
    ]);
	
	Route::post('setting/add', [
        'as' => 'admin.setting', 'uses' => 'SettingController@postCreate'
    ]);
	
	Route::get('setting/edit/{id}', 'SettingController@getEdit');
	Route::post('setting/edit/{id}', 'SettingController@postEdit');
	Route::get('setting/status/{status}/{id}', 'SettingController@Status');
	Route::get('setting/delete/{id}', 'SettingController@Delete');
	Route::post('setting/deleteall', 'SettingController@Deleteall');


	


	/* Languages */
	
	Route::get('languages', [
        'as' => 'admin.languages', 'uses' => 'LanguageController@index'
    ]);
	
	Route::get('languages/add', [
        'as' => 'admin.languages', 'uses' => 'LanguageController@add'
    ]);
	
	Route::post('languages/add', [
        'as' => 'admin.languages', 'uses' => 'LanguageController@postCreate'
    ]);
	
	Route::get('languages/edit/{id}', 'LanguageController@getEdit');
	Route::post('languages/edit/{id}', 'LanguageController@postEdit');
	Route::get('languages/status/{status}/{id}', 'LanguageController@Status');
	Route::get('languages/delete/{id}', 'LanguageController@Delete');
	Route::post('languages/deleteall', 'LanguageController@Deleteall');

	/* Country */
	
	Route::get('country', [
        'as' => 'admin.country', 'uses' => 'CountryController@index'
    ]);
	
	Route::get('country/add', [
        'as' => 'admin.country', 'uses' => 'CountryController@add'
    ]);
	
	Route::post('country/add', [
        'as' => 'admin.country', 'uses' => 'CountryController@postCreate'
    ]);
	
	Route::get('country/edit/{id}', 'CountryController@getEdit');
	Route::post('country/edit/{id}', 'CountryController@postEdit');
	Route::get('country/status/{status}/{id}', 'CountryController@Status');
	Route::get('country/delete/{id}', 'CountryController@Delete');
	Route::post('country/deleteall', 'CountryController@Deleteall');

    /* State */
	
	Route::get('state', [
        'as' => 'admin.state', 'uses' => 'StateController@index'
    ]);
	
	Route::get('state/add', [
        'as' => 'admin.state', 'uses' => 'StateController@add'
    ]);
	
	Route::post('state/add', [
        'as' => 'admin.state', 'uses' => 'StateController@postCreate'
    ]);
	
	Route::get('state/edit/{id}', 'StateController@getEdit');
	Route::post('state/edit/{id}', 'StateController@postEdit');
	Route::get('state/status/{status}/{id}', 'StateController@Status');
	Route::get('state/delete/{id}', 'StateController@Delete');
	Route::post('state/deleteall', 'StateController@Deleteall');

	Route::get('state/importcsv', [
        'as' => 'admin.state', 'uses' => 'StateController@importcsv'
    ]);
	
	Route::post('state/importcsv', [
        'as' => 'admin.state', 'uses' => 'StateController@postImportCsv'
    ]);

	/* City */
	
	Route::get('city', [
        'as' => 'admin.city', 'uses' => 'CityController@index'
    ]);
	
	Route::any('city', [
        'as' => 'admin.city', 'uses' => 'CityController@index'
    ]);

	Route::get('city/add', [
        'as' => 'admin.city', 'uses' => 'CityController@add'
    ]);
	
	Route::post('city/add', [
        'as' => 'admin.city', 'uses' => 'CityController@postCreate'
    ]);
	
	Route::get('city/edit/{id}', 'CityController@getEdit');
	Route::post('city/edit/{id}', 'CityController@postEdit');
	Route::get('city/status/{status}/{id}', 'CityController@Status');
	Route::get('city/delete/{id}', 'CityController@Delete');
	Route::post('city/deleteall', 'CityController@Deleteall');

	Route::get('city/importcsv', [
        'as' => 'admin.city', 'uses' => 'CityController@importcsv'
    ]);
	
	Route::post('city/importcsv', [
        'as' => 'admin.city', 'uses' => 'CityController@postImportCsv'
    ]);

	
	/* Cms Pages */
	
	Route::get('cmspages', [
        'as' => 'admin.cmspages', 'uses' => 'CmsPagesController@index'
    ]);
	
	Route::get('cmspages/add', [
        'as' => 'admin.cmspages', 'uses' => 'CmsPagesController@add'
    ]);
	
	Route::post('cmspages/add', [
        'as' => 'admin.cmspages', 'uses' => 'CmsPagesController@postCreate'
    ]);
	
	Route::get('cmspages/edit/{id}', 'CmsPagesController@getEdit');
	Route::post('cmspages/edit/{id}', 'CmsPagesController@postEdit');
	Route::get('cmspages/status/{status}/{id}', 'CmsPagesController@Status');
	Route::get('cmspages/delete/{id}', 'CmsPagesController@Delete');
	Route::post('cmspages/deleteall', 'CmsPagesController@Deleteall');

	/* Subject Module */
	
	Route::get('subject', [
        'as' => 'admin.subject', 'uses' => 'SubjectController@index'
    ]);
	Route::get('subject/add', [
        'as' => 'admin.subject', 'uses' => 'SubjectController@add'
    ]);
	Route::post('subject/add', [
        'as' => 'admin.subject', 'uses' => 'SubjectController@postCreate'
    ]);
    Route::get('subject/edit/{id}', 'SubjectController@getEdit');
	Route::post('subject/edit/{id}', 'SubjectController@postEdit');
	Route::get('subject/delete/{id}', 'SubjectController@Delete');
	Route::get('subject/plans/{id}', 'SubjectController@getPlan');
	Route::get('plan/addplan/{id}', 'SubjectController@addplan');
	Route::post('plan/store', 'SubjectController@postPlan');
	Route::get('plan/delete/{id}', 'SubjectController@DeletePlan');
	Route::get('plan/edit/{id}', 'SubjectController@getEditPlan');
	Route::post('plan/update/{id}', 'SubjectController@postUpdatePlan');
	/* Subject Module */
	
	Route::get('topics', [
        'as' => 'admin.topics', 'uses' => 'TopicsController@index'
    ]);
	Route::get('topics/add', [
        'as' => 'admin.topics', 'uses' => 'TopicsController@add'
    ]);
	Route::post('topics/add', [
        'as' => 'admin.topics', 'uses' => 'TopicsController@postCreate'
    ]);
    Route::get('topics/edit/{id}', 'TopicsController@getEdit');
	Route::post('topics/edit/{id}', 'TopicsController@postEdit');
	Route::get('topics/delete/{id}', 'TopicsController@Delete');
    Route::get('/get-subjects/{categoryId}', 'TopicsController@getSubjects')->name('topics.cat');

    /*Words*/

    Route::get('unnecessarywords', 'UnnecessaryWordsController@index')->name('unnecessarywords');
    Route::get('unnecessarywords/add', 'UnnecessaryWordsController@create')->name('unnecessarywords.add');
    Route::Post('unnecessarywords/add', 'UnnecessaryWordsController@store')->name('unnecessarywords.store');
    Route::get('unnecessarywords/edit/{id}', 'UnnecessaryWordsController@edit')->name('unnecessarywords.edit');
    Route::post('unnecessarywords/update/{id}', 'UnnecessaryWordsController@update')->name('unnecessarywords.update');
    Route::get('unnecessarywords/delete/{id}', 'UnnecessaryWordsController@destroy');
    //Banner
	Route::get('banner', [
        'as' => 'admin.banner', 'uses' => 'BannerController@index'
    ]);
    Route::get('banner/add', [
        'as' => 'admin.banner', 'uses' => 'BannerController@add'
    ]);
    Route::post('banner/add', [
        'as' => 'admin.banner', 'uses' => 'BannerController@postCreate'
    ]);
    Route::get('banner/edit/{id}', 'BannerController@getEdit');
    Route::put('banner/edit/{id}', 'BannerController@postEdit');
    Route::get('banner/delete/{id}', 'BannerController@Delete');
    // Settings
    //----------------------------------
    Route::group(['prefix' => 'settings'], function(){

        Route::get('/social', [
            'as' => 'admin.settings.index', 'uses' => 'SettingsController@index'
        ]);

        Route::post('/social', [
            'as' => 'admin.settings.social', 'uses' => 'SettingsController@postSocial'
        ]);

        Route::group(['prefix' => 'mail'], function(){

            Route::get('/', [
                'as' => 'admin.settings.mail.index', 'uses' => 'SettingsController@mail'
            ]);

            Route::post('/', [
                'as' => 'admin.settings.mail.post', 'uses' => 'SettingsController@postMail'
            ]);

            Route::post('/send-test-email', [
                'as' => 'admin.settings.mail.send', 'uses' => 'SettingsController@sendTestMail'
            ]);
        });

    });
});

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
| Guest routes cannot be accessed if the user is already logged in.
| He will be redirected to '/" if he's logged in.
|
*/

	

Route::group(['middleware' => ['guest']], function(){

    Route::get('login', [
        'as' => 'login', 'uses' => 'AuthController@login'
    ]);

    Route::get('register', [
        'as' => 'register', 'uses' => 'AuthController@register'
    ]);

    Route::post('login', [
        'as' => 'login.post', 'uses' => 'AuthController@postLogin'
    ]);

    Route::get('forgot-password', [
        'as' => 'forgot-password.index', 'uses' => 'ForgotPasswordController@getEmail'
    ]);

    Route::post('/forgot-password', [
        'as' => 'send-reset-link', 'uses' => 'ForgotPasswordController@postEmail'
    ]);

    Route::get('/password/reset/{token}', [
        'as' => 'password.reset', 'uses' => 'ForgotPasswordController@GetReset'
    ]);

    Route::post('/password/reset', [
        'as' => 'reset.password.post', 'uses' => 'ForgotPasswordController@postReset'
    ]);

    Route::get('auth/{provider}', 'AuthController@redirectToProvider');

    Route::get('auth/{provider}/callback', 'AuthController@handleProviderCallback');
    /*Editor*/
    Route::Post('/ckeditor/upload', 'CkeditorController@upload')->name('ckeditor.upload');
});

Route::get('logout', [
    'as' => 'logout', 'uses' => 'AuthController@logout'
]);

Route::get('install', [
    'as' => 'logout', 'uses' => 'AuthController@logout'
]);


/*payment*/
Route::post('/payment/notification', 'PaymentController@handleNotification');
Route::get('/payment/success', 'PaymentController@handleSuccessCallback');
Route::get('/payment/error', 'PaymentController@handleErrorCallback');

Route::get('/checktransaction', 'PaymentController@checktransaction');