<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Http\Resources\CMSResource;
use App\Http\Resources\ProductResource;
use App\Models\CMS;
use App\Models\Product;
use App\Models\Setting;
use App\Traits\CMSData;

class HomeController extends Controller
{
    use CMSData;
    public static function banner()
    {
        $banner = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::BANNER)->first();
        return $banner ? new CMSResource($banner) : null;
    }
    public static function hero()
    {
        $hero  = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::HERO)->first();
        return $hero ? new CMSResource($hero) : null;
    }

    public static function whatMakes()
    {
        $whats_make             = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::WHAT_MAKES->value)->get();
        $whats_make_section       = CMS::where('page', PageEnum::HOME)
            ->where('section', PageEnum::HOME->value . '-' . SectionEnum::WHAT_MAKES->value)
            ->where('slug', SectionEnum::WHAT_MAKES)->first();
        return [
            'section' => $whats_make_section ? new CMSResource($whats_make_section) : null,
            'data' => $whats_make ? CMSResource::collection($whats_make) : null
        ];
    }
    public static function lifeWithout()
    {
        $life_without             = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::LIFE_WITHOUT->value)->get();
        $life_without_section       = CMS::where('page', PageEnum::HOME)
            ->where('section', PageEnum::HOME->value . '-' . SectionEnum::LIFE_WITHOUT->value)
            ->where('slug', SectionEnum::LIFE_WITHOUT)->first();
        return [
            'section' => $life_without_section ? new CMSResource($life_without_section) : null,
            'data' => $life_without ? CMSResource::collection($life_without) : null
        ];
    }
    public static function supplyment()
    {
        $supplyment             = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::SUPPLYMENT->value)->get();
        $supplyment_section       = CMS::where('page', PageEnum::HOME)
            ->where('section', PageEnum::HOME->value . '-' . SectionEnum::SUPPLYMENT->value)
            ->where('slug', SectionEnum::SUPPLYMENT)->first();
        return [
            'section' => $supplyment_section ? new CMSResource($supplyment_section) : null,
            'data' => $supplyment ? CMSResource::collection($supplyment) : null
        ];
    }
    public static function founderStory()
    {
        $founder_story          = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::FOUNDER_STORY->value)->first();
        return $founder_story ? new CMSResource($founder_story) : null;
    }
    public static function homeAbout()
    {
        $home_about             = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::ABOUT->value)->first();
        return $home_about ? new CMSResource($home_about) : null;
    }
    public static function settings()
    {
        $settings               = Setting::first();
        return $settings ?? null;
    }
    public static function subscriber()
    {
        $subscriber = CMS::where('page', 'all')->where('section', 'all-subscriber')->where('slug', 'subscriber')->orderBy('id', 'desc')->first();
        return $subscriber ? new CMSResource($subscriber) : null;
    }
    public static function pdf_request()
    {
        $pdf_request = CMS::where('page', 'all')->where('section', 'all-pdf-request')->where('slug', 'pdf-request')->orderBy('id', 'desc')->first();
        return $pdf_request ? new CMSResource($pdf_request) : null;
    }

    public static function bundleProduct()
    {
        $section = CMS::where('page', 'product')->where('section', 'product-bundle')->where('slug', 'bundle')->orderBy('id', 'desc')->first();
        return $section ? new CMSResource($section) : null;
    }
    public static function singleProduct()
    {
        $section = CMS::where('page', 'product')->where('section', 'product-single')->where('slug', 'single')->orderBy('id', 'desc')->first();
        return $section ? new CMSResource($section) : null;
    }

    public function index()
    {
        $data = [];

        // $banner                 = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::BANNER)->first();
        // $hero                   = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::HERO)->first();
        // $whats_make             = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::WHAT_MAKES->value)->get();
        // $whats_make_section       = CMS::where('page', PageEnum::HOME)
        //     ->where('section', PageEnum::HOME->value . '-' . SectionEnum::WHAT_MAKES->value)
        //     ->where('slug', SectionEnum::WHAT_MAKES)->first();

        // $supplyment             = CMS::where('page', PageEnum::HOME->value)->where('section', SectionEnum::SUPPLYMENT->value)->get();
        // $supplyment_section       = CMS::where('page', PageEnum::HOME)
        //     ->where('section', PageEnum::HOME->value . '-' . SectionEnum::SUPPLYMENT->value)
        //     ->where('slug', SectionEnum::SUPPLYMENT)->first();
        // $life_without           = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::LIFE_WITHOUT->value)->get();
        // $life_without_section       = CMS::where('page', PageEnum::HOME)
        //     ->where('section', PageEnum::HOME->value . '-' . SectionEnum::LIFE_WITHOUT->value)
        //     ->where('slug', SectionEnum::LIFE_WITHOUT)->first();

        // $founder_story          = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::FOUNDER_STORY->value)->first();
        // $home_about             = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::ABOUT->value)->first();
        // $settings               = Setting::first();

        $data['banner']             = $this->banner();
        $data['hero']               = $this->hero();
        $data['whats_make']         = $this->whatMakes();
        $data['supplyment']         = $this->supplyment();
        $data['life_without']       = $this->lifeWithout();
        $data['founder_story']      = $this->founderStory();
        // $data['home_about']         = $this->homeAbout();
        $data['subscriber']         = $this->subscriber();
        $data['pdf_request']        = $this->pdf_request();
        $data['settings']           = $this->settings();

        return Helper::jsonResponse(true, 'Home Page', 200, $data);
    }
    public function educations()
    {
        $education_approach     = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::EDUCATION_APPROACH->value)->get();
        $education_approach_section       = CMS::where('page', PageEnum::HOME)
            ->where('section', PageEnum::HOME->value . '-' . SectionEnum::EDUCATION_APPROACH->value)
            ->where('slug', SectionEnum::EDUCATION_APPROACH)->first();
        $data = $education_approach ? CMSResource::collection($education_approach) : null;

        $data = $data->map(function ($data) {
            return [
                'id' => $data->id,
                'title' => $data->title,
                'sub_title' => $data->sub_title,
                'image' => asset($data->image),
            ];
        });

        return Helper::jsonResponse(true, 'Education Approach', 200, [
            'section' => $education_approach_section ? new CMSResource($education_approach_section) : null,
            'data' => $data
        ]);
    }
    public function educationsSingle($education_approach_id)
    {
        $education_approach     = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::EDUCATION_APPROACH->value)->where('id', $education_approach_id)->first();
        $data = $education_approach ? new CMSResource($education_approach) : null;

        return Helper::jsonResponse(true, 'Education Approach', 200, $data);
    }




    public function productIndex()
    {
        $data = [];

        // $hero           = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::HERO)->first();
        $bundle        = Product::where('status', 'active')->where('type','bundle')->orderBy('id', 'desc')->first();
        $product        = Product::where('status', 'active')->where('type','single')->orderBy('id', 'desc')->get();
        $worktogether   = CMS::where('page', PageEnum::HOME->value)->where('section', SectionEnum::WORK_TOGETHER->value)->first();
        // $life_without   = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::LIFE_WITHOUT->value)->get();


        $data['hero'] = $this->hero();
        $data['product'] = [
            'section'=>$this->singleProduct(),
            'data'=>$product ?  ProductResource::collection($product) : null
        ];
        $data['bundle'] = [
            'section'=>$this->bundleProduct(),
            'data'=>$bundle ?  new ProductResource($bundle) : null
        ];
        $data['life_without'] = $this->lifeWithout();
        $data['work_together'] = $worktogether ? new CMSResource($worktogether) : null;



        return Helper::jsonResponse(true, 'Home Page', 200, $data);
    }
}
