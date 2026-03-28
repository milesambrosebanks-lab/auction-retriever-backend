<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LocationService;

class CountryController extends Controller
{
    /**
     * Get all countries
     */
    public function countries()
    {
        $countries = LocationService::getCountries();

        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }

    /**
     * Get states by country
     */
    public function states(Request $request)
    {
        $request->validate([
            'country' => 'nullable|string'
        ]);
             $states = LocationService::getStatesByCountry('us');

        if ($request->filled('country')) {
             $states = LocationService::getStatesByCountry($request->country);
        }


        return response()->json([
            'success' => true,
            'country' => $request->country??'United States',
            'data' => $states
        ]);
    }
}
