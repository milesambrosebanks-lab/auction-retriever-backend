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

class HomeHeroController extends Controller
{
    protected $cmsService;

    public $name = "home";
    public $section = "hero";
    public $page = PageEnum::HOME;
    public $item = 'hero';

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

    public function content(CmsRequest $request)
    {

        try {
            $validatedData = $request->validated();
            $validatedData['page']    = $this->page;
            $validatedData['section'] = $this->item;

            $section = CMS::where('page', $this->page)->where('section', $this->item)->first();

            /* ---------- IMAGE ---------- */
            if ($request->hasFile('image')) {
                if ($section && $section->image && file_exists(public_path($section->image))) {
                    Helper::fileDelete(public_path($section->image));
                }
                $validatedData['image'] = Helper::fileUpload($request->file('image'), $this->section, time() . '_' . getFileName($request->file('image')));
            }

            /* ---------- SAVE ---------- */
            if ($section) {
                $section->update($validatedData);
            } else {
                CMS::create($validatedData);
            }


            return redirect()->route("admin.cms.{$this->name}.{$this->section}.index")->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }
}
