<?php

namespace App\Http\Controllers\Web\Backend\CMS\Web\Home;

use App\Http\Controllers\Controller;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Models\CMS;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Requests\CmsRequest;
use App\Services\CmsService;
use Illuminate\Support\Facades\Log;

class HomeBannerController extends Controller
{
    protected $cmsService;

    public $name = "home";
    public $section = "banner";
    public $page = PageEnum::HOME;
    public $item = SectionEnum::BANNER;

    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $data = CMS::where('page', $this->page)->where('section', $this->item)->latest()->first();
        return view("backend.layouts.cms.{$this->name}.{$this->section}.index", ["data" => $data, "name" => $this->name, "section" => $this->section]);
    }

    public function content(Request $request)
    {

        try {
            $validatedData = $request->validate([
                'image' => 'nullable|file|mimes:jpg,jpeg,png,gif,svg',
                'bg'    => 'nullable|file|mimes:mp4,mov,ogg,qt',
            ]);
            $validatedData['page']    = $this->page;
            $validatedData['section'] = $this->item;

            $section = CMS::where('page', $this->page)->where('section', $this->item)->first();

            /* ---------- IMAGE ---------- */
            if ($request->hasFile('image')) {
                if ($section && $section->image && file_exists(public_path($section->image))) {
                    Helper::fileDelete(public_path($section->image));
                }
                $validatedData['image'] = Helper::fileUpload($request->file('image'), $this->section, time() . '_' . getFileName($request->file('image')));
                $validatedData['title'] = 'image';
                $validatedData['bg'] = null;

            }
             if ($request->hasFile('bg')) {
                if ($section && $section->bg && file_exists(public_path($section->bg))) {
                    Helper::fileDelete(public_path($section->bg));
                }
                $validatedData['bg'] = Helper::fileUpload($request->file('bg'), $this->section, time() . '_' . getFileName($request->file('bg')));
                $validatedData['title'] = 'video';
                $validatedData['image'] = null;

            }

            /* ---------- SAVE ---------- */
            if ($section) {
                $section->update($validatedData);
            } else {
                CMS::create($validatedData);
            }


            return redirect()->route("admin.cms.{$this->name}.{$this->section}.index")->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            Log::error("Error in HomeBannerController@content: " . $e->getMessage());
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }
}
