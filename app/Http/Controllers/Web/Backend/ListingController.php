<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;


class ListingController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'Auction Listing');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Listing::orderBy('id', 'desc')->get();
        //  dd($data);

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return '<span title="' . e($data->title) . '">' . e($data->title)  . '</span>';
                })
                ->addColumn('source_url', function ($data) {
                    $title = $data->source_url ? Str::limit($data->source_url, 20) : '-';
                    return "<a href='" . $data->source_url . "'>" . $title . "</a>";
                })
                ->addColumn('property_type', function ($data) {
                    return '<span title="' . e($data->property_type) . '">' . e($data->property_type)  . '</span>';
                })
                ->addColumn('starting_bid', function ($data) {
                    return '<span class="badge bg-warning">' . $data->starting_bid . '</span>';
                })
                ->addColumn('country', function ($data) {
                    return '<span class="text-gray">' . ($data->country ? $data->country : 'N/A') . '</span>';
                })
                ->addColumn('auction_date', function ($data) {
                    return '<span class="badge bg-primary">' . ($data->auction_date ? $data->auction_date->format('d M Y') : 'N/A') . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-success fs-14 text-white delete-icn" title="View">
                                    <i class="fe fe-eye"></i>
                                </a>

                            </div>';
                })
                ->rawColumns(['title', 'source_url', 'property_type', 'starting_bid', 'country', 'auction_date', 'action'])
                ->make();
        }
        return view("backend.layouts.listing.index");
    }

    public function show(int $id)
    {
        $listing = Listing::find($id);
        return view('backend.layouts.listing.show', compact('listing'));
    }
}
