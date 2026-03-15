<?php

// namespace App\Http\Controllers\Web\Backend;

// use App\Http\Controllers\Controller;
// use App\Models\Subscription;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\View;

// use Yajra\DataTables\Facades\DataTables;

// class AdminSubsciptionController extends Controller
// {
//     public function __construct()
//     {
//         View::share('crud', 'Subsscriptions');
//     }

//     public function index(Request $request, $user_id = null)
//     {

//         $data = Subscription::with('user')->orderBy('id', 'desc');

// // dd($data);
//         if ($request->ajax()) {
//             return DataTables::of($data)
//                 ->addIndexColumn()
//                 ->addColumn('trx_id', function ($data) {
//                     // return "<span title='" . $data->user_id . "'>" . Str::limit($data->user_id, 50) . "</span>";
//                     return "<a href='" . route('admin.users.show', $data->user->id) . "'>" . $data->user->name . "</a>";
//                 })
//                 ->addColumn('order', function ($data) {
//                     return "<span>" . $data->type . "</span>";
//                 })
//                 ->addColumn('user', function ($data) {
//                     return "<span>" . $data->stripe_id . "</span>";
//                 })->addColumn('gateway', function ($data) {
//                     return "<span>" . $data->stripe_status . "</span>";
//                 })->addColumn('type', function ($data) {
//                     return "<span>" . $data->stripe_price . "</span>";
//                 })->addColumn('amount', function ($data) {
//                     return "<span>" . $data->trial_ends_at . "</span>";
//                 })
//                 ->addColumn('action', function ($data) {
//                     return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

//                                 <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-info fs-14 text-white delete-icn" title="view">
//                                     <i class="fe fe-eye"></i>
//                                 </a>

//                             </div>';
//                 })
//                 ->rawColumns(['trx_id', 'order', 'user', 'gateway', 'type', 'amount', 'action'])
//                 ->make();
//         }

//         return view("backend.layouts.subscriptions.index");
//     }

//     public function show($id)
//     {
//         $subscriptions = Subscription::with(['user'])->find($id);

//         if (!$subscriptions) {
//             return redirect()->route('admin.subscriptions.index')->with('error', 'Subscription not found');
//         }

//         return view("backend.layouts.subscriptions.show", compact('subscriptions'));
//     }
// }


namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Stripe;

