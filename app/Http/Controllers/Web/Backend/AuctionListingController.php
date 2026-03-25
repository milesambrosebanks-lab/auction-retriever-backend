<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuctionListing;
use App\Models\ScrapeLog;
use App\Services\Bid4AssetsScraper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class AuctionListingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = AuctionListing::query();

            // Search filter
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('search_keyword')) {
                $query->where('title', 'like', '%' . $request->search_keyword . '%');
            }

            return DataTables::of($query->latest('scraped_at'))
                ->addIndexColumn()
                ->addColumn('image', function ($row) {
                    if ($row->image_url) {
                        return '<img src="' . e($row->image_url) . '"
                                     alt="thumb"
                                     style="width:60px; height:45px; object-fit:cover; border-radius:4px;"
                                     onerror="this.src=\'https://cdn.bid4assets.com/app/mvc/images/photo_icon.png\'">';
                    }
                    return '<span class="text-muted">—</span>';
                })
                ->addColumn('title_col', function ($row) {
                    return '<a href="' . e($row->source_url) . '" target="_blank"
                               title="' . e($row->title) . '">'
                        . \Str::limit($row->title, 50)
                        . '</a>';
                })
                ->addColumn('type_badge', function ($row) {
                    $colors = [
                        'Land'      => 'success',
                        'Financed'  => 'info',
                        'Residential' => 'primary',
                        'Commercial'  => 'warning',
                    ];
                    $color = $colors[$row->type] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . e($row->type) . '</span>';
                })
                ->addColumn('bid_info', function ($row) {
                    return '<strong>$' . e($row->current_bid) . '</strong>
                            <br><small class="text-muted">' . $row->bid_count . ' bids</small>';
                })
                ->addColumn('time_badge', function ($row) {
                    $color = str_contains($row->time_left, 'hrs') ? 'danger' : 'warning';
                    return '<span class="badge bg-' . $color . '">' . e($row->time_left) . '</span>';
                })
                ->addColumn('scraped', function ($row) {
                    return $row->scraped_at
                        ? '<span title="' . $row->scraped_at . '">'
                        . $row->scraped_at->diffForHumans()
                        . '</span>'
                        : '—';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('admin.auction.listings.show', $row->id) . '"
               class="btn btn-sm btn-success" title="View Detail">
                <i class="fa fa-eye"></i>
            </a>';
                })
                ->rawColumns(['image', 'title_col', 'type_badge', 'bid_info', 'time_badge', 'scraped', 'action'])
                ->make();
        }

        $stats = [
            'total'        => AuctionListing::count(),
            'land'         => AuctionListing::where('type', 'Land')->count(),
            'financed'     => AuctionListing::where('type', 'Financed')->count(),
            'last_scraped' => Cache::get('bid4assets_last_success', 'Never'),
            'last_count'   => Cache::get('bid4assets_last_count', 0),
            'types'        => AuctionListing::select('type')
                ->distinct()
                ->whereNotNull('type')
                ->pluck('type'),
        ];

        return view('backend.layouts.auction_listing.index', compact('stats'));
    }

    public function show(int $id)
    {
        $listing = AuctionListing::findOrFail($id);

        $isSaved = auth('web')->check()
            ? $listing->isSavedBy(auth('web')->id())
            : false;

        return view('backend.layouts.auction_listing.show', compact('listing', 'isSaved'));
    }

    // Manual scrape trigger
    public function scrapeNow(Request $request)
    {
        $source = $request->input('source', 'bid4assets');

        try {
            if ($source === 'bid4assets') {
                $scraper = app(Bid4AssetsScraper::class);
                $total = $scraper->scrapeAll();

                return response()->json([
                    'success' => true,
                    'message' => "Bid4Assets scraping complete! $total listings saved.",
                    'total' => $total,
                ]);
            } elseif ($source === 'auction_com') {
                // Run the Auction.com scrape command
                Artisan::call('scrape:auction', ['--limit' => 50, '--max' => 500]);

                return response()->json([
                    'success' => true,
                    'message' => "Auction.com scraping started in background.",
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown source.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Scraping failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Scrape logs datatable
    // AuctionListingController.php

    public function scrapeLogs(Request $request)
    {
        $source = $request->get('source');

        $sources = ScrapeLog::select('source')->distinct()->get();

        $sourceCards = [];
        foreach ($sources as $src) {
            $query = ScrapeLog::where('source', $src->source);

            $sourceCards[] = [
                'source' => $src->source,
                'label' => match ($src->source) {
                    'bid4assets' => 'Bid4Assets',
                    'auction_com' => 'Auction.com',
                    default => ucfirst(str_replace('_', ' ', $src->source)),
                },
                'success_runs' => (clone $query)->where('status', 'success')->count(),
                'failed_runs' => (clone $query)->where('status', 'failed')->count(),
                'total_runs' => (clone $query)->count(),
                'last_success' => (clone $query)->where('status', 'success')->latest('finished_at')->value('finished_at') ?: 'Never',
                'last_failed' => (clone $query)->where('status', 'failed')->latest('finished_at')->value('finished_at') ?: 'Never',
                'running_now' => (clone $query)->where('status', 'running')->exists(),
                'current_run' => (clone $query)->where('status', 'running')->latest('started_at')->first(),
                'last_count' => (clone $query)->where('status', 'success')->latest('finished_at')->value('total_scraped') ?: 0,
            ];
        }

        return view('backend.layouts.auction_listing.scrape-logs', compact('sourceCards', 'source'));
    }

    public function scrapeLogsData(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Not ajax'], 400);
        }

        $data = ScrapeLog::latest();

        // Filter by source if provided
        if ($request->filled('source')) {
            $data->where('source', $request->source);
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('source_badge', function ($row) {
                $colors = [
                    'bid4assets' => 'primary',
                    'auction'    => 'info',
                    // Add more sources as needed
                ];
                $color = $colors[$row->source] ?? 'secondary';
                $label = match ($row->source) {
                    'bid4assets' => 'Bid4Assets',
                    'auction_com' => 'Auction.com',
                    default => ucfirst($row->source),
                };
                return '<span class="badge bg-' . $color . '">' . e($label) . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                $icon = match ($row->status) {
                    'success' => 'fa-check-circle',
                    'failed'  => 'fa-times-circle',
                    'running' => 'fa-spinner fa-spin',
                    default   => 'fa-circle',
                };
                return '<span class="badge bg-' . $row->status_color . '">
                        <i class="fa ' . $icon . ' me-1"></i>'
                    . strtoupper($row->status) .
                    '</span>';
            })
            ->addColumn('message_col', function ($row) {
                $message = $row->status === 'failed' ? ($row->error_message ?: $row->message) : $row->message;
                if (!$message) return '<span class="text-muted">—</span>';
                $color = match ($row->status) {
                    'success' => 'text-success',
                    'failed'  => 'text-danger',
                    'running' => 'text-warning',
                    default   => 'text-muted',
                };
                return '<span class="' . $color . '" title="' . e($message) . '">'
                    . \Str::limit($message, 70)
                    . '</span>';
            })
            ->addColumn('duration_col', function ($row) {
                return $row->duration;
            })
            ->addColumn('started_col', function ($row) {
                return $row->started_at
                    ? $row->started_at->format('d M Y, h:i A')
                    : '—';
            })
            ->addColumn('finished_col', function ($row) {
                return $row->finished_at
                    ? $row->finished_at->format('d M Y, h:i A')
                    : '<span class="text-warning"><i class="fa fa-spinner fa-spin me-1"></i>In progress...</span>';
            })
            ->rawColumns(['source_badge', 'status_badge', 'message_col', 'finished_col'])
            ->make();
    }
}
