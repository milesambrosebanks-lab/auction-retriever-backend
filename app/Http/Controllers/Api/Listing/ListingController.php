<?php

namespace App\Http\Controllers\Api\Listing;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{

    public function index(Request $request)
    {

        $query = Listing::query();

        if ($request->state) {
            $query->where('state', $request->state);
        }

        if ($request->county) {
            $query->where('county', $request->county);
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

        return response()->json(
            $query->paginate(20)
        );
    }

    public function saveListing($id)
    {

        auth('api')->user()->savedListings()->create([
            'listing_id' => $id
        ]);

        return response()->json([
            'message' => 'Listing saved successfully'
        ]);
    }
}
