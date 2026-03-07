<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Models\Survey;
use App\Models\SurveyVote;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SurveyOpinion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;


class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Survey::with(['opinions'])->orderBy('id', 'desc')->get();
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
                ->rawColumns(['title', 'thumbnail', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.survey.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.layouts.survey.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250',
            'end_date'          => 'nullable|date|after_or_equal:today',
            'description'       => 'nullable|string',
            'opinion'           => 'required|array|min:4|max:10',
            'opinion.*'         => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $survey = new Survey();

            $survey->title = $data['title'];
            $survey->end_date = $data['end_date'];
            $survey->description = $data['description'];
            $survey->save();

            foreach ($data['opinion'] as $opinion) {
                $opinion = new SurveyOpinion([
                    'survey_id' => $survey->id,
                    'opinion' => $opinion,
                ]);
                $opinion->save();
            }

            session()->put('t-success', 'Survey created successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.survey.index')->with('t-success', 'Survey created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $survey = Survey::with(['opinions'])->findOrFail($id);
        return view('backend.layouts.survey.show', compact('survey'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $survey = Survey::with(['opinions'])->findOrFail($id);
        return view('backend.layouts.survey.edit', compact('survey'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'            => 'required|max:250',
            'end_date'         => 'nullable|date|after_or_equal:today',
            'description'      => 'nullable|string',
            'opinion'           => 'required|array|min:4|max:10',
            'opinion.*.text'   => 'required|string',
            'opinion.*.id'     => 'nullable|integer|exists:survey_opinions,id',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();

            $survey = Survey::findOrFail($id);

            $survey->update([
                'title'       => $data['title'],
                'end_date'    => $data['end_date'],
                'description' => $data['description'],
            ]);

            foreach ($data['opinion'] as $opinion) {
                if (!empty($opinion['id'])) {
                    $existing = $survey->opinions()->find($opinion['id']);
                    if ($existing) {
                        $existing->update(['opinion' => $opinion['text']]);
                    }
                } else {
                    $survey->opinions()->create(['opinion' => $opinion['text']]);
                }
            }

            DB::commit();
            session()->put('t-success', 'Survey updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.survey.index')->with('t-success', 'Survey updated successfully');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = Survey::findOrFail($id);

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
        $data = Survey::findOrFail($id);
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

    public function  removeOpinion($id)
    {
        try {
            $data = SurveyOpinion::findOrFail($id);
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
}
