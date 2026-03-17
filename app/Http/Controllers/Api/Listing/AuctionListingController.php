<?php

namespace App\Http\Controllers\Api\Listing;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuctionListingResource;
use App\Models\AuctionListing;
use App\Models\SavedListing;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;

class AuctionListingController extends Controller
{
    use ApiResponse;
    // ── Browse listings with filters ────────────────────────────────────────
    public function index(Request $request)
    {

        $current_page = 1;
        $per_page = 18;
        $paginate = false;

        $query = AuctionListing::query();

        // ── Filters ──────────────────────────────────────────────────────
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('county')) {
            $query->where('county', 'like', '%' . $request->county . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('bid_min')) {
            $query->where('current_bid', '>=', $request->bid_min);
        }

        if ($request->filled('bid_max')) {
            $query->where('current_bid', '<=', $request->bid_max);
        }

        if ($request->filled('auction_date_from')) {
            $query->whereDate('auction_date', '>=', $request->auction_date_from);
        }

        if ($request->filled('auction_date_to')) {
            $query->whereDate('auction_date', '<=', $request->auction_date_to);
        }

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        // ── Sorting ───────────────────────────────────────────────────────
        $sortBy  = $request->input('sort_by', 'scraped_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = ['bid_amount', 'bid_count', 'auction_date', 'scraped_at', 'title'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        if ($request->filled('per_page')) {
            $per_page = $request->per_page;
            $paginate = true;
        }
        if ($request->filled('current_page')) {
            $current_page = $request->current_page;
            $paginate = true;
        }
        if ($paginate) {
            $data = $query->paginate($per_page, ['*'], 'page', $current_page);
        } else {
            $data = $query->orderBy('id', 'desc')->get();
        }

        $listings = AuctionListingResource::collection($data);
        $filters = [
            'states'  => AuctionListing::select('state')->distinct()->whereNotNull('state')->orderBy('state')->pluck('state'),
            'types'   => AuctionListing::select('type')->distinct()->whereNotNull('type')->orderBy('type')->pluck('type'),
        ];
        $data = ['data' => $listings, 'filters' => $filters];

        return jsonResponse(true, 'data retrive successfully done', 200, $data, $paginate, $listings);



        // $listings = $query->paginate($request->input('per_page', 15));

        // return AuctionListingResource::collection($listings)->additional([
        //     'filters' => [
        //         'states'  => AuctionListing::select('state')->distinct()->whereNotNull('state')->orderBy('state')->pluck('state'),
        //         'types'   => AuctionListing::select('type')->distinct()->whereNotNull('type')->orderBy('type')->pluck('type'),
        //     ],
        //     'meta' => [
        //         'total'       => $listings->total(),
        //         'per_page'    => $listings->perPage(),
        //         'current_page' => $listings->currentPage(),
        //         'last_page'   => $listings->lastPage(),
        //     ]
        // ]);
    }

    // ── Single listing ───────────────────────────────────────────────────────
    public function show(int $id)
    {
        $listing = AuctionListing::findOrFail($id);
        return new AuctionListingResource($listing);
    }

    // ── Save listing ──────────────────────────────────────────────────────────
    public function save(int $id)
    {
        try {
             $listing = AuctionListing::findOrFail($id);

            $saved = SavedListing::where('user_id', auth('api')->id())
                ->where('auction_listing_id', $id)
                ->first();

            if ($saved) {
                $saved->delete();
                return jsonResponse(true, 'Listing removed from saved.', 200, [
                    'saved'    => false,
                    'message'  => 'Listing removed from saved.',
                ]);
            }

            SavedListing::create([
                'user_id'            => auth('api')->id(),
                'auction_listing_id' => $id,
            ]);

            return jsonResponse(true, 'Listing saved successfully.', 200, [
                'saved'   => true,
                'message' => 'Listing saved successfully.',
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return jsonErrorResponse('inavlid auction listing id', 500,[
                'message'=>$th->getMessage(),
            ]);
        }
    }

    // ── User's saved listings ─────────────────────────────────────────────────
    public function savedListings(Request $request)
    {
        $current_page = 1;
        $per_page = 18;
        $paginate = false;

        $query = AuctionListing::whereHas('savedByUsers', function ($q) {
            $q->where('user_id', auth('api')->id());
        });
        // ->paginate($request->input('per_page', 15));

        // ── Filters ──────────────────────────────────────────────────────
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('county')) {
            $query->where('county', 'like', '%' . $request->county . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('bid_min')) {
            $query->where('current_bid', '>=', $request->bid_min);
        }

        if ($request->filled('bid_max')) {
            $query->where('current_bid', '<=', $request->bid_max);
        }

        if ($request->filled('auction_date_from')) {
            $query->whereDate('auction_date', '>=', $request->auction_date_from);
        }

        if ($request->filled('auction_date_to')) {
            $query->whereDate('auction_date', '<=', $request->auction_date_to);
        }

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        // ── Sorting ───────────────────────────────────────────────────────
        $sortBy  = $request->input('sort_by', 'scraped_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = ['bid_amount', 'bid_count', 'auction_date', 'scraped_at', 'title'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        if ($request->filled('per_page')) {
            $per_page = $request->per_page;
            $paginate = true;
        }
        if ($request->filled('current_page')) {
            $current_page = $request->current_page;
            $paginate = true;
        }
        if ($paginate) {
            $data = $query->paginate($per_page, ['*'], 'page', $current_page);
        } else {
            $data = $query->orderBy('id', 'desc')->get();
        }

        $listings = AuctionListingResource::collection($data);

        $filters = [
            'states'  => AuctionListing::select('state')->distinct()->whereNotNull('state')->orderBy('state')->pluck('state'),
            'types'   => AuctionListing::select('type')->distinct()->whereNotNull('type')->orderBy('type')->pluck('type'),
        ];
        $data = ['data' => $listings, 'filters' => $filters];

        return jsonResponse(true, 'data retrive successfully done', 200, $data, $paginate, $listings);

        // return AuctionListingResource::collection($listings)->additional([
        //     'meta' => [
        //         'total'        => $listings->total(),
        //         'per_page'     => $listings->perPage(),
        //         'current_page' => $listings->currentPage(),
        //         'last_page'    => $listings->lastPage(),
        //     ]
        // ]);
    }

    // ── Filter options (state, county, type list) ─────────────────────────────
    public function filterOptions()
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'states'  => AuctionListing::select('state')
                    ->distinct()
                    ->whereNotNull('state')
                    ->orderBy('state')
                    ->pluck('state'),
                'counties' => AuctionListing::select('county')
                    ->distinct()
                    ->whereNotNull('county')
                    ->orderBy('county')
                    ->pluck('county'),
                'types'   => AuctionListing::select('type')
                    ->distinct()
                    ->whereNotNull('type')
                    ->orderBy('type')
                    ->pluck('type'),
                'bid_range' => [
                    'min' => AuctionListing::min('bid_amount'),
                    'max' => AuctionListing::max('bid_amount'),
                ],
            ]
        ]);
    }

    public function delete($id)
    {
        $listing = SavedListing::where('user_id', auth('api')->user()->id)->where('auction_listing_id', $id)->first();
        if (!$listing) {
            return $this->error([], 'Listing not found');
        }
        $listing->delete();

        return $this->success($listing, 'Listing deleted successfully', 200);
    }
}
