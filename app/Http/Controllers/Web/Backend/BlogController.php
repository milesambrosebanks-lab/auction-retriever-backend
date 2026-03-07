<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class BlogController extends Controller
{

    public $part;
    public $route;
    public $view;

    public function __construct()
    {
        $this->part = 'blog';
        $this->route = 'admin.' . $this->part;
        $this->view = 'backend.layouts.' . $this->part;
        View::share('crud', 'blog');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Blog::orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return Str::limit($data->title, 20);
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';

                    $status = '<div class="d-flex justify-content-center align-items-center">';
                    $status .= '<div class="form-check form-switch" style="position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX(' . $sliderTranslateX . ');"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {

                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-edit"></i>
                                </a>

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-success fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-eye"></i>
                                </a>

                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['title', 'status', 'action'])
                ->make();
        }

        return view($this->view . ".index", [
            'part' => $this->part,
            'route' => $this->route
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view($this->view . ".create", [
            'part' => $this->part,
            'route' => $this->route
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250',
            'description'       => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $blog = new Blog();

            $blog->title = $data['title'];
            $blog->description = $data['description'];
            $blog->save();

            session()->put('t-success', 'Created Successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route($this->route . '.index')->with('t-success', 'Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog, $id)
    {
        $blog = Blog::where('id', $id)->first();
        return view($this->view . ".show", [
            'part' => $this->part,
            'route' => $this->route,
            'blog' => $blog
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog, $id)
    {
        $blog = Blog::findOrFail($id);
        return view($this->view . ".edit", [
            'part' => $this->part,
            'route' => $this->route,
            'blog' => $blog
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250',
            'description'       => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $blog = Blog::findOrFail($id);

            $blog->title = $data['title'];
            $blog->description = $data['description'];
            $blog->save();

            session()->put('t-success', 'Updated Successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route($this->route . '.edit', $blog->id)->with('t-success', 'Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = Blog::findOrFail($id);

            $data->delete();
            return response()->json([
                'status' => 't-success',
                'message' => 'Your action was successful!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Your action was successful!'
            ]);
        }
    }

    public function status(int $id): JsonResponse
    {
        $data = Blog::findOrFail($id);
        if (!$data) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Item not found.',
            ]);
        }
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status' => 't-success',
            'message' => 'Your action was successful!',
        ]);
    }
}
