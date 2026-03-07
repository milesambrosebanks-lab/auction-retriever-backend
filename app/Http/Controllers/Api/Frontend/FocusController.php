<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Focus;

class FocusController extends Controller
{
    public function index()
    {
        $focus = Focus::where('status', 'active')->get();
        $data = [
            'focus' => $focus
        ];
        return Helper::jsonResponse(true, 'Category', 200, $data);

    }
}
