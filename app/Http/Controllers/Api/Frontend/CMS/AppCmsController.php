<?php

namespace App\Http\Controllers\Api\Frontend\CMS;

use App\Http\Controllers\Controller;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Models\CMS;

class AppCmsController extends Controller
{
    public function welcome()
    {
        $data = [];

        $cmsItems           = CMS::where('page', PageEnum::APP_WELCOME)->where('status', 'active')->get();

        $data['screen']     = $cmsItems->where('section', SectionEnum::SCREEN)->first();

        return Helper::jsonResponse(true, 'welcome', 200, $data);

    }

    public function home()
    {
        $data = [];

        $cmsItems           = CMS::where('page', PageEnum::APP_HOME)->where('status', 'active')->get();

        $data['screen']     = $cmsItems->where('section', SectionEnum::SCREEN)->first();

        return Helper::jsonResponse(true, 'home', 200, $data);

    }

    public function donation()
    {
        $data = [];

        $cmsItems           = CMS::where('page', PageEnum::APP_DONATION)->where('status', 'active')->get();

        $data['screen']     = $cmsItems->where('section', SectionEnum::SCREEN)->first();

        return Helper::jsonResponse(true, 'Who We Are Page', 200, $data);

    }

    public function onboarding()
    {
        $data = [];

        $cmsItems           = CMS::where('page', PageEnum::APP_ONBOARDING)->where('status', 'active')->get();

        $data['first_screen']    = $cmsItems->where('section', SectionEnum::FIRST)->first();
        $data['second_screen']   = $cmsItems->where('section', SectionEnum::SECOND)->first();
        $data['third_screen']    = $cmsItems->where('section', SectionEnum::THIRD)->first();

        return Helper::jsonResponse(true, 'Who We Are Page', 200, $data);

    }

    public function partyPolicy()
    {
        $data = [];

        $cmsItems           = CMS::where('page', PageEnum::APP_PARTY_POLICY)->where('status', 'active')->get();

        $data['screen']     = $cmsItems->where('section', SectionEnum::SCREEN)->first();

        return Helper::jsonResponse(true, 'Who We Are Page', 200, $data);

    }

    public function whoWeAre()
    {
        $data = [];

        $cmsItems           = CMS::where('page', PageEnum::APP_WHO_WE_ARE)->where('status', 'active')->get();

        $data['committed_section']    = $cmsItems->where('section', SectionEnum::COMMITTED)->first();

        $data['different_section']    = $cmsItems->where('section', SectionEnum::DIFFERENT)->first();
        $data['different_sections']   = $cmsItems->where('section', SectionEnum::DIFFERENTS)->values();

        $data['mession_and_vision']     = $cmsItems->where('section', SectionEnum::MESSION_AND_VISION)->first();
        $data['mession_and_visions']    = $cmsItems->where('section', SectionEnum::MESSION_AND_VISIONS)->values();

        $data['discover_section']     = $cmsItems->where('section', SectionEnum::DISCOVER)->first();
        $data['discover_sections']    = $cmsItems->where('section', SectionEnum::DISCOVERS)->values();

        return Helper::jsonResponse(true, 'Who We Are Page', 200, $data);

    }
}
