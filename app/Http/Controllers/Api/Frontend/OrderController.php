<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Api\Gateway\Stripe\StripeCallBackController;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Product::query()->with('category');

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
        $products->getCollection()->transform(function ($item) {
            return [
                'id'                => $item->id,
                'name'              => $item->name,
                'slug'              => $item->slug,
                'thumbnail'         => asset($item->thumbnail),
                'price'             => $item->price,
                'stock'             => $item->stock,
                'status'            => $item->status,
                'category'          => $item->category,
                'description_html'  => $item->description,
                'description_plain' => strip_tags($item->description),
                'created_at'        => $item->created_at,
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

    public function createPayment(Request $request, $product_id)
    {
        $product = Product::with('category')->where('status', 'active')->first();

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


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'payment_type' => 'required|in:monthly,one_time',
            'quantity' => 'required|integer|min:1',
            'order_id'=>'required|exists:orders,id'

        ]);


        // if ($validator->fails()) {
        //     return $this->error([], 'Validation failed', 422, $validator->errors());
        // }
        try {
            $product = Product::findOrFail($request->order_id);

            if ($request->payment_type === 'monthly') {

                $payment_type = $request->payment_type;
            } else {

                $payment_type = "payment";
            }

            $order = new Order();
            $order->uid = 'ORD-' . now()->format('Ymd') . '-' . rand(1000, 9999);
            $order->product_id = $product->id;
            // $order->customer_id = $customer->id;
            $order->quantity = $request->quantity;
            $order->total_price = $product->price * $request->quantity;
            $order->payment_type = $request->payment_type;
            $order->save();
            $checkout = new StripeCallBackController();
            return $checkout->checkout($request, $order->id, $payment_type);
        } catch (\Exception $e) {
            Log::info($e);
            return $this->error([], 'Failed to create order: ' . $e->getMessage(), 500);
        }

        // return $this->success($order, 'Order created successfully', 200);
    }

}
