<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'customer_id' => $this->customer_id,
            'loyalty_card_id' => $this->loyalty_card_id,
            'type' => $this->type,
            'points' => $this->points,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'reference_number' => $this->reference_number,
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'processed_at' => $this->processed_at?->format('Y-m-d H:i:s'),
            'is_expired' => $this->isExpired(),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'loyalty_card' => new LoyaltyCardResource($this->whenLoaded('loyaltyCard')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
