<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Subscription;
use Carbon\Carbon;
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

        $currentYear = now()->year;
        $months = collect(range(1, 12))->map(function ($month) use ($currentYear) {
            return Carbon::create($currentYear, $month, 1)->startOfMonth();
        });

        $chartLabels = $months->map(fn ($month) => $month->format('M'))->values();

        $revenueRows = Transaction::selectRaw('MONTH(created_at) as month_number')
            ->selectRaw("SUM(CASE WHEN status IN ('paid','succeeded') THEN amount ELSE 0 END) as paid_total")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_total")
            ->selectRaw('COUNT(*) as total_count')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month_number')
            ->get()
            ->keyBy('month_number');

        $revenueChart = [
            'paid' => [],
            'pending' => [],
            'count' => [],
        ];

        foreach ($months as $month) {
            $row = $revenueRows->get($month->month);
            $revenueChart['paid'][] = (float) ($row->paid_total ?? 0);
            $revenueChart['pending'][] = (float) ($row->pending_total ?? 0);
            $revenueChart['count'][] = (int) ($row->total_count ?? 0);
        }

        $plansByPrice = Plan::query()
            ->get()
            ->keyBy('stripe_price_id');

        $subscriptions = Subscription::query()
            ->select(['id', 'stripe_price', 'quantity', 'created_at', 'ends_at'])
            ->whereNotNull('stripe_price')
            ->get();

        $mrrValues = [];
        $mrrGrowthValues = [];
        $subscriberGrowthValues = [];
        $activeSubscriberValues = [];

        $previousMrr = null;

        foreach ($months as $month) {
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $newSubscribers = $subscriptions
                ->filter(fn ($subscription) => $subscription->created_at && $subscription->created_at->between($monthStart, $monthEnd))
                ->count();

            $activeSubscriptions = $subscriptions->filter(function ($subscription) use ($monthEnd) {
                if (!$subscription->created_at || $subscription->created_at->gt($monthEnd)) {
                    return false;
                }

                if ($subscription->ends_at && $subscription->ends_at->lte($monthEnd)) {
                    return false;
                }

                return true;
            });

            $monthMrr = $activeSubscriptions->sum(function ($subscription) use ($plansByPrice) {
                $plan = $plansByPrice->get($subscription->stripe_price);

                if (!$plan) {
                    return 0;
                }

                $intervalCount = max((int) ($plan->interval_count ?: 1), 1);
                $quantity = max((int) ($subscription->quantity ?: 1), 1);
                $price = (float) $plan->price;

                if ($plan->interval === 'year') {
                    $monthlyValue = $price / (12 * $intervalCount);
                } else {
                    $monthlyValue = $price / $intervalCount;
                }

                return $monthlyValue * $quantity;
            });

            $growthRate = $previousMrr && $previousMrr > 0
                ? round((($monthMrr - $previousMrr) / $previousMrr) * 100, 2)
                : 0;

            $mrrValues[] = round($monthMrr, 2);
            $mrrGrowthValues[] = $growthRate;
            $subscriberGrowthValues[] = $newSubscribers;
            $activeSubscriberValues[] = $activeSubscriptions->count();

            $previousMrr = $monthMrr;
        }

        $currentMrr = end($mrrValues) ?: 0;
        $currentSubscriberGrowth = end($subscriberGrowthValues) ?: 0;
        $currentActiveSubscribers = end($activeSubscriberValues) ?: 0;

        return view('backend.layouts.dashboard',
            compact(
                'userStats',
                'transactionStats',
                'currentYear',
                'chartLabels',
                'revenueChart',
                'mrrValues',
                'mrrGrowthValues',
                'subscriberGrowthValues',
                'activeSubscriberValues',
                'currentMrr',
                'currentSubscriberGrowth',
                'currentActiveSubscribers'
            ));
    }
}
