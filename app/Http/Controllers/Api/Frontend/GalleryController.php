<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;


class GalleryController extends Controller
{

    public function index()
    {
        $imagesDirectory = public_path("uploads/gallery/");
        $files = array_values(array_diff(scandir($imagesDirectory), ['.', '..']));
        $imageFiles = array_filter($files, function ($file) use ($imagesDirectory) {
            return is_file($imagesDirectory . $file);
        });

        $data = [];

        $i = 0;

        foreach ($imageFiles as $file) {
            $data[$i++] = [
                'src' => asset("uploads/gallery/$file")
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

}
