<?php
namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PhotoGallery;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class GalleryPhotoController extends Controller
{

    use ApiResponse;

    public function list(Request $request)
    {
        $gallery = PhotoGallery::orderBy('created_at', 'desc')->get();

        return $this->success($gallery, 'Image gallery retrieved successfully', 200);
    }

    public function details(Request $request, $id)
    {
        $gallery = PhotoGallery::with('images')->find($id);

        return $this->success($gallery, 'Image gallery details retrieved successfully', 200);
    }



}
