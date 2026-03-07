<?php

namespace App\Http\Controllers\Api\Gateway\Stripe;

use App\Helpers\Helper;
use App\Helpers\Notify;
use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StripeDonationCallBackController extends Controller
{
    public $successRedirect;
    public $errorRedirect;

    public function __construct()
    {
        $this->successRedirect = env("FRONTEND")."/payment-success";
        $this->errorRedirect = env("FRONTEND")."/payment-error";
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function checkout(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'occupation'    => 'required|string|max:255',
            'amount'        => 'required|numeric',
            'address'       => 'required|string|max:255',
            'city'          => 'required|string|max:255',
            'state'         => 'required|string|max:255',
            'country'       => 'required|string|max:255',
            'zip_code'      => 'required|string|max:255',
            'phone'         => 'required|string|max:255',
            'type'          => 'required|string|in:one_time,monthly',
            'informed_by'   => 'required|string|in:email,sms',
        ]);

        if ($validator->fails()) {
            return Helper::jsonResponse(false, 'Validation failed', 422, $validator->errors());
        }

        try {
            DB::beginTransaction();
            $data = $validator->validated();
            $uid = Str::uuid(); 

            do {
                $uid = Str::uuid();
            } while (Donation::where('uid', $uid)->exists());

            $donation = Donation::create([
                'user_id'       => auth('api')->user()->id,
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'email'         => $data['email'],
                'occupation'    => $data['occupation'],
                'amount'        => $data['amount'],
                'address'       => $data['address'],
                'city'          => $data['city'],
                'state'         => $data['state'],
                'country'       => $data['country'],
                'zip_code'      => $data['zip_code'],
                'phone'         => $data['phone'],
                'type'          => $data['type'],
                'informed_by'   => $data['informed_by'],
                'uid'           => $uid
            ]);

            $successUrl = route('payment.stripe.donation.success') . '?token={CHECKOUT_SESSION_ID}';
            $cancelUrl = route('payment.stripe.donation.cancel');

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'donation'
                        ],
                        'unit_amount' => $data['amount'] * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => [
                    'order_id' => $uid,
                    'donation_id' => $donation->id,
                    'user_id' => auth('api')->user()->id
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            DB::commit();
            return Helper::jsonResponse(true, 'Checkout session created successfully', 200, $session->url); 

        } catch (ModelNotFoundException $e) {

            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->to($this->errorRedirect);

        } catch (ApiErrorException $e) {

            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->to($this->errorRedirect);

        }
    }

    public function success(Request $request)
    { 
        $validatedData = $request->validate([
            'token' => ['required', 'string']
        ]);

        try {

            $session = Session::retrieve($validatedData['token']);
            if ($session->payment_status === 'paid') {

                $donation = Donation::find($session->metadata['donation_id']);
                $donation->payment_status = 'paid';
                $donation->save();

                $user = User::find($session->metadata['user_id']);
                $admin  = User::role('admin', 'web')->first();

                Transaction::create([
                    'title'     => "donation",
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
                    'title'     => "donation",
                    'user_id'   => $session->metadata['user_id'],
                    'amount'    => $session->amount_total / 100,
                    'currency'  => $session->currency,
                    'trx_id'    => $session->id,
                    'type'      => 'decrement',
                    'gateway'   => 'stripe',
                    'status'    => 'success',
                    'metadata'  => json_encode($session->metadata)
                ]);

                Notify::Firebase('Donation', 'Donation Submit successfully', $user->id);
                Notify::inApp('Donation', 'Donation Submit successfully', $user->id);

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
