<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletDesignSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'customer_id',
        'organization_name',
        'background_color',
        'background_color_secondary',
        'text_color',
        'label_color',
        'background_image_url',
        'background_opacity',
        'use_background_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'use_background_image' => 'boolean',
        'background_opacity' => 'integer',
    ];

    /**
     * Get the customer this design belongs to.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get global design settings.
     */
    public static function getGlobalSettings()
    {
        return static::where('type', 'global')
            ->where('is_active', true)
            ->first() ?: static::getDefaultSettings();
    }

    /**
     * Get design settings for a specific customer.
     */
    public static function getCustomerSettings($customerId)
    {
        $customerSettings = static::where('type', 'customer')
            ->where('customer_id', $customerId)
            ->where('is_active', true)
            ->first();

        return $customerSettings ?: static::getGlobalSettings();
    }

    /**
     * Get default settings if none exist.
     */
    public static function getDefaultSettings()
    {
        return (object) [
            'organization_name' => 'Tammer Loyalty',
            'background_color' => '#1e3a8a',
            'background_color_secondary' => '#3b82f6',
            'text_color' => '#ffffff',
            'label_color' => '#ffffff',
            'background_image_url' => null,
            'background_opacity' => 50,
            'use_background_image' => false,
        ];
    }

    /**
     * Save or update global settings.
     */
    public static function saveGlobalSettings($data)
    {
        return static::updateOrCreate(
            ['type' => 'global', 'customer_id' => null],
            array_merge($data, ['is_active' => true])
        );
    }

    /**
     * Save or update customer-specific settings.
     */
    public static function saveCustomerSettings($customerId, $data)
    {
        return static::updateOrCreate(
            ['type' => 'customer', 'customer_id' => $customerId],
            array_merge($data, ['is_active' => true])
        );
    }
}