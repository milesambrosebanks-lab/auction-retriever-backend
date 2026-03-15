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
        View::share('crud', 'Transactions');
    }
   public function index(Request $request)
{
    if ($request->ajax()) {
        $data = Transaction::with('user')->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('trx_id', function ($row) {
                return '<small class="text-muted" title="' . e($row->trx_id) . '">'
                    . \Str::limit($row->trx_id, 25)
                    . '</small>';
            })
            ->addColumn('user_col', function ($row) {
                if (!$row->user) return '<span class="text-muted">—</span>';
                return '<a href="' . route('admin.users.show', $row->user->id) . '">'
                    . e($row->user->name)
                    . '</a><br><small class="text-muted">' . e($row->user->email) . '</small>';
            })
            ->addColumn('amount_col', function ($row) {
                return '<strong>$' . number_format($row->amount, 2) . '</strong>';
            })
            ->addColumn('status_col', function ($row) {
                $colors = [
                    'paid'      => 'success',
                    'succeeded' => 'success',
                    'failed'    => 'danger',
                    'pending'   => 'warning',
                    'refunded'  => 'info',
                ];
                $color = $colors[strtolower($row->status ?? '')] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($row->status ?? '—') . '</span>';
            })
            ->addColumn('invoice_col', function ($row) {
                $btns = '<div class="d-flex gap-1">';
                if ($row->hosted_invoice_url) {
                    $btns .= '<a href="' . e($row->hosted_invoice_url) . '" target="_blank"
                                 class="btn btn-xs btn-outline-primary" title="View">
                                <i class="fa fa-eye"></i>
                              </a>';
                }
                if ($row->invoice_pdf) {
                    $btns .= '<a href="' . e($row->invoice_pdf) . '" target="_blank"
                                 class="btn btn-xs btn-outline-danger" title="PDF">
                                <i class="fa fa-file-pdf"></i>
                              </a>';
                }
                if (!$row->hosted_invoice_url && !$row->invoice_pdf) {
                    $btns .= '<span class="text-muted">—</span>';
                }
                $btns .= '</div>';
                return $btns;
            })
            ->addColumn('date_col', function ($row) {
                return $row->created_at
                    ? $row->created_at->format('d M Y')
                    : '—';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('admin.transaction.show', $row->id) . '"
                           class="btn btn-sm btn-info" title="View">
                            <i class="fa fa-eye"></i>
                        </a>';
            })
            ->rawColumns(['trx_id', 'user_col', 'amount_col', 'status_col',
                         'invoice_col', 'date_col', 'action'])
            ->make();
    }

    return view('backend.layouts.transaction.index');
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