class AdminSubsciptionController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'Subscriptions');
        Stripe::setApiKey(config('cashier.secret'));
    }

    public function index(Request $request)
    {
        $data = Subscription::with('user')->orderBy('id', 'desc');

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_col', function ($row) {
                    if (!$row->user) return '<span class="text-muted">—</span>';
                    return '<a href="' . route('admin.users.show', $row->user->id) . '">'
                        . e($row->user->name) . '</a>
                        <br><small class="text-muted">' . e($row->user->email) . '</small>';
                })
                ->addColumn('type_col', function ($row) {
                    return '<span class="badge bg-secondary">' . e($row->type) . '</span>';
                })
                ->addColumn('stripe_id_col', function ($row) {
                    return '<small class="text-muted">' . \Str::limit($row->stripe_id, 20) . '</small>';
                })
                ->addColumn('status_col', function ($row) {
                    $colors = [
                        'active'   => 'success',
                        'trialing' => 'info',
                        'canceled' => 'danger',
                        'past_due' => 'warning',
                        'paused'   => 'secondary',
                    ];
                    $color = $colors[$row->stripe_status] ?? 'secondary';
                    $label = $row->stripe_status === 'trialing' ? 'Trial' : ucfirst($row->stripe_status);
                    return '<span class="badge bg-' . $color . '">' . $label . '</span>';
                })
                ->addColumn('price_col', function ($row) {
                    return '<small>' . e($row->stripe_price) . '</small>';
                })
                ->addColumn('trial_ends_col', function ($row) {
                    if (!$row->trial_ends_at) return '<span class="text-muted">—</span>';
                    $date = \Carbon\Carbon::parse($row->trial_ends_at);
                    return '<span class="' . ($date->isPast() ? 'text-danger' : 'text-success') . '">'
                        . $date->format('d M Y') . '</span>';
                })
                ->addColumn('ends_col', function ($row) {
                    if (!$row->ends_at) return '<span class="text-muted">—</span>';
                    $date = \Carbon\Carbon::parse($row->ends_at);
                    return '<span class="' . ($date->isPast() ? 'text-danger' : 'text-success') . '">'
                        . $date->format('d M Y') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btns = '<div class="btn-group btn-group-sm">';

                    // View
                    $btns .= '<a href="' . route('admin.subscriptions.show', $row->id) . '"
                                 class="btn btn-info" title="View">
                                <i class="fa fa-eye"></i>
                              </a>';

                    // Pause
                    if ($row->stripe_status === 'active') {
                        $btns .= '<button onclick="confirmAction(\'' . route('admin.subscriptions.pause', $row->id) . '\', \'pause\')"
                                          class="btn btn-warning" title="Pause">
                                    <i class="fa fa-pause"></i>
                                  </button>';
                    }

                    // Resume
                    if (in_array($row->stripe_status, ['paused', 'canceled'])) {
                        $btns .= '<button onclick="confirmAction(\'' . route('admin.subscriptions.resume', $row->id) . '\', \'resume\')"
                                          class="btn btn-success" title="Resume">
                                    <i class="fa fa-play"></i>
                                  </button>';
                    }

                    // Cancel
                    if (!in_array($row->stripe_status, ['canceled'])) {
                        $btns .= '<button onclick="confirmAction(\'' . route('admin.subscriptions.cancel', $row->id) . '\', \'cancel\')"
                                          class="btn btn-danger" title="Cancel">
                                    <i class="fa fa-times"></i>
                                  </button>';
                    }

                    $btns .= '</div>';
                    return $btns;
                })
                ->rawColumns([
                    'user_col',
                    'type_col',
                    'stripe_id_col',
                    'status_col',
                    'price_col',
                    'trial_ends_col',
                    'ends_col',
                    'action'
                ])
                ->make();
        }

        return view('backend.layouts.subscriptions.index');
    }

    public function show($id)
    {
        $subscription = Subscription::with('user')->findOrFail($id);
        // ── Transactions for this subscription's user ────────────────
        $transactions = collect();

        if ($subscription->user) {
            $transactions = Transaction::where('user_id', $subscription->user->id)
                ->orWhere('customer_id', $subscription->user->stripe_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view(
            'backend.layouts.subscriptions.show',
            compact('subscription', 'transactions')
        );
    }

    // ── Pause ────────────────────────────────────────────────────────
    public function pause($id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            $user         = $subscription->user;

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Stripe pause
            $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_id);
            \Stripe\Subscription::update($subscription->stripe_id, [
                'pause_collection' => ['behavior' => 'void'],
            ]);

            // Local DB update
            $subscription->update(['stripe_status' => 'paused']);

            Log::info("Subscription paused: #{$subscription->id} — User: {$user->email}");

            return response()->json([
                'success' => true,
                'message' => "Subscription paused for {$user->name}",
            ]);
        } catch (\Exception $e) {
            Log::error("Subscription pause failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to pause: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ── Resume ───────────────────────────────────────────────────────
    public function resume($id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            $user         = $subscription->user;

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Stripe resume (pause_collection remove করুন)
            \Stripe\Subscription::update($subscription->stripe_id, [
                'pause_collection' => '',
            ]);

            // Local DB update
            $subscription->update(['stripe_status' => 'active']);

            Log::info("Subscription resumed: #{$subscription->id} — User: {$user->email}");

            return response()->json([
                'success' => true,
                'message' => "Subscription resumed for {$user->name}",
            ]);
        } catch (\Exception $e) {
            Log::error("Subscription resume failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to resume: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ── Cancel ───────────────────────────────────────────────────────
    public function cancel($id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            $user         = $subscription->user;

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Cashier cancel (period end এ cancel)
            $cashierSub = $user->subscriptions()
                ->where('stripe_id', $subscription->stripe_id)
                ->first();

            if ($cashierSub) {
                $cashierSub->cancel(); // period end এ cancel
            } else {
                // Direct Stripe cancel
                \Stripe\Subscription::update($subscription->stripe_id, [
                    'cancel_at_period_end' => true,
                ]);
            }

            // Local DB update
            $subscription->update([
                'stripe_status' => 'canceled',
                'ends_at'       => now()->endOfMonth(),
            ]);

            Log::info("Subscription cancelled: #{$subscription->id} — User: {$user->email}");

            return response()->json([
                'success' => true,
                'message' => "Subscription cancelled for {$user->name}. Active until period end.",
            ]);
        } catch (\Exception $e) {
            Log::error("Subscription cancel failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel: ' . $e->getMessage(),
            ], 500);
        }
    }
}
