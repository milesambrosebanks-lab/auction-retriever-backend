<?php

namespace App\Http\Controllers\Web\Backend\Access;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{

    public function __construct()
    {
        View::share('crud', 'user');
    }

    public function index_draft(Request $request)
    {
        $user = Auth::guard('web')->user();

        $user = User::where('id', '!=', $user->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'customer');
            })->with('roles');

        if ($request->ajax()) {
            return DataTables::of($user)
                ->addIndexColumn()
                ->addColumn('last_activity', fn($user) => $user->last_activity_at ? $user->last_activity_at : null)
                ->addColumn('created', fn($user) => $user->created_at)
                ->addColumn('action', function ($user) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('admin.users.edit', $user->id) . '" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>';
                    $btn .= '<a href="' . route('admin.users.show', $user->id) . '" class="btn btn-info"><i class="fa-solid fa-eye"></i></a>';

                    $btn .= '<form action="' . route('admin.users.destroy', $user->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure?\')">';
                    $btn .= csrf_field() . method_field('DELETE');
                    $btn .= '<button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i></button>';
                    $btn .= '</form>';

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('backend.layouts.access.users.index', compact('user'));
    }

    public function index(Request $request)
    {
        $authUser = Auth::guard('web')->user();

        $query = User::where('id', '!=', $authUser->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'customer');
            })
            ->with([
                'roles',
                'activeSubscription',
                'plan',
            ]);

        // ── Filter by subscription status ────────────────────────────
        if ($request->filled('status')) {
            $status = $request->status;

            // stripe_status mapping
            $stripeStatus = match ($status) {
                'trialing' => 'trialing',
                'active'   => 'active',
                'canceled' => 'canceled',
                'expired'  => 'past_due',
                default    => null,
            };

            if ($stripeStatus) {
                $query->whereHas('subscriptions', function ($q) use ($stripeStatus) {
                    $q->where('stripe_status', $stripeStatus);
                });
            }
        }
        // ── Filter by plan ────────────────────────────────────────────
        if ($request->filled('plan')) {
            $plan = Plan::find($request->plan);
            if ($plan) {
                $query->whereHas('subscriptions', function ($q) use ($plan) {
                    $q->where('stripe_price', $plan->stripe_price_id);
                });
            }
        }
        // ── Filter by signup date ─────────────────────────────────────
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name_col', function ($user) {
                    $avatar = $user->avatar
                        ? asset($user->avatar)
                        : asset('default/profile.jpg');
                    return '<div class="d-flex align-items-center gap-2">
                            <img src="' . $avatar . '" class="rounded-circle"
                                 style="width:32px;height:32px;object-fit:cover;">
                            <div>
                                <div class="fw-500">' . e($user->name) . '</div>
                                <small class="text-muted">' . e($user->email) . '</small>
                            </div>
                        </div>';
                })
                ->addColumn('status_badge', function ($user) {

                    // activeSubscription থেকে status নিন
                    $status = $user->activeSubscription?->stripe_status ?? 'N/A';

                    $colors = [
                        'trialing' => 'info',
                        'active'   => 'success',
                        'canceled' => 'danger',
                        'past_due' => 'warning',
                    ];
                    $icons = [
                        'trialing' => 'fa-clock',
                        'active'   => 'fa-check-circle',
                        'canceled' => 'fa-times-circle',
                        'past_due' => 'fa-exclamation-circle',
                    ];
                    $labels = [
                        'trialing' => 'Trial',
                        'active'   => 'Active',
                        'canceled' => 'Cancelled',
                        'past_due' => 'Past Due',
                    ];

                    $color = $colors[$status] ?? 'secondary';
                    $icon  = $icons[$status]  ?? 'fa-circle';
                    $label = $labels[$status] ?? ucfirst($status);

                    return '<span class="badge bg-' . $color . '">
                <i class="fa ' . $icon . ' me-1"></i>' . $label .
                        '</span>';
                })
                ->addColumn('plan_col', function ($user) {
                    if ($user->activeSubscription) {
                        $plan = Plan::where(
                            'stripe_price_id',
                            $user->activeSubscription->stripe_price
                        )->first();

                        if ($plan) {
                            return '<span class="badge bg-primary">' . e($plan->name) . '</span>';
                        }
                    }
                    return '<span class="text-muted">—</span>';
                })
                ->addColumn('trial_ends', function ($user) {

                    $date = $user->trial_ends_at
                        ?? $user->activeSubscription?->trial_ends_at;

                    if (!$date) return '<span class="text-muted">—</span>';

                    $carbon = \Carbon\Carbon::parse($date);
                    return '<span class="' . ($carbon->isPast() ? 'text-danger' : 'text-success') . '">'
                        . $carbon->format('d M Y')
                        . '</span>';
                })
                ->addColumn('sub_ends', function ($user) {
                    // subscription_ends_at অথবা subscription এর ends_at
                    $date = $user->subscription_ends_at
                        ?? $user->activeSubscription?->ends_at;

                    if (!$date) return '<span class="text-muted">—</span>';

                    $carbon = \Carbon\Carbon::parse($date);
                    return '<span class="' . ($carbon->isPast() ? 'text-danger' : 'text-success') . '">'
                        . $carbon->format('d M Y')
                        . '</span>';
                })
                ->addColumn('last_login', function ($user) {
                    if (!$user->last_activity_at) {
                        return '<span class="text-muted">Never</span>';
                    }
                    return \Carbon\Carbon::parse($user->last_activity_at)->diffForHumans();
                })
                ->addColumn('created', function ($user) {
                    return $user->created_at
                        ? $user->created_at->format('d M Y')
                        : '—';
                })
                ->addColumn('action', function ($user) {
                    return '<div class="btn-group btn-group-sm">
                    <a href="' . route('admin.users.show', $user->id) . '"
                       class="btn btn-info" title="View">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="' . route('admin.users.edit', $user->id) . '"
                       class="btn btn-primary" title="Edit">
                        <i class="fa-solid fa-pencil"></i>
                    </a>

                </div>';
                })
                ->rawColumns([
                    'name_col',
                    'status_badge',
                    'plan_col',
                    'trial_ends',
                    'sub_ends',
                    'last_login',
                    'created',
                    'action'
                ])
                ->make(true);
        }

        // Plan list for filter dropdown
        $plans = Plan::where('is_active', 1)->orderBy('name')->get(['id', 'name']);

        return view('backend.layouts.access.users.index', compact('plans'));
    }

    public function create()
    {
        return view('backend.layouts.access.users.create', ['roles' => Role::all()]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        foreach ($request->roles as $role) {
            DB::table('model_has_roles')->insert([
                'role_id' => $role,
                'model_type' => 'App\Models\User',
                'model_id' => $user->id
            ]);
        }

        return redirect()->route('admin.users.index')->with('t-success', 'User created t-successfully');
    }

    public function show_draft($id)
    {
        $user = User::with(['profile'])->find($id);
        return view('backend.layouts.access.users.show', compact('user'));
    }
    public function show($id)
    {
        $user = User::with([
            'roles',
            'plan',
            'subscriptions' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
        ])->findOrFail($id);

        // $payments = collect();
        $transactions = $user->transactions;
// dd($transactions);
        // Stripe payment history
        // if ($user->stripe_id) {
        //     try {
        //         \Stripe\Stripe::setApiKey(config('cashier.secret'));
        //         $charges  = \Stripe\Charge::all([
        //             'customer' => $user->stripe_id,
        //             'limit'    => 10,
        //         ]);
        //         $payments = collect($charges->data);
        //     } catch (\Exception $e) {
        //         // Stripe না থাকলে skip
        //     }
        // }
        // dd($transactions);

        return view('backend.layouts.access.users.show', compact('user', 'transactions'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::all();
        return view('backend.layouts.access.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $id,
            // 'roles' => 'required|array',
            // 'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $user = User::find($id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return redirect()->back()->with('t-success', 'User updated t-successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);
        DB::table('model_has_roles')->where('model_id', $id)->delete();
        $user->delete();
        return redirect()->route('admin.users.index')->with('t-success', 'User deleted t-successfully');
    }

    public function status(int $id)
    {
        $user = User::findOrFail($id);
        if (!$user) {
            redirect()->back()->with('t-error', 'User not found');
        }
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();
        session()->put('t-success', 'Status updated successfully');
        return view('backend.layouts.access.users.show', compact('user'));
    }
}
