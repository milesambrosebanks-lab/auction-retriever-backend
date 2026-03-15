<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'dashboard');
    }

    public function index()
    {
        // ── User Stats ───────────────────────────────────────────────
        $userStats = [
            'total'     => User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->count(),
            'trial'     => User::whereHas('roles', fn($q) => $q->where('name', 'customer'))
                               ->whereHas('subscriptions', fn($q) => $q->where('stripe_status', 'trialing'))
                               ->count(),
            'active'    => User::whereHas('roles', fn($q) => $q->where('name', 'customer'))
                               ->whereHas('subscriptions', fn($q) => $q->where('stripe_status', 'active'))
                               ->count(),
            'cancelled' => User::whereHas('roles', fn($q) => $q->where('name', 'customer'))
                               ->whereHas('subscriptions', fn($q) => $q->where('stripe_status', 'canceled'))
                               ->count(),
            'past_due'  => User::whereHas('roles', fn($q) => $q->where('name', 'customer'))
                               ->whereHas('subscriptions', fn($q) => $q->where('stripe_status', 'past_due'))
                               ->count(),
                               'subscriptions' => Subscription::count(), // ← নতুন
        ];

        // ── Transaction Stats ────────────────────────────────────────
        $transactionStats = [
            'total'       => Transaction::count(),
            'total_amount'=> Transaction::whereIn('status', ['paid', 'succeeded'])->sum('amount'),
            'paid'        => Transaction::whereIn('status', ['paid', 'succeeded'])->count(),
            'pending'     => Transaction::where('status', 'pending')->count(),
            'failed'      => Transaction::where('status', 'failed')->count(),

        ];

        // ── Monthly Transaction Chart (current year) ─────────────────
        $currentYear = now()->year;
        $all_months  = [
            'january', 'february', 'march', 'april',
            'may', 'june', 'july', 'august',
            'september', 'october', 'november', 'december',
        ];

        $transactions = Transaction::select(
            DB::raw("MONTHNAME(created_at) as month"),
            DB::raw("SUM(CASE WHEN status IN ('paid','succeeded') THEN amount ELSE 0 END) as success_total"),
            DB::raw("SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_total"),
            DB::raw("COUNT(*) as total_count")
        )
        ->whereYear('created_at', $currentYear)
        ->groupBy('month')
        ->get()
        ->mapWithKeys(function ($item) {
            return [
                strtolower($item->month) => [
                    'success' => number_format($item->success_total, 2),
                    'pending' => number_format($item->pending_total, 2),
                    'count'   => $item->total_count,
                ]
            ];
        });

        $formatted_data = collect($all_months)->mapWithKeys(function ($month) use ($transactions) {
            return [
                $month => $transactions->get($month, [
                    'success' => '0.00',
                    'pending' => '0.00',
                    'count'   => 0,
                ])
            ];
        });

        // JSON file save
        $jsonPath = public_path('transactions/' . auth('web')->user()->slug . '.json');
        if (!file_exists(dirname($jsonPath))) {
            mkdir(dirname($jsonPath), 0755, true);
        }
        file_put_contents($jsonPath, json_encode($formatted_data));

        return view('backend.layouts.dashboard',
            compact('userStats', 'transactionStats', 'formatted_data', 'currentYear'));
    }
}
