{{-- resources/views/backend/layouts/cms/auction-listings/scrape-logs.blade.php --}}

@extends('backend.app', ['title' => 'Scrape Logs'])

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
    <style>
        .source-status-card {
            border-left: 4px solid;
        }

        .source-status-card.success {
            border-color: #28a745;
        }

        .source-status-card.failed {
            border-color: #dc3545;
        }

        .source-status-card.running {
            border-color: #ffc107;
        }

        .pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }
    </style>
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                {{-- Header --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Scrape Logs</h1>
                        <p class="text-muted mb-0">Extraction run history & monitoring</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.auction.listings.index') }}">Auction Listings</a>
                            </li>
                            <li class="breadcrumb-item active">Scrape Logs</li>
                        </ol>
                    </div>
                </div>

                {{-- Source Status Card --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div
                            class="card source-status-card {{ $cards['running_now'] ? 'running' : ($cards['success_runs'] > 0 ? 'success' : 'failed') }}">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-1 text-center">
                                        @if ($cards['running_now'])
                                            <i class="fa fa-spinner fa-spin fa-2x text-warning pulse"></i>
                                        @elseif($cards['success_runs'] > 0)
                                            <i class="fa fa-check-circle fa-2x text-success"></i>
                                        @else
                                            <i class="fa fa-times-circle fa-2x text-danger"></i>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-0 text-muted small">Source</p>
                                        <strong class="fs-16">
                                            <a href="https://www.bid4assets.com" target="_blank">
                                                Bid4Assets
                                                <i class="fa fa-external-link ms-1 fs-12"></i>
                                            </a>
                                        </strong>
                                        <br>
                                        @if ($cards['running_now'])
                                            <span class="badge bg-warning">
                                                <i class="fa fa-spinner fa-spin me-1"></i>
                                                Running — Attempt {{ $cards['current_run']->attempt ?? 1 }}
                                            </span>
                                        @else
                                            <span class="badge bg-success">Active Source</span>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-0 text-muted small">Last Successful Run</p>
                                        <strong>{{ $cards['last_success'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $cards['last_count'] }} items scraped</small>
                                    </div>
                                    <div class="col-md-2">
                                        <p class="mb-0 text-muted small">Last Failed</p>
                                        <strong
                                            class="{{ $cards['last_failed'] !== 'Never' ? 'text-danger' : 'text-muted' }}">
                                            {{ $cards['last_failed'] }}
                                        </strong>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <div class="d-flex gap-3 justify-content-end">
                                            <div class="text-center">
                                                <h4 class="text-success mb-0">{{ $cards['success_runs'] }}</h4>
                                                <small class="text-muted">Success</small>
                                            </div>
                                            <div class="text-center">
                                                <h4 class="text-danger mb-0">{{ $cards['failed_runs'] }}</h4>
                                                <small class="text-muted">Failed</small>
                                            </div>
                                            <div class="text-center">
                                                <h4 class="text-primary mb-0">{{ $cards['total_runs'] }}</h4>
                                                <small class="text-muted">Total</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Current run message --}}
                                @if ($cards['running_now'] && $cards['current_run']?->message)
                                    <hr class="my-2">
                                    <div class="alert alert-warning mb-0 py-2">
                                        <i class="fa fa-info-circle me-1"></i>
                                        <strong>Live:</strong> {{ $cards['current_run']->message }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Logs Table --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex align-items-center gap-3">
                                <a href="{{ route('admin.auction.listings.index') }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-arrow-left"></i>
                                </a>
                                <h3 class="card-title mb-0">Run History</h3>
                                <div class="ms-auto d-flex gap-2">
                                    <button id="scrapeNowBtn" class="btn btn-sm btn-warning">
                                        <i class="fa fa-download me-1"></i> Scrape Now
                                    </button>
                                    <button id="refreshBtn" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa fa-refresh me-1"></i> Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered text-nowrap border-bottom" id="logsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Status</th>
                                            <th>Message</th>
                                            <th>Items Saved</th>
                                            <th>Attempt</th>
                                            <th>Duration</th>
                                            <th>Started At</th>
                                            <th>Finished At</th>
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

            var table = $('#logsTable').DataTable({
                order: [
                    [0, 'desc']
                ],
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50],
                    [10, 25, 50]
                ],
                ajax: {
                    url: "{{ route('admin.auction.listings.scrape.logs.data') }}",
                    type: "GET",
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
                        data: 'status_badge',
                        name: 'status'
                    },
                    {
                        data: 'message_col',
                        name: 'message',
                        orderable: false
                    },
                    {
                        data: 'total_scraped',
                        name: 'total_scraped'
                    },
                    {
                        data: 'attempt',
                        name: 'attempt'
                    },
                    {
                        data: 'duration_col',
                        name: 'duration_col',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'started_col',
                        name: 'started_at'
                    },
                    {
                        data: 'finished_col',
                        name: 'finished_at',
                        orderable: false
                    },
                ],
            });

            // Refresh button
            $('#refreshBtn').on('click', function() {
                table.ajax.reload(null, false);
                location.reload(); // cards ও refresh হবে
            });

            // Scrape Now
            $('#scrapeNowBtn').on('click', function() {
                var btn = $(this);

                Swal.fire({
                    title: 'Start Scraping?',
                    text: 'This will extract all listings from Bid4Assets.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Start!',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    btn.prop('disabled', true)
                        .html('<i class="fa fa-spinner fa-spin me-1"></i> Scraping...');

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('admin.auction.listings.scrape') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(resp) {
                            toastr.success(resp.message);
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message ??
                                'Scraping failed!');
                        },
                        complete: function() {
                            btn.prop('disabled', false)
                                .html('<i class="fa fa-download me-1"></i> Scrape Now');

                            // table + page reload
                            table.ajax.reload(null, false);
                            setTimeout(() => location.reload(), 1500);
                        }
                    });
                });
            });
            // Running থাকলে auto-refresh প্রতি ১০ সেকেন্ডে
            @if ($cards['running_now'])
                setInterval(function() {
                    table.ajax.reload(null, false);
                }, 10000);
            @endif

        });
    </script>
@endpush
