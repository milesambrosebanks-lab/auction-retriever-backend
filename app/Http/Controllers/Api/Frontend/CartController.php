<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Models\Product;
use App\Models\CartItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use ApiResponse;
    public function addToCart(Request $request, $productId)
    {

        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $product = Product::find($productId);

        if (!$product) {
            return $this->error([], 'Product not found', 404);
        }

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->input('quantity');
            $cartItem->save();
        } else {
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'quantity' => $request->input('quantity'),
                'price' => $product->price, // You can set the price here if needed
            ]);
        }
        return $this->success([], 'Product added to cart successfully', 200);
    }

    public function list()
    {
        $cartItems = auth('api')->user()->cartItems()->with('product')->get();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $shipping = $subtotal > 0 ? 20 : 0; // you can change this logic later
        $total = $subtotal + $shipping;

        return $this->success([
            'items' => $cartItems->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'product_id' => $item->product_id,
                    'name'       => $item->product->name,
                    'thumbnail'  => asset($item->product->thumbnail),
                    'quantity'   => $item->quantity,
                    'price'      => $item->price,
                    'total'      => $item->quantity * $item->price,
                ];
            }),
            'summary' => [
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total'    => $total,
            ]
        ], 'Cart retrieved successfully', 200);
    }

    public function updateQuantity(Request $request)
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|integer|exists:cart_items,id',
            'action'     => 'required|in:increase,decrease',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('id', $request->input('cart_item_id'))
            ->first();

        if (!$cartItem) {
            return $this->error([], 'Cart item not found', 404);
        }

        // Adjust the quantity based on the action
        if ($request->action == 'increase') {
            $cartItem->increment('quantity');  // Increase by 1
        } elseif ($request->action == 'decrease') {
            if ($cartItem->quantity > 1) {
                $cartItem->decrement('quantity');  // Decrease by 1, but not below 1
            }
        }

        return $this->success([
            'product_id' => $cartItem->product_id,
            'quantity' => $cartItem->quantity,
            'price' => $cartItem->price,
        ], 'Cart quantity updated successfully', 200);
    }

    public function removeFromCart($id)
    {
        $user = auth('api')->user();

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return $this->error([], 'Cart item not found', 404);
        }

        $cartItem->delete();

        return $this->success([], 'Product removed from cart successfully', 200);
    }

    public function removeProductFromCart($id)
    {
        $user = auth('api')->user();

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('product_id', $id)
            ->first();

        if (!$cartItem) {
            return $this->error([], 'Cart item not found', 404);
        }

        $cartItem->delete();

        return $this->success([], 'Product removed from cart successfully', 200);
    }

}
