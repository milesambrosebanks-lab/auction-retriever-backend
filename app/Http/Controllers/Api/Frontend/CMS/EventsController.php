<?php

namespace App\Http\Controllers\Api\Frontend\CMS;

use App\Http\Controllers\Controller;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Models\CMS;

class EventsController extends Controller
{
    public function index()
    {
        $data = [];

        $cmsItems           = CMS::where('page', PageEnum::EVENTS)->where('status', 'active')->get();
        $cmsCommon          = CMS::where('page', PageEnum::COMMON)->where('status', 'active')->get();

        $data['events_banner']                  = $cmsItems->where('section', SectionEnum::EVENTS_BANNER)->first();
        $data['events_list']                    = $cmsItems->where('section', SectionEnum::EVENTS_LIST)->first();
        $data['events_gallery']                 = $cmsItems->where('section', SectionEnum::EVENTS_GALLERY)->first();

        $data['common']['subscribe']     = $cmsCommon->where('section', SectionEnum::SUBSCRIBE)->first();
        $data['common']['faq']           = $cmsCommon->where('section', SectionEnum::FAQ)->first();
        $data['common']['signin']        = $cmsCommon->where('section', SectionEnum::SIGNIN)->first();
        $data['common']['help']          = $cmsCommon->where('section', SectionEnum::HELP)->first();
        $data['common']['footer']        = $cmsCommon->where('section', SectionEnum::FOOTER)->first();
        return Helper::jsonResponse(true, 'Who We Are Page', 200, $data);

    }
}
