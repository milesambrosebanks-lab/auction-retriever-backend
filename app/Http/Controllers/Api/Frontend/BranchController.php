<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index()
    {
        $branch = Branch::where('status', 'active')->get();
        $data = [
            'branch' => $branch
        ];
        return Helper::jsonResponse(true, 'Branch retrived successfully', 200, $data);
    }

    public function getByArea($area_id)
    {
        $branch = Branch::where('area_id', $area_id)->where('status', 'active')->get();
        $data = [
            'branch' => $branch
        ];
        return Helper::jsonResponse(true, 'Branch retrived successfully', 200, $data);
    }

    public function show($id)
    {
        $branch = Branch::where('id', $id)->first();
        $data = [
            'branch' => $branch
        ];
        return Helper::jsonResponse(true, 'Branch retrived successfully', 200, $data);
    }

}
