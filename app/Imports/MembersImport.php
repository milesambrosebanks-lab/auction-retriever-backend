<?php
namespace App\Imports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class MembersImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Assume first row is header
        $header = $rows->shift()->toArray();

        foreach ($rows as $row) {
            $data = array_combine($header, $row->toArray());
            // dd($data);

            if (! isset($data['email']) || User::withTrashed()->where('email', $data['email'])->exists()) {
                continue; // skip this row if email exists
            }

            $password = Str::random(8);

            // Get next ID including soft-deleted users
            $lastUser = User::withTrashed()->latest('id')->first();
            // dd($lastUser);
            $nextId = $lastUser ? $lastUser->id + 1 : 1;

            $maxIdNumber = User::max('id_number') ?? 0;

            //  Auto-generate email if missing
            $email = $data['email'];
            if (empty($email)) {
                $first  = strtolower(preg_replace('/\s+/', '', $data['first_name']));
                $last   = strtolower(preg_replace('/\s+/', '', $data['last_name']));
                $random = rand(10, 999); // 2–3 digits
                $email  = $first . '.' . $last . $random . '@gmail.com';
            }

            $user = User::create([
                'name'              => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
                'slug'              => 'cmc_' . $nextId,
                'email'             => strtolower($email),
                'password'          => Hash::make($password),
                'email_verified_at' => Carbon::now(),
                'avatar'            => $data['avatar'] ?? 'avatars/default.png',
                'id_number'         => ($maxIdNumber ?? 0) + 1,
            ]);

            $profileData = [
                'registered_by'           => $data['registered_by'] ?? '',
                'first_name'           => $data['first_name'] ?? '',
                'last_name'            => $data['last_name'] ?? '',
                'phone'                => $data['phone'] ?? '',
                'gender'               => $data['gender'] ?? '',
                'dob'                  => $data['dob'] ?? null,
                'postal_code'          => $data['postal_code'] ?? '',
                'message'              => $data['message'] ?? '',
                'content'              => $data['content'] ?? '',
                'type'                 => $data['type'] ?? 'member',
                'country_of_residence' => $data['country_of_residence'] ?? '',
                'city'                 => $data['city'] ?? '',
            ];

            if (($data['country_of_residence'] ?? '') === 'Liberia') {
                $profileData['county']   = $data['county'] ?? '';
                $profileData['district'] = $data['district'] ?? '';
                $profileData['country']  = null;
                $profileData['address']  = null;
                $profileData['state']    = null;
            } else {
                $profileData['country']  = $data['country'] ?? '';
                $profileData['address']  = $data['address'] ?? '';
                $profileData['state']    = $data['state'] ?? '';
                $profileData['county']   = null;
                $profileData['district'] = null;
            }

            $user->profile()->create($profileData);
            $user->assignRole('member');
        }
    }
}
