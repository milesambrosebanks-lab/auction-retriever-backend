<?php

namespace App\Http\Controllers\Api\Gateway\Stripe;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use UnexpectedValueException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StripeDonationWebHookController extends Controller
{
    public function intent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return Helper::jsonResponse(false, 'Validation failed', 422, $validator->errors());
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $data = $validator->validated();
            $uid = Str::uuid();

            $paymentIntent = PaymentIntent::create([
                'amount'   => $data['price'] * 100,
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => $uid,
                    'user_id' => auth('api')->user()->id
                ],
            ]);

            $data = [
                'client_secret' => $paymentIntent->client_secret
            ];

            return Helper::jsonResponse(true, 'Payment intent created successfully', 200, $data);

        } catch (ApiErrorException $e) {

            return Helper::jsonResponse(false, $e->getMessage(), 500, []);

        } catch (Exception $e) {

            return Helper::jsonResponse(false, $e->getMessage(), 500, []);

        }
    }


    public function webhook(Request $request): JsonResponse
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $payload        = $request->getContent();
        $sigHeader      = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_DONATION_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (UnexpectedValueException $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 400, []);
        } catch (SignatureVerificationException $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 400, []);
        }

        //? Handle the event based on its type
        try {
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->success($event->data->object);
                    return Helper::jsonResponse(true, 'Payment successful', 200, []);

                case 'payment_intent.payment_failed':
                    $this->failure($event->data->object);
                    return Helper::jsonResponse(true, 'Payment failed', 200, []);

                default:
                    return Helper::jsonResponse(true, 'Unhandled event type', 200, []);
            }
        } catch (Exception $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 500, []);
        }
    }

    protected function success($paymentIntent): void
    {
        Transaction::create([
            'user_id'   => $paymentIntent->metadata->user_id,
            'amount'    => $paymentIntent->amount / 100,
            'currency'  => $paymentIntent->currency,
            'trx_id'    => $paymentIntent->id,
            'type'      => 'increment',
            'status'    => 'success',
            'metadata'  => json_encode($paymentIntent->metadata)
        ]);

    }

    protected function failure($paymentIntent): void
    {
        //? Handle payment failure    
    }
    
}
