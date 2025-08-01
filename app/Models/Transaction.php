<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'loyalty_card_id',
        'type',
        'points',
        'amount',
        'currency',
        'description',
        'reference_number',
        'expires_at',
        'processed_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function loyaltyCard()
    {
        return $this->belongsTo(LoyaltyCard::class);
    }

    public function scopeEarned($query)
    {
        return $query->where('type', 'earned');
    }

    public function scopeRedeemed($query)
    {
        return $query->where('type', 'redeemed');
    }

    public function scopeExpired($query)
    {
        return $query->where('type', 'expired');
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())
                    ->orWhereNull('expires_at');
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function generateReferenceNumber()
    {
        $this->reference_number = 'TXN' . time() . mt_rand(1000, 9999);
        return $this;
    }
}
