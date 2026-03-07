<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        $query = Blog::query()->where('status', 'active');

        if ($request->filled('type') && in_array($request->type, ['article', 'live'])) {
            $query->where('type', $request->type);
        }

        $paginated = $query->with(['user:id,name'])->orderBy('created_at', 'desc')->paginate(10);

        // Transform each blog item to match the desired structure
        $paginated->getCollection()->transform(function ($item) {
            // Extract image URLs from content
            preg_match_all('/<img[^>]+src="([^">]+)"/i', $item->content, $matches);
            $images = $matches[1] ?? [];

            $plainContent = strip_tags($item->content);

            return [
                'id'                => $item->id,
                'user_id'           => $item->user_id,
                'title'             => $item->title,
                'slug'              => $item->slug,
                'thumbnail'         => asset($item->thumbnail),
                'content'           => $item->content,
                'status'            => $item->status,
                'created_at'        => $item->created_at,
                'updated_at'        => $item->updated_at,
                'user'              => $item->user,
                'images'            => $images,
                'short_description' => Str::length($plainContent) > 200 ? Str::substr($plainContent, 0, 200) . '...' : $plainContent,
                'plain_content'     => $plainContent,
            ];
        });

        return $this->success($paginated, 'Blog list retrieved successfully', 200);
    }

    public function show($id)
    {
        $blog = Blog::with(['user:id,name'])->where('id', $id)->firstOrFail();

        // Extract all image URLs from content
        preg_match_all('/<img[^>]+src="([^">]+)"/i', $blog->content, $matches);
        $images = $matches[1] ?? [];

        // Strip HTML for plain content
        $plainContent = strip_tags($blog->content);

        return $this->success([
            'blog' => $blog,
            'images' => $images,
            'plain_content' => $plainContent,
        ], 'Blog details retrieved successfully', 200);
    }

    public function latestNews(Request $request)
    {
        $blog = Blog::where('status', 'active')->orderBy('created_at', 'desc')->take(5)->get();

        if (!$blog) {
            return $this->error([], 'Blog not found', 404);
        }

        return $this->success($blog, 'Blog list retrieved successfully', 200);
    }
}

