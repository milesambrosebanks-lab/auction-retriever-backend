<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Product::query()->with('category')->where('type', 'bundle');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filter === 'new_arrivals') {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);

        // Transform collection to include both description formats
        $products->getCollection()->map(function ($product) {
            return [
                'id'                => $product->id,
                'name'              => $product->name,
                'title'              => $product->title,
                'slug'              => $product->slug,
                'thumbnail'         => asset($product->thumbnail),
                'monthly_price'             => $product->monthly_price,
                'one_time_price'             => $product->one_time_price,
                'stock'             => $product->stock,
                'supply_days'            => $product->supply_days,
                // 'category'          => $product->category,
                'description'  => $product->description,
                'included_items'  => $product->bundleItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item' => $item->title,
                    ];
                }),
                // 'description_plain' => strip_tags($product->description),
                'created_at'        => $product->created_at,
            ];
        });

        return $this->success($products, 'Products retrieved successfully', 200);
    }

    public function show($product_id)
    {
        $product = Product::with('category')->find($product_id);

        if (!$product) {
            return $this->error([], 'Product not found', 404);
        }

        return $this->success([
            'id'                => $product->id,
            'title'              => $product->title,
            'slug'              => $product->slug,
            'thumbnail'         => asset($product->thumbnail),
            'price'             => $product->price,
            'stock'             => $product->stock,
            'supply_days'            => $product->supply_days,
            // 'category'          => $product->category,
            'description'  => $product->description,
            'included_items'  => $product->bundleItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item' => $item->title,
                ];
            }),
            // 'description_plain' => strip_tags($product->description),
            'created_at'        => $product->created_at,
        ], 'Product retrieved successfully', 200);
    }

    public function oneProduct()
    {
        $product = Product::with('category')->where('type', 'bundle')->where('status', 'active')->orderBy('id', 'desc')->first();

        if (!$product) {
            return $this->error([], 'Product not found', 404);
        }

        return $this->success([
            'id'                => $product->id,
            'name'              => $product->name,
            'title'              => $product->title,
            'slug'              => $product->slug,
            'thumbnail'         => asset($product->thumbnail),
            'monthly_price'             => round((float) $product->monthly_price, 2),
            'one_time_price'             => round((float) $product->one_time_price,2),
            'stock'             => $product->stock,
            'supply_days'            => $product->supply_days,
          
            'description'  => $product->description,
            'included_items'  => $product->bundleItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item' => $item->title,
                ];
            }),
            // 'description_plain' => strip_tags($product->description),
            'created_at'        => $product->created_at,
        ], 'Product retrieved successfully', 200);
    }


    public function relatedProduct($product_id)
    {
        $product = Product::find($product_id);

        if (!$product) {
            return $this->error([], 'Product not found', 404);
        }

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        // Transform each item to include description_html and description_plain
        $transformed = $relatedProducts->map(function ($item) {
            return [
                'id'                => $item->id,
                'name'              => $item->name,
                'slug'              => $item->slug,
                'thumbnail'         => asset($item->thumbnail),
                'price'             => $item->price,
                'stock'             => $item->stock,
                'status'            => $item->status,
                'description_html'  => $item->description,
                'description_plain' => strip_tags($item->description),
                'created_at'        => $item->created_at,
            ];
        });

        return $this->success($transformed, 'Related products retrieved successfully', 200);
    }
}
