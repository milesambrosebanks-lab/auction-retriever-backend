<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Stripe\Invoice;
use Stripe\Stripe;
use Throwable;
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
                ->rawColumns([
                    'trx_id',
                    'user_col',
                    'amount_col',
                    'status_col',
                    'invoice_col',
                    'date_col',
                    'action'
                ])
                ->make();
        }

        return view('backend.layouts.transaction.index');
    }

    public function transactions(Request $request, $user_id = null)
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'cursor' => ['nullable', 'string'],
            'direction' => ['nullable', 'in:next,prev'],
        ]);

        $stripeSecret = config('services.stripe.secret');
        $transactions = collect();
        $stripeUsers = collect();
        $stripeError = null;

        $perPage = (int) ($filters['per_page'] ?? 20);
        $direction = $filters['direction'] ?? 'next';
        $cursor = $filters['cursor'] ?? null;

        $nextCursor = null;
        $prevCursor = null;

        if (blank($stripeSecret)) {
            $stripeError = 'Stripe secret key is not configured yet.';
        } else {
            try {
                Stripe::setApiKey($stripeSecret);

                $params = [
                    'limit' => $perPage,
                ];

                $created = [];

                if (!empty($filters['start_date'])) {
                    $created['gte'] = Carbon::parse($filters['start_date'])->startOfDay()->timestamp;
                }

                if (!empty($filters['end_date'])) {
                    $created['lte'] = Carbon::parse($filters['end_date'])->endOfDay()->timestamp;
                }

                if (!empty($created)) {
                    $params['created'] = $created;
                }

                if ($cursor) {
                    if ($direction === 'prev') {
                        $params['ending_before'] = $cursor;
                    } else {
                        $params['starting_after'] = $cursor;
                    }
                }

                $invoiceList = Invoice::all($params);
                $transactions = collect($invoiceList->data);

                $customerIds = $transactions
                    ->pluck('customer')
                    ->filter()
                    ->unique()
                    ->values();

                if ($customerIds->isNotEmpty()) {
                    $stripeUsers = User::whereIn('stripe_id', $customerIds)
                        ->get()
                        ->keyBy('stripe_id');
                }

                $firstInvoiceId = optional($transactions->first())->id;
                $lastInvoiceId = optional($transactions->last())->id;

                $nextCursor = $invoiceList->has_more && $lastInvoiceId ? $lastInvoiceId : null;
                $prevCursor = $cursor && $firstInvoiceId ? $firstInvoiceId : null;
            } catch (Throwable $exception) {
                $stripeError = $exception->getMessage();
            }
        }

        return view("backend.layouts.transaction.transactions", [
            'transactions' => $transactions,
            'stripeUsers' => $stripeUsers,
            'stripeError' => $stripeError,
            'perPage' => $perPage,
            'nextCursor' => $nextCursor,
            'prevCursor' => $prevCursor,
            'filters' => [
                'start_date' => $filters['start_date'] ?? null,
                'end_date' => $filters['end_date'] ?? null,
                'direction' => $direction,
            ],
        ]);
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
