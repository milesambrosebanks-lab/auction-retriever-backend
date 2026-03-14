@extends('backend.app', ['title' => 'Extraction log'])

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
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}"><i
                                        class="fe fe-home me-2 fs-14"></i>Home</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Data Listing</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Show</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card post-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Show</h3>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <th>ID</th>
                                        <td>{{ $listing->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Title</th>
                                        <td>{{ $listing->title ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Source Url</th>
                                        <td>{{ $listing->source_url ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Property Type</th>
                                        <td>{{ $listing->property_type ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Starting Bid</th>
                                        <td>{{ $listing->starting_bid }}</td>
                                    </tr>
                                    <tr>
                                        <th>Auction Date</th>
                                        <td>{{ $listing->auction_date }}</td>
                                    </tr>
                                    <tr>
                                        <th>Source Website</th>
                                        <td>{{ $listing->source_website }}</td>
                                    </tr>

                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ $listing->created_at ? $listing->created_at : 'N/A' }}</td>
                                    </tr>
                                   
                                    {{-- <tr>
                                        <th>Action</th>
                                        <td>
                                            <button class="btn btn-sm btn-danger"
                                                onclick="showDeleteConfirm(`{{ $order->id }}`)">Delete</button>
                                            <button class="btn btn-sm btn-primary"
                                                onclick="goToEdit(`{{ $order->id }}`)">Edit</button>
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
