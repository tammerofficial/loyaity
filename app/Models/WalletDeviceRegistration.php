<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletDeviceRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_library_identifier',
        'pass_type_identifier',
        'serial_number',
        'apple_wallet_pass_id',
        'push_token',
        'registered_at',
        'last_updated',
        'is_active',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'last_updated' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the Apple Wallet pass this registration belongs to.
     */
    public function appleWalletPass()
    {
        return $this->belongsTo(AppleWalletPass::class);
    }

    /**
     * Get the customer through the Apple Wallet pass.
     */
    public function customer()
    {
        return $this->hasOneThrough(Customer::class, AppleWalletPass::class, 'id', 'id', 'apple_wallet_pass_id', 'customer_id');
    }

    /**
     * Scope for active registrations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific pass type.
     */
    public function scopeForPassType($query, $passTypeId)
    {
        return $query->where('pass_type_identifier', $passTypeId);
    }

    /**
     * Mark registration as updated.
     */
    public function markAsUpdated()
    {
        $this->update(['last_updated' => now()]);
        return $this;
    }

    /**
     * Get all registrations that need updates for a specific pass.
     */
    public static function getRegistrationsForPass($serialNumber, $passTypeId)
    {
        return static::where('serial_number', $serialNumber)
            ->where('pass_type_identifier', $passTypeId)
            ->active()
            ->get();
    }

    /**
     * Get registrations for a device.
     */
    public static function getDeviceRegistrations($deviceLibraryIdentifier, $passTypeId = null)
    {
        $query = static::where('device_library_identifier', $deviceLibraryIdentifier)
            ->active();

        if ($passTypeId) {
            $query->where('pass_type_identifier', $passTypeId);
        }

        return $query->get();
    }
}