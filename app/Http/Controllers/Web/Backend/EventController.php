<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Leader;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;


class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Event::orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return Str::limit($data->title, 20);
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

                                <a href="#" type="button" onclick="ShowEventRegisterList(' . $data->id . ')" class="btn btn-warning fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-users"></i>
                                </a>

                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>

                            </div>';
                })
                ->rawColumns(['title', 'thumbnail', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.event.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $leaders = Leader::all();

        return view('backend.layouts.event.create', compact('leaders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Convert user-friendly date format to required Y-m-d H:i:s
        if ($request->filled('start_time')) {
            $request->merge([
                'start_time' => Carbon::parse($request->input('start_time'))->format('Y-m-d H:i:s'),
            ]);
        }

        if ($request->filled('end_time')) {
            $request->merge([
                'end_time' => Carbon::parse($request->input('end_time'))->format('Y-m-d H:i:s'),
            ]);
        }


        $validator = Validator::make($request->all(), [
            'title'                 => 'required|max:250',
            'sub_title'             => 'nullable|string|max:255',
            'start_time'            => 'required|date_format:Y-m-d H:i:s',
            'end_time'              => 'required|date_format:Y-m-d H:i:s|after_or_equal:start_time',
            'phone'                 => 'nullable|string|max:20',
            'organizer'             => 'nullable|string|max:255',
            'venue'                 => 'nullable|string|max:255',
            'thumbnail'             => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'description'           => 'required|string',
            'speakers'              => 'nullable|array',
            'speakers.*'            => 'exists:leaders,id',
            'registration_enabled'  => 'nullable|string|in:yes,no',
            'created_at'            => 'nullable|date',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $event = new Event();

            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = Helper::fileUpload(
                    $request->file('thumbnail'),
                    'events',
                    time() . '_' . getFileName($request->file('thumbnail'))
                );

                // Save relative path, not full URL
                $data['thumbnail'] = url($thumbnailPath);
            }

            $event->slug = Helper::makeSlug(Event::class, $data['title']);

            $event->title = $data['title'];
            $event->sub_title = $data['sub_title'] ?? null;
            $event->start_time = $data['start_time'];
            $event->end_time = $data['end_time'];
            $event->phone = $data['phone'] ?? null;
            $event->organizer = $data['organizer'] ?? null;
            $event->venue = $data['venue'] ?? null;
            $event->thumbnail = $data['thumbnail'] ?? null;
            $event->description = $data['description'];
            $event->registration_enabled = $data['registration_enabled'] ?? 'no';
            $event->created_at = $data['created_at'] ?? now();
            $event->save();

            if (!empty($data['speakers'])) {
                $event->speakers()->attach($data['speakers']);
            }

            session()->put('t-success', 'post created successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.event.index')->with('t-success', 'post created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $event = Event::with('speakers')->findOrFail($id);
        return view('backend.layouts.event.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $event = Event::with('speakers')->findOrFail($id);
        // dd($event);

        $leaders = Leader::all();

        // dd($event->speakers->pluck('id')->toArray());

        return view('backend.layouts.event.edit', compact('event', 'leaders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Convert datetime-local input to Y-m-d H:i:s format for validation
        if ($request->filled('start_time')) {
            $request->merge([
                'start_time' => Carbon::parse($request->input('start_time'))->format('Y-m-d H:i:s'),
            ]);
        }

        if ($request->filled('end_time')) {
            $request->merge([
                'end_time' => Carbon::parse($request->input('end_time'))->format('Y-m-d H:i:s'),
            ]);
        }
        $validator = Validator::make($request->all(), [
            'title'                 => 'required|max:250',
            'sub_title'             => 'nullable|string|max:255',
            'start_time'            => 'required|date_format:Y-m-d H:i:s',
            'end_time'              => 'required|date_format:Y-m-d H:i:s|after_or_equal:start_time',
            'phone'                 => 'nullable|string|max:20',
            'organizer'             => 'nullable|string|max:255',
            'venue'                 => 'nullable|string|max:255',
            'thumbnail'             => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'description'           => 'required|string',
            'speakers'              => 'nullable|array',
            'speakers.*'            => 'exists:leaders,id',
            'registration_enabled'  => 'nullable|string|in:yes,no',
            'created_at'            => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $event = Event::findOrFail($id);

            if ($request->hasFile('thumbnail')) {
                // Delete old file if exists
                if ($event->thumbnail && file_exists(public_path($event->thumbnail))) {
                    Helper::fileDelete(public_path($event->thumbnail));
                }

                // Upload new file and store relative path
                $thumbnailPath = Helper::fileUpload(
                    $request->file('thumbnail'),
                    'events',
                    time() . '_' . getFileName($request->file('thumbnail'))
                );

                $data['thumbnail'] = url($thumbnailPath);
            }

            $event->title                   = $data['title'];
            $event->sub_title               = $data['sub_title'] ?? null;
            $event->start_time              = $data['start_time'];
            $event->end_time                = $data['end_time'];
            $event->phone                   = $data['phone'] ?? null;
            $event->organizer               = $data['organizer'] ?? null;
            $event->thumbnail               = $data['thumbnail'] ?? $event->thumbnail;
            $event->venue                   = $data['venue'] ?? null;
            $event->description             = $data['description'];
            $event->registration_enabled    = $data['registration_enabled'] ?? $event->registration_enabled;
            $event->created_at              = $data['created_at'] ?? now();
            $event->save();
            
            $event->speakers()->sync($data['speakers'] ?? []);

            session()->put('t-success', 'post updated successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.event.edit', $event->id)->with('t-success', 'post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = Event::findOrFail($id);

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
        $data = Event::findOrFail($id);
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
