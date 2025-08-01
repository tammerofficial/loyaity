<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'tier' => $this->tier,
            'total_points' => $this->total_points,
            'available_points' => $this->available_points,
            'membership_number' => $this->membership_number,
            'joined_at' => $this->joined_at?->format('Y-m-d H:i:s'),
            'loyalty_cards' => LoyaltyCardResource::collection($this->whenLoaded('loyaltyCards')),
            'recent_transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
