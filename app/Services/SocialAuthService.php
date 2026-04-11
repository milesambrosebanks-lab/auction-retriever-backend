<?php
namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SocialAuthService
{
    public function findOrCreateUser(object $socialUser, string $provider): User
    {
        $field = $provider . '_id'; // google_id | apple_id

        // আগে social ID দিয়ে খোঁজো
        $user = User::withoutGlobalScope('active_account')
            ->where($field, $socialUser->getId())
            ->first();

        // না পেলে email দিয়ে খোঁজো
        if (!$user && $socialUser->getEmail()) {
            $user = User::withoutGlobalScope('active_account')
                ->where('email', $socialUser->getEmail())
                ->first();
        }

        if ($user && (bool) $user->is_deleted) {
            abort(403, 'Your account has been deleted.');
        }

        // একদমই নতুন user
        if (!$user) {
            $user = $this->createUser($socialUser, $provider, $field);
        } else {
            // Existing user এ social ID update করো
            $user->update([
                $field           => $socialUser->getId(),
                'social_avatar'  => $socialUser->getAvatar() ?? $user->social_avatar,
            ]);
        }

        return $user;
    }

    private function createUser(object $socialUser, string $provider, string $field): User
    {
        do {
            $slug = "user_" . rand(1000000000, 9999999999);
        } while (User::where('slug', $slug)->exists());

        $user = User::create([
            'name'             => $socialUser->getName() ?? 'User',
            'slug'             => $slug,
            'email'            => $socialUser->getEmail(),
            'password'         => bcrypt(Str::random(32)),
            $field             => $socialUser->getId(),
            'social_avatar'    => $socialUser->getAvatar(),
            'auth_type'        => $provider,
            'otp_verified_at'  => now(), // social login এ email already verified
            'status'           => 'active',
            'last_activity_at' => now(),
        ]);

        // Role assign
        DB::table('model_has_roles')->insert([
            'role_id'    => 4,
            'model_type' => 'App\Models\User',
            'model_id'   => $user->id,
        ]);

        return $user;
    }
}
