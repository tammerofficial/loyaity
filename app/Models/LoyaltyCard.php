<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoyaltyCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'card_number',
        'qr_code',
        'barcode',
        'status',
        'issued_at',
        'last_used_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function generateCardNumber()
    {
        do {
            $cardNumber = 'LC' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (self::where('card_number', $cardNumber)->exists());

        $this->card_number = $cardNumber;
        return $this;
    }

    public function generateQRCode()
    {
        $this->qr_code = 'QR' . $this->id . time();
        $this->barcode = 'BC' . $this->id . time();
        return $this;
    }

    public function activate()
    {
        $this->status = 'active';
        $this->issued_at = now();
        $this->save();
        return $this;
    }

    public function deactivate()
    {
        $this->status = 'inactive';
        $this->save();
        return $this;
    }
}
