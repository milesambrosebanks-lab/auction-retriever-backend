<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Stripe\StripeClient;

class DashboardController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'dashboard');
    }

    public function index()
    {
        $activeTab = request('tab', 'overview');
        $stripeApiError = null;

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
        $pendingAmount = Transaction::where('status', 'pending')->sum('amount');

        $currentYear = now()->year;
        $months = collect(range(1, 12))->map(function ($month) use ($currentYear) {
            return Carbon::create($currentYear, $month, 1)->startOfMonth();
        });

        $chartLabels = $months->map(fn ($month) => $month->format('M'))->values();
        $stripeRevenueChart = [
            'paid' => array_fill(0, 12, 0),
            'pending' => array_fill(0, 12, 0),
            'count' => array_fill(0, 12, 0),
        ];
        $stripeSubscriberGrowthLive = array_fill(0, 12, 0);
        $stripeActiveSubscriberLive = array_fill(0, 12, 0);
        $stripeMrrValuesLive = array_fill(0, 12, 0);
        $stripeMrrGrowthValuesLive = array_fill(0, 12, 0);
        $stripePayoutCounts = ['paid' => 0, 'pending' => 0, 'failed' => 0];

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

        // ── Stripe Snapshot Data (for sidebar tab) ───────────────────
        $stripeCustomers = User::select('id', 'name', 'email', 'stripe_id', 'created_at')
            ->whereNotNull('stripe_id')
            ->latest()
            ->take(8)
            ->get();

        $stripeSubscriptions = Subscription::select('id', 'user_id', 'stripe_status', 'stripe_price', 'quantity', 'created_at', 'ends_at')
            ->with('user:id,name,email,stripe_id')
            ->latest()
            ->take(8)
            ->get();

        $stripeInvoices = Transaction::select('id', 'title', 'invoice_id', 'customer_id', 'amount', 'currency', 'status', 'created_at')
            ->latest()
            ->take(8)
            ->get();

        $stripeKpis = [
            'mrr'              => round($currentMrr, 2),
            'arr'              => round($currentMrr * 12, 2),
            'active_customers' => $userStats['active'],
            'churn_rate'       => $userStats['total'] > 0 ? round(($userStats['cancelled'] / $userStats['total']) * 100, 2) : 0,
            'pending_revenue'  => round($pendingAmount, 2),
        ];

        // ── Live Stripe pull (uses Stripe API) ───────────────────────
        $stripeLiveCustomers = collect();
        $stripeLiveSubscriptions = collect();
        $stripeLiveInvoices = collect();
        $stripeLiveBalanceTxns = collect();
        $stripeLiveKpis = [
            'mrr' => 0,
            'arr' => 0,
            'active_subscribers' => 0,
            'churn_rate' => 0,
            'pending_revenue' => 0,
            'live_mode' => false,
        ];

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            $stripeLiveCustomers = collect($stripe->customers->all([
                'limit' => 20,
            ])->data);

            $stripeLiveSubscriptions = collect($stripe->subscriptions->all([
                'limit' => 20,
                'expand' => ['data.items.data.price'],
            ])->data);

            $stripeLiveInvoices = collect($stripe->invoices->all([
                'limit' => 20,
            ])->data);

            $stripeLiveBalanceTxns = collect($stripe->balanceTransactions->all([
                'limit' => 20,
            ])->data);

            $stripeLiveKpis['active_subscribers'] = $stripeLiveSubscriptions
                ->whereIn('status', ['active', 'trialing'])
                ->count();

            $cancelled = $stripeLiveSubscriptions->where('status', 'canceled')->count();
            $totalSubs = max($stripeLiveSubscriptions->count(), 1);
            $stripeLiveKpis['churn_rate'] = round(($cancelled / $totalSubs) * 100, 2);

            $stripeLiveKpis['pending_revenue'] = $stripeLiveInvoices
                ->whereIn('status', ['draft', 'open'])
                ->sum(function ($invoice) {
                    return ($invoice->amount_remaining ?? 0) / 100;
                });

            $stripeLiveKpis['mrr'] = $stripeLiveSubscriptions->sum(function ($subscription) {
                $item = $subscription->items->data[0] ?? null;
                if (!$item || !$item->price) {
                    return 0;
                }
                $price = $item->price;
                $amount = ($price->unit_amount ?? 0) / 100;
                $interval = $price->recurring->interval ?? 'month';
                $intervalCount = max((int)($price->recurring->interval_count ?? 1), 1);
                $qty = max((int)($item->quantity ?? 1), 1);

                $monthly = $interval === 'year'
                    ? $amount / (12 * $intervalCount)
                    : $amount / $intervalCount;

                return $monthly * $qty;
            });
            $stripeLiveKpis['arr'] = $stripeLiveKpis['mrr'] * 12;
            $stripeLiveKpis['live_mode'] = true;

            // ── Stripe revenue chart (by invoice) ──
            foreach ($stripeLiveInvoices as $invoice) {
                $created = $invoice->created ?? null;
                $createdAt = $invoice->created_at ?? null;
                $ts = $createdAt instanceof Carbon ? $createdAt : ($created ? Carbon::createFromTimestamp($created) : null);
                if (!$ts || $ts->year != $currentYear) {
                    continue;
                }
                $idx = $ts->month - 1;
                $amountTotal = isset($invoice->created_at) ? ($invoice->amount ?? 0) : (($invoice->total ?? 0) / 100);
                if (($invoice->status ?? '') === 'paid') {
                    $stripeRevenueChart['paid'][$idx] += (float) $amountTotal;
                    $stripePayoutCounts['paid']++;
                } elseif (in_array(($invoice->status ?? ''), ['open', 'draft'])) {
                    $stripeRevenueChart['pending'][$idx] += (float) $amountTotal;
                    $stripePayoutCounts['pending']++;
                } else {
                    $stripeRevenueChart['pending'][$idx] += (float) $amountTotal;
                    $stripePayoutCounts['failed']++;
                }
                $stripeRevenueChart['count'][$idx] += 1;
            }

            // ── Stripe subscriber + MRR trends ──
            $prevMrr = null;
            foreach ($months as $month) {
                $monthStart = $month->copy()->startOfMonth();
                $monthEnd = $month->copy()->endOfMonth();

                $newSubs = $stripeLiveSubscriptions->filter(function ($sub) use ($monthStart, $monthEnd) {
                    $created = $sub->created ?? null;
                    if (!$created) {
                        return false;
                    }
                    $createdAt = Carbon::createFromTimestamp($created);
                    return $createdAt->between($monthStart, $monthEnd);
                })->count();

                $activeSubs = $stripeLiveSubscriptions->filter(function ($sub) use ($monthEnd) {
                    $created = $sub->created ?? null;
                    if (!$created) {
                        return false;
                    }
                    $createdAt = Carbon::createFromTimestamp($created);
                    if ($createdAt->gt($monthEnd)) {
                        return false;
                    }
                    $canceledAt = $sub->canceled_at ?? null;
                    if ($canceledAt && Carbon::createFromTimestamp($canceledAt)->lte($monthEnd)) {
                        return false;
                    }
                    return true;
                });

                $monthMrr = $activeSubs->sum(function ($subscription) {
                    $item = $subscription->items->data[0] ?? null;
                    if (!$item || !$item->price) {
                        return 0;
                    }
                    $price = $item->price;
                    $amount = ($price->unit_amount ?? 0) / 100;
                    $interval = $price->recurring->interval ?? 'month';
                    $intervalCount = max((int)($price->recurring->interval_count ?? 1), 1);
                    $qty = max((int)($item->quantity ?? 1), 1);

                    $monthly = $interval === 'year'
                        ? $amount / (12 * $intervalCount)
                        : $amount / $intervalCount;

                    return $monthly * $qty;
                });

                $growth = $prevMrr && $prevMrr > 0
                    ? round((($monthMrr - $prevMrr) / $prevMrr) * 100, 2)
                    : 0;

                $idx = $month->month - 1;
                $stripeSubscriberGrowthLive[$idx] = $newSubs;
                $stripeActiveSubscriberLive[$idx] = $activeSubs->count();
                $stripeMrrValuesLive[$idx] = round($monthMrr, 2);
                $stripeMrrGrowthValuesLive[$idx] = $growth;

                $prevMrr = $monthMrr;
            }
        } catch (\Throwable $e) {
            $stripeApiError = $e->getMessage();
        }

        return view('backend.layouts.dashboard',
            compact(
                'activeTab',
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
                'currentActiveSubscribers',
                'stripeCustomers',
                'stripeSubscriptions',
                'stripeInvoices',
                'stripeKpis',
                'stripeLiveCustomers',
                'stripeLiveSubscriptions',
                'stripeLiveInvoices',
                'stripeLiveBalanceTxns',
                'stripeLiveKpis',
                'stripeApiError',
                'stripeRevenueChart',
                'stripeSubscriberGrowthLive',
                'stripeActiveSubscriberLive',
                'stripeMrrValuesLive',
                'stripeMrrGrowthValuesLive',
                'stripePayoutCounts'
            ));
    }
}
