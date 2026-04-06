@extends('backend.app', ['title' => 'Stripe Invoices'])

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Stripe Invoices</h1>
                    <p class="text-muted mb-0">Live invoices from Stripe.</p>
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
                    <form method="GET" action="{{ route('admin.stripe.invoices') }}" class="row g-3 align-items-end">
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
                            <a href="{{ route('admin.stripe.invoices') }}" class="btn btn-light btn-sm px-3">Reset</a>
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
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $invoice)
                                @php $amount = ($invoice->total ?? 0) / 100; @endphp
                                <tr>
                                    <td>#{{ $invoice->id }}</td>
                                    <td class="small text-muted">{{ $invoice->customer ?? 'N/A' }}</td>
                                    <td>${{ number_format($amount, 2) }} {{ strtoupper($invoice->currency ?? 'USD') }}</td>
                                    <td><span class="badge bg-{{ ($invoice->status ?? '') === 'paid' ? 'success' : (($invoice->status ?? '') === 'open' ? 'warning text-dark' : 'secondary') }}">{{ ucfirst($invoice->status ?? 'n/a') }}</span></td>
                                    <td>{{ \Carbon\Carbon::createFromTimestamp($invoice->created)->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            @if(!empty($invoice->hosted_invoice_url))
                                                <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            @endif
                                            @if(!empty($invoice->invoice_pdf))
                                                <a href="{{ $invoice->invoice_pdf }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                                    PDF
                                                </a>
                                            @endif
                                            @if(empty($invoice->hosted_invoice_url) && empty($invoice->invoice_pdf))
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No invoices found.</td></tr>
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
                               href="{{ route('admin.stripe.invoices', array_filter([
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
                               href="{{ route('admin.stripe.invoices', array_filter([
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
