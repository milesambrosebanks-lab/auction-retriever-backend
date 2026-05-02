@extends('backend.app', ['title' => 'Subscription Detail'])

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            {{-- Page Header --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title">Subscription Detail</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ url('admin/dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.subscriptions.index') }}">Subscriptions</a>
                        </li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">

                    {{-- ── Subscription Card ──────────────────────────── --}}
                    <div class="card mb-3">
                        <div class="card-header border-bottom d-flex align-items-center gap-3">
                            <a href="{{ route('admin.subscriptions.index') }}"
                               class="btn btn-sm btn-primary">
                                <i class="fa fa-arrow-left"></i>
                            </a>
                            <h5 class="mb-0">Subscription # {{ $subscription->id }}</h5>

                            @php
                                $colors = [
                                    'active'   => 'success',
                                    'trialing' => 'info',
                                    'canceled' => 'danger',
                                    'past_due' => 'warning',
                                    'paused'   => 'secondary',
                                ];
                                $color = $colors[$subscription->stripe_status] ?? 'secondary';
                                $label = $subscription->stripe_status === 'trialing'
                                    ? 'Trial'
                                    : ucfirst($subscription->stripe_status);
                            @endphp

                            <span class="badge bg-{{ $color }} ms-auto fs-12">
                                {{ $label }}
                            </span>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                {{-- User --}}
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">User</p>
                                    @if($subscription->user)
                                        <a href="{{ route('admin.users.show', $subscription->user->id) }}">
                                            <strong>{{ $subscription->user->name }}</strong>
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $subscription->user->email }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>

                                {{-- Type --}}
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Type</p>
                                    <strong>{{ $subscription->type ?? '—' }}</strong>
                                </div>

                                {{-- Quantity --}}
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Quantity</p>
                                    <strong>{{ $subscription->quantity ?? 1 }}</strong>
                                </div>

                                {{-- Stripe Subscription ID --}}
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Stripe Subscription ID</p>
                                    <small class="text-muted">{{ $subscription->stripe_id ?? '—' }}</small>
                                </div>

                                {{-- Stripe Price ID --}}
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Stripe Price ID</p>
                                    <small class="text-muted">{{ $subscription->stripe_price ?? '—' }}</small>
                                </div>

                                {{-- Created At --}}
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Created At</p>
                                    <strong>
                                        {{ $subscription->created_at
                                            ? $subscription->created_at->format('d M Y, h:i A')
                                            : '—' }}
                                    </strong>
                                </div>

                                {{-- Trial Ends --}}
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Trial Ends At</p>
                                    <strong class="{{ $subscription->trial_ends_at && \Carbon\Carbon::parse($subscription->trial_ends_at)->isPast() ? 'text-danger' : 'text-success' }}">
                                        {{ $subscription->trial_ends_at
                                            ? \Carbon\Carbon::parse($subscription->trial_ends_at)->format('d M Y, h:i A')
                                            : '—' }}
                                    </strong>
                                </div>

                                {{-- Ends At --}}
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Ends At</p>
                                    <strong class="{{ $subscription->ends_at && \Carbon\Carbon::parse($subscription->ends_at)->isPast() ? 'text-danger' : 'text-success' }}">
                                        {{ $subscription->ends_at
                                            ? \Carbon\Carbon::parse($subscription->ends_at)->format('d M Y, h:i A')
                                            : '—' }}
                                    </strong>
                                </div>

                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="card-footer border-top d-flex align-items-center gap-2">
                            <small class="text-muted me-2">Manage:</small>

                            @if($subscription->stripe_status === 'active')
                                <button onclick="confirmAction('{{ route('admin.subscriptions.pause', $subscription->id) }}', 'pause')"
                                        class="btn btn-warning btn-sm">
                                    <i class="fa fa-pause me-1"></i> Pause
                                </button>
                            @endif

                            @if($subscription->stripe_status === 'paused')
                                <button onclick="confirmAction('{{ route('admin.subscriptions.resume', $subscription->id) }}', 'resume')"
                                        class="btn btn-success btn-sm">
                                    <i class="fa fa-play me-1"></i> Resume
                                </button>
                            @endif

                            @if(!in_array($subscription->stripe_status, ['canceled']))
                                <button onclick="confirmAction('{{ route('admin.subscriptions.cancel', $subscription->id) }}', 'cancel')"
                                        class="btn btn-danger btn-sm">
                                    <i class="fa fa-times me-1"></i> Cancel
                                </button>
                            @endif

                            @if($subscription->user)
                                <a href="{{ route('admin.users.show', $subscription->user->id) }}"
                                   class="btn btn-info btn-sm ms-auto">
                                    <i class="fa fa-user me-1"></i> View User
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- ── Transaction History Card ────────────────────── --}}
                    <div class="card">
                        <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <i class="fa fa-credit-card me-1 text-warning"></i>
                                Payment History
                            </h6>
                            <span class="badge bg-secondary">
                                {{ isset($transactions) ? $transactions->count() : 0 }} transactions
                            </span>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($transactions) && $transactions->count() > 0)
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
                                            @foreach($transactions as $trx)
                                                @php
                                                    $statusColors = [
                                                        'paid'      => 'success',
                                                        'succeeded' => 'success',
                                                        'failed'    => 'danger',
                                                        'pending'   => 'warning',
                                                        'refunded'  => 'info',
                                                    ];
                                                    $statusColor = $statusColors[strtolower($trx->status ?? '')] ?? 'secondary';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <small class="text-muted">
                                                            {{ \Str::limit($trx->trx_id, 20) }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <strong>
                                                            {{ strtoupper($trx->currency ?? 'USD') }}
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
                                                            @if($trx->hosted_invoice_url)
                                                                <a href="{{ $trx->hosted_invoice_url }}"
                                                                   target="_blank"
                                                                   class="btn btn-xs btn-outline-primary"
                                                                   title="View Invoice">
                                                                    <i class="fa fa-eye"></i>
                                                                </a>
                                                            @endif
                                                            @if($trx->invoice_pdf)
                                                                <a href="{{ $trx->invoice_pdf }}"
                                                                   target="_blank"
                                                                   class="btn btn-xs btn-outline-danger"
                                                                   title="Download PDF">
                                                                    <i class="fa fa-file-pdf"></i>
                                                                </a>
                                                            @endif
                                                            @if(!$trx->hosted_invoice_url && !$trx->invoice_pdf)
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            {{ $trx->created_at
                                                                ? \Carbon\Carbon::parse($trx->created_at)->format('d M Y, h:i A')
                                                                : '—' }}
                                                        </small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="2" class="text-end fw-500">Total Paid:</td>
                                                <td colspan="3">
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

@push('scripts')
<script>
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

window.confirmAction = function(url, action) {
    const messages = {
        pause  : 'Are you sure you want to pause this subscription?',
        resume : 'Are you sure you want to resume this subscription?',
        cancel : 'Are you sure you want to cancel? User will remain active until period end.',
    };

    Swal.fire({
        title             : 'Confirm ' + action.charAt(0).toUpperCase() + action.slice(1),
        text              : messages[action],
        icon              : 'warning',
        showCancelButton  : true,
        confirmButtonText : 'Yes, ' + action + ' it!',
        cancelButtonText  : 'No',
        confirmButtonColor: action === 'cancel' ? '#dc3545' : '#ffc107',
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            type   : 'POST',
            url    : url,
            success: function (resp) {
                toastr.success(resp.message);
                setTimeout(() => window.location.reload(), 1500);
            },
            error  : function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Action failed!');
            }
        });
    });
};
</script>
@endpush
