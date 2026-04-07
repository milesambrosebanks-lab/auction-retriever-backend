@extends('backend.app')

@push('styles')
    <style>
        .dashboard-stat-card {
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
            border: 1px solid #eef2f7;
        }

        .dashboard-stat-card .card-body {
            padding: 1rem 0.85rem;
        }

        .dashboard-stat-icon {
            font-size: 1.5rem;
        }

        .dashboard-stat-value {
            font-size: 1.45rem;
            line-height: 1.1;
        }

        .dashboard-stat-label {
            font-size: 0.78rem;
            letter-spacing: 0.01em;
        }

        @media (min-width: 1200px) {
            .dashboard-stat-grid .col-xl-2 {
                width: 20%;
            }

            .dashboard-stat-grid.transaction-grid .col-xl-3 {
                width: 25%;
            }
        }

        .chart-280 {
            height: 280px !important;
        }
        .chart-200 { height: 200px !important; }
        .chart-center {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            @php
                $useLiveStripe = isset($stripeLiveKpis['live_mode']) && $stripeLiveKpis['live_mode'] === true;
                $stripeCards = $useLiveStripe ? $stripeLiveKpis : $stripeKpis;
                $stripeCustomerRows = $useLiveStripe ? $stripeLiveCustomers : $stripeCustomers;
                $stripeSubscriptionRows = $useLiveStripe ? $stripeLiveSubscriptions : $stripeSubscriptions;
                $stripeInvoiceRows = $useLiveStripe ? $stripeLiveInvoices : $stripeInvoices;
                $stripeTxnRows = $useLiveStripe ? $stripeLiveBalanceTxns : $stripeInvoices;
                // dd($stripeSubscriptionRows);
            @endphp

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">{{ $crud ? ucwords(str_replace('_', ' ', $crud)) : 'N/A' }}</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}"><i
                                        class="fe fe-home me-2 fs-14"></i>Home</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>

                <ul class="nav nav-pills mb-4" id="dashboardTabs">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}"
                           href="{{ route('admin.dashboard', ['tab' => 'overview']) }}">
                            <i class="fa-solid fa-gauge-high me-1"></i> Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'stripe' ? 'active' : '' }}"
                           href="{{ route('admin.dashboard', ['tab' => 'stripe']) }}">
                            <i class="fa-brands fa-stripe-s me-1"></i> Stripe
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                <div class="tab-pane fade {{ $activeTab === 'overview' ? 'show active' : '' }}" id="tab-overview">
                <!-- PAGE-HEADER END -->
                {{-- ── User Stats Cards ──────────────────────────────────────── --}}
                <div class="row mb-4 dashboard-stat-grid">
                    <div class="col-12">
                        <h5 class="text-muted mb-3">User Overview</h5>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-users dashboard-stat-icon text-primary"></i>
                                </div>
                                <h3 class="mb-1 fw-bold dashboard-stat-value">{{ number_format($userStats['total']) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Total Users</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-clock dashboard-stat-icon text-info"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-info dashboard-stat-value">{{ number_format($userStats['trial']) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Trial</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-check-circle dashboard-stat-icon text-success"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-success dashboard-stat-value">{{ number_format($userStats['active']) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Active</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-times-circle dashboard-stat-icon text-danger"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-danger dashboard-stat-value">{{ number_format($userStats['cancelled']) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Cancelled</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-exclamation-circle dashboard-stat-icon text-warning"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-warning dashboard-stat-value">{{ number_format($userStats['past_due']) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Past Due</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-star dashboard-stat-icon text-warning"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-warning dashboard-stat-value">{{ number_format($userStats['subscriptions']) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Total Subscriptions</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Transaction Stats Cards ─────────────────────────────────── --}}
                <div class="row mb-4 dashboard-stat-grid transaction-grid">
                    <div class="col-12">
                        <h5 class="text-muted mb-3">Transaction Overview</h5>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-receipt dashboard-stat-icon text-primary"></i>
                                </div>
                                <h3 class="mb-1 fw-bold dashboard-stat-value">{{ number_format($transactionStats['total']) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Total Transactions</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-dollar-sign dashboard-stat-icon text-success"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-success dashboard-stat-value">
                                    ${{ number_format($transactionStats['total_amount'], 2) }}
                                </h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Total Revenue</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-check dashboard-stat-icon text-success"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-success dashboard-stat-value">{{ number_format($transactionStats['paid']) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Paid</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-xmark dashboard-stat-icon text-danger"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-danger dashboard-stat-value">{{ number_format($transactionStats['failed']) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Failed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">
                                    <i class="fa-solid fa-chart-line me-1 text-primary"></i>
                                    Revenue Over Time
                                </h5>
                                <span class="badge bg-primary">{{ $currentYear }}</span>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card h-100">
                            <div class="card-header border-bottom">
                                <h5 class="mb-0">
                                    <i class="fa-solid fa-bolt me-1 text-warning"></i>
                                    Metrics Snapshot
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <span class="text-muted small d-block mb-1">Current Estimated MRR</span>
                                    <h3 class="fw-bold text-success mb-0">${{ number_format($currentMrr, 2) }}</h3>
                                </div>
                                <div class="mb-4">
                                    <span class="text-muted small d-block mb-1">New Subscribers This Month</span>
                                    <h3 class="fw-bold text-primary mb-0">{{ number_format($currentSubscriberGrowth) }}</h3>
                                </div>
                                <div>
                                    <span class="text-muted small d-block mb-1">Active Subscriber Snapshot</span>
                                    <h3 class="fw-bold text-info mb-0">{{ number_format($currentActiveSubscribers) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-xl-6 mb-4 mb-xl-0">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">
                                    <i class="fa-solid fa-chart-column me-1 text-success"></i>
                                    MRR Growth Over Time
                                </h5>
                                <span class="badge bg-success">Monthly</span>
                            </div>
                            <div class="card-body">
                                <canvas id="mrrChart" height="110"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">
                                    <i class="fa-solid fa-user-plus me-1 text-info"></i>
                                    Subscriber Growth Over Time
                                </h5>
                                <span class="badge bg-info">Monthly</span>
                            </div>
                            <div class="card-body">
                                <canvas id="subscriberChart" height="110"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div> {{-- /tab-pane overview --}}
            {{-- ───────────────── Stripe Tab ───────────────── --}}
            <div class="tab-pane fade {{ $activeTab === 'stripe' ? 'show active' : '' }}" id="tab-stripe">
                <div class="row mb-4 dashboard-stat-grid">
                    <div class="col-12">
                        <h5 class="text-muted mb-3">Stripe Snapshot</h5>
                        @if($stripeApiError)
                            <div class="alert alert-warning small mb-3">
                                Stripe API fallback to cached data. Error: {{ $stripeApiError }}
                            </div>
                        @elseif($useLiveStripe)
                            <div class="alert alert-success small mb-3">
                                Live Stripe data loaded from API ({{ config('app.server') }} mode).
                            </div>
                        @endif
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2"><i class="fa-solid fa-bolt dashboard-stat-icon text-success"></i></div>
                                <h3 class="mb-1 fw-bold text-success dashboard-stat-value">${{ number_format($stripeCards['mrr'], 2) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Current MRR</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2"><i class="fa-solid fa-calendar dashboard-stat-icon text-primary"></i></div>
                                <h3 class="mb-1 fw-bold text-primary dashboard-stat-value">${{ number_format($stripeCards['arr'], 2) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Run Rate (ARR)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2"><i class="fa-solid fa-user-check dashboard-stat-icon text-info"></i></div>
                                <h3 class="mb-1 fw-bold text-info dashboard-stat-value">{{ number_format($stripeCards['active_subscribers'] ?? $stripeCards['active_customers'] ?? 0) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Active Subscribers</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2"><i class="fa-solid fa-arrow-trend-down dashboard-stat-icon text-danger"></i></div>
                                <h3 class="mb-1 fw-bold text-danger dashboard-stat-value">{{ number_format($stripeCards['churn_rate'], 2) }}%</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Churn Rate</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100 dashboard-stat-card">
                            <div class="card-body">
                                <div class="mb-2"><i class="fa-solid fa-circle-pause dashboard-stat-icon text-warning"></i></div>
                                <h3 class="mb-1 fw-bold text-warning dashboard-stat-value">${{ number_format($stripeCards['pending_revenue'], 2) }}</h3>
                                <p class="text-muted mb-0 dashboard-stat-label">Pending Revenue</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="mb-0"><i class="fa-solid fa-chart-column me-1 text-primary"></i> Stripe Revenue & Volume</h5>
                                <span class="badge bg-primary">{{ $currentYear }}</span>
                            </div>
                            <div class="card-body">
                                <canvas id="stripeRevenueChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="mb-0"><i class="fa-solid fa-receipt me-1 text-warning"></i> Payout Mix</h5>
                                <span class="badge bg-warning text-dark">Counts</span>
                            </div>
                            <div class="card-body chart-center">
                                <canvas id="stripeStatusChart" class="chart-100" style="max-width:260px; max-height:260px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-xl-6 mb-4 mb-xl-0">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="mb-0"><i class="fa-solid fa-chart-line me-1 text-success"></i> MRR Trend</h5>
                                <span class="badge bg-success">Stripe</span>
                            </div>
                            <div class="card-body">
                                <canvas id="stripeMrrChart" class="chart-280"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="mb-0"><i class="fa-solid fa-user-group me-1 text-info"></i> Subscriber Growth</h5>
                                <span class="badge bg-info">Monthly</span>
                            </div>
                            <div class="card-body">
                                <canvas id="stripeSubscriberChart" class="chart-280"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fa-solid fa-users me-1 text-primary"></i> Recent Customers</h5>
                                    <span class="badge bg-light text-muted">{{ $stripeCustomerRows->count() }} records</span>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.stripe.customers') }}">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Stripe ID</th>
                                            <th>Joined</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($stripeCustomerRows->take(14) as $customer)
                                            <tr>
                                                <td>{{ $customer->name ?? ($customer->description ?? 'N/A') }}</td>
                                                <td>{{ $customer->email ?? 'N/A' }}</td>
                                                <td class="text-muted small">{{ $customer->stripe_id ?? $customer->id }}</td>
                                                <td>
                                                    @php $created = $customer->created ?? null; @endphp
                                                    {{ isset($customer->created_at) ? optional($customer->created_at)->format('M d, Y') : ($created ? \Carbon\Carbon::createFromTimestamp($created)->format('M d, Y') : '—') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">No Stripe customers yet.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fa-solid fa-star me-1 text-success"></i> Active Subscriptions</h5>
                                    <span class="badge bg-light text-muted">{{ $stripeSubscriptionRows->count() }} records</span>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.stripe.subscriptions') }}">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Status</th>
                                            <th>Price ID</th>
                                            <th>Started</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @forelse($stripeSubscriptionRows->take(10) as $sub)

                                            <tr>
                                                <td>
                                                    {{ optional($sub->user)->name ?? ($sub->customer?->name ?? 'N/A') }}
                                                    <div class="small text-muted">{{ optional($sub->user)->email ?? ($sub->customer?->email ?? 'N/A') }}</div>
                                                </td>
                                                <td><span class="badge bg-{{ $sub->status === 'active' ? 'success' : ($sub->status === 'trialing' ? 'info' : 'secondary') }}">{{ ucfirst($sub->status ?? 'n/a') }}</span></td>
                                                <td class="text-muted small">{{ $sub->stripe_price ?? ($sub->items->data[0]->price->id ?? '—') }}</td>
                                                <td>
                                                    @php $created = $sub->created ?? null; @endphp
                                                    {{ isset($sub->created_at) ? optional($sub->created_at)->format('M d, Y') : ($created ? \Carbon\Carbon::createFromTimestamp($created)->format('M d, Y') : '—') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">No subscriptions yet.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-xl-6 mb-4 mb-xl-0">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fa-solid fa-file-invoice-dollar me-1 text-warning"></i> Latest Invoices</h5>
                                    <span class="badge bg-light text-muted">{{ $stripeInvoiceRows->count() }} records</span>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.stripe.invoices') }}">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($stripeInvoiceRows->take(10) as $invoice)
                                            <tr>
                                                <td>
                                                    <span class="fw-semibold">{{ $invoice->title ?? 'Invoice' }}</span>
                                                    <div class="small text-muted">#{{ $invoice->invoice_id ?? $invoice->id }}</div>
                                                </td>
                                                <td class="small text-muted">{{ $invoice->customer_id ?? $invoice->customer ?? 'N/A' }}</td>
                                                <td>
                                                    @php
                                                        $amountVal = isset($invoice->created_at) ? ($invoice->amount ?? 0) : (($invoice->total ?? $invoice->amount ?? 0) / 100);
                                                        $currencyVal = strtoupper($invoice->currency ?? 'USD');
                                                    @endphp
                                                    ${{ number_format($amountVal, 2) }} {{ $currencyVal }}
                                                </td>
                                                <td><span class="badge bg-{{ ($invoice->status ?? '') === 'paid' ? 'success' : (($invoice->status ?? '') === 'pending' || ($invoice->status ?? '') === 'open' ? 'warning text-dark' : 'secondary') }}">{{ ucfirst($invoice->status ?? 'n/a') }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">No invoices found.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fa-solid fa-money-bill-transfer me-1 text-primary"></i> Recent Transactions</h5>
                                    <span class="badge bg-light text-muted">{{ $stripeTxnRows->count() }} shown</span>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.stripe.transactions') }}">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                        <tr>
                                            <th>TRX</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($stripeTxnRows->take(13) as $trx)
                                            <tr>
                                                <td class="small text-muted">#{{ $trx->invoice_id ?? $trx->id }}</td>
                                                <td>
                                                    @php
                                                        $amt = $trx->amount ?? 0;
                                                        $divisor = isset($trx->created_at) ? 1 : 100;
                                                    @endphp
                                                    ${{ number_format($amt / $divisor, 2) }} {{ strtoupper($trx->currency ?? 'USD') }}
                                                </td>
                                                <td><span class="badge bg-{{ ($trx->status ?? $trx->type ?? '') === 'succeeded' || ($trx->status ?? '') === 'paid' ? 'success' : (($trx->status ?? '') === 'pending' ? 'warning text-dark' : 'danger') }}">{{ ucfirst($trx->status ?? $trx->type ?? 'n/a') }}</span></td>
                                                <td>
                                                    @php $created = $trx->created ?? null; @endphp
                                                    {{ isset($trx->created_at) ? optional($trx->created_at)->format('M d, Y') : ($created ? \Carbon\Carbon::createFromTimestamp($created)->format('M d, Y') : '—') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">No transactions available.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> {{-- /tab-pane stripe --}}
        </div> {{-- /tab-content --}}
        </div>
    </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const months = @json($chartLabels);
        const revenuePaid = @json($revenueChart['paid']);
        const revenuePending = @json($revenueChart['pending']);
        const revenueCount = @json($revenueChart['count']);
        const mrrValues = @json($mrrValues);
        const mrrGrowthValues = @json($mrrGrowthValues);
        const subscriberGrowthValues = @json($subscriberGrowthValues);
        const activeSubscriberValues = @json($activeSubscriberValues);
        const payoutCounts = {
            paid: {{ $transactionStats['paid'] }},
            pending: {{ $transactionStats['pending'] }},
            failed: {{ $transactionStats['failed'] }},
        };
        // Stripe live datasets
        const stripeMonths = @json($chartLabels);
        const stripeRevenuePaid = @json($stripeRevenueChart['paid']);
        const stripeRevenuePending = @json($stripeRevenueChart['pending']);
        const stripeRevenueCount = @json($stripeRevenueChart['count']);
        const stripeSubscriberGrowth = @json($stripeSubscriberGrowthLive);
        const stripeActiveSubscribers = @json($stripeActiveSubscriberLive);
        const stripeMrrValues = @json($stripeMrrValuesLive);
        const stripeMrrGrowth = @json($stripeMrrGrowthValuesLive);
        const stripePayoutCounts = @json($stripePayoutCounts);

        const revenueChartEl = document.getElementById('revenueChart');
        if (revenueChartEl) new Chart(revenueChartEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                        label: 'Paid Revenue ($)',
                        data: revenuePaid,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Pending Revenue ($)',
                        data: revenuePending,
                        backgroundColor: 'rgba(255, 193, 7, 0.7)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Transaction Count',
                        data: revenueCount,
                        type: 'line',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        fill: false,
                        yAxisID: 'y1',
                    },
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.dataset.yAxisID === 'y') {
                                    return ctx.dataset.label + ': $' + ctx.parsed.y.toFixed(2);
                                }
                                return ctx.dataset.label + ': ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Amount ($)'
                        },
                        ticks: {
                            callback: val => '$' + val.toLocaleString()
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Count'
                        },
                        grid: {
                            drawOnChartArea: false
                        },
                    },
                }
            }
        });

        const mrrChartEl = document.getElementById('mrrChart');
        if (mrrChartEl) new Chart(mrrChartEl.getContext('2d'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                        label: 'Estimated MRR ($)',
                        data: mrrValues,
                        borderColor: 'rgba(25, 135, 84, 1)',
                        backgroundColor: 'rgba(25, 135, 84, 0.12)',
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Growth %',
                        data: mrrGrowthValues,
                        borderColor: 'rgba(255, 193, 7, 1)',
                        backgroundColor: 'rgba(255, 193, 7, 0.12)',
                        fill: false,
                        tension: 0.35,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.dataset.yAxisID === 'y1') {
                                    return ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(2) + '%';
                                }
                                return ctx.dataset.label + ': $' + ctx.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        title: {
                            display: true,
                            text: 'MRR ($)'
                        },
                        ticks: {
                            callback: val => '$' + val.toLocaleString()
                        }
                    },
                    y1: {
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Growth %'
                        },
                        ticks: {
                            callback: val => val + '%'
                        }
                    }
                }
            }
        });

        const subscriberChartEl = document.getElementById('subscriberChart');
        if (subscriberChartEl) new Chart(subscriberChartEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                        label: 'New Subscribers',
                        data: subscriberGrowthValues,
                        backgroundColor: 'rgba(13, 202, 240, 0.75)',
                        borderColor: 'rgba(13, 202, 240, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Active Subscribers Snapshot',
                        data: activeSubscriberValues,
                        type: 'line',
                        borderColor: 'rgba(111, 66, 193, 1)',
                        backgroundColor: 'rgba(111, 66, 193, 0.12)',
                        borderWidth: 2,
                        pointRadius: 4,
                        fill: false,
                        tension: 0.35,
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Subscribers'
                        }
                    }
                }
            }
        });

        // ── Stripe tab charts (live) ──
        const stripeRevenueEl = document.getElementById('stripeRevenueChart');
        if (stripeRevenueEl) new Chart(stripeRevenueEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: stripeMonths,
                datasets: [
                    {
                        label: 'Paid Revenue ($)',
                        data: stripeRevenuePaid,
                        backgroundColor: 'rgba(62, 132, 247, 0.75)',
                        borderColor: 'rgba(62, 132, 247, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Pending Revenue ($)',
                        data: stripeRevenuePending,
                        backgroundColor: 'rgba(255, 193, 7, 0.6)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Transactions',
                        data: stripeRevenueCount,
                        type: 'line',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        fill: false,
                        yAxisID: 'y1',
                        tension: 0.35,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        ticks: { callback: val => '$' + val.toLocaleString() }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { callback: val => val }
                    }
                }
            }
        });

        const stripeSubscriberEl = document.getElementById('stripeSubscriberChart');
        if (stripeSubscriberEl) new Chart(stripeSubscriberEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: stripeMonths,
                datasets: [
                    {
                        label: 'New Subs',
                        data: stripeSubscriberGrowth,
                        backgroundColor: 'rgba(13, 202, 240, 0.75)',
                        borderColor: 'rgba(13, 202, 240, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Active Snapshot',
                        data: stripeActiveSubscribers,
                        type: 'line',
                        borderColor: 'rgba(111, 66, 193, 1)',
                        backgroundColor: 'rgba(111, 66, 193, 0.12)',
                        borderWidth: 2,
                        pointRadius: 4,
                        fill: false,
                        tension: 0.35,
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Subscribers' } }
                }
            }
        });

        const stripeMrrEl = document.getElementById('stripeMrrChart');
        if (stripeMrrEl) new Chart(stripeMrrEl.getContext('2d'), {
            type: 'line',
            data: {
                labels: stripeMonths,
                datasets: [
                    {
                        label: 'MRR ($)',
                        data: stripeMrrValues,
                        borderColor: 'rgba(25, 135, 84, 1)',
                        backgroundColor: 'rgba(25, 135, 84, 0.12)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                    },
                    {
                        label: 'Growth %',
                        data: stripeMrrGrowth,
                        borderColor: 'rgba(255, 193, 7, 1)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: {
                        title: { display: true, text: 'MRR ($)' },
                        ticks: { callback: val => '$' + val.toLocaleString() }
                    },
                    y1: {
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { callback: val => val + '%' }
                    }
                }
            }
        });

        const stripeStatusEl = document.getElementById('stripeStatusChart');
        if (stripeStatusEl) new Chart(stripeStatusEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Failed'],
                datasets: [{
                    data: [stripePayoutCounts.paid, stripePayoutCounts.pending, stripePayoutCounts.failed],
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.85)',
                        'rgba(255, 193, 7, 0.85)',
                        'rgba(220, 53, 69, 0.8)',
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                layout: { padding: 0 }
            }
        });
    </script>
@endpush
