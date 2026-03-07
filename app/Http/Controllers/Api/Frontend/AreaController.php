<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Area;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::where('status', 'active')->get();
        $data = [
            'areas' => $areas
        ];
        return Helper::jsonResponse(true, 'Areas retrived successfully', 200, $data);

    }
}
