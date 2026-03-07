<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Exception;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:50',
            'email'     => 'required|email|max:100',
            'phone'     => 'required|string|max:20',
            'subject'   => 'required|string|max:100',
            'message'   => 'required|string|max:1000'
        ]);

        try {
            Contact::create($request->only('name', 'email', 'phone', 'subject', 'message'));
            return Helper::jsonResponse(true, 'your message has been sent', 200, []);
        } catch (Exception $e) {
            return Helper::jsonResponse(false, 'Something went wrong', 500, []);
        }
    }
}
