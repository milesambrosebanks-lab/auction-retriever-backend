@extends('backend.app', ['title' => 'Live Transactions'])

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ $crud ? ucwords(str_replace('_', ' ', $crud)) : 'Transactions' }}</h1>
                    <p class="text-muted mb-0">Live Stripe invoices with date filtering and cursor pagination.</p>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}"><i class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Transactions</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.transaction.index') }}" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input
                                        type="date"
                                        name="start_date"
                                        id="start_date"
                                        class="form-control"
                                        value="{{ $filters['start_date'] ?? '' }}"
                                    >
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input
                                        type="date"
                                        name="end_date"
                                        id="end_date"
                                        class="form-control"
                                        value="{{ $filters['end_date'] ?? '' }}"
                                    >
                                </div>
                                <div class="col-md-2">
                                    <label for="per_page" class="form-label">Per Page</label>
                                    <select name="per_page" id="per_page" class="form-select">
                                        @foreach([10, 20, 50, 100] as $option)
                                            <option value="{{ $option }}" @selected($perPage == $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-filter me-1"></i>Filter
                                    </button>
                                    <a href="{{ route('admin.transaction.index') }}" class="btn btn-light">
                                        Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            @if ($stripeError)
                <div class="alert alert-danger">
                    {{ $stripeError }}
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card transaction-sales-main">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Invoice ID</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Invoice</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transactions as $transaction)
                                            @php
                                                $matchedUser = $stripeUsers->get($transaction->customer);
                                                $currency = strtoupper($transaction->currency ?? 'USD');
                                                $amount = number_format(($transaction->amount_paid ?? 0) / 100, 2);
                                                $status = strtolower($transaction->status ?? 'draft');
                                                $badgeClass = match ($status) {
                                                    'paid', 'open' => 'success',
                                                    'draft' => 'secondary',
                                                    'uncollectible', 'void' => 'danger',
                                                    default => 'warning',
                                                };
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($transaction->id, 24) }}</div>
                                                    <small class="text-muted">{{ $transaction->customer ?? 'No customer id' }}</small>
                                                </td>
                                                <td>
                                                    @if ($matchedUser)
                                                        <a href="{{ route('admin.users.show', $matchedUser->id) }}" class="fw-semibold">
                                                            {{ $matchedUser->name }}
                                                        </a>
                                                        <div class="text-muted small">{{ $transaction->customer_email ?? $matchedUser->email }}</div>
                                                    @else
                                                        <div class="fw-semibold">{{ $transaction->customer_name ?? 'Stripe Customer' }}</div>
                                                        <div class="text-muted small">{{ $transaction->customer_email ?? 'No email found' }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-semibold">{{ $currency }} {{ $amount }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::createFromTimestamp($transaction->created)->format('d M Y, h:i A') }}
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @if ($transaction->hosted_invoice_url)
                                                            <a href="{{ $transaction->hosted_invoice_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                View
                                                            </a>
                                                        @endif
                                                        @if ($transaction->invoice_pdf)
                                                            <a href="{{ $transaction->invoice_pdf }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                                                PDF
                                                            </a>
                                                        @endif
                                                        @if (!$transaction->hosted_invoice_url && !$transaction->invoice_pdf)
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">No Stripe transactions found for the selected filters.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div class="text-muted small">
                                Stripe live list uses cursor pagination, so navigation works with Previous and Next.
                            </div>
                            <div class="d-flex gap-2">
                                @if ($prevCursor)
                                    <a
                                        href="{{ route('admin.transaction.index', array_filter([
                                            'start_date' => $filters['start_date'] ?? null,
                                            'end_date' => $filters['end_date'] ?? null,
                                            'per_page' => $perPage,
                                            'cursor' => $prevCursor,
                                            'direction' => 'prev',
                                        ])) }}"
                                        class="btn btn-outline-secondary"
                                    >
                                        Previous
                                    </a>
                                @endif

                                @if ($nextCursor)
                                    <a
                                        href="{{ route('admin.transaction.index', array_filter([
                                            'start_date' => $filters['start_date'] ?? null,
                                            'end_date' => $filters['end_date'] ?? null,
                                            'per_page' => $perPage,
                                            'cursor' => $nextCursor,
                                            'direction' => 'next',
                                        ])) }}"
                                        class="btn btn-primary"
                                    >
                                        Next
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
