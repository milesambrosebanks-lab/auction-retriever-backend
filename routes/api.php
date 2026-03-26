<?php

use App\Http\Controllers\Admin\TrackingSettingController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\FirebaseTokenController;
use App\Http\Controllers\Api\Frontend\CategoryController;
use App\Http\Controllers\Api\Frontend\FaqController;
use App\Http\Controllers\Api\Frontend\HomeController;
use App\Http\Controllers\Api\Frontend\ImageController;
use App\Http\Controllers\Api\Frontend\OrderController;
use App\Http\Controllers\Api\Frontend\PageController;
use App\Http\Controllers\Api\Frontend\PostController;
use App\Http\Controllers\Api\Frontend\ProductController;
use App\Http\Controllers\Api\Frontend\SubcategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Frontend\SettingsController;
use App\Http\Controllers\Api\Frontend\SocialLinksController;
use App\Http\Controllers\Api\Frontend\SubscriberController;
use App\Http\Controllers\Api\Gateway\Stripe\StripeCallBackController;
use App\Http\Controllers\Api\Listing\AuctionListingController;
use App\Http\Controllers\Api\Listing\ListingController;
use App\Http\Controllers\Api\User\SubscriptionController;
use Illuminate\Support\Facades\Route;


//page
Route::get('/page/home', [HomeController::class, 'index']);
Route::get('/category', [CategoryController::class, 'index']);
Route::get('/subcategory', [SubcategoryController::class, 'index']);
Route::get('/social/links', [SocialLinksController::class, 'index']);
Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/faq', [FaqController::class, 'index']);
Route::post('subscriber/store', [SubscriberController::class, 'store'])->name('api.subscriber.store');
Route::post('subscriber/remove/{token}', [SubscriberController::class, 'remove'])->name('api.subscriber.remove');
Route::post('pdf-request/store', [SubscriberController::class, 'pdfGuide'])->name('api.pdf.request.store');
Route::get('/subscription/plans', [SubscriptionController::class, 'getPlanDetails']);


