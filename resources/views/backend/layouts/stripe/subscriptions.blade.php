@extends('backend.app', ['title' => 'Stripe Subscriptions'])

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Stripe Subscriptions</h1>
                    <p class="text-muted mb-0">Live Stripe subscriptions with cursor pagination.</p>
                </div>
                <a href="{{ route('admin.dashboard', ['tab' => 'stripe']) }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            @if($error)
                <div class="alert alert-warning">{{ $error }}</div>
            @endif

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.stripe.subscriptions') }}" class="row g-3 align-items-end">
                        <div class="col-sm-6 col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm"
                                   value="{{ $filters['start_date'] ?? '' }}">
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                                   value="{{ $filters['end_date'] ?? '' }}">
                        </div>
                        <div class="col-sm-6 col-md-2">
                            <label for="limit" class="form-label">Per Page</label>
                            <select name="limit" id="limit" class="form-select form-select-sm">
                                @foreach([10,25,50,100] as $opt)
                                    <option value="{{ $opt }}" @selected($limit == $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3 d-flex align-items-end gap-2 flex-wrap">
                            <button class="btn btn-success btn-sm px-3" type="submit"><i class="fe fe-filter me-1"></i>Apply</button>
                            <a href="{{ route('admin.stripe.subscriptions') }}" class="btn btn-light btn-sm px-3">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card transaction-sales-main">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><strong>Showing</strong> {{ $items->count() }} of {{ $limit }} requested</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Started</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $sub)
                                <tr>
                                    <td>
                                        {{ $sub->customer->name ?? 'N/A' }}
                                        <div class="small text-muted">{{ $sub->customer->email ?? '' }}</div>
                                    </td>
                                    <td><span class="badge bg-{{ $sub->status === 'active' ? 'success' : ($sub->status === 'trialing' ? 'info' : 'secondary') }}">{{ ucfirst($sub->status) }}</span></td>
                                    <td class="small text-muted">{{ optional($sub->items->data[0]->price)->id ?? '—' }}</td>
                                    <td>{{ $sub->items->data[0]->quantity ?? 1 }}</td>
                                    <td>{{ \Carbon\Carbon::createFromTimestamp($sub->created)->format('d M Y, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No subscriptions found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="text-muted small">Cursor pagination: use Previous / Next.</div>
                    <div class="d-flex gap-2">
                        @if(request('starting_after'))
                            <a class="btn btn-outline-secondary"
                               href="{{ route('admin.stripe.subscriptions', array_filter([
                                    'ending_before' => request('starting_after'),
                                    'limit' => $limit,
                                    'start_date' => $filters['start_date'] ?? null,
                                    'end_date' => $filters['end_date'] ?? null,
                               ])) }}">
                                Previous
                            </a>
                        @endif
                        @if($hasMore && $items->last())
                            <a class="btn btn-primary"
                               href="{{ route('admin.stripe.subscriptions', array_filter([
                                    'starting_after' => $items->last()->id,
                                    'limit' => $limit,
                                    'start_date' => $filters['start_date'] ?? null,
                                    'end_date' => $filters['end_date'] ?? null,
                               ])) }}">
                                Next
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
