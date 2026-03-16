<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrackingSetting;
use Illuminate\Http\Request;

class TrackingSettingController extends Controller
{
    public function show(): \Illuminate\Http\JsonResponse
    {
        return response()->json(TrackingSetting::instance());
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'meta_pixel_id'      => 'nullable|string|max:100',
            'ga_measurement_id'  => 'nullable|string|max:100',
            'clarity_id'         => 'nullable|string|max:100',
            'meta_enabled'       => 'boolean',
            'ga_enabled'         => 'boolean',
            'clarity_enabled'    => 'boolean',
        ]);

        $setting = TrackingSetting::instance();
        $setting->update($validated);

        return response()->json([
            'message' => 'Tracking settings updated.',
            'data'    => $setting,
        ]);
    }
}
