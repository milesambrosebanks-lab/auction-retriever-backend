@extends('backend.app', ['title' => 'Show Subscriptions'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ $crud ? ucwords(str_replace('_', ' ', $crud)) : 'N/A' }}</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url("admin/dashboard") }}"><i class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Subscriptions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card transaction-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">Show</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-header border-bottom">
                            {{-- <h3 class="card-title mb-0">{{ Str::limit($subscriptions->title, 50) }}</h3> --}}
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                @if ($subscriptions->title != null)
                                    <tr>
                                    <th>Title</th>
                                    <td>{{ $subscriptions->title ?? 'N/A' }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>User ID</th>
                                    <td>{{ $subscriptions->user_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>User Email</th>
                                    <td>{{ $subscriptions->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $subscriptions->type ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Stripe ID</th>
                                    <td>{{ $subscriptions->stripe_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Stripe Status</th>
                                    <td>{{ $subscriptions->stripe_status ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Stripe Price</th>
                                    <td>{{ $subscriptions->stripe_price ?? 'N/A' }}</td>
                                </tr>
                                 <tr>
                                    <th>Quantity</th>
                                    <td>{{ $subscriptions->quantity ?? 'N/A' }}</td>
                                </tr>
                                 <tr>
                                    <th>Trial Ends At</th>
                                    <td>{{ $subscriptions->trial_ends_at ?? 'N/A' }}</td>
                                </tr>
                                 <tr>
                                    <th>Ends At</th>
                                    <td>{{ $subscriptions->ends_at ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $subscriptions->created_at ? $subscriptions->created_at : 'N/A' }}</td>
                                </tr>
                                
                                {{-- <tr>
                                    <th>Action</th>
                                    <td>
                                        @if($subscriptions->metadata_json != null)
                                        @foreach($subscriptions->metadata_json as $key => $value)
                                            @if($key == 'product' && $value != null)
                                            <a href="{{ route('admin.product.show', $value) }}" class="btn btn-primary">View Product</a>
                                            @endif
                                            @if($key == 'owner' && $value != null)
                                            <a href="{{ route('admin.users.show', $value) }}" class="btn btn-primary">View Owner</a>
                                            @endif
                                            @if($key == 'customer' && $value != null)
                                            <a href="{{ route('admin.users.show', $value) }}" class="btn btn-primary">View Customer</a>
                                            @endif
                                          
                                        @endforeach
                                        @endif
                                    </td>
                                </tr> --}}
                            </table>
                        </div>
                    </div>
                </div><!-- COL END -->
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection
@push('scripts')

@endpush