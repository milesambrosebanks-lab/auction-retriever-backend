<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Models\Leader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class LeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Leader::orderBy('order_id', 'asc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($data) {
                    return Str::limit($data->name, 20);
                })
                ->addColumn('position', function ($data) {
                    return Str::limit($data->position, 20);
                })
                ->addColumn('leader', function ($data) {
                    return $data->is_leader ? 'Yes' : 'No';
                })
                ->addColumn('thumbnail', function ($data) {
                    if ($data->thumbnail) {
                        $url = asset($data->thumbnail);
                        return '<img src="' . $url . '" alt="thumbnail" width="50px" height="50px" style="margin-left:20px;">';
                    } else {
                        return '<img src="' . asset('default/logo.svg') . '" alt="image" width="50px" height="50px" style="margin-left:20px;">';
                    }
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
                ->rawColumns(['name', 'position', 'leader', 'thumbnail', 'status', 'action'])
                ->setRowId(function ($data) {
                    return $data->id;
                })
                ->setRowAttr([
                    'order_id' => function($data) {
                        return $data->order_id;
                    },
                ])
                ->make();
        }
        return view("backend.layouts.leader.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $positions = Leader::select('position')->distinct()->get()->pluck('position')->toArray();
        return view('backend.layouts.leader.create', compact('positions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|max:250',
            'content'          => 'nullable|string',
            'thumbnail'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'position'         => 'nullable|string|max:250',
            'is_leader'        => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $leader = new Leader();

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = Helper::fileUpload($request->file('thumbnail'), 'leader', time() . '_' . getFileName($request->file('thumbnail')));
            }

            $leader->slug = Helper::makeSlug(Leader::class, $data['name']);

            if ($data['is_leader'] == true) {
                Leader::where('is_leader', true)->update(['is_leader' => false]);
            }

            $leader->name = $data['name'];
            $leader->thumbnail = $data['thumbnail'];
            $leader->content = $data['content'] ?? '';
            $leader->position = $data['position'] ?? '';
            $leader->is_leader = $data['is_leader'] ?? false;
            $leader->order_id = Leader::max('order_id') + 1;
            $leader->save();

            session()->put('t-success', 'leader created successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.leader.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $leader = Leader::findOrFail($id);
        return view('backend.layouts.leader.show', compact('leader'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $leader = Leader::findOrFail($id);
        $positions = Leader::select('position')->distinct()->get()->pluck('position')->toArray();
        return view('backend.layouts.leader.edit', compact('leader', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|max:250',
            'content'           => 'nullable|string',
            'thumbnail'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'position'          => 'nullable|string|max:250',
            'is_leader'         => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $leader = Leader::findOrFail($id);

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = Helper::fileUpload($request->file('thumbnail'), 'leader', time() . '_' . getFileName($request->file('thumbnail')));
            }

            if ($data['is_leader'] == true) {
                Leader::where('is_leader', true)->update(['is_leader' => false]);
            }

            $leader->name = $data['name'];
            $leader->thumbnail = $data['thumbnail'] ?? $leader->thumbnail;
            $leader->content = $data['content'];
            $leader->position = $data['position'];
            $leader->is_leader = $data['is_leader'];
            $leader->save();

            session()->put('t-success', 'leader updated successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.leader.edit', $leader->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = Leader::findOrFail($id);

            if ($data->thumbnail && file_exists(public_path($data->thumbnail))) {
                Helper::fileDelete(public_path($data->thumbnail));
            }

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
        $data = Leader::findOrFail($id);
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

    public function sort(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'ids'       => 'required|array'
        ]);

        if (!$validator->passes()) {
            Log::info('Sorting leaders', ['ids' => $request->ids]);
            return response()->json(['t-error' => $validator->errors()->toArray()]);
        } else {
            Log::info('Sorting leaders', ['ids' => $request->ids]);
            foreach ($request->ids as $key => $id) {
                Leader::where('id', $id)->update(['order_id' => $key + 1]);
            }
        }

    }

}
