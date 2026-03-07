<?php
namespace App\Http\Controllers\Api\Frontend\CMS;

use App\Models\CMS;
use App\Enums\PageEnum;
use App\Helpers\Helper;
use App\Enums\SectionEnum;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class HomePageController extends Controller
{
    public function index()
    {
        $data = [];

        $cmsItems  = CMS::where('page', PageEnum::HOME)->where('status', 'active')->get();
        $cmsCommon = CMS::where('page', PageEnum::COMMON)->where('status', 'active')->get();

        $data['stat'] = DB::table('users')->whereNotNull('id_number')->count();

        $data['home_banner']    = $cmsItems->where('section', SectionEnum::HOME_BANNER)->first();
        $data['home_about_cmc'] = $cmsItems->where('section', SectionEnum::HOME_ABOUT_CMC)->first();
        $data['home_news']      = $cmsItems->where('section', SectionEnum::HOME_NEWS)->first();
        $data['home_leaders']   = $cmsItems->where('section', SectionEnum::HOME_LEADERS)->first();
        $data['home_events']    = $cmsItems->where('section', SectionEnum::HOME_EVENTS)->first();
        $data['home_donate']    = $cmsItems->where('section', SectionEnum::HOME_DONATE)->first();

        $data['common']['subscribe'] = $cmsCommon->where('section', SectionEnum::SUBSCRIBE)->first();
        $data['common']['faq']       = $cmsCommon->where('section', SectionEnum::FAQ)->first();
        $data['common']['signin']    = $cmsCommon->where('section', SectionEnum::SIGNIN)->first();
        $data['common']['help']      = $cmsCommon->where('section', SectionEnum::HELP)->first();
        $data['common']['footer']    = $cmsCommon->where('section', SectionEnum::FOOTER)->first();

        return Helper::jsonResponse(true, 'Home Page', 200, $data);

    }
}
