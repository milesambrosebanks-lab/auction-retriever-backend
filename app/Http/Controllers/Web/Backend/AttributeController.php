<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class AttributeController extends Controller
{

    public $route = 'admin.attribute';
    public $view = 'backend.layouts.attribute';

    public function __construct()
    {
        View::share('crud', 'attribute');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Attribute::with(['property'])->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($data) {

                    $backgroundColor = $data->status == 1 ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == 1 ? '26px' : '2px';
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

                                <a href="#" type="button" onclick="goToProperty(' . $data->id . ')" class="btn btn-info fs-14 text-white delete-icn" title="Property">
                                    <i class="fe fe-sliders"></i>
                                </a>

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-success fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-eye"></i>
                                </a>

                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }

        return view($this->view . ".index", [
            'route' => $this->route, 
            'view' => $this->view
        ]);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view($this->view . ".create", [
            'route' => $this->route,
            'view' => $this->view
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|max:250'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();
            $attribute = new Attribute();
            $attribute->name = $data['name'];
            $attribute->save();
            session()->put('t-success', 'Created Successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route($this->route . '.index')->with('t-success', 'Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $attribute = Attribute::with(['property'])->where('id', $id)->first();
        return view($this->view . ".show", [
            'route' => $this->route,
            'view' => $this->view,
            'attribute' => $attribute
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $attribute = Attribute::findOrFail($id);
        return view($this->view . ".edit", [
            'route' => $this->route,
            'view' => $this->view,
            'attribute' => $attribute
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|max:250'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();
            $attribute = Attribute::findOrFail($id);
            $attribute->name = $data['name'];
            $attribute->save();
            session()->put('t-success', 'Updated Successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route($this->route . '.edit', $attribute->id)->with('t-success', 'Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Attribute::findOrFail($id);
        try {
            $data->delete();
            return response()->json([
                'status' => 't-success',
                'message' => 'Your action was successful!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Something went wrong! ' . $e->getMessage()
            ]);
        }
    }

    public function status(int $id): JsonResponse
    {
        $data = Attribute::findOrFail($id);
        if (!$data) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Item not found.',
            ]);
        }
        $data->status = $data->status === 1 ? 0 : 1;
        $data->save();
        return response()->json([
            'status' => 't-success',
            'message' => 'Your action was successful!',
        ]);
    }
}
