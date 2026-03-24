{{-- resources/views/backend/layouts/cms/auction-listings/show.blade.php --}}

@extends('backend.app', ['title' => 'Listing Detail'])

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            {{-- Header --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title">Auction List Detail</h1>
                    <p class="text-muted mb-0"># {{ $listing->auction_id }}</p>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.auction.listings.index') }}">Auction Listings</a>
                        </li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>

            <div class="row">

                {{-- Left: Image + Actions --}}
                <div class="col-md-4">

                    {{-- Image Card --}}
                    <div class="card mb-3">
                        <div class="card-body text-center p-2">
                            @if($listing->image_url)
                                <img src="{{ $listing->image_url }}"
                                     alt="{{ $listing->title }}"
                                     class="img-fluid rounded"
                                     style="max-height: 280px; width:100%; object-fit:cover;"
                                     onerror="this.src='https://cdn.bid4assets.com/app/mvc/images/photo_icon.png'">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded"
                                     style="height:200px;">
                                    <i class="fa fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions Card --}}
                    <div class="card mb-3">
                        <div class="card-body d-grid gap-2">
                            <a href="{{ $listing->source_url }}"
                               target="_blank"
                               class="btn btn-success">
                                <i class="fa fa-external-link me-1"></i>
                                View on Bid4Assets
                            </a>
                            <a href="{{ route('admin.auction.listings.index') }}"
                               class="btn btn-outline-secondary">
                                <i class="fa fa-arrow-left me-1"></i>
                                Back to Listings
                            </a>
                        </div>
                    </div>

                    {{-- Bid Info Card --}}
                    <div class="card mb-3">
                        <div class="card-header border-bottom">
                            <h6 class="mb-0">Bid Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 border-end">
                                    <h4 class="text-success mb-0">{{ $listing->current_bid ?? '—' }}</h4>
                                    <small class="text-muted">Current Bid</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-primary mb-0">{{ $listing->bid_count ?? 0 }}</h4>
                                    <small class="text-muted">Total Bids</small>
                                </div>
                            </div>
                            @if($listing->time_left)
                                <hr class="my-2">
                                <div class="text-center">
                                    <span class="badge bg-{{ str_contains($listing->time_left, 'hrs') ? 'danger' : 'warning' }} fs-12">
                                        <i class="fa fa-clock me-1"></i>
                                        {{ $listing->time_left }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Right: Details --}}
                <div class="col-md-8">

                    {{-- Title Card --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <h4 class="mb-2">{{ $listing->title }}</h4>
                                @if($listing->type)
                                    @php
                                        $colors = [
                                            'Land'        => 'success',
                                            'Financed'    => 'info',
                                            'Residential' => 'primary',
                                            'Commercial'  => 'warning',
                                        ];
                                        $color = $colors[$listing->type] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }} text-nowrap">
                                        {{ $listing->type }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-muted mb-0">
                                <i class="fa fa-hashtag me-1"></i>
                                Auction ID: <strong>{{ $listing->auction_id }}</strong>
                            </p>
                        </div>
                    </div>

                    {{-- Location Card --}}
                    <div class="card mb-3">
                        <div class="card-header border-bottom">
                            <h6 class="mb-0">
                                <i class="fa fa-map-marker me-1 text-danger"></i>
                                Location
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">City</p>
                                    <strong>{{ $listing->city ?? '—' }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">State</p>
                                    <strong>{{ $listing->state ?? '—' }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">ZIP Code</p>
                                    <strong>{{ $listing->zip ?? '—' }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Country</p>
                                    <strong>{{ $listing->country ?? '—' }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Parcel Number</p>
                                    <strong>{{ $listing->parcel_number ?? '—' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Auction Timeline Card --}}
                    <div class="card mb-3">
                        <div class="card-header border-bottom">
                            <h6 class="mb-0">
                                <i class="fa fa-calendar me-1 text-primary"></i>
                                Auction Timeline
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Auction Started</p>
                                    <strong>
                                        {{ $listing->auction_started_at
                                            ? \Carbon\Carbon::parse($listing->auction_started_at)->format('d M Y, h:i A')
                                            : '—' }}
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Auction Closes</p>
                                    <strong class="{{ $listing->auction_date && \Carbon\Carbon::parse($listing->auction_date)->isPast() ? 'text-danger' : 'text-success' }}">
                                        {{ $listing->auction_date
                                            ? \Carbon\Carbon::parse($listing->auction_date)->format('d M Y, h:i A')
                                            : '—' }}
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Time Left</p>
                                    @if($listing->time_left)
                                        <span class="badge bg-{{ str_contains($listing->time_left, 'hrs') ? 'danger' : 'warning' }}">
                                            {{ $listing->time_left }}
                                        </span>
                                    @else
                                        <strong>—</strong>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Last Scraped</p>
                                    <strong>
                                        {{ $listing->scraped_at
                                            ? $listing->scraped_at->diffForHumans()
                                            : '—' }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Source Info Card --}}
                    <div class="card mb-3">
                        <div class="card-header border-bottom">
                            <h6 class="mb-0">
                                <i class="fa fa-info-circle me-1 text-info"></i>
                                Source Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Source</p>
                                    <strong>Bid4Assets</strong>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Channel Code</p>
                                    <strong>{{ $listing->channel_code ?? '—' }}</strong>
                                </div>
                                <div class="col-md-12">
                                    <p class="text-muted small mb-1">Source URL</p>
                                    <a href="{{ $listing->source_url }}"
                                       target="_blank"
                                       class="text-primary text-break">
                                        {{ $listing->source_url }}
                                        <i class="fa fa-external-link ms-1 fs-11"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
