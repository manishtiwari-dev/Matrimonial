<?php

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\MatchesController;
use App\Http\Controllers\Api\Auth\MessageController;
use App\Http\Controllers\Api\Auth\DashboardController;
use App\Http\Controllers\Api\Auth\KycDetailController;
use App\Http\Controllers\Api\Auth\ContactUsController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\SocialiteController;
use App\Http\Controllers\Api\InterestController;
use App\Http\Controllers\Api\ActionController;
use App\Http\Controllers\Api\IgnoredProfileController;
use App\Http\Controllers\Api\GalleryController;


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


Route::namespace('Api')->name('api.')->group(function () {


    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('social-login/{provider}', [SocialiteController::class, 'socialLogin']);
    Route::post('social-login/callback/{provider}', [SocialiteController::class, 'callback']);

    Route::post('user-attributes', [RegisterController::class, 'user_attributes']);

    Route::post('religion-community', [RegisterController::class, 'religion_community']);

    
    Route::group(['prefix' => 'password'], function () {
        Route::post('/email', [ForgotPasswordController::class, 'sendResetCodeEmail']);
        Route::post('/verify-code', [ForgotPasswordController::class, 'verifyCode']);
        Route::post('/reset', [ForgotPasswordController::class, 'reset']);
    });

    Route::controller('ResetPasswordController')->group(function () {
        Route::post('/update', 'reset')->name('password.update');
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
    });


    Route::post('get-countries', function () {
        $c = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $notify[] = 'General setting data';
        foreach ($c as $k => $country) {
            $countries[] = [
                'country' => $country->country,
                'dial_code' => $country->dial_code,
                'country_code' => $k,
            ];
        }
        return response()->json([
            'remark' => 'country_data',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'countries' => $countries,
            ],
        ]);
    });

    Route::middleware('auth:sanctum')->group(function () {
        // profile 
        Route::post('profileSetting', [ProfileController::class, 'profileSetting']);
        Route::post('attributes', [ProfileController::class, 'userData']);
        Route::post('profileData', [ProfileController::class, 'profileData']);

        Route::post('user-update', [ProfileController::class, 'updateUser']);

        Route::post('profile-update', [ProfileController::class, 'updateProfile']);
        Route::post('profile/picture/update', [ProfileController::class, 'updateProfileImage']);
        Route::post('profile/picture/show', [ProfileController::class, 'profileShow']);
        Route::post('profile-setting/education/add', [ProfileController::class, 'AddEducationInfo']);
        Route::post('profile-setting/education/update', [ProfileController::class, 'updateEducationInfo']);
        Route::post('profile-setting/education/delete', [ProfileController::class, 'deleteEducationInfo']);
        Route::post('profile-setting/career/update', [ProfileController::class, 'updateCareerInfo']);
        Route::post('profile-setting/career/delete', [ProfileController::class, 'deleteCareerInfo']);
        //end profile api
       
        //matches profile
        Route::post('matches', [MatchesController::class, 'matches']);
        Route::post('/matches-profile', [MatchesController::class, 'profile']);
        Route::post('profile/delete', [MatchesController::class, 'deleteUserAccount']);
        Route::post('/interestStatus', [MatchesController::class, 'interestStatus']);
        Route::post('bookmarkStatus', [MatchesController::class, 'bookmarkStatus']);


        
        
       //end matches profile api 

       //prefered profile
       

        //message details
        Route::post('inbox', [MessageController::class, 'index']);
        Route::post('/messageStore', [MessageController::class, 'messageStore']);
        Route::post('/loadMessage', [MessageController::class, 'loadMessage']);
        Route::post('/memberSearch', [MessageController::class, 'memberSearch']);
        Route::post('/appendMember', [MessageController::class, 'appendMember']);
       //end message api 
         
        Route::post('dashboard', [DashboardController::class, 'dashboard_data']);
        Route::post('storyDetails', [DashboardController::class, 'storyDetails']);
        //contact 
        Route::post('contact', [ContactUsController::class, 'contact']);
        Route::post('contact/store', [ContactUsController::class, 'contactSubmit']);
        Route::post('kyc/store', [KycDetailController::class, 'store']);

        //general details
        Route::post('general-setting', function () {
            $general = GeneralSetting::first();
            $notify[] = 'General setting data';
            return response()->json([
                'remark' => 'general_setting',
                'status' => 'success',
                'message' => ['success' => $notify],
                'data' => [
                    'general_setting' => $general,
                ],
            ]);
        });

        //authorization
        Route::controller('AuthorizationController')->group(function () {
            Route::get('authorization', 'authorization')->name('authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
            Route::post('verify-email', 'emailVerification')->name('verify.email');
            Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
            Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
        });

        Route::middleware(['check.status'])->group(function () {
            Route::post('user-data-submit', 'UserController@userDataSubmit')->name('data.submit');

            Route::middleware('registration.complete')->group(function () {
                Route::get('dashboard', function () {
                    return auth()->user();
                });

                Route::get('user-info', function () {
                    $notify[] = 'User information';
                    return response()->json([
                        'remark' => 'user_info',
                        'status' => 'success',
                        'message' => ['success' => $notify],
                        'data' => [
                            'user' => auth()->user()
                        ]
                    ]);
                });

                Route::controller('UserController')->group(function () {

                    //KYC
                    Route::get('kyc-form', 'kycForm')->name('kyc.form');
                    Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                    //Report
                    Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                });

                //Profile setting
                Route::controller('UserController')->group(function () {
                    Route::post('profile-setting', 'submitProfile');
                    Route::post('change-password', 'submitPassword');
                });

                // Payment
                Route::controller('PaymentController')->group(function () {
                    Route::get('deposit/methods', 'methods')->name('deposit');
                    Route::post('deposit/insert', 'depositInsert')->name('deposit.insert');
                    Route::get('deposit/confirm', 'depositConfirm')->name('deposit.confirm');
                    Route::get('deposit/manual', 'manualDepositConfirm')->name('deposit.manual.confirm');
                    Route::post('deposit/manual', 'manualDepositUpdate')->name('deposit.manual.update');
                });
            });
        });


        Route::post('logout', [LoginController::class, 'logout']);

        Route::post('interest/all', [InterestController::class, 'interestList']);
        Route::post('interest/request', [InterestController::class, 'interestRequests']);
        Route::post('interest/accept-interest', [InterestController::class, 'acceptInterest']);
        Route::post('interest/remove', [InterestController::class, 'remove']);

        Route::post('express-interest', [ActionController::class, 'expressInterest']);
        Route::post('ignore', [ActionController::class, 'ignore']);
        Route::post('bookmarkSave', [ActionController::class, 'addToShortList']);
        Route::post('removeBookmark', [ActionController::class, 'removeFromShortList']);
        Route::post('/bookmarkSaveMatches', [ActionController::class, 'bookmarkSaveMatches']);


        //Ignored profile
        Route::post('ignore-list', [IgnoredProfileController::class, 'index']);
        Route::post('remove', [IgnoredProfileController::class, 'remove']);


        //Gallery
        Route::post('gallery', [GalleryController::class, 'index']);
        Route::post('gallery/upload', [GalleryController::class, 'upload']);
        Route::post('gallery/delete', [GalleryController::class, 'delete']);


    });
});
