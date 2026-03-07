<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\CMS;
use App\Models\PdfRequest;
use App\Models\Subscriber;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class PdfRequestController extends Controller
{
    public $components;
    public function __construct()
    {
        View::share('crud', 'pdf-request');
        $this->components = ['title', 'sub_title'];
    }

    public function index(Request $request)
    {
        $sectioninfo = CMS::where('page', 'all')->where('section', 'all-pdf-request')->where('slug', 'pdf-request')->orderBy('id', 'desc')->first();

        if ($request->ajax()) {
            $data = PdfRequest::all();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_subcribe', function ($data) {
                    return '<span class="badge bg-' . ($data->subscriber ? 'success' : 'danger') . '">' . ($data->subscriber ? 'Yes' : 'No') . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-primary fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-eye"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['is_subcribe', 'action'])
                ->make();
        }
        return view("backend.layouts.pdf_request.index", [
            'sectioninfo' => $sectioninfo,
            'page' => 'all',
            'section' => 'pdf-request',
            'components' => $this->components,
        ]);
    }

    public function show($id)
    {
        $subcribe = PdfRequest::with('emailLogs')->find($id);

        if (!$subcribe) {
            return redirect()->route('admin.pdf_request.index')->with('error', 'Pdf Request not found');
        }

        return view("backend.layouts.pdf_request.show", compact('subcribe'));
    }

    public function destroy($id)
    {
        try {
            $subscriber = PdfRequest::findOrFail($id);
            $subscriber->delete();
            return new JsonResponse(['t-success' => true, 'message' => 'Pdf Request deleted successfully.']);
            // return redirect()->route('admin.pdf_request.index')->with('t-success', 'Pdf Request deleted successfully.');

        } catch (Exception $e) {
            Log::error('Failed to delete pdf request: ' . $e->getMessage());
            return new JsonResponse(['t-error' => false, 'message' => 'Failed to delete pdf request. Please try again later.'], 500);
        }
    }
}
