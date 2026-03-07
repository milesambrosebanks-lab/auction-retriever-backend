<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\CMS;
use App\Models\Subscriber;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class SubscriberController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'subscriber');
    }

    public function index(Request $request)
    {
        $sectioninfo = CMS::where('page', 'all')->where('section', 'all-subscriber')->where('slug', 'subscriber')->orderBy('id', 'desc')->first();

        if ($request->ajax()) {
            $data = Subscriber::all();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-primary fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-eye"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make();
        }
        return view("backend.layouts.subscriber.index",[
            'sectioninfo' => $sectioninfo,
            'page' => 'all',
            'section' => 'subscriber',
        ]);
    }

     public function show($id)
    {
        $subcribe = Subscriber::with('emailLogs')->find($id);

        if (!$subcribe) {
            return redirect()->route('admin.subscriber.index')->with('error', 'Subscriber not found');
        }

        return view("backend.layouts.subscriber.show", compact('subcribe'));
    }

     public function destroy($id)
    {
        try {
            $subscriber = Subscriber::findOrFail($id);
            $subscriber->delete();
             return new JsonResponse(['t-success' => true, 'message' => 'Subscriber deleted successfully.']);
              // return redirect()->route('admin.subscriber.index')->with('t-success', 'Subscriber deleted successfully.');

        } catch (Exception $e) {
            Log::error('Failed to delete subscriber: ' . $e->getMessage());
            return new JsonResponse(['t-error' => false, 'message' => 'Failed to delete subscriber. Please try again later.'], 500);
        }
    }
}
