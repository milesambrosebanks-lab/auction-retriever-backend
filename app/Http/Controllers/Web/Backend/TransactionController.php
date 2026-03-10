<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Stripe\Invoice;
use Stripe\Stripe;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'transaction');
    }
    public function index(Request $request, $user_id = null)
    {

      
        $data = Transaction::with(['user'])->orderBy('id', 'desc')->get();

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('trx_id', function ($data) {
                    return "<span title='" . $data->trx_id  . "'>" . Str::limit($data->trx_id, 30) . "</span>";
                })
                ->addColumn('user', function ($data) {
                    // $user = User::where('stripe_id', $data->customer_id)->first();
                    if ($data->user) {
                        return "<a href='" . route('admin.users.show', $data->user->id) . "'>" . $data->user->email . "</a>";
                    }
                    return "<span>N/A</span>";
                })
                ->addColumn('amount', function ($data) {
                    return "<span>" . $data->amount . "</span>";
                })

                ->addColumn('status', function ($data) {
                    return "<span class='badge bg-" . ($data->status == 'paid' ? 'success' : 'danger') . "'>" . $data->status . "</span>";
                })
                ->addColumn('invoice', function ($data) {
                    return "<span><a href='" . $data->hosted_invoice_url . "' target='_blank'>Download</span>";
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-info fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-eye"></i>
                                </a>

                            </div>';
                })
                ->rawColumns(['trx_id', 'user', 'amount',  'status', 'invoice', 'action'])
                ->make();
        }

        return view("backend.layouts.transaction.index");
    }

    public function transactions(Request $request, $user_id = null)
    {
          // Stripe::setApiKey(config('cashier.secret'));

        // $invoices = Invoice::all([
        //     'limit' => 20,
        //     // 'starting_after'=>'cus_U7YWMcr32RysOD',
        // ]);
        // $data = $invoices->data;
        // dd($data);

        $data = Transaction::with('user')->orderBy('id', 'desc')->get();

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('trx_id', function ($data) {
                    return "<span title='" . $data->id  . "'>" . Str::limit($data->id, 30) . "</span>";
                })
                ->addColumn('user', function ($data) {
                    $user = User::where('stripe_id', $data->customer)->first();
                    if ($user) {
                        return "<a href='" . route('admin.users.show', $user->id) . "'>" . $data->customer_email . "</a>";
                    }
                    return "<span>" . $data->customer_email . "</span>";
                })
                ->addColumn('amount', function ($data) {
                    return "<span>" . $data->amount_paid / 100 . "</span>";
                })

                ->addColumn('status', function ($data) {
                    return "<span class='badge bg-" . ($data->status == 'paid' ? 'success' : 'danger') . "'>" . $data->status . "</span>";
                })
                ->addColumn('invoice', function ($data) {
                    return "<span><a href='" . $data->hosted_invoice_url . "' target='_blank'>Download</span>";
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-info fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-eye"></i>
                                </a>

                            </div>';
                })
                ->rawColumns(['trx_id', 'user', 'amount',  'status', 'invoice', 'action'])
                ->make();
        }

        return view("backend.layouts.transaction.transactions");
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
