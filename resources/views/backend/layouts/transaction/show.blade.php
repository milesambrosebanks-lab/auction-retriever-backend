{{-- resources/views/backend/layouts/transaction/show.blade.php --}}

@extends('backend.app', ['title' => 'Transaction Detail'])

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Transaction Detail</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ url('admin/dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.transaction.index') }}">Transactions</a>
                        </li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-md-10 mx-auto">

                    <div class="card">
                        <div class="card-header border-bottom d-flex align-items-center gap-3">
                            <a href="{{ route('admin.transaction.index') }}"
                               class="btn btn-sm btn-primary">
                                <i class="fa fa-arrow-left"></i>
                            </a>
                            <h5 class="mb-0">Transaction Detail</h5>

                            @php
                                $statusColors = [
                                    'paid'      => 'success',
                                    'succeeded' => 'success',
                                    'failed'    => 'danger',
                                    'pending'   => 'warning',
                                    'refunded'  => 'info',
                                ];
                                $statusColor = $statusColors[strtolower($transaction->status ?? '')] ?? 'secondary';
                            @endphp

                            <span class="badge bg-{{ $statusColor }} ms-auto fs-12">
                                {{ ucfirst($transaction->status ?? '—') }}
                            </span>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                {{-- Transaction ID --}}
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Transaction ID</p>
                                    <small class="text-muted">{{ $transaction->trx_id ?? '—' }}</small>
                                </div>

                                {{-- Invoice ID --}}
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Invoice ID</p>
                                    <small class="text-muted">{{ $transaction->invoice_id ?? '—' }}</small>
                                </div>

                                {{-- User --}}
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">User</p>
                                    @if($transaction->user)
                                        <a href="{{ route('admin.users.show', $transaction->user->id) }}">
                                            <strong>{{ $transaction->user->name }}</strong>
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $transaction->user->email }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>

                                {{-- Customer ID --}}
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Stripe Customer ID</p>
                                    <small class="text-muted">{{ $transaction->customer_id ?? '—' }}</small>
                                </div>

                                {{-- Amount --}}
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Amount</p>
                                    <h4 class="text-success mb-0">
                                        ${{ number_format($transaction->amount, 2) }}
                                        <small class="text-muted fs-12">
                                            {{ strtoupper($transaction->currency ?? 'USD') }}
                                        </small>
                                    </h4>
                                </div>

                                {{-- Status --}}
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Status</p>
                                    <span class="badge bg-{{ $statusColor }} fs-12">
                                        {{ ucfirst($transaction->status ?? '—') }}
                                    </span>
                                </div>

                                {{-- Date --}}
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Date</p>
                                    <strong>
                                        {{ $transaction->created_at
                                            ? $transaction->created_at->format('d M Y, h:i A')
                                            : '—' }}
                                    </strong>
                                </div>

                                {{-- Metadata --}}
                                @if($transaction->metadata)
                                    <div class="col-md-12">
                                        <p class="text-muted small mb-1">Metadata</p>
                                        <pre class="bg-light p-2 rounded small">{{ json_encode(json_decode($transaction->metadata), JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                @endif

                            </div>
                        </div>

                        {{-- Invoice Actions --}}
                        <div class="card-footer border-top d-flex gap-2">
                            @if($transaction->hosted_invoice_url)
                                <a href="{{ $transaction->hosted_invoice_url }}"
                                   target="_blank"
                                   class="btn btn-primary btn-sm">
                                    <i class="fa fa-eye me-1"></i> View Invoice
                                </a>
                            @endif

                            @if($transaction->invoice_pdf)
                                <a href="{{ $transaction->invoice_pdf }}"
                                   target="_blank"
                                   class="btn btn-danger btn-sm">
                                    <i class="fa fa-file-pdf me-1"></i> Download PDF
                                </a>
                            @endif

                            @if($transaction->user)
                                <a href="{{ route('admin.users.show', $transaction->user->id) }}"
                                   class="btn btn-info btn-sm ms-auto">
                                    <i class="fa fa-user me-1"></i> View User
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
