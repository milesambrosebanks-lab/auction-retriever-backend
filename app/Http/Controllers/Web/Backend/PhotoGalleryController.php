<?php
namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\PhotoGallery;
use App\Models\PhotoGalleryImage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PhotoGalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PhotoGallery::with('images')->get();
            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('title', function ($data) {
                    return Str::limit($data->title, 20);
                })

                ->addColumn('description', function ($data) {
                    return Str::limit(strip_tags($data->description), 40);
                })
                ->addColumn('thumbnail', function ($data) {
                    if ($data->thumbnail) {
                        $url = asset($data->thumbnail);
                        return '<img src="' . $url . '" alt="thumbnail" width="50px" height="50px" style="margin-left:20px;">';
                    } else {
                        return '<img src="' . asset('default/logo.svg') . '" alt="image" width="50px" height="50px" style="margin-left:20px;">';
                    }
                })

                ->addColumn('images', function ($data) {
                    if ($data->images && $data->images->count() > 0) {
                        $html = '';
                        foreach ($data->images as $image) {
                            $html .= '<img src="' . $image->image . '" alt="image" width="50px" height="50px" style="margin-left:5px; margin-bottom:5px; border-radius:4px;">';
                        }
                        return $html;
                    } else {
                        return '<img src="' . asset('default/logo.svg') . '" alt="image" width="50px" height="50px" style="margin-left:5px;">';
                    }
                })

                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-edit"></i>
                                </a>



                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['title', 'thumbnail', 'images', 'status', 'action'])
                ->make();
        }
        return view('backend.layouts.photo_gallery.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.layouts.photo_gallery.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|max:250',
            'description' => 'required',
            'thumbnail'   => 'required|image',
            'images.*'    => 'nullable|image', // multiple images

        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $gallery = new PhotoGallery();

            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = Helper::fileUpload(
                    $request->file('thumbnail'),
                    'photo_gallery',
                    time() . '_' . getFileName($request->file('thumbnail'))
                );

                // Save relative path, not full URL
                $data['thumbnail'] = asset($thumbnailPath);
            }

            $gallery->title       = $data['title'];
            $gallery->thumbnail   = $data['thumbnail'];
            $gallery->description = $data['description'] ?? null;
            $gallery->save();

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = Helper::fileUpload(
                        $image,
                        'photo_gallery',
                        time() . '_' . getFileName($image)
                    );

                    $gallery->images()->create([
                        'image' => asset($path),
                    ]);
                }
            }

            session()->put('t-success', 'Photos Gallery created successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.image-gallery.index')->with('t-success', 'Images created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $blog = Blog::with(['user'])->where('id', $id)->first();
        return view('backend.layouts.photo_gallery.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $gallery = PhotoGallery::findOrFail($id);
        return view('backend.layouts.photo_gallery.edit', compact('gallery'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'     => 'required|max:250',
            'thumbnail' => 'nullable|image',
            'images.*'  => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $gallery              = PhotoGallery::findOrFail($id);
            $gallery->title       = $data['title'];
            $gallery->description = $request->description ?? $gallery->description;

            // Update thumbnail if new one uploaded
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = Helper::fileUpload(
                    $request->file('thumbnail'),
                    'photo_gallery/thumbnails',
                    time() . '_' . getFileName($request->file('thumbnail'))
                );

                $gallery->thumbnail = asset($thumbnailPath);
            }

            $gallery->save();

            // Save multiple new images
            if ($request->hasFile('images')) {
                // $gallery->images()->delete();
                foreach ($request->file('images') as $image) {
                    $imagePath = Helper::fileUpload(
                        $image,
                        'photo_gallery/images',
                        time() . '_' . getFileName($image)
                    );

                    $gallery->images()->create([
                        'image' => asset($imagePath),
                    ]);
                }
            }

            return redirect()->route('admin.image-gallery.index')
                ->with('t-success', 'Photo Gallery updated successfully');

        } catch (Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = PhotoGallery::with('images')->findOrFail($id);

            if ($data->thumbnail && file_exists(public_path($data->thumbnail))) {
                Helper::fileDelete(public_path($data->thumbnail));
            }


            $data->delete();
            return response()->json([
                'status'  => 't-success',
                'message' => 'Your action was successful!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 't-error',
                'message' => 'Your action was successful!',
            ]);
        }
    }

    public function status(int $id): JsonResponse
    {
        $data = Blog::findOrFail($id);
        if (! $data) {
            return response()->json([
                'status'  => 't-error',
                'message' => 'Item not found.',
            ]);
        }
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status'  => 't-success',
            'message' => 'Your action was successful!',
        ]);
    }

    public function deleteImage($id)
    {
        try {
            $image = PhotoGalleryImage::findOrFail($id);

            // remove file from storage if exists
            if ($image->image && file_exists(public_path($image->image))) {
                @unlink(public_path($image->image));
            }

            $image->delete();

            return response()->json(['status' => 'success', 'message' => 'Image deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

}
