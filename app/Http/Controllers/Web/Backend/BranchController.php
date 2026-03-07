<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Branch;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Branch::with(['area'])->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('area', function ($data) {
                    return "<a href='" . route('admin.area.show', $data->area_id) . "'>" . $data->area->title . "</a>";
                })
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
                ->rawColumns(['title', 'area', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.branch.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $areas = Area::where('status', 'active')->get();
        return view('backend.layouts.branch.create', compact('areas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id'           => 'required|exists:areas,id',
            'title'             => 'required|max:250',
            'description'       => 'nullable|string',
            'longitude'         => 'nullable|string',
            'latitude'          => 'nullable|string',
            'phone'             => 'nullable|string|max:15',
            'email'             => 'nullable|string|email|max:255',
            'address'           => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $branch = new Branch();

            $branch->title = $data['title'];
            $branch->description = $data['description'] ?? null;
            $branch->longitude = $data['longitude'] ?? null;
            $branch->latitude = $data['latitude'] ?? null;
            $branch->phone = $data['phone'] ?? null;
            $branch->email = $data['email'] ?? null;
            $branch->address = $data['address'] ?? null;
            $branch->area_id = $data['area_id'];
            $branch->save();

            session()->put('t-success', 'Branch created successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.branch.index')->with('t-success', 'Branch created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $branch = Branch::with(['area'])->where('id', $id)->first();
        return view('backend.layouts.branch.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        $areas = Area::where('status', 'active')->get();
        return view('backend.layouts.branch.edit', compact('branch', 'areas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'area_id'           => 'required|exists:areas,id',
            'title'             => 'required|max:250',
            'description'       => 'nullable|string',
            'longitude'         => 'nullable|string',
            'latitude'          => 'nullable|string',
            'phone'             => 'nullable|string|max:15',
            'email'             => 'nullable|string|email|max:255',
            'address'           => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $branch = Branch::findOrFail($id);

            $branch->title = $data['title'];
            $branch->description = $data['description'] ?? null;
            $branch->longitude = $data['longitude'] ?? null;
            $branch->latitude = $data['latitude'] ?? null;
            $branch->phone = $data['phone'] ?? null;
            $branch->email = $data['email'] ?? null;
            $branch->address = $data['address'] ?? null;
            $branch->area_id = $data['area_id'];
            $branch->save();

            session()->put('t-success', 'branch updated successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.branch.edit', $branch->id)->with('t-success', 'branch updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = Branch::findOrFail($id);

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
        $data = Branch::findOrFail($id);
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
