<?php

// use App\Services\StripeService;

// public function verified(Request $request)
// {
//     $user = auth()->user();

//     if(!$user->stripe_customer_id){

//         $stripe = new StripeService();

//         $customer = $stripe->createCustomer($user);

//         $user->update([
//             'stripe_customer_id'=>$customer->id
//         ]);
//     }

//     return redirect('/subscription');
// // }