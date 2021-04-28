<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ChangePasswordController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreCardController;
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
    Route::post('StoreCard', 'StoreCardController@store_card');
    Route::post('CardDetail', 'StoreCardController@card_details');
    Route::post('CardDelete', 'StoreCardController@card_delete');

    Route::post('ChangeProfile', 'API\ChangePasswordController@change_profile');
    Route::post(
        'ChangePassword',
        'API\ChangePasswordController@change_password'
    );
});
