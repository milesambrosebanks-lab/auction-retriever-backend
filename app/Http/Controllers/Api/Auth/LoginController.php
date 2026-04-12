<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public $select;
    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'name', 'email', 'avatar', 'otp_verified_at', 'last_activity_at'];
    }

    public function Login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email|exists:users,email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return validationError($validator);
            }

            $user = User::where('email', $request->email);

            if (!$user) {
                return Helper::jsonResponse(false, 'User not found', 404);
            }

            $user = $user->where('status', 'active')->first();

            if (!$user) {
                return Helper::jsonResponse(false, 'user is not active', 404);
            }

            // if ((bool) $user->is_deleted) {
            //     return Helper::jsonErrorResponse('Your account has been deleted.', 403);
            // }

            //! Check the password
            if (!Hash::check($request->password, $user->password)) {
                return Helper::jsonResponse(false, 'Invalid password', 401);
            }

            //? Check if the email is verified before login is successful
            if (!$user->otp_verified_at) {
                return Helper::jsonResponse(false, 'Email not verified. Please verify your email before logging in.', 403, ['is_otp_verified' => $user->isOtpVerified]);
            } else {
                $user->update([
                    'otp'            => null,
                    'otp_expires_at' => null,
                    'reset_password_token' => null,
                    'reset_password_token_expire_at' => null
                ]);
            }

            $user->update([
                'last_activity_at' => now(),
            ]);

            //* Generate token if email is verified
            $token = auth('api')->login($user);

            $data = User::select($this->select)->with('roles')->find(auth('api')->user()->id);

            return response()->json([
                'status'     => true,
                'message'    => 'Login successful',
                'code'       => 200,
                'token_type' => 'bearer',
                'token'      => $token,
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'data'       => [
                    'email' => $data->email,
                    'name' => $data->name,
                    'token'      => $token,
                ],
            ], 200);
        } catch (Exception $e) {
            return Helper::jsonResponse(false, 'An error occurred during login.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function refreshToken()
    {
        if ((bool) auth('api')->user()?->is_deleted) {
            auth('api')->logout();
            return Helper::jsonErrorResponse('Your account has been deleted.', 403);
        }

        $refreshToken = auth('api')->refresh();

        if (empty($refreshToken)) {
            return Helper::jsonErrorResponse('Failed to refresh the token.', 401);
        }

        return response()->json([
            'status'     => true,
            'message'    => 'Access token refreshed successfully.',
            'code'       => 200,
            'token_type' => 'bearer',
            'token'      => $refreshToken,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'data' => auth('api')->user()
        ]);
    }
}
