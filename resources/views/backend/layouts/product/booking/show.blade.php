@extends('backend.app', ['title' => 'Show Booking'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Booking</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Booking</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card booking-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">{{ Str::limit($booking->product->title ?? 'No Title', 50) }}</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>Product Title</th>
                                    <td><a href="{{ route('admin.product.show', $booking->product->id) }}">{{ $booking->product->title ?? 'N/A' }}</a></td>
                                </tr>
                                <tr>
                                    <th>Customer</th>
                                    <td><a href="{{ route('admin.users.show', $booking->user->id) }}">{{ $booking->user->name ?? 'N/A' }}</a></td>
                                </tr>
                                <tr>
                                    <th>Package</th>
                                    @if ($booking->package)
                                    <td><a href="{{ route('admin.packages.show', $booking->package->id) }}">{{ $booking->package->name ?? 'N/A' }}</a></td>
                                    @else
                                    <td>N/A</td>
                                    @endif
                                </tr>
                                <tr>
                                    <th>Total Price</th>
                                    <td>{{ $booking->total_price ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Status</th>
                                    <td>{{ $booking->payment_status ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $booking->created_at ? $booking->created_at : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $booking->updated_at ? $booking->updated_at : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card booking-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">Transaction</h3>
                        </div>
                        @if($transactions->count() > 0)
                        @foreach($transactions as $transaction)
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>Title</th>
                                    <td>{{ $transaction->title ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ $transaction->trx_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Action</th>
                                    <td><a href="{{ route('admin.transaction.show', $transaction->id) }}" class="btn btn-primary">View</a></td>
                                </tr>
                            </table>
                        </div>
                        @endforeach
                        @else
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>No Transaction</th>
                                </tr>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection
@push('scripts')

@endpush