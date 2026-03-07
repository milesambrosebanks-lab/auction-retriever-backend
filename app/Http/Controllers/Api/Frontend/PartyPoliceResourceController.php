<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\Police;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartyPoliceResourceController extends Controller
{
    public function partyPoliceList(Request $request)
    {

        $policies = Police::where('status', 'active')->get();

        // Transform each policy to add description for Flutter (strip HTML tags)
        $policies->transform(function ($item) {
            // Create a new field for Flutter's description, with HTML stripped
            $item->description_for_flutter = strip_tags($item->content);
            return $item;
        });

        if ($policies->isEmpty()) {
            return Helper::jsonResponse(false, 'No policies found', 404);
        }
        return Helper::jsonResponse(true, 'Policies retrieved successfully', 200, $policies);
        
    }

    public function partyRelatedPolice()
    {
        $relatedPolicies = Police::query()
            ->where('status', 'active')
            ->inRandomOrder()
            ->take(4)
            ->get();

        if ($relatedPolicies->isEmpty()) {
            return Helper::jsonResponse(false, 'No related policies found', 404);
        }

        return Helper::jsonResponse(true, 'Related policies retrieved successfully', 200, $relatedPolicies);
    }

    public function partyPoliceShow($id)
    {
        $policy = Police::where('id', $id)->where('status', 'active')->first();
        if (!$policy) {
            return Helper::jsonResponse(false, 'Policy not found', 404);
        }
        $data = [
            'policy' => $policy
        ];
        return Helper::jsonResponse(true, 'Policy retrieved successfully', 200, $data);
    }

    public function partyResourceList(Request $request)
    {
        $partyResources = DB::table('party_resources')->where('status', 'active')->get();

        // Transform each resource to add description for Flutter (strip HTML tags)
        $partyResources->transform(function ($item) {
            // Create a new field for Flutter's description, with HTML stripped
            $item->description_for_flutter = strip_tags($item->description);
            $item->short_description = Str::length(strip_tags($item->description)) > 200 ? Str::substr(strip_tags($item->description), 0, 200) . '...' : strip_tags($item->description);
            return $item;
        });

        if ($partyResources->isEmpty()) {
            return Helper::jsonResponse(false, 'No party resources found', 404);
        }
        return Helper::jsonResponse(true, 'Party resources retrieved successfully', 200, $partyResources);
    }
    
}
