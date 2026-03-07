<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PartyResource;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;

class PartyResourceController extends Controller
{
    use ApiResponse;
     /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PartyResource::orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return Str::limit($data->title, 20);
                })
                ->addColumn('image', function ($data) {
                    if ($data->image) {
                        $url = asset($data->image);
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
                ->rawColumns(['title', 'image', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.party-resource.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.layouts.party-resource.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'         => 'required|max:250',
            'link'          => 'nullable|url|max:5120',
            'description'   => 'required|string',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
            'pdf_file'      => 'required|mimes:pdf|max:5048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $partyResource = new PartyResource();

            if ($request->hasFile('image')) {
                $imagePath = Helper::fileUpload(
                    $request->file('image'),
                    'partyResource',
                    time() . '_' . getFileName($request->file('image'))
                );

                // Save relative path, not full URL
                $data['image'] = url($imagePath);
            }
            if ($request->hasFile('pdf_file')) {
                $pdfPath = Helper::fileUpload(
                    $request->file('pdf_file'),
                    'partyResource',
                    time() . '_' . getFileName($request->file('pdf_file'))
                );

                // Save relative path, not full URL
                $data['pdf_file'] = url($pdfPath);
            }
            $partyResource->title = $data['title'];
            $partyResource->link = $data['link'] ?? null;
            $partyResource->description = $data['description'];
            $partyResource->image = $data['image'] ?? null;
            $partyResource->pdf_file = $data['pdf_file'] ?? null;
            $partyResource->save();

            session()->put('t-success', 'Party Resource created successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.party.resource.index')->with('t-success', 'Party Resource created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $partyResource = PartyResource::findOrFail($id);
        return view('backend.layouts.party-resource.show', compact('partyResource'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $partyResource = PartyResource::findOrFail($id);
        return view('backend.layouts.party-resource.edit', compact('partyResource'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'         => 'required|max:250',
            'link'          => 'nullable|url|max:5120',
            'description'   => 'required|string',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
            'pdf_file'      => 'nullable|mimes:pdf|max:5048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $partyResource = PartyResource::findOrFail($id);
            // Handle the image upload if a new image is provided
            if ($request->hasFile('image')) {
                $imagePath = Helper::fileUpload(
                    $request->file('image'),
                    'partyResource',
                    time() . '_' . getFileName($request->file('image'))
                );

                // Save relative path, not full URL
                $data['image'] = url($imagePath);
            }

            if ($request->hasFile('pdf_file')) {
                $pdfPath = Helper::fileUpload(
                    $request->file('pdf_file'),
                    'partyResourcePdf',
                    time() . '_' . getFileName($request->file('pdf_file'))
                );

                // Save relative path, not full URL
                $data['pdf_file'] = url($pdfPath);
            }

            $partyResource->title = $data['title'];
            $partyResource->link = $data['link'] ?? $partyResource->link;
            $partyResource->description = $data['description'];
            $partyResource->image = $data['image'] ?? $partyResource->image;
            $partyResource->pdf_file = $data['pdf_file'] ?? $partyResource->pdf_file;
            $partyResource->save();

            session()->put('t-success', 'Party Resource updated successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.party.resource.edit', $partyResource->id)->with('t-success', 'Party Resource updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = PartyResource::findOrFail($id);

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
        $data = PartyResource::findOrFail($id);
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
