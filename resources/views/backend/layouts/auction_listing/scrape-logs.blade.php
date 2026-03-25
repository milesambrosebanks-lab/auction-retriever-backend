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

                {{-- Source Status Cards --}}
                <div class="row mb-4">
                    @foreach($sourceCards as $card)
                        <div class="col-lg-6 col-md-6 mb-3">
                            <div class="card source-status-card {{ $card['running_now'] ? 'running' : ($card['success_runs'] > 0 ? 'success' : 'failed') }}">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 text-center">
                                            @if ($card['running_now'])
                                                <i class="fa fa-spinner fa-spin fa-2x text-warning pulse"></i>
                                            @elseif($card['success_runs'] > 0)
                                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                            @else
                                                <i class="fa fa-times-circle fa-2x text-danger"></i>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-0 text-muted small">Source</p>
                                            <strong class="fs-16">
                                                @if ($card['source'] === 'auction_com')
                                                    <a href="https://www.auction.com" target="_blank">
                                                        {{ $card['label'] }}
                                                        <i class="fa fa-external-link ms-1 fs-12"></i>
                                                    </a>
                                                @elseif ($card['source'] === 'bid4assets')
                                                    <a href="https://www.bid4assets.com" target="_blank">
                                                        {{ $card['label'] }}
                                                        <i class="fa fa-external-link ms-1 fs-12"></i>
                                                    </a>
                                                @else
                                                    {{ $card['label'] }}
                                                @endif
                                            </strong>
                                            <br>
                                            @if ($card['running_now'])
                                                <span class="badge bg-warning">
                                                    <i class="fa fa-spinner fa-spin me-1"></i>
                                                    Running — Attempt {{ $card['current_run']->attempt ?? 1 }}
                                                </span>
                                            @else
                                                <span class="badge bg-success">Active Source</span>
                                            @endif
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-0 text-muted small">Last Success</p>
                                            <strong>{{ $card['last_success'] }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $card['last_count'] }} items</small>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="d-flex gap-2 justify-content-end align-items-center">
                                                <div class="text-center">
                                                    <h5 class="text-success mb-0">{{ $card['success_runs'] }}</h5>
                                                    <small class="text-muted">Success</small>
                                                </div>
                                                <div class="text-center">
                                                    <h5 class="text-danger mb-0">{{ $card['failed_runs'] }}</h5>
                                                    <small class="text-muted">Failed</small>
                                                </div>
                                                <div class="text-center">
                                                    <h5 class="text-primary mb-0">{{ $card['total_runs'] }}</h5>
                                                    <small class="text-muted">Total</small>
                                                </div>
                                                <button class="btn btn-sm btn-warning scrape-btn" data-source="{{ $card['source'] }}" data-label="{{ $card['label'] }}">
                                                    <i class="fa fa-download"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Current run message --}}
                                    @if ($card['running_now'] && $card['current_run']?->message)
                                        <hr class="my-2">
                                        <div class="alert alert-warning mb-0 py-2">
                                            <i class="fa fa-info-circle me-1"></i>
                                            <strong>Live:</strong> {{ $card['current_run']->message }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
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
                                <div class="d-flex align-items-center gap-2">
                                    <label for="sourceFilter" class="form-label mb-0">Filter by Source:</label>
                                    <select id="sourceFilter" class="form-select form-select-sm" style="width: auto;">
                                        <option value="">All Sources</option>
                                        <option value="bid4assets">Bid4Assets</option>
                                        <option value="auction_com">Auction.com</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                </div>
                                <div class="ms-auto d-flex gap-2">
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
                                            <th>Source</th>
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
                    data: function(d) {
                        d.source = $('#sourceFilter').val();
                    }
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
                        data: 'source_badge',
                        name: 'source'
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

            // restore selected source from query
            var urlParams = new URLSearchParams(window.location.search);
            var selectedSource = urlParams.get('source');
            if (selectedSource) {
                $('#sourceFilter').val(selectedSource);
            }

            // Source filter
            $('#sourceFilter').on('change', function() {
                var val = $(this).val();
                if (val) {
                    urlParams.set('source', val);
                } else {
                    urlParams.delete('source');
                }
                window.history.replaceState({}, '', window.location.pathname + '?' + urlParams.toString());
                table.ajax.reload();
            });

            // Refresh button
            $('#refreshBtn').on('click', function() {
                table.ajax.reload(null, false);
                location.reload(); // cards ও refresh হবে
            });

            // Scrape Now
            $('.scrape-btn').on('click', function() {
                var btn = $(this);
                var source = btn.data('source');
                var label = btn.data('label');

                Swal.fire({
                    title: 'Start Scraping ' + label + '?',
                    text: 'This will extract all listings from ' + label + '.',
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
                        data: { source: source },
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
                                .html('<i class="fa fa-download"></i>');

                            // table + page reload
                            table.ajax.reload(null, false);
                            setTimeout(() => location.reload(), 1500);
                        }
                    });
                });
            });
            // Running থাকলে auto-refresh প্রতি ১০ সেকেন্ডে
            @if (collect($sourceCards)->contains('running_now', true))
                setInterval(function() {
                    table.ajax.reload(null, false);
                }, 10000);
            @endif

        });
    </script>
@endpush
