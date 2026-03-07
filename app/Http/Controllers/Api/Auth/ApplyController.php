<?php
namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApplyController extends Controller
{

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type'                        => 'required|in:representative,senator',
            'first_name'                  => 'required|string|max:100',
            'last_name'                   => 'required|string|max:100',
            'dob'                         => 'required|date|before:today',
            'email'                       => 'nullable|string|email|max:150|unique:users',
            'phone'                       => 'required|numeric|unique:profiles',
            'gender'                      => 'required',
            'address'                     => 'nullable|string',
            'district'                    => 'nullable|string',
            'county'                      => 'nullable|string',
            'year_of_residence'           => 'required|string|max:50',
            'is_contested_district'       => 'nullable',
            'is_contested_other_district' => 'nullable',
            'contested_district'          => 'nullable',
            'contested_county'            => 'nullable',
            'higest_education'            => 'required|string|max:50',
            'institution'                 => 'required|string|max:255',
            'year_of_completion'          => 'required|string|max:50',
            'current_occupation'          => 'required|string|max:50',
            'year_of_experience'          => 'nullable|string|max:50',
            'relavent_skills'             => 'nullable|string|max:255',
            'political_statment'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Helper::jsonResponse(false, 'Validation failed', 422, $validator->errors());
        }

        try {
            $maxIdNumber = User::max('id_number') ?? 0;

            // Auto-generate email if missing
            $email = $request->input('email');
            if (empty($email)) {
                $first  = strtolower(preg_replace('/\s+/', '', $request->input('first_name')));
                $last   = strtolower(preg_replace('/\s+/', '', $request->input('last_name')));
                $random = rand(10, 999);
                $email  = $first . '.' . $last . $random . '@gmail.com';
            }

            $user = User::create([
                'name'           => $request->input('first_name') . ' ' . $request->input('last_name'),
                'slug'           => "cmc_" . (User::max('id') + 1),
                'email'          => strtolower($email),
                'password'       => Hash::make('12345678'), // static default password
                'otp'            => rand(1000, 9999),
                'otp_expires_at' => \Carbon\Carbon::now()->addMinutes(60),
                'id_number'      => $maxIdNumber + 1,
            ]);

            $profile = $user->profile()->create([
                'type'                        => $request->input('type'),
                'first_name'                  => $request->input('first_name'),
                'last_name'                   => $request->input('last_name'),
                'dob'                         => $request->input('dob'),
                'gender'                      => $request->input('gender'),
                'phone'                       => $request->input('phone'),
                'address'                     => $request->input('address') ?? '',
                'county'                      => $request->input('county') ?? '',
                'district'                    => $request->input('district') ?? '',
                'year_of_residence'           => $request->input('year_of_residence') ?? '',
                'is_contested_district'       => $request->input('is_contested_district') ?? false,
                'is_contested_other_district' => $request->input('is_contested_other_district') ?? false,
                'contested_district'          => $request->input('contested_district') ?? '',
                'contested_county'            => $request->input('contested_county') ?? '',
                'higest_education'            => $request->input('higest_education') ?? '',
                'institution'                 => $request->input('institution') ?? '',
                'year_of_completion'          => $request->input('year_of_completion') ?? '',
                'current_occupation'          => $request->input('current_occupation') ?? '',
                'year_of_experience'          => $request->input('year_of_experience') ?? '',
                'relavent_skills'             => $request->input('relavent_skills') ?? '',
                'political_statment'          => $request->input('political_statment') ?? '',
            ]);

            $user->assignRole('member');

            // Return user with profile loaded
            $user->load('profile');

            return Helper::jsonResponse(true, 'Registration successful', 200, $user);

        } catch (Exception $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 500);
        }
    }

    // public function store(Request $request): JsonResponse
    // {
    //     $validator = Validator::make($request->all(), [

    //         'type'                        => 'required|in:representative,senator',

    //         'first_name'                  => 'required|string|max:100',
    //         'last_name'                   => 'required|string|max:100',
    //         'dob'                         => 'required|date|before:today',
    //         'email'                       => 'nullable|string|email|max:150|unique:users',
    //         'phone'                       => 'required|numeric|unique:profiles',
    //         'gender'                      => 'required',

    //         'address'                     => 'nullable|string',
    //         'district'                    => 'nullable|string',
    //         'county'                      => 'nullable|string',
    //         'year_of_residence'           => 'required|string|max:50',
    //         'is_contested_district'       => 'nullable',
    //         'is_contested_other_district' => 'nullable',
    //         'contested_district'          => 'nullable',
    //         'contested_county'            => 'nullable',

    //         // 'country_of_residence'        => 'nullable|string',
    //         // 'state'                       => 'nullable|string',
    //         // 'postal_code'                 => 'nullable|string',
    //         // 'nid'                         => 'required',
    //         // 'cityzenship'                 => 'required|string|max:50',
    //         // 'postal_code'                 => 'required|string|max:50',
    //         // 'district'                    => 'required|string|max:50',
    //         // 'subdistrict'                 => 'required|string|max:50',

    //         'higest_education'            => 'required|string|max:50',
    //         'institution'                 => 'required|string|max:255',
    //         'year_of_completion'          => 'required|string|max:50',
    //         'current_occupation'          => 'required|string|max:50',
    //         'year_of_experience'          => 'nullable|string|max:50',
    //         'relavent_skills'             => 'nullable|string|max:255',
    //         'political_statment'          => 'nullable|string',

    //         // 'previous_political_position' => 'nullable|string',
    //         // 'political_experience'        => 'nullable|string',
    //         // 'political_platform_summary'  => 'nullable|string',
    //         // 'key_policy_political'        => 'nullable|string',
    //         // 'focus'                       => 'required|array',
    //         // 'focus.*'                     => 'required|exists:foci,id',
    //         // 'agree'                       => 'required|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return Helper::jsonResponse(false, 'Validation failed', 422, $validator->errors());
    //     }

    //     try {

    //         $maxIdNumber = User::max('id_number') ?? 0;

    //         //  Auto-generate email if missing
    //         $email = $request->input('email');
    //         if (empty($email)) {
    //             $first  = strtolower(preg_replace('/\s+/', '', $request->input('first_name')));
    //             $last   = strtolower(preg_replace('/\s+/', '', $request->input('last_name')));
    //             $random = rand(10, 999); // 2–3 digits
    //             $email  = $first . '.' . $last . $random . '@gmail.com';
    //         }

    //         $user = User::create([
    //             'name'           => $request->input('first_name') . ' ' . $request->input('last_name'),
    //             'slug'           => "cmc_" . (User::max('id') + 1),
    //             'email'          => strtolower($email),
    //             'password'       => Hash::make($request->input('12345678')),
    //             'otp'            => rand(1000, 9999),
    //             'otp_expires_at' => \Carbon\Carbon::now()->addMinutes(60),
    //             'id_number'      => $maxIdNumber + 1,
    //         ]);

    //         $user->profile()->create([
    //             'type'                        => $request->input('type'),
    //             'first_name'                  => $request->input('first_name'),
    //             'last_name'                   => $request->input('last_name'),
    //             'dob'                         => $request->input('dob'),
    //             'gender'                      => $request->input('gender'),
    //             'phone'                       => $request->input('phone'),

    //             'address'                     => $request->input('address') ?? '',
    //             'county'                      => $request->input('county') ?? '',
    //             'district'                    => $request->input('district') ?? '',

    //             'year_of_residence'           => $request->input('year_of_residence') ?? '',
    //             'is_contested_district'       => $request->input('is_contested_district') ?? '',
    //             'is_contested_other_district' => $request->input('is_contested_other_district') ?? '',
    //             'contested_district'          => $request->input('contested_district') ?? '',
    //             'contested_county'            => $request->input('contested_county') ?? '',

    //             'higest_education'            => $request->input('higest_education') ?? '',
    //             'institution'                 => $request->input('institution') ?? '',
    //             'year_of_completion'          => $request->input('year_of_completion') ?? '',
    //             'current_occupation'          => $request->input('current_occupation') ?? '',
    //             'year_of_experience'          => $request->input('year_of_experience') ?? '',
    //             'relavent_skills'             => $request->input('relavent_skills') ?? '',
    //             'political_statment'          => $request->input('political_statment') ?? '',

    //         ]);

    //         $user->assignRole('member');

    //         $data = [
    //             'user' => $user,
    //             'profile' => $user->profile,
    //         ];

    //         return Helper::jsonResponse(true, 'Registration  successfully', 200, $data);
    //     } catch (Exception $e) {
    //         return Helper::jsonResponse(false, $e->getMessage(), 500);
    //     }
    // }

}
