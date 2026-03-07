<?php

namespace App\Http\Controllers\Api\Frontend\CMS;

use App\Http\Controllers\Controller;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Models\CMS;

class MeetOurLeadersDetailsController extends Controller
{
    public function index()
    {
        $data = [];

        $cmsItems           = CMS::where('page', PageEnum::MEETOURLEADERSDETAILS)->where('status', 'active')->get();
        $cmsCommon          = CMS::where('page', PageEnum::COMMON)->where('status', 'active')->get();

        $data['meetourleaders_banner']                = $cmsItems->where('section', SectionEnum::MEETOURLEADERSDETAILS_BANNER)->first();
        $data['meetourleaders_related_post']          = $cmsItems->where('section', SectionEnum::MEETOURLEADERSDETAILS_RELATED_POST)->first();

        $data['common']['subscribe']     = $cmsCommon->where('section', SectionEnum::SUBSCRIBE)->first();
        $data['common']['faq']           = $cmsCommon->where('section', SectionEnum::FAQ)->first();
        $data['common']['signin']        = $cmsCommon->where('section', SectionEnum::SIGNIN)->first();
        $data['common']['help']          = $cmsCommon->where('section', SectionEnum::HELP)->first();
        $data['common']['footer']        = $cmsCommon->where('section', SectionEnum::FOOTER)->first();
        
        return Helper::jsonResponse(true, 'Who We Are Page', 200, $data);

    }
}
