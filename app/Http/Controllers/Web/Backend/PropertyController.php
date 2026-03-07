<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class PropertyController extends Controller
{

    public $route = 'admin.property';
    public $view = 'backend.layouts.property';

    public function __construct()
    {
        View::share('crud', 'property');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $attribute_id = null)
    {
        if ($request->ajax()) {
            $data = Property::query();
            if ($attribute_id != null) {
                $data = $data->where('attribute_id', $attribute_id);
            }
            $data = $data->with(['attribute'])
            ->orderBy('id', 'desc')
            ->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('attribute', function ($data) {
                    return "<a href='" . route('admin.attribute.show', $data->attribute_id) . "'>" . $data->attribute->name . "</a>";
                })
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

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-success fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-eye"></i>
                                </a>

                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['attribute', 'status', 'action'])
                ->make();
        }

        return view($this->view . ".index", [
            'attribute_id' => $attribute_id,
            'route' => $this->route,
            'view' => $this->view
        ]);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $attributes = Attribute::where('status', 1)->get();
        return view($this->view . ".create", [
            'attributes' => $attributes,
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
            'name'             => 'required|max:50',
            'attribute_id'     => 'required|exists:attributes,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();
            $property = new Property();
            $property->name = $data['name'];
            $property->attribute_id = $data['attribute_id'];
            $property->save();
            $message = 'post created successfully';
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return redirect()->route($this->route . '.index', $data['attribute_id'])->with('t-success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property, $id)
    {
        $property = Property::with(['attribute'])->where('id', $id)->first();
        return view($this->view . ".show", [
            'property' => $property,
            'route' => $this->route,
            'view' => $this->view
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property, $id)
    {
        $property = Property::findOrFail($id);
        $attributes = Attribute::where('status', 1)->get();
        return view($this->view . ".edit", [
            'property' => $property,
            'attributes' => $attributes,
            'route' => $this->route,
            'view' => $this->view
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|max:50',
            'attribute_id'     => 'required|exists:attributes,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();
            $property = Property::findOrFail($id);
            $property->name = $data['name'];
            $property->save();
            $message = 'post updated successfully';
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return redirect()->route($this->route . '.edit', $property->id)->with('t-success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = Property::findOrFail($id);

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
        $data = Property::findOrFail($id);
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
