@extends('backend.app')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

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
                <!-- PAGE-HEADER END -->
                {{-- ── User Stats Cards ──────────────────────────────────────── --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="text-muted mb-3">User Overview</h5>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-users fa-2x text-primary"></i>
                                </div>
                                <h3 class="mb-1 fw-bold">{{ number_format($userStats['total']) }}</h3>
                                <p class="text-muted small mb-0">Total Users</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-clock fa-2x text-info"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-info">{{ number_format($userStats['trial']) }}</h3>
                                <p class="text-muted small mb-0">Trial</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-check-circle fa-2x text-success"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-success">{{ number_format($userStats['active']) }}</h3>
                                <p class="text-muted small mb-0">Active</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-times-circle fa-2x text-danger"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-danger">{{ number_format($userStats['cancelled']) }}</h3>
                                <p class="text-muted small mb-0">Cancelled</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-exclamation-circle fa-2x text-warning"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-warning">{{ number_format($userStats['past_due']) }}</h3>
                                <p class="text-muted small mb-0">Past Due</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-star fa-2x text-warning"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-warning">{{ number_format($userStats['subscriptions']) }}</h3>
                                <p class="text-muted small mb-0">Total Subscriptions</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Transaction Stats Cards ─────────────────────────────────── --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="text-muted mb-3">Transaction Overview</h5>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-receipt fa-2x text-primary"></i>
                                </div>
                                <h3 class="mb-1 fw-bold">{{ number_format($transactionStats['total']) }}</h3>
                                <p class="text-muted small mb-0">Total Transactions</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-dollar-sign fa-2x text-success"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-success">
                                    ${{ number_format($transactionStats['total_amount'], 2) }}
                                </h3>
                                <p class="text-muted small mb-0">Total Revenue</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-check fa-2x text-success"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-success">{{ number_format($transactionStats['paid']) }}</h3>
                                <p class="text-muted small mb-0">Paid</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fa-solid fa-xmark fa-2x text-danger"></i>
                                </div>
                                <h3 class="mb-1 fw-bold text-danger">{{ number_format($transactionStats['failed']) }}</h3>
                                <p class="text-muted small mb-0">Failed</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Transaction Chart ───────────────────────────────────────── --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">
                                    <i class="fa-solid fa-chart-line me-1 text-primary"></i>
                                    Monthly Transactions — {{ $currentYear }}
                                </h5>
                                <span class="badge bg-primary">{{ $currentYear }}</span>
                            </div>
                            <div class="card-body">
                                <canvas id="transactionChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = @json($formatted_data);
        const months = Object.keys(chartData).map(m => m.charAt(0).toUpperCase() + m.slice(1));
        const successData = Object.values(chartData).map(d => parseFloat(d.success));
        const pendingData = Object.values(chartData).map(d => parseFloat(d.pending));
        const countData = Object.values(chartData).map(d => d.count);

        const ctx = document.getElementById('transactionChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                        label: 'Paid ($)',
                        data: successData,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Pending ($)',
                        data: pendingData,
                        backgroundColor: 'rgba(255, 193, 7, 0.7)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Count',
                        data: countData,
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
    </script>
@endpush
