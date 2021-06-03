<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ChangePasswordController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CardShareController;
use App\Http\Controllers\StoreCardController;
use App\Http\Controllers\API\ContactUsController;
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

Route::post('Forgot_Password', 'API\ChangePasswordController@forgot_password');
Route::post('OTP_Verify', 'API\ChangePasswordController@otp_verify');
Route::post(
    'Create_New_Password',
    'API\ChangePasswordController@create_new_password'
);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'API\RegisterController@register')->name(
    'registration'
);

Route::post('login', 'API\RegisterController@login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('AddStore', 'StoreController@add_store')->name('add_store');
    Route::post('StoreList', 'StoreController@store_listing')->name(
        'StoreList'
    );
    Route::post('StoreDetail', 'StoreController@store_details')->name(
        'StoreDetail'
    );
    Route::post('StoreDelete', 'StoreController@store_delete');
    Route::post('AddFavorite', 'StoreController@add_favorite');
    Route::post('RemoveFavorite', 'StoreController@remove_favorite');
    Route::post('Filter', 'StoreController@filter');

    Route::post('StoreCard', 'StoreCardController@store_card');
    Route::post('CardDetail', 'StoreCardController@card_details');
    Route::post('CardDelete', 'StoreCardController@card_delete');
    Route::post('HideCard', 'StoreCardController@hide_card');
    Route::post('ShowCard', 'StoreCardController@show_card');
    Route::post('Card_Disable', 'StoreCardController@card_disable');

    Route::post('StoreSuggest', 'StoreSuggestionController@index');

    Route::post('ChangeProfile', 'API\ChangePasswordController@change_profile');
    Route::post(
        'ChangePassword',
        'API\ChangePasswordController@change_password'
    );

    Route::post('ShareCode', 'CardShareController@random_code');
    Route::post('AddShareCard', 'CardShareController@add_share_card');

    Route::post('Forgot_Pin', 'API\ChangePinController@forgot_pin');
    Route::post('OTP_Verify_Pin', 'API\ChangePinController@otp_verify');
    Route::post('Create_New_Pin', 'API\ChangePinController@create_new_pin');
    Route::post('Change_Pin', 'API\ChangePinController@change_pin');

    Route::post('ContactUs', 'API\ContactUsController@contactus');

    Route::post('FetchAll', 'CategoryController@fetchall');
});
