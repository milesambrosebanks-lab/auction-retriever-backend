<?php

namespace App\Http\Controllers\Web\Backend\CMS\Web\Home;

use App\Http\Controllers\Controller;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Models\CMS;
use Exception;
use App\Http\Requests\CmsRequest;
use App\Services\CmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use App\Traits\ApiResponse;

class HomeLifeWithoutController extends Controller
{
    use ApiResponse;
    protected $cmsService;

    public $page;
    public $component;
    public $section;

    public $sections;
    public $components;
    public $count;

    public $sub_section = false;

    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;

        $this->page = PageEnum::HOME;

        $this->component = ['title', 'sub_title', 'image',];
        $this->section = SectionEnum::LIFE_WITHOUT;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = CMS::where('page', $this->page)->where('section', $this->section)->orderBy('id', 'desc')->get();
        // dd($data);
        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {
                    $url = asset($data->image && file_exists(public_path($data->image)) ? $data->image : 'default/logo.svg');
                    return '<img src="' . $url . '" alt="image" style="width: 50px; max-height: 100px; margin-left: 20px;">';
                })
                ->addColumn('title', function ($data) {
                    return Str::limit($data->title, 20);
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin:auto; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white" title="edit">
                                    <i class="fa fa-edit"></i>
                                </a>

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-success fs-14 text-white " title="view">
                                    <i class="fa fa-eye"></i>
                                </a>

                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['image', 'title',  'status', 'action'])
                ->make();
        }
        return view("backend.layouts.cms.blade.index", ["data" => $data, "page" => $this->page->value, "section" => $this->section->value, "components" => $this->component, 'sections' => $this->sections]);
    }

    public function edit($id)
    {
        $data = CMS::where('page', $this->page)->where('section', $this->section)->where('id', $id)->first();
        return view("backend.layouts.cms.blade.edit", ["data" => $data, "page" => $this->page->value, "section" => $this->section->value, "components" => $this->component, 'sections' => $this->sections]);
    }
    public function create()
    {
        // $data = CMS::where('page', $this->page)->where('section', $this->section)->latest()->first();
        return view("backend.layouts.cms.blade.create", ["page" => $this->page->value, "section" => $this->section->value, "components" => $this->component, 'sections' => $this->sections]);
    }
    public function store(CmsRequest $request)
    {
        $validatedData = $request->validated();
        try {
            $validatedData['page'] = $this->page;
            $validatedData['section'] = $this->section;
            $section = CMS::where('page', $this->page)->where('section', $this->section)->first();

            if ($request->hasFile('bg')) {
                if ($section && $section->bg && file_exists(public_path($section->bg))) {
                    Helper::fileDelete(public_path($section->bg));
                }
                $validatedData['bg'] = Helper::fileUpload($request->file('bg'), $this->section->value, time() . '_' . getFileName($request->file('bg')));
            }

            if ($request->hasFile('image')) {
                if ($section && $section->image && file_exists(public_path($section->image))) {
                    Helper::fileDelete(public_path($section->image));
                }
                $validatedData['image'] = Helper::fileUpload($request->file('image'), $this->section->value, time() . '_' . getFileName($request->file('image')));
            }


            // Generate a unique slug
            do {
                $validatedData['slug'] = 'slug_' . Str::random(8);
            } while (CMS::where('slug', $validatedData['slug'])->exists());

            CMS::create($validatedData);


            // Clear the cache and refresh it
            if (Cache::has('cms')) {
                Cache::forget('cms');
            }
            Cache::put('cms', CMS::where('status', 'active')->get());

            return redirect()->route("admin.cms.{$this->page->value}.{$this->section->value}.index")->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $data = CMS::where('id', $id)->first();
        return view("backend.layouts.cms.blade.show", ["data" => $data, "page" => $this->page->value, "section" => $this->section->value, "components" => $this->component,]);
    }

    public function update(CmsRequest $request, $id)
    {
        $validatedData = $request->validated();
        try {
            $validatedData['page'] = $this->page;
            $validatedData['section'] = $this->section;
            $section = CMS::find($id);

            if ($request->hasFile('bg')) {
                if ($section && $section->bg && file_exists(public_path($section->bg))) {
                    Helper::fileDelete(public_path($section->bg));
                }
                $validatedData['bg'] = Helper::fileUpload($request->file('bg'), $this->section->value, time() . '_' . getFileName($request->file('bg')));
            }

            if ($request->hasFile('image')) {
                if ($section && $section->image && file_exists(public_path($section->image))) {
                    Helper::fileDelete(public_path($section->image));
                }
                $validatedData['image'] = Helper::fileUpload($request->file('image'), $this->section->value, time() . '_' . getFileName($request->file('image')));
            }


            if ($section) {
                $section->update($validatedData);
            }

            // Clear the cache and refresh it
            if (Cache::has('cms')) {
                Cache::forget('cms');
            }
            Cache::put('cms', CMS::where('status', 'active')->get());

            return redirect()->route("admin.cms.{$this->page->value}.{$this->section->value}.index")->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }



    public function status($id)
    {

        try {
            $section = CMS::find($id);
            if ($section) {
                $section->update(['status' => $section->status == 'active' ? 'inactive' : 'active']);
            }
            return $this->success(['status' => 't-success', 'message' => 'Status updated successfully'], 'Status updated successfully');
        } catch (Exception $e) {
            // return back()->with('t-error', $e->getMessage());
            return $this->success(['status' => 't-error', 'message' => 'Status updated failed', 'Status updated failed']);
        }
    }
    public function destroy($id)
    {

        try {
            $section = CMS::find($id);
            if ($section) {
                if ($section->bg && file_exists(public_path($section->bg))) {
                    Helper::fileDelete(public_path($section->bg));
                }
                if ($section->image && file_exists(public_path($section->image))) {
                    Helper::fileDelete(public_path($section->image));
                }
                $section->delete();
            }
            return $this->success(['status' => 't-success', 'message' => 'Deleted successfully'], 'Deleted successfully');
        } catch (Exception $e) {
            return back()->with('t-error', $e->getMessage());
            return $this->error(['status' => 't-error', 'message' => 'Delete failed', 'Delete failed']);
        }
    }
}
