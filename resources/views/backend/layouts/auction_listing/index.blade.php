@extends('backend.app', ['title' => 'Auction Listings'])

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                {{-- Page Header --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Auction Listings</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">CMS</a></li>
                            <li class="breadcrumb-item active">Auction Listings</li>
                        </ol>
                    </div>
                </div>

                {{-- Stats Cards --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <h4 class="text-primary mb-1">{{ number_format($stats['total']) }}</h4>
                            <small class="text-muted">Total Listings</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <h4 class="text-success mb-1">{{ number_format($stats['land']) }}</h4>
                            <small class="text-muted">Land</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <h4 class="text-info mb-1">{{ number_format($stats['Residential']) }}</h4>
                            <small class="text-muted">Residential</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <h4 class="text-warning mb-1">{{ $stats['last_count'] }}</h4>
                            <small class="text-muted">Last Scrape Count</small>
                            <br><small class="text-muted fs-10">{{ $stats['last_scraped'] }}</small>
                        </div>
                    </div>
                </div>

                {{-- Main Table Card --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex align-items-center gap-3">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-arrow-left"></i>
                                </a>
                                <h3 class="card-title mb-0">Listings</h3>

                                <div class="ms-auto d-flex gap-2">

                                    <select id="states" class="form-select form-select-sm" style="width:150px;">
                                        <option value="">States</option>
                                        @foreach ($states as $state)
                                            <option value="{{ $state['value'] }}">{{ $state['name'] }}</option>
                                        @endforeach
                                    </select>
                                    {{-- Type Filter --}}
                                    <select id="typeFilter" class="form-select form-select-sm" style="width:150px;">
                                        <option value="">All Types</option>
                                        {{-- @foreach ($stats['types'] as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach --}}

                                            <option value="Land">Land</option>
                                            <option value="Residential">Residential</option>
                                            <option value="Commercial">Commercial</option>

                                    </select>

                                    {{-- Keyword Search --}}
                                    <input type="text" id="keywordFilter" class="form-control form-control-sm"
                                        placeholder="Search title..." style="width:200px;">

                                    {{-- Manual Scrape Button --}}
                                    {{-- <button id="scrapeNowBtn" class="btn btn-sm btn-warning">
                                        <i class="fa fa-refresh me-1"></i> Scrape Now
                                    </button> --}}

                                    <a href="{{ route('admin.auction.listings.scrape.logs') }}"
                                        class="btn btn-sm btn-outline-info ms-2">
                                        <i class="fa fa-history me-1"></i> View Scrape Logs
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                    <thead>
                                        <tr>
                                            <th class="wp-5">ID</th>
                                            <th class="wp-5">Image</th>
                                            <th>Title</th>
                                            <th>Type</th>
                                            <th>Current Bid</th>
                                            <th>Time Left</th>
                                            <th>Scraped</th>
                                            <th class="wp-5">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
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
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var table = $('#datatable').DataTable({
                order: [],
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                ajax: {
                    url: "{{ route('admin.auction.listings.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.type = $('#typeFilter').val();
                        d.state = $('#states').val();
                        d.search_keyword = $('#keywordFilter').val();
                    },
                    cache: false,
                },
                language: {
                    processing: `<div class="text-center">
                <img src="{{ asset('default/loader.gif') }}" style="width:50px;">
            </div>`
                },
                dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title_col',
                        name: 'title',
                        orderable: true
                    },
                    {
                        data: 'type_badge',
                        name: 'type',
                        orderable: true
                    },
                    {
                        data: 'bid_info',
                        name: 'current_bid',
                        orderable: true
                    },
                    {
                        data: 'time_badge',
                        name: 'time_left',
                        orderable: false
                    },
                    {
                        data: 'scraped',
                        name: 'scraped_at',
                        orderable: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
            });

            // Filter apply
            $('#typeFilter, #keywordFilter, #states').on('change keyup', function() {
                table.ajax.reload();
            });


            // Manual Scrape
            $('#scrapeNowBtn').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Scraping...');

                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.auction.listings.scrape') }}",
                    success: function(resp) {
                        toastr.success(resp.message);
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ?? 'Scraping failed!');
                    },
                    complete: function() {
                        btn.prop('disabled', false)
                            .html('<i class="fa fa-refresh me-1"></i> Scrape Now');
                    }
                });
            });


        });
    </script>
@endpush
