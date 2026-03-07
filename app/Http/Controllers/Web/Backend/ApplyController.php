<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Apply;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;


class ApplyController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Apply::orderBy('id', 'desc')->where('is_action', 0)->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user', function ($data) {
                    return "<a href='" . route('admin.users.show', $data->user_id) . "'>" . $data->user->name . "</a>";
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "approved" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "approved" ? '26px' : '2px';
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
                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['user', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.apply.index");
    }

    public function status(int $id): JsonResponse
    {
        $data = Apply::findOrFail($id);
        if (!$data) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Item not found.',
            ]);
        }
        $data->status = $data->status == 'approved' || $data->status == 'pending' ? 'rejected' : 'approved';
        $data->is_action = true;
        $data->save();

        $user = User::with('profile')->find($data->user_id);
        $user->profile->update(['type' => $data->status == 'approved' ? $data->type : 'member']);

        return response()->json([
            'status' => 't-success',
            'message' => 'Your action was successful!',
        ]);
    }

    public function destroy(string $id)
    {
        try {

            $data = Apply::findOrFail($id);

            if (!$data) {
                return response()->json([
                    'status' => 't-error',
                    'message' => 'Item not found.'
                ]);
            }

            $data->delete();
            return response()->json([
                'status' => 't-success',
                'message' => 'Your action was successful!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 't-error',
                'message' => 'An error occurred while deleting the item: ' . $e->getMessage()
            ]);
        }
    }

}
