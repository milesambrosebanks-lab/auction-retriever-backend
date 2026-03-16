<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use App\Services\StripeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        protected SocialAuthService $socialAuthService
    ) {
        parent::__construct();
    }

    // ── Google ───────────────────────────────────────────────────
    public function googleCallback(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->handleCallback($request, 'google');
    }

    // ── Apple ────────────────────────────────────────────────────
    public function appleCallback(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->handleCallback($request, 'apple');
    }

    // ── Common handler ───────────────────────────────────────────
    private function handleCallback(Request $request, string $provider): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'token' => 'required|string', // Frontend থেকে access token আসবে
        ]);

        try {
            // Frontend এর token দিয়ে social user info আনো
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->userFromToken($request->token);

            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);

            // Stripe customer create (নতুন user হলে)
            if (!$user->stripe_id) {
                $stripe   = new StripeService();
                $customer = $stripe->createCustomer($user);
                $user->update(['stripe_id' => $customer->id]);
            }

            $token = auth('api')->login($user);

            return Helper::jsonResponse(true, 'Login successful.', 200, [
                'name'       => $user->name,
                'email'      => $user->email,
                'avatar'     => $user->social_avatar,
                'auth_type'  => $user->auth_type,
                'token'      => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ]);

        } catch (Exception $e) {
            Log::error("Social auth error ({$provider}): " . $e->getMessage());
            return Helper::jsonErrorResponse('Social login failed.', 500);
        }
    }
}
