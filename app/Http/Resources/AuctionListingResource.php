<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuctionListingResource extends JsonResource
{
    public function toArray($request): array
    {
        $userId = auth('sanctum')->id();

        return [
            'id'           => $this->id,
            'auction_id'   => $this->auction_id,
            'title'        => $this->title,
            'type'         => $this->type,
            'state'        => $this->state,
            'county'       => $this->county,
            'current_bid'  => $this->current_bid,
            'bid_amount'   => $this->bid_amount,
            'bid_count'    => $this->bid_count,
            'time_left'    => $this->time_left,
            'auction_date' => $this->auction_date?->format('Y-m-d H:i:s'),
            'image_url'    => $this->image_url,
            'source_url'   => $this->source_url,
            'scraped_at'   => $this->scraped_at?->diffForHumans(),
            'is_saved'     => $userId ? $this->isSavedBy($userId) : false,
        ];
    }
}
