<?php

namespace App\Http\Resources;

use App\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'user_id' => $this->user_id,

            'category_id' => $this->when(
                !is_null($this->category_id),
                $this->category_id
            ),

            'name' => $this->when(
                !is_null($this->name),
                $this->name
            ),

            'title' => $this->title,

            'slug' => $this->slug,

            'sku' => $this->when(!is_null($this->sku), $this->sku),

            'short_description' => $this->when(!is_null($this->short_description), $this->short_description),

            'description' => $this->when(!is_null($this->description), $this->description),

            'thumbnail' => $this->when(!is_null($this->thumbnail),  asset($this->thumbnail)),

            'type' => $this->type,

            'monthly_price'             => round((float) $this->monthly_price, 2),
            'one_time_price'             => round((float) $this->one_time_price, 2),


            'sale_price' => $this->when(
                !is_null($this->sale_price),
                (float) $this->sale_price
            ),

            'discount' => $this->when(
                !is_null($this->discount),
                $this->discount
            ),

            'supply_days' => $this->when(
                !is_null($this->supply_days),
                $this->supply_days
            ),
            'included_items'  => $this->bundleItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item' => $item->title,
                ];
            }),

            'others' => $this->when(
                !is_null($this->others),
                $this->others
            ),

            'stock' => (int) $this->stock,

            'status' => $this->status,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
