<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'transaction');
    }

    public function index(Request $request, $user_id = null)
    {
        $data = Transaction::with(['order'])->orderBy('id', 'desc')->get();

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('trx_id', function ($data) {
                    return "<span title='" . $data->trx_id . "'>" . Str::limit($data->trx_id, 30) . "</span>";
                })
                ->addColumn('order', function ($data) {
                    return "<a href='" . route('admin.order.show', $data->order->id) . "'>" . $data->order->uid . "</a>";
                })
                ->addColumn('user', function ($data) {
                    return "<span>" . optional($data->order->customer)->email . "</span>";
                })->addColumn('gateway', function ($data) {
                    return "<span>" . $data->gateway . "</span>";
                })->addColumn('type', function ($data) {
                    return "<span class='badge bg-" . ($data->status == 'success' ? 'success' : 'danger') . "'>" . $data->status . "</span>";
                })->addColumn('amount', function ($data) {
                    return "<span>" . $data->amount . "$</span>";
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-info fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-eye"></i>
                                </a>

                            </div>';
                })
                ->rawColumns(['trx_id', 'order', 'user', 'gateway', 'type', 'amount', 'action'])
                ->make();
        }

        return view("backend.layouts.transaction.index");
    }

    public function show($id)
    {
        $transaction = Transaction::with(['user'])->find($id);

        if (!$transaction) {
            return redirect()->route('admin.transaction.index')->with('error', 'Transaction not found');
        }

        return view("backend.layouts.transaction.show", compact('transaction'));
    }
}
