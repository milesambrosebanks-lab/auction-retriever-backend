<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;


class OrderController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'order');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Order::with(['product', 'user'])->orderBy('id', 'desc')->get();
       //  dd($data->customer);

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('product', function ($data) {
                    $title = $data->product->title ? Str::limit($data->product->title, 20) : '-';
                    return "<a href='" . route('admin.product.show', $data->product_id) . "'>" . $title . "</a>";
                })
                ->addColumn('customer', function ($data) {
                    $status = '<span title="' . optional($data->customer)->email. '">' . optional($data->customer)->first_name  . '</span>';
                    return $status;

                    // return "<a href='" . route('admin.users.show', $data->user_id) . "'>" . $data->user->name . "</a>";
                })
                ->addColumn('status', function ($data) {
                    if ($data->status == 'pending') {
                        $status = '<span class="badge bg-warning">' . $data->status . '</span>';
                    } else {
                        $status = '<span class="badge bg-info">' . $data->status . '</span>';
                    }

                    return $status;
                })->addColumn('datetime', function ($data) {
                    return '<span class="badge bg-primary">' . ($data->created_at ? $data->created_at->format('d M Y') : 'N/A') . '</span>';
                })->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-success fs-14 text-white delete-icn" title="View">
                                    <i class="fe fe-eye"></i>
                                </a>

                            </div>';
                })
                ->rawColumns(['product', 'customer', 'status', 'datetime', 'action'])
                ->make();
        }
        return view("backend.layouts.order.index");
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
