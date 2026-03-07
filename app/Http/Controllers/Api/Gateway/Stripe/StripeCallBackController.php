<?php

namespace App\Http\Controllers\Api\Gateway\Stripe;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;

use App\Traits\ApiResponse;

class StripeCallBackController extends Controller
{
    use ApiResponse;
    public $redirectFail;
    public $redirectSuccess;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $this->redirectFail = env("APP_URL") . "/payment-error";
        $this->redirectSuccess = env("APP_URL") . "/payment-success";
    }

    // public function checkout(Request $request, $order_id, $paymentType)
    // {

    //     try {
    //         $order = Order::find($order_id);
    //         if (!$order) {
    //             return $this->error([], 'Order not found', 404);
    //         }

    //         dd( $order->product->stripe_monthly_price_id);
    //          //dd($order->product);

    //         $successUrl = route('api.payment.stripe.success') . '?token={CHECKOUT_SESSION_ID}';
    //         $cancelUrl = route('api.payment.stripe.cancel') . '?token={CHECKOUT_SESSION_ID}';

    //         $session = Session::create([
    //             'payment_method_types' => ['card'],
    //             'line_items' => [[
    //                 'price_data' => [
    //                     'currency' => 'usd',
    //                     'product_data' => [
    //                         'name' => $order->product->name,
    //                     ],
    //                     'unit_amount' => $order->total_price * 100,
    //                 ],
    //                 'price' => $order->product->stripe_monthly_price_id,
    //                 'quantity' => 1,
    //             ]],

    //             'mode' => $paymentType,

    //             // 1. Shipping Address (Required countries specified)
    //             // 'shipping_address_collection' => [
    //             //     'allowed_countries' => ['US', 'CA', 'GB', 'FR', 'DE', 'IT', 'ES', 'NL', 'BE', 'AU'],
    //             // ],

    //             // 2. Billing Address (Always require full billing details)
    //             // 'billing_address_collection'  => 'required',

    //             // 3. Phone Number (Required field)
    //             'phone_number_collection'     => ['enabled' => true],
    //             'metadata' => [
    //                 'order_id' => $order->id,
    //                 // 'customer_email' => $order->customer->email,
    //             ],
    //             'success_url' => $successUrl,
    //             'cancel_url' => $cancelUrl,
    //         ]);

    //         $data = [
    //             'checkout_url' => $session->url
    //         ];

    //         return Helper::jsonResponse(true, 'Checkout session created successfully', 200, $data);
    //     } catch (ModelNotFoundException $e) {

    //         Log::error($e->getMessage());
    //         return redirect()->to($this->redirectFail);
    //     } catch (ApiErrorException $e) {

    //         Log::error($e->getMessage());
    //         return redirect()->to($this->redirectFail);
    //     }
    // }

    public function Checkout(Request $request, $order_id, $paymentType)
    {


        $order = Order::find($order_id);
        if (!$order) {
            return $this->error([], 'Order not found', 404);
        }

        // dd( $order->product->stripe_monthly_price_id);
        //dd($order->product);

        $successUrl = route('api.payment.stripe.success') . '?token={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('api.payment.stripe.cancel') . '?token={CHECKOUT_SESSION_ID}';

        if ($paymentType === 'monthly') {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[

                    'price' => $order->product->stripe_monthly_price_id,
                    'quantity' => $request->quantity,
                ]],

                'mode' => 'subscription',
                'phone_number_collection'     => ['enabled' => true],
                'metadata' => [
                    'order_id' => $order->id,
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                // 2. Billing Address (Always require full billing details)
                'billing_address_collection'  => 'required',
            ]);

            $data = [
                'checkout_url' => $session->url
            ];
            return Helper::jsonResponse(true, 'Checkout session created successfully', 200, $data);
        } else {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[

                    'price' => $order->product->stripe_one_time_price_id,
                    'quantity' => $request->quantity,
                ]],

                'mode' => 'payment',

                'phone_number_collection'     => ['enabled' => true],
                'metadata' => [
                    'order_id' => $order->id,
                    // 'customer_email' => $order->customer->email,
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'billing_address_collection'  => 'required',

            ]);
            $data = [
                'checkout_url' => $session->url
            ];
            return Helper::jsonResponse(true, 'Checkout session created successfully', 200, $data);
        }
    }




    public function success(Request $request)
    {
        return redirect()->to($this->redirectSuccess);
    }


    public function failure(Request $request)
    {
        return redirect()->to($this->redirectFail);
       
    }
}
