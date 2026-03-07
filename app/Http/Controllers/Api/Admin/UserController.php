<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $users = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'member');
            })
            ->select(['id', 'email', 'avatar', 'created_at', 'id_number']) // include id_number if needed
            ->with(['profile' => function ($query) {
                $query->select('user_id', 'first_name', 'last_name', 'phone', 'gender', 'dob', 'country', 'city', 'postal_code', 'address', 'message');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        if ($users->isEmpty()) {
            return response()->json([]);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'users' => $users,
            ],
        ], 200);
    }

    public function register(Request $request)
    {

        $request->validate([
            'role'        => 'nullable|in:member',
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'nullable|string|max:100',
            // 'email'         => 'required|string|email|max:150|unique:users',
            'phone'       => 'required|numeric|unique:profiles',
            'dob'         => 'required|date',
            'gender'      => 'required|in:male,female,other',
            'country'     => 'nullable|string',
            'city'        => 'nullable|string',
            'postal_code' => 'nullable|string',
            'address'     => 'nullable|string',
            'message'     => 'nullable|string',
            'avatar'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {

            do {
                $slug = "cmc_" . rand(100, 999);
            } while (User::where('slug', $slug)->exists());

            $password = Str::random(8);

            // for random user
            $email = strtolower($request->input('first_name')) . rand(100, 999) . "@gmail.com";

            $avatar = '';
            if ($request->hasFile('avatar')) {
                $avatar = Helper::fileUpload($request->file('avatar'), 'user', time() . '_' . getFileName($request->file('avatar')));
            }

            // last id number
            // $lastUser = User::orderBy('id_number', 'desc')->first();
            // $id_number = $lastUser ? $lastUser->id_number  : 1;
            $maxIdNumber = User::max('id_number') ?? 0;

            $user = User::create([
                'name'              => $request->input('first_name') . ' ' . $request->input('last_name'),
                'slug'              => $slug,
                'email'             => $email,
                'avatar'            => $avatar,
                'password'          => Hash::make($password),
                'email_verified_at' => Carbon::now(),
                'id_number'         => $maxIdNumber + 1,

            ]);

            $user->profile()->create([
                'first_name'  => $request->input('first_name'),
                'last_name'   => $request->input('last_name'),
                'phone'       => $request->input('phone'),
                'gender'      => $request->input('gender'),
                'dob'         => $request->input('dob'),
                'country'     => $request->input('country') ?? '',
                'city'        => $request->input('city') ?? '',
                'address'     => $request->input('address') ?? '',
                'postal_code' => $request->input('postal_code') ?? '',
                'message'     => $request->input('message') ?? '',
            ]);

            $user->assignRole('member');

            // not need to send the email for random users
            // Mail::to($user->email)->send(new WelcomeMail($user->name));

            $data = User::select(['id', 'name', 'email', 'avatar', 'otp_verified_at', 'last_activity_at'])->with(['roles', 'profile'])->find($user->id);

            return response()->json([
                'status'  => true,
                'message' => 'User register in successfully.',
                'code'    => 200,
                'data'    => $data,
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
