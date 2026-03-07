<?php

use App\Http\Controllers\Api\Gateway\Stripe\StripeDonationCallBackController;
use App\Http\Controllers\Api\Gateway\Stripe\StripeOnBoardingController;
use App\Http\Controllers\Api\Gateway\Stripe\StripeSubscriptionsController;
use App\Http\Controllers\Api\Gateway\Stripe\StripeDonationWebHookController;
use App\Http\Controllers\Api\Gateway\Stripe\StripeProductCallBackController;
use Illuminate\Support\Facades\Route;

/*
# Donation
*/
//stripe callback
Route::controller(StripeDonationCallBackController::class)->prefix('payment/stripe/donation')->name('payment.stripe.donation.')->group(function () {
    Route::post('/checkout', 'checkout')->middleware(['auth:api']);
});

Route::controller(StripeProductCallBackController::class)->prefix('payment/stripe/product')->name('payment.stripe.product.')->group(function () {
    Route::post('/checkout/{product_id?}', 'checkout')->middleware(['auth:api']);
});

//stripe webhook
Route::controller(StripeDonationWebHookController::class)->prefix('payment/stripe')->name('payment.stripe.')->group(function () {
    Route::post('/intent', 'intent')->middleware(['auth:api']);
    Route::post('/webhook', 'webhook');
});



//stripe account
Route::controller(StripeOnBoardingController::class)->prefix('payment/stripe/account')->name('payment.stripe.account.')->group(function () {
    Route::middleware(['auth:api'])->get('/connect', 'accountConnect')->name('connect');
    Route::get('/connect/success/{account_id}', 'accountSuccess')->name('connect.success');
    Route::get('/connect/refresh/{account_id}', 'accountRefresh')->name('connect.refresh');
    Route::middleware(['auth:api'])->get('/url', 'AccountUrl')->name('url');
    Route::middleware(['auth:api'])->get('/info', 'accountInfo')->name('info');
    Route::middleware(['auth:api'])->post('/withdraw', 'withdrawRequest')->name('withdraw');
});

Route::controller(StripeSubscriptionsController::class)->prefix('payment/stripe/subscriptions')->name('payment.stripe.subscriptions.')->group(function () {
    Route::post('/plan', 'plan');
    Route::get('/my/plan', 'myPlan');
    Route::get('/cancel/plan', 'cancelPlan');
});
