<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Focus;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class FocusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Focus::orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return Str::limit($data->title, 20);
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
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
                ->rawColumns(['category', 'subcategory', 'author', 'title', 'thumbnail', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.focus.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.layouts.focus.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $focus = new Focus();

            $focus->title = $data['title'];
            $focus->save();

            session()->put('t-success', 'area of focus created successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.focus.index')->with('t-success', 'area of focus created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $focus = Focus::findOrFail($id);
        return view('backend.layouts.focus.show', compact('focus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $focus = focus::findOrFail($id);
        return view('backend.layouts.focus.edit', compact('focus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'   => 'required|max:250'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $focus = Focus::findOrFail($id);

            $focus->title = $data['title'];
            $focus->save();

            session()->put('t-success', 'area of focus updated successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.focus.edit', $focus->id)->with('t-success', 'area of focus updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = Focus::findOrFail($id);

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
        $data = Focus::findOrFail($id);
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
