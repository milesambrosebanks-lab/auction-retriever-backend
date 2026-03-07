<?php

namespace App\Http\Controllers\Web\Frontend;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CardController extends Controller
{

    public function check($slug)
    {
        $user = User::with('profile')->where('slug', $slug)->first();
        if ($user) {
            $logoBase64 = base64_encode(file_get_contents(public_path('default/logo.png')));
            $whitelogoBase64 = base64_encode(file_get_contents(public_path('default/whitelogo.png')));
            $backLogoBase64 = base64_encode(file_get_contents(public_path('default/liberia-seal.png')));

            $avatarPath = public_path(
                $user->avatar && file_exists(public_path($user->avatar)) ? $user->avatar : 'default/profile.jpg'
            );

            $avatarBase64 = base64_encode(file_get_contents($avatarPath));
            $qrCode = QrCode::size(90)->generate(route('card.check', $user->slug));

            return view('card.web', compact('user', 'logoBase64', 'whitelogoBase64', 'avatarBase64', 'qrCode', 'backLogoBase64'));
        } else {
            return "<div style='display: flex; justify-content: center; align-items: center; height: 100vh;'><h1 style='text-align: center; font-size: 50px; color: #0047ab;'>User Not Found</h1></div>";
        }
    }



    public function bulkPrint(Request $request)
    {
        $ids   = explode(',', $request->input('ids'));
        $users = User::with('profile')->whereIn('id', $ids)->get();

        $logoBase64 = base64_encode(file_get_contents(public_path('default/logo.png')));

        foreach ($users as $user) {
            $avatarPath = public_path(
                $user->avatar && file_exists(public_path($user->avatar))
                ? $user->avatar
                : 'default/profile.jpg'
            );

            $user->avatarBase64 = base64_encode(file_get_contents($avatarPath));
            $user->qrCode       = QrCode::size(80)->generate(route('card.check', $user->slug));
        }

        return view('card.group', compact('users', 'logoBase64'));
    }
}
