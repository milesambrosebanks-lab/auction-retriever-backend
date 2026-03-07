<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoGallery extends Model
{

    protected $guarded = ['id'];

    public function images()
    {
        return $this->hasMany(PhotoGalleryImage::class, 'photo_gallery_id', 'id');
    }


    // thumbnail image


}
