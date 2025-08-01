<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyCardResource extends JsonResource
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
            'card_number' => $this->card_number,
            'qr_code' => $this->qr_code,
            'barcode' => $this->barcode,
            'status' => $this->status,
            'issued_at' => $this->issued_at?->format('Y-m-d H:i:s'),
            'last_used_at' => $this->last_used_at?->format('Y-m-d H:i:s'),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
