{{-- resources/views/backend/layouts/access/users/show.blade.php --}}

@extends('backend.app', ['title' => 'User Detail'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">User Detail</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ url('admin/dashboard') }}">
                                    <i class="fe fe-home me-2 fs-14"></i>Home
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.users.index') }}">Users</a>
                            </li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </div>
                </div>

                <div class="row">

                    {{-- Left: Avatar + Actions --}}
                    <div class="col-md-3">
                        <div class="card mb-3 text-center p-3">
                            <img src="{{ $user->avatar ? asset($user->avatar) : asset('default/profile.jpg') }}"
                                class="rounded-circle mx-auto mb-3" style="width:100px;height:100px;object-fit:cover;">
                            <h5 class="mb-1">{{ $user->name }}</h5>
                            <p class="text-muted small mb-2">{{ $user->email }}</p>
                            <span class="badge bg-{{ $user->status_color }} mb-3">
                                <i class="fa {{ $user->status_icon }} me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $user->status ?? 'trial')) }}
                            </span>
                            @if ($user->is_deleted)
                                <div class="mb-3">
                                    <span class="badge bg-danger">
                                        <i class="fa fa-trash me-1"></i> Deleted Account
                                    </span>
                                </div>
                            @endif
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-pencil me-1"></i> Edit
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa fa-arrow-left me-1"></i> Back
                                </a>
                                {{-- Delete button ─────────────────────────────── --}}
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                        <i class="fa fa-trash me-1"></i> Delete User Permanently
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Details --}}
                    <div class="col-md-9">

                        {{-- Account Info --}}
                        <div class="card mb-3">
                            <div class="card-header border-bottom">
                                <h6 class="mb-0">
                                    <i class="fa fa-user me-1 text-primary"></i>
                                    Account Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <p class="text-muted small mb-1">Full Name</p>
                                        <strong>{{ $user->name }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-muted small mb-1">Email</p>
                                        <strong>{{ $user->email }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-muted small mb-1">Roles</p>
                                        @forelse($user->roles as $role)
                                            <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-muted">—</span>
                                        @endforelse
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-muted small mb-1">Stripe Customer ID</p>
                                        <strong>{{ $user->stripe_id ?? '—' }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-muted small mb-1">Last Login</p>
                                        <strong>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '—' }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-muted small mb-1">Joined</p>
                                        <strong>{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</strong>
                                    </div>
                                    @if ($user->is_deleted)
                                        <div class="col-md-4">
                                            <p class="text-muted small mb-1">Account State</p>
                                            <span class="badge bg-danger">
                                                <i class="fa fa-trash me-1"></i> Deleted Account
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Subscription Status --}}
                        <div class="card mb-3">
                            <div class="card-header border-bottom">
                                <h6 class="mb-0">
                                    <i class="fa fa-credit-card me-1 text-success"></i>
                                    Subscription Status
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <p class="text-muted small mb-1">Status</p>
                                        <span class="badge bg-{{ $user->status_color }} fs-12">
                                            {{ ucfirst(str_replace('_', ' ', $user->status ?? 'trial')) }}
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="text-muted small mb-1">Plan</p>
                                        <strong>{{ $user->subscription_plan ?? '—' }}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="text-muted small mb-1">Trial Ends</p>
                                        <strong
                                            class="{{ $user->trial_ends_at?->isPast() ? 'text-danger' : 'text-success' }}">
                                            {{ $user->trial_ends_at ? $user->trial_ends_at->format('d M Y') : '—' }}
                                        </strong>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="text-muted small mb-1">Sub Start</p>
                                        <strong>
                                            {{ $user->subscription_starts_at ? $user->subscription_starts_at->format('d M Y') : '—' }}
                                        </strong>
                                    </div>
                                    @if ($user->cancelled_at)
                                        <div class="col-md-3">
                                            <p class="text-muted small mb-1">Cancelled At</p>
                                            <strong class="text-danger">
                                                {{ $user->cancelled_at->format('d M Y') }}
                                            </strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Subscription History --}}
                        <div class="card mb-3">
                            <div class="card-header border-bottom">
                                <h6 class="mb-0">
                                    <i class="fa fa-history me-1 text-info"></i>
                                    Subscription History
                                </h6>
                            </div>
                            <div class="card-body">
                                @if ($user->subscriptions && $user->subscriptions->count() > 0)
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Stripe Price</th>
                                                <th>Status</th>
                                                <th>Trial Ends</th>
                                                <th>Ends At</th>
                                                <th>Started</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($user->subscriptions as $sub)
                                                <tr>
                                                    <td>{{ $sub->type ?? '—' }}</td>
                                                    <td>{{ $sub->stripe_price ?? '—' }}</td>
                                                    <td>
                                                        @php
                                                            $subColors = [
                                                                'active' => 'success',
                                                                'trialing' => 'info',
                                                                'canceled' => 'danger',
                                                                'past_due' => 'warning',
                                                            ];
                                                            $subColor = $subColors[$sub->stripe_status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $subColor }}">
                                                            {{ ucfirst($sub->stripe_status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{ $sub->trial_ends_at ? \Carbon\Carbon::parse($sub->trial_ends_at)->format('d M Y') : '—' }}
                                                    </td>
                                                    <td>
                                                        {{ $sub->ends_at ? \Carbon\Carbon::parse($sub->ends_at)->format('d M Y') : '—' }}
                                                    </td>
                                                    <td>{{ $sub->created_at->format('d M Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted mb-0">No subscription history found.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Payment History --}}
                        {{-- Payment / Transaction History --}}
                        <div class="card mb-3">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0">
                                    <i class="fa fa-credit-card me-1 text-warning"></i>
                                    Payment History
                                </h6>
                                <span class="badge bg-secondary">{{ $transactions->count() }} transactions</span>
                            </div>
                            <div class="card-body p-0">
                                @if ($transactions && $transactions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Invoice</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($transactions as $trx)
                                                    @php
                                                        $statusColors = [
                                                            'paid' => 'success',
                                                            'succeeded' => 'success',
                                                            'failed' => 'danger',
                                                            'pending' => 'warning',
                                                            'refunded' => 'info',
                                                        ];
                                                        $statusColor =
                                                            $statusColors[strtolower($trx->status ?? '')] ??
                                                            'secondary';
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <small class="text-muted">
                                                                {{ \Str::limit($trx->trx_id) }}
                                                            </small>
                                                        </td>

                                                        <td>
                                                            <strong>
                                                                {{ strtoupper($trx->currency) }}
                                                                ${{ number_format($trx->amount, 2) }}
                                                            </strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $statusColor }}">
                                                                {{ ucfirst($trx->status ?? '—') }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-1">
                                                                @if ($trx->hosted_invoice_url)
                                                                    <a href="{{ $trx->hosted_invoice_url }}"
                                                                        target="_blank"
                                                                        class="btn btn-xs btn-outline-primary"
                                                                        title="View Invoice">
                                                                        <i class="fa fa-eye"></i>
                                                                    </a>
                                                                @endif
                                                                @if ($trx->invoice_pdf)
                                                                    <a href="{{ $trx->invoice_pdf }}" target="_blank"
                                                                        class="btn btn-xs btn-outline-danger"
                                                                        title="Download PDF">
                                                                        <i class="fa fa-file-pdf"></i>
                                                                    </a>
                                                                @endif
                                                                @if (!$trx->hosted_invoice_url && !$trx->invoice_pdf)
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <small>
                                                                {{ $trx->created_at ? \Carbon\Carbon::parse($trx->created_at)->format('d M Y, h:i A') : '—' }}

                                                            </small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            {{-- Total --}}
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="2" class="text-end fw-500">Total Paid:</td>
                                                    <td colspan="4">
                                                        <strong class="text-success">
                                                            ${{ number_format($transactions->whereIn('status', ['paid', 'succeeded'])->sum('amount'), 2) }}
                                                        </strong>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-3 text-center text-muted">
                                        <i class="fa fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">No payment history found.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
