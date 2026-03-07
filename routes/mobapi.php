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

Route::post('login', 'API\MobApiController@login');
Route::post('deleteuser', 'API\MobApiController@deleteuser');
Route::post('businesscategory', 'API\MobApiController@getbusinesscategory');
Route::post('idprooftype', 'API\MobApiController@getidprooftype');
Route::post('checkversion', 'API\MobApiController@checkVersion');
Route::post('forgotpassword', 'API\MobApiController@forgotpassword');
Route::post('servicetype', 'API\MobApiController@getservicetype');
Route::post('customerforgotpassword', 'API\MobApiController@forgotpasswordCustomer');

Route::post('register', 'API\MobApiController@register');
Route::post('customerregister', 'API\MobApiController@customerregister');
Route::post('customerlogin', 'API\MobApiController@customerlogin');
Route::post('favouritevendor', 'API\MobApiController@favouritevendor');
Route::post('serviceproviderList', 'API\MobApiController@providerList');
Route::post('vendorDetails', 'API\MobApiController@vendorDetails');
Route::post('getMessages', 'API\MobApiController@getMessages');
Route::post('addVendorAchievement', 'API\MobApiController@addVendorAchievement');
Route::post('addVendorPortFolio', 'API\MobApiController@addVendorPortFolio');
Route::post('favouriteVendorList', 'API\MobApiController@favouriteVendorList');
Route::post('addVendorProof', 'API\MobApiController@addVendorProof');
Route::post('addVendorServices', 'API\MobApiController@addVendorServices');
Route::post('getVendorServices', 'API\MobApiController@getVendorServices');
Route::post('addVendorSubServices', 'API\MobApiController@addVendorSubServices');
Route::post('getVendorSubServices', 'API\MobApiController@getVendorSubServices');
Route::post('postLead', 'API\MobApiController@postLead');
Route::post('addEditLocation', 'API\MobApiController@addEditLocation');
Route::post('getCustomerLocation', 'API\MobApiController@getCustomerLocation');
Route::post('deleteCustomerLocation', 'API\MobApiController@deleteCustomerLocation');
Route::post('pendingBooking', 'API\MobApiController@pendingBooking');
Route::post('awardedBooking', 'API\MobApiController@awardedBooking');
Route::post('leadDetail', 'API\MobApiController@leadDetail');
Route::post('assignLeadtoVendor', 'API\MobApiController@assignLeadtoVendor');
Route::post('deleteVendorIdProof', 'API\MobApiController@deleteVendorIdProof');
Route::post('LeadComplete', 'API\MobApiController@LeadComplete');
Route::post('RateType', 'API\MobApiController@RateType');
Route::post('leadRate', 'API\MobApiController@leadRate');
Route::post('HowDidYouKnow', 'API\MobApiController@HowDidYouKnow');
Route::post('pendingLeadRequest', 'API\MobApiController@pendingLeadRequest');
Route::post('deleteVendorService', 'API\MobApiController@deleteVendorService');
Route::post('deleteVendorSubServices', 'API\MobApiController@deleteVendorSubServices');
Route::post('LeadCancel', 'API\MobApiController@LeadCancel');
Route::post('ongoingLeads', 'API\MobApiController@ongoingLeads');
Route::post('completedLeads', 'API\MobApiController@completedLeads');
Route::post('deleteLeadFile', 'API\MobApiController@deleteLeadFile');
Route::post('Languages', 'API\MobApiController@Languages');
Route::post('chat', 'API\MobApiController@chat');
Route::post('chatList', 'API\MobApiController@chatList');
Route::post('chatUserList', 'API\MobApiController@chatUserList');
Route::post('deleteAchievement', 'API\MobApiController@deleteAchievement');
Route::post('deletePortFolio', 'API\MobApiController@deletePortFolio');
Route::post('productcategory', 'API\MobApiController@getproductcategory');
Route::post('addEditProduct', 'API\MobApiController@addEditProduct');
Route::post('productList', 'API\MobApiController@productList');
Route::post('favouriteProduct', 'API\MobApiController@favouriteProduct');
Route::post('favouriteProductList', 'API\MobApiController@favouriteProductList');
Route::post('productConditionType', 'API\MobApiController@productConditionType');
Route::post('productMarkAsSold', 'API\MobApiController@productMarkAsSold');
Route::post('productDelete', 'API\MobApiController@productDelete');
Route::post('productDetails', 'API\MobApiController@productDetails');
Route::post('notificationList', 'API\MobApiController@notificationList');
Route::post('Report', 'API\MobApiController@Report');
Route::post('locationList', 'API\MobApiController@locationList');
Route::post('viewProduct', 'API\MobApiController@viewProduct');
Route::post('countryList', 'API\MobApiController@countryList');
Route::post('stateList', 'API\MobApiController@stateList');
Route::post('cityList', 'API\MobApiController@cityList');
Route::post('readNotification', 'API\MobApiController@readNotification');
Route::post('reportProduct', 'API\MobApiController@reportProduct');
Route::post('addVendorLocation', 'API\MobApiController@addVendorLocation');
Route::post('UpdateVendorStatus', 'API\MobApiController@UpdateVendorStatus');
Route::post('deleteChat', 'API\MobApiController@deleteChat');
Route::post('UnreadTotalCount', 'API\MobApiController@UnreadTotalCount');
Route::post('bankList', 'API\MobApiController@bankList');
Route::post('addEditVendorPaymentType', 'API\MobApiController@addEditVendorPaymentType');
Route::post('deleteVendorPaymentType', 'API\MobApiController@deleteVendorPaymentType');
Route::post('changePassword', 'API\MobApiController@changePassword');
Route::post('reSendVerificationCode', 'API\MobApiController@reSendVerificationCode');
Route::post('verifyUserCode', 'API\MobApiController@verifyUserCode');
Route::post('bulksms', 'API\MobApiController@sendSms');
Route::post('planList', 'API\MobApiController@planList');
Route::post('vendorCharity', 'API\MobApiController@vendorCharity');
Route::post('vendorSubscriptionPlan', 'API\MobApiController@vendorSubscriptionPlan');
Route::post('addVendorBusinessCategory', 'API\MobApiController@addVendorBusinessCategory');
Route::post('deleteVendorBusinessCategory', 'API\MobApiController@deleteVendorBusinessCategory');
Route::post('cancelSubscription', 'PayPalController@cancelSubscription');








Route::group(['middleware' => 'auth:api'], function(){
Route::post('details', 'API\MobApiController@details');
});
