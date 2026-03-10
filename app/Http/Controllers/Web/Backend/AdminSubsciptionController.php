<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

use Yajra\DataTables\Facades\DataTables;

class AdminSubsciptionController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'transaction');
    }

    public function index(Request $request, $user_id = null)
    {

        $data = Subscription::with('user')->orderBy('id', 'desc')->get();

// dd($data);
        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('trx_id', function ($data) {
                    // return "<span title='" . $data->user_id . "'>" . Str::limit($data->user_id, 50) . "</span>";
                    return "<a href='" . route('admin.users.show', $data->user->id) . "'>" . $data->user->name . "</a>";
                })
                ->addColumn('order', function ($data) {
                    return "<span>" . $data->type . "</span>";
                })
                ->addColumn('user', function ($data) {
                    return "<span>" . $data->stripe_id . "</span>";
                })->addColumn('gateway', function ($data) {
                    return "<span>" . $data->stripe_status . "</span>";
                })->addColumn('type', function ($data) {
                    return "<span>" . $data->stripe_price . "</span>";
                })->addColumn('amount', function ($data) {
                    return "<span>" . $data->trial_ends_at . "</span>";
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-info fs-14 text-white delete-icn" title="view">
                                    <i class="fe fe-eye"></i>
                                </a>

                            </div>';
                })
                ->rawColumns(['trx_id', 'order', 'user', 'gateway', 'type', 'amount', 'action'])
                ->make();
        }

        return view("backend.layouts.subscriptions.index");
    }

    public function show($id)
    {
        $subscriptions = Subscription::with(['user'])->find($id);

        if (!$subscriptions) {
            return redirect()->route('admin.subscriptions.index')->with('error', 'Subscription not found');
        }

        return view("backend.layouts.subscriptions.show", compact('subscriptions'));
    }
}
