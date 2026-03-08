<?php

namespace App\Http\Controllers\Api\Listing;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\SavedListing;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class ListingController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $current_page = 1;
        $per_page = 18;
        $paginate = false;

        $query = Listing::query();

        if ($request->state) {
            $query->where('state', $request->state);
        }

        if ($request->county) {
            $query->where('country', $request->county);
        }

        if ($request->property_type) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->min_bid && $request->max_bid) {
            $query->whereBetween('starting_bid', [$request->min_bid, $request->max_bid]);
        }

        if ($request->auction_date) {
            $query->whereDate('auction_date', $request->auction_date);
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

        return jsonResponse(true, 'data retrive successfully done', 200, $data, $paginate,);
    }

    public function saveListingView(Request $request)
    {
        $current_page = 1;
        $per_page = 18;
        $paginate = false;

        $query = SavedListing::with('listing')->where('user_id', auth('api')->user()->id);

        $query->whereHas('listing', function ($q) use ($request) {

            if ($request->state) {
                $q->where('state', $request->state);
            }

            if ($request->county) {
                $q->where('country', $request->county);
            }

            if ($request->property_type) {
                $q->where('property_type', $request->property_type);
            }

            if ($request->min_bid && $request->max_bid) {
                $q->whereBetween('starting_bid', [$request->min_bid, $request->max_bid]);
            }

            if ($request->auction_date) {
                $q->whereDate('auction_date', $request->auction_date);
            }
        });

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

        return jsonResponse(true, 'data retrive successfully done', 200, $data, $paginate);
    }


    public function saveListing($id)
    {

        auth('api')->user()->savedListings()->create([
            'listing_id' => $id
        ]);
        return $this->success([], 'Listing saved successfully', 200);
    }

    public function view($id)
    {
        // Find the listing by ID
        $listing = Listing::find($id);

        // Check if the listing exists
        if (!$listing) {
            return $this->error([], 'Listing not found');
        }
        return $this->success($listing, 'Listing saved successfully', 200);
    }

    // Method to delete a specific listing
    public function delete($id)
    {
        // Find the listing by ID
        $listing = Listing::find($id);

        // Check if the listing exists
        if (!$listing) {
            return $this->error([], 'Listing not found');
        }

        // Delete the listing
        $listing->delete();

        return $this->success($listing, 'Listing deleted successfully', 200);
    }
}
