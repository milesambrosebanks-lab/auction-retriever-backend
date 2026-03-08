<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ExtractionLog;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;


class ExtractionLogController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'Extraction Log');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = ExtractionLog::orderBy('id', 'desc')->get();
        //  dd($data);

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('product', function ($data) {
                    $title = $data->source? Str::limit($data->source, 20) : '-';
                    return "<a href='" .$data->source . "'>" . $title . "</a>";
                })
                ->addColumn('customer', function ($data) {
                    $status = '<span title="' . e($data->last_successful_run). '">' . e($data->last_successful_run)  . '</span>';
                    return $status;
                })
                ->addColumn('status', function ($data) {
                    if ($data->status == 0) {
                        $status = '<span class="badge bg-warning">Failed</span>';
                    } else {
                        $status = '<span class="badge bg-info">Success</span>';
                    }

                    return $status;
                })
                ->addColumn('message', function ($data) {
                    return '<span class="text-gray">' . ($data->message ? $data->message: 'N/A') . '</span>';
                })
                ->addColumn('datetime', function ($data) {
                    return '<span class="badge bg-primary">' . ($data->created_at ? $data->created_at->format('d M Y') : 'N/A') . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-success fs-14 text-white delete-icn" title="View">
                                    <i class="fe fe-eye"></i>
                                </a>

                            </div>';
                })
                ->rawColumns(['product', 'customer', 'status', 'message','datetime', 'action'])
                ->make();
        }
        return view("backend.layouts.extraction_log.index");
    }

    public function show(int $id)
    {
        $order = Order::with(['product', 'user'])->where('id', $id)->first();
        return view('backend.layouts.order.show', compact('order'));
    }

    public function status(int $id): JsonResponse
    {
        $data = Order::findOrFail($id);
        if (!$data) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Item not found.',
            ]);
        }
        $data->status = $data->status === 'accept' ? 'reject' : 'accept';
        $data->save();
        return response()->json([
            'status' => 't-success',
            'message' => 'Your action was successful!',
        ]);
    }
}
