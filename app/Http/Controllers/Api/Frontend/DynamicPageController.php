<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;

class DynamicPageController extends Controller
{
    public function privacyAndPolicy() {
        $dynamicPage = Page::query()
                        ->where('status','active')
                        ->where('slug', 'privacy-policy')
                        ->firstOrFail();
        return view('frontend.layouts.page.singleDynamicPage', compact('dynamicPage'));
    }

    public function termsCndCondation() {
        $dynamicPage = Page::query()
                        ->where('status','active')
                        ->where('slug', 'terms-and-conditions')
                        ->firstOrFail();
        return view('frontend.layouts.page.singleDynamicPage', compact('dynamicPage'));
    }
}
