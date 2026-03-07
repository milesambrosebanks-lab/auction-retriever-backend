<?php

namespace App\Http\Controllers\Api\Gateway\Stripe;

use App\Enums\Enum;
use App\Helpers\Notify;
use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Donation;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Str;

class StripeProductCallBackController extends Controller
{
    public $successRedirect;
    public $errorRedirect;

    public function __construct()
    {
        $this->successRedirect = env("FRONTEND") . "/payment-success";
        $this->errorRedirect = env("FRONTEND") . "/payment-error";
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function checkout(Request $request, $product_id = null)
    {
        // Get the authenticated user
        $user = auth('api')->user();

        // Validate the incoming request data
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email',
            'phone'      => 'required|string|max:20',
            'address'    => 'required|string|max:255',
            'city'       => 'required|string|max:100',
            'zip_code'   => 'required|string|max:20',
            'country'    => 'required|string|max:100',
            'notes'      => 'nullable|string|max:500',
        ]);

        $lineItems = [];
        $subtotal = 0;

        $uid = Str::uuid();
        do {
            $uid = Str::uuid();
        } while (Order::where('uid', $uid)->exists());

        if ($product_id) {

            $product = Product::find($product_id);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $subtotal += $product->price;

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => $product->price * 100,  // Amount in cents
                ],
                'quantity' => 1,
            ];
        } else {

            // Retrieve the user's cart items
            $cartItems = $user->cartItems()->with('product')->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Cart is empty'], 400);
            }

            foreach ($cartItems as $item) {
                $subtotal += $item->price * $item->quantity;
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $item->product->name,
                        ],
                        'unit_amount' => $item->price * 100,  // Amount in cents
                    ],
                    'quantity' => $item->quantity,
                ];
            }
        }

        $shipping = Enum::SHIPPING;

        $lineItems[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Shipping',
                ],
                'unit_amount' => $shipping * 100,
            ],
            'quantity' => 1,
        ];

        // Calculate the total
        $total = $subtotal + $shipping;

        // Create the order
        $order = Order::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'city'       => $request->city,
            'zip_code'   => $request->zip_code,
            'country'    => $request->country,
            'subtotal'   => $subtotal,
            'shipping'   => $shipping,
            'total'      => $total,
            'status'     => 'pending',
            'user_id'    => $user->id,
            'uid'        => $uid
        ]);

        // Add items to the order
        if ($product_id) {
            $order->items()->create([
                'product_id' => $product_id,
                'quantity'   => 1,
                'price'      => $product->price,
            ]);
        } else {
            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->price,
                ]);
            }
        }

        // Create a checkout session with Stripe
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('payment.stripe.product.success') . '?token={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('payment.stripe.product.cancel') . '?token={CHECKOUT_SESSION_ID}',
                'metadata' => [
                    'order_id'    => $order->id,
                    'user_id'     => $user->id
                ],
                'customer_email' => $request->email,
            ]);

            // Return the Stripe checkout URL
            return response()->json(['url' => $session->url]);
        } catch (Exception $e) {
            // Handle Stripe errors
            return response()->json(['code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function success(Request $request)
    {

        $validatedData = $request->validate(['token' => ['required', 'string']]);

        try {

            $session = Session::retrieve($validatedData['token']);
            if ($session->payment_status === 'paid') {

                $order_id = $session->metadata['order_id'];
                $order = Order::find($order_id);
                $order->status = 'paid';
                $order->save();

                $customer = User::find($session->metadata['user_id']);

                $admin  = User::role('admin', 'web')->first();

                Transaction::create([
                    'title'     => "payment",
                    'user_id'   => $admin->id,
                    'amount'    => $session->amount_total / 100,
                    'currency'  => $session->currency,
                    'trx_id'    => $session->id,
                    'type'      => 'increment',
                    'gateway'   => 'stripe',
                    'status'    => 'success',
                    'metadata'  => json_encode($session->metadata)
                ]);

                Transaction::create([
                    'title'     => "payment",
                    'user_id'   => $customer->id,
                    'amount'    => $session->amount_total / 100,
                    'currency'  => $session->currency,
                    'trx_id'    => $session->id,
                    'type'      => 'decrement',
                    'gateway'   => 'stripe',
                    'status'    => 'success',
                    'metadata'  => json_encode($session->metadata)
                ]);

                Mail::to($customer->email)->send(new InvoiceMail('Invoice', $order->id));
                Notify::Firebase('Product Purchased', 'Product purchased successfully', $customer->id);
                Notify::inApp('Product Purchased', 'Product purchased successfully', $customer->id);

                return redirect()->to($this->successRedirect);
            }

            if ($session->payment_status === 'unpaid' || $session->payment_status === 'no_payment_required') {
                return redirect()->to($this->errorRedirect);
            }

            return redirect()->to($this->errorRedirect);
        } catch (ApiErrorException $e) {

            Log::error($e->getMessage());
            return redirect()->to($this->errorRedirect);
        } catch (ModelNotFoundException $e) {

            Log::error($e->getMessage());
            return redirect()->to($this->errorRedirect);
        }
    }


    public function failure(Request $request)
    {
        return redirect()->to($this->errorRedirect);
    }
}
