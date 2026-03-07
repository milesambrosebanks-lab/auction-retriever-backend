<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Donation;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;


class DonationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Donation::where('payment_status', 'paid')->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($data) {
                    $name = $data->first_name ?? '' . ' ' . $data->last_name ?? '';
                    return Str::limit($name  , 20);
                })
                ->rawColumns(['name'])
                ->make();
        }

        $total_price = Donation::where('payment_status', 'paid')->sum('amount');

        return view("backend.layouts.donation.index", compact('total_price'));
    }

    public function show($id)
    {
        $donation = Donation::where('id', $id)->first();
        return view('backend.layouts.donation.show', compact('donation'));
    }

}