Route::middleware('auth:api')->prefix('auth')->group(function () {

    Route::post('/subscription/setup-intent', [SubscriptionController::class, 'createSetupIntent']);
    Route::post('/subscription/create', [SubscriptionController::class, 'createSubscription']);
    Route::get('/subscription/plan', [SubscriptionController::class, 'myPlan']);

    Route::post('/subscription/update', [SubscriptionController::class, 'updateSubscription']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancelSubscription']);

    Route::post('/subscription/resume', [SubscriptionController::class, 'resumeSubscription']);

    Route::get('/subscription/status', [SubscriptionController::class, 'subscriptionStatus']);
});

Route::middleware(['auth:api'])->prefix('admin')->group(function () {
    Route::get('tracking-settings', [TrackingSettingController::class, 'show']);
    Route::post('tracking-settings', [TrackingSettingController::class, 'update']);
});

Route::middleware('auth:api')->prefix('auction')->group(function () {
    Route::get('/', [AuctionListingController::class, 'index']);
    Route::get('/filter-options', [AuctionListingController::class, 'filterOptions']);
    Route::get('/{id}/view', [AuctionListingController::class, 'show']);

    Route::post('/{id}/save', [AuctionListingController::class, 'save']);
    Route::get('/saved', [AuctionListingController::class, 'savedListings']);
    Route::post('/saved/{id}/delete', [AuctionListingController::class, 'delete']);
});


Route::middleware(['auth:api'])->controller(PostController::class)->prefix('auth/post')->group(function () {
    Route::get('/', 'index');
    Route::post('/store', 'store');
    Route::get('/show/{id}', 'show');
    Route::post('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});

Route::get('/posts', [PostController::class, 'posts']);
Route::get('/post/show/{post_id}', [PostController::class, 'post']);

Route::middleware(['auth:api'])->controller(ImageController::class)->prefix('auth/post/image')->group(function () {
    Route::get('/', 'index');
    Route::post('/store', 'store');
    Route::get('/delete/{id}', 'destroy');
});

Route::get('dynamic/page', [PageController::class, 'index']);
Route::get('dynamic/page/show/{slug}', [PageController::class, 'show']);


Route::group(['middleware' => 'guest:api'], function ($router) {
    //register
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']);
    Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']);
    Route::post('/verify-otp', [RegisterController::class, 'VerifyEmail']);
    //login
    Route::post('login', [LoginController::class, 'login'])->name('api.login');
    //forgot password
    Route::post('/forget-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/otp-token', [ResetPasswordController::class, 'MakeOtpToken']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);
    //social login
    Route::post('/social-login', [SocialLoginController::class, 'SocialLogin']);

    Route::post('google/callback',  [SocialAuthController::class, 'googleCallback']);
    Route::post('apple/callback',   [SocialAuthController::class, 'appleCallback']);

    Route::get('verify-email/{id}', [RegisterController::class, 'verifyEmailLink'])
        ->name('verify.email')
        ->middleware('signed');

    Route::get('reset-password/{id}', [ResetPasswordController::class, 'Generate_RP_Link'])
        ->name('generate.token')
        ->middleware('signed');
});

Route::group(['middleware' => ['auth:api', 'api-otp']], function ($router) {
    Route::get('/refresh-token', [LoginController::class, 'refreshToken']);
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::get('/me', [UserController::class, 'me']);
    Route::get('/account/switch', [UserController::class, 'accountSwitch']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/change-password', [UserController::class, 'changePassword'])->middleware('throttle:3,1');
    Route::post('/update-avatar', [UserController::class, 'updateAvatar']);
    Route::delete('/delete-profile', [UserController::class, 'destroy']);
});

/*
# Firebase Notification Route
*/

Route::middleware(['auth:api'])->controller(FirebaseTokenController::class)->prefix('firebase')->group(function () {
    Route::get("test", "test");
    Route::post("token/add", "store");
    Route::post("token/get", "getToken");
    Route::post("token/delete", "deleteToken");
});

/*
# In App Notification Route
*/

Route::middleware(['auth:api'])->controller(NotificationController::class)->prefix('notify')->group(function () {
    Route::get('test', 'test');
    Route::get('/', 'index');
    Route::get('status/read/all', 'readAll');
    Route::get('status/read/{id}', 'readSingle');
});

/*
# Chat Route
*/

Route::middleware(['auth:api'])->controller(ChatController::class)->prefix('auth/chat')->group(function () {
    Route::get('/list', 'list');
    Route::post('/send/{receiver_id}', 'send');
    Route::get('/conversation/{receiver_id}', 'conversation');
    Route::get('/room/{receiver_id}', 'room');
    Route::get('/search', 'search');
    Route::get('/seen/all/{receiver_id}', 'seenAll');
    Route::get('/seen/single/{chat_id}', 'seenSingle');
});

/*
# CMS
*/

Route::prefix('cms')->name('cms.')->group(function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');
    Route::get('product', [HomeController::class, 'productIndex']);
    Route::get('educations/approach', [HomeController::class, 'educations'])->name('educations.approach');
    Route::get('educations/approach/{education_approach_id}', [HomeController::class, 'educationsSingle'])->name('educations.approach.single');
});

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'oneProduct'])->name('index');
    Route::get('/{id}', [ProductController::class, 'show'])->name('show');
    Route::post('/order', [OrderController::class, 'store'])->name('order.store');
    Route::post('/{id}/checkout', [StripeCallBackController::class, 'checkout'])->name('order.checkout');
});
// Route::prefix('orders')->name('orders.')->group(function () {
//     Route::post('/{id}/checkout', [StripeCallBackController::class, 'checkout'])->name('order.checkout');
// });
/*
# prayer time
# http:://127.0.0.1:8000/api/prayer-times?date=2025-12-25&lat=23.7018&lng=90.3742&timezone=6&method=1
# http:://127.0.0.1:8000/api/prayer-times/today?lat=23.7018&lng=90.3742&timezone=6&method=1
*/
// Route::prefix('prayer-times')->group(function () {
//     Route::get('/', [PrayerTimesController::class, 'index']);
//     Route::get('/today', [PrayerTimesController::class, 'today']);
//     Route::get('/methods', [PrayerTimesController::class, 'methods']);
// });

Route::post('contact/store', [ContactController::class, 'store'])->name('contact.store');

    // Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
    // Route::get('/payments/success', [StripeWebhookController::class, 'paymentSuccess'])->name('payment.success');
    // Route::get('/payments/failed', [StripeWebhookController::class, 'paymentFailed'])->name('payment.failed');
