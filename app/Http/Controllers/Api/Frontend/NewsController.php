<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        $query = News::query()->where('status', 'active');

        if ($request->filled('type') && in_array($request->type, ['article', 'live'])) {
            $query->where('type', $request->type);
        }

        $paginated = $query->with(['user:id,name'])->orderBy('created_at', 'desc')->paginate(10);

        // Transform each news item to match the desired structure
        $paginated->getCollection()->transform(function ($item) {
            // Extract image URLs from content
            preg_match_all('/<img[^>]+src="([^">]+)"/i', $item->content, $matches);
            $images = $matches[1] ?? [];

            $plainContent = strip_tags($item->content);

            return [
                'id'          => $item->id,
                'user_id'     => $item->user_id,
                'title'       => $item->title,
                'slug'        => $item->slug,
                'thumbnail'   => asset($item->thumbnail),
                'content'     => $item->content,
                'status'      => $item->status,
                'created_at'  => $item->created_at,
                'updated_at'  => $item->updated_at,
                'user'        => $item->user,
                'images'      => $images,
                'type'        => $item->type,
                'short_description' => Str::length($plainContent) > 200 ? Str::substr($plainContent, 0, 200) . '...' : $plainContent,
                'plain_content' => $plainContent,
            ];
        });

        return $this->success($paginated, 'News list retrieved successfully', 200);
    }

    public function show($id)
    {
        $news = News::with(['user:id,name'])->where('id', $id)->firstOrFail();

        // Extract all image URLs from content
        preg_match_all('/<img[^>]+src="([^">]+)"/i', $news->content, $matches);
        $images = $matches[1] ?? [];

        // Strip HTML for plain content
        $plainContent = strip_tags($news->content);

        return $this->success([
            'news' => $news,
            'images' => $images,
            'plain_content' => $plainContent,
        ], 'News details retrieved successfully', 200);
    }

    public function latestNews(Request $request)
    {
        $news = News::where('status', 'active')->orderBy('created_at', 'desc')->take(5)->get();

        if (!$news) {
            return $this->error([], 'News not found', 404);
        }

        return $this->success($news, 'News list retrieved successfully', 200);
    }
}


