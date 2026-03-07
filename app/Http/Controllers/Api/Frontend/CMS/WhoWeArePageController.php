<?php

namespace App\Http\Controllers\Api\Frontend\CMS;

use App\Http\Controllers\Controller;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Models\CMS;

class WhoWeArePageController extends Controller
{
    public function index()
    {
        $data = [];

        $cmsItems           = CMS::where('page', PageEnum::WHOWEARE)->where('status', 'active')->get();
        $cmsCommon          = CMS::where('page', PageEnum::COMMON)->where('status', 'active')->get();


        $data['whoweare_banner']                = $cmsItems->where('section', SectionEnum::WHOWEARE_BANNER)->first();
        $data['whoweare_committed']             = $cmsItems->where('section', SectionEnum::WHOWEARE_COMMITTED)->first();
        $data['whoweare_committeds']            = $cmsItems->where('section', SectionEnum::WHOWEARE_COMMITTEDS)->values();
        $data['whoweare_difference']            = $cmsItems->where('section', SectionEnum::WHOWEARE_DIFFERENCE)->first();
        $data['whoweare_differences']           = $cmsItems->where('section', SectionEnum::WHOWEARE_DIFFERENCES)->values();
        $data['whoweare_mission']               = $cmsItems->where('section', SectionEnum::WHOWEARE_MISSION)->first();
        $data['whoweare_vission']               = $cmsItems->where('section', SectionEnum::WHOWEARE_VISSION)->first();
        $data['whoweare_discover_value']        = $cmsItems->where('section', SectionEnum::WHOWEARE_DISCOVER_VALUES)->first();
        $data['whoweare_discover_values']       = $cmsItems->where('section', SectionEnum::WHOWEARE_DISCOVER_VALUESS)->values();

        $data['common']['subscribe']     = $cmsCommon->where('section', SectionEnum::SUBSCRIBE)->first();
        $data['common']['footer']        = $cmsCommon->where('section', SectionEnum::FOOTER)->first();
        return Helper::jsonResponse(true, 'Who We Are Page', 200, $data);

    }
}
