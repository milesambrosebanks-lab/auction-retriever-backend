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
                            <h3 class="card-title mb-0">{{ $order->first_name ?? 'N/A' }} {{ $order->last_name ?? 'N/A' }}</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>Name</th>
                                    <td><a href="{{ route('admin.users.show', $order->user_id) }}"> {{ $order->first_name ?? 'N/A' }} {{ $order->last_name ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $order->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $order->phone ?? 'N/A' }}</td>
                                </tr>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ $order->address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>City</th>
                                    <td>{{ $order->city ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Zip Code</th>
                                    <td>{{ $order->zip_code ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Country</th>
                                    <td>{{ $order->country ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $order->created_at ? $order->created_at : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $order->updated_at ? $order->updated_at : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card booking-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">Items</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>Items</th>
                                    <td>
                                        <ol class="list-group list-group-numbered">
                                            @foreach($order->items as $item)
                                            <li class="list-group-item">
                                                <a href="{{ route('admin.product.show', $item->product->id) }}">{{ $item->product->name ?? 'N/A' }} x {{ $item->quantity ?? 'N/A' }} ({{ $item->price ?? 'N/A' }}) = {{ $item->quantity * $item->price }}</a>
                                            </li>
                                            @endforeach
                                        </ol>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Subtotal</th>
                                    <td class="text-success fw-bold">{{ $order->subtotal ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Shipping</th>
                                    <td class="text-success fw-bold">{{ $order->shipping ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td class="text-success fw-bold">{{ $order->total ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
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