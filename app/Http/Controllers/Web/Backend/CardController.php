<?php
namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CardController extends Controller
{
    public function print($slug)
    {

        $user            = User::where('slug', $slug)->first();
        // mark as printed
        // if ($user && $user->profile) {
        //     $user->profile->is_card_printed = true;
        //     $user->profile->save();
        // }

        $logoBase64      = base64_encode(file_get_contents(public_path('default/logo.png')));
        $whitelogoBase64 = base64_encode(file_get_contents(public_path('default/whitelogo.png')));
        $backLogoBase64  = base64_encode(file_get_contents(public_path('default/liberia-seal.png')));

        $avatarPath = public_path(
            $user->avatar && file_exists(public_path($user->avatar)) ? $user->avatar : 'default/profile.jpg'
        );

        $avatarBase64 = base64_encode(file_get_contents($avatarPath));

        /* $qrCode = base64_encode(QrCode::size(90)->generate(route('card.check', $user->slug)));
        $pdf = Pdf::loadView('card.web', compact('user', 'logoBase64', 'whitelogoBase64', 'avatarBase64', 'qrCode', 'backLogoBase64'))->setPaper('a4', 'landscape');
        return $pdf->stream(); */

        $qrCode = QrCode::size(90)->generate(route('card.check', $user->slug));
        return view('card.web', compact('user', 'logoBase64', 'whitelogoBase64', 'avatarBase64', 'qrCode', 'backLogoBase64'));

    }

    public function bulkPrint(Request $request)
    {
        $id_number      = explode(',', $request->input('id_numbers'));

        $users      = User::with('profile')->whereIn('id_number', $id_number)->orderBy('id_number','desc')->get();


        // $logoBase64 = base64_encode(file_get_contents(public_path('default/logo.png')));

        foreach ($users as $user) {


            // mark as printed
            // if ($user && $user->profile) {
            //     $user->profile->is_card_printed = true;
            //     $user->profile->save();
            // }

            $avatarPath = public_path(
                $user->avatar && file_exists(public_path($user->avatar))
                ? $user->avatar
                : 'default/profile.jpg'
            );

            // $user->avatarBase64 = base64_encode(file_get_contents($avatarPath));
            $user->qrCode       = QrCode::size(80)->generate(route('card.check', $user->slug));
        }

        return view('card.group', compact('users'));
    }

}
