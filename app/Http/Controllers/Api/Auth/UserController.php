<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserController extends Controller
{
    public $select;
    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'name', 'email', 'avatar', 'last_activity_at'];
    }

    public function me()
    {
        $user = auth('api')->user();

        $data = User::select($this->select)->find(auth('api')->user()->id);

        $subscription = $user->subscription('default');
        $plan = null;

        if (! $subscription) {
            $ss = [
                'active'    => false,
                'cancelled' => false,
                'on_grace'  => false,
                'ends_at'   => null,
                'plan'      => null,
            ];
        } else {
            $plan = Plan::where('stripe_price_id', $subscription->stripe_price)->first();

            $ss = [
                'id'            => $subscription->id,
                'stripe_price'  => $subscription->stripe_price,
                'starts_at'    => $subscription->created_at,
                'active'        => $subscription->active(),
                'canceled'      => $subscription->canceled(),
                'on_grace'      => $subscription->onGracePeriod(),
                'ends_at'       => $subscription->ends_at,
                'trial_ends_at' => $subscription->trial_ends_at,
                'subscription_plan_id' => $plan->id??null,
                // 'plan'          => $plan ? [
                //     'id'              => $plan->id,
                //     'name'            => $plan->name,
                //     'price'           => $plan->price,
                //     'currency'        => $plan->currency,
                //     'interval'        => $plan->interval,
                //     'interval_count'  => $plan->interval_count,
                //     'trial_days'      => $plan->trial_days,
                //     'stripe_price_id' => $plan->stripe_price_id,
                // ] : null,
            ];
        }
        // $data->is_subscribed = $data->activeSubscription()->exists();
        $data->subscription = $ss;
        return Helper::jsonResponse(true, 'User details fetched successfully', 200, $data);
    }

    public function changePassword(Request $request)
    {
        $validatedData = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail(auth('api')->id());

        // Check current password
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return Helper::jsonResponse(false, 'Current password is incorrect', 400);
        }

        // Update password
        $user->update([
            'password' => bcrypt($validatedData['new_password']),
        ]);

        return Helper::jsonResponse(true, 'Password updated successfully', 200);
    }

    public function updateProfile(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'phone' => 'nullable|string|numeric|max_digits:20',
            // 'password' => 'nullable|string|min:6|confirmed',
            'address' => 'nullable|string|max:255',
        ]);


        // if (!empty($validatedData['password'])) {
        //     $validatedData['password'] = bcrypt($validatedData['password']);
        // } else if (array_key_exists('password', $validatedData)) {
        //     unset($validatedData['password']);
        // }

        $user = User::findOrFail(auth('api')->id());

        if ($request->hasFile('avatar')) {
            if (!empty($user->avatar)) {
                Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
            }
            $validatedData['avatar'] = Helper::fileUpload($request->file('avatar'), 'user/avatar', getFileName($request->file('avatar')));
        } else {
            $validatedData['avatar'] = $user->avatar;
        }

        $user->update($validatedData);

        $data = User::select($this->select)->find($user->id);
        return Helper::jsonResponse(true, 'Profile updated successfully', 200, $data);
    }

    public function updateAvatar(Request $request)
    {
        $validatedData = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);
        $user = auth('api')->user();
        if (!empty($user->avatar)) {
            Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
        }
        $validatedData['avatar'] = Helper::fileUpload($request->file('avatar'), 'user/avatar', getFileName($request->file('avatar')));
        $user->update($validatedData);
        $data = User::select($this->select)->find($user->id);
        return Helper::jsonResponse(true, 'Avatar updated successfully', 200, $data);
    }

    public function delete()
    {
        return $this->deleteAccount();
    }

    public function destroy()
    {
        return $this->deleteAccount();
    }

    private function deleteAccount()
    {
        $user = auth('api')->user();

        if (! $user) {
            return Helper::jsonResponse(false, 'Unauthorized', 401);
        }

        try {
            $subscription = $user->subscription('default');

            if ($subscription && ! $subscription->canceled()) {
                $subscription->cancel();
            }

            DB::transaction(function () use ($user) {
                if (! empty($user->getRawOriginal('avatar'))) {
                    Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
                }

                $user->firebaseTokens()->delete();
                $user->subscriptions()->delete();
                $user->delete();
            });

            Auth::guard('api')->logout();

            return Helper::jsonResponse(true, 'Profile deleted successfully', 200);
        } catch (Throwable $e) {
            return Helper::jsonResponse(false, 'Failed to delete profile: ' . $e->getMessage(), 500);
        }
    }
}
