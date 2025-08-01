<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppleWalletPass extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'pass_type_id',
        'serial_number',
        'authentication_token',
        'pass_data',
        'pkpass_url',
        'last_updated',
        'download_count',
        'is_active',
    ];

    protected $casts = [
        'pass_data' => 'json',
        'last_updated' => 'datetime',
        'download_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function generateSerialNumber()
    {
        do {
            $serialNumber = 'PWS' . time() . mt_rand(10000, 99999);
        } while (self::where('serial_number', $serialNumber)->exists());

        $this->serial_number = $serialNumber;
        return $this;
    }

    public function generateAuthenticationToken()
    {
        $this->authentication_token = bin2hex(random_bytes(32));
        return $this;
    }

    public function updatePassData()
    {
        $customer = $this->customer;
        
        $this->pass_data = [
            'formatVersion' => 1,
            'passTypeIdentifier' => config('apple-wallet.pass_type_id'),
            'teamIdentifier' => config('apple-wallet.team_id'),
            'serialNumber' => $this->serial_number,
            'authenticationToken' => $this->authentication_token,
            'webServiceURL' => 'http://127.0.0.1:8000/api/v1/apple-wallet',
            'storeCard' => [
                'headerFields' => [
                    [
                        'key' => 'tier',
                        'label' => 'Tier',
                        'value' => $customer->tier
                    ]
                ],
                'primaryFields' => [
                    [
                        'key' => 'name',
                        'label' => 'Member',
                        'value' => $customer->name
                    ]
                ],
                'secondaryFields' => [
                    [
                        'key' => 'points',
                        'label' => 'Available Points',
                        'value' => number_format($customer->available_points)
                    ],
                    [
                        'key' => 'member_since',
                        'label' => 'Member Since',
                        'value' => $customer->joined_at ? $customer->joined_at->format('M Y') : 'N/A'
                    ]
                ],
                'auxiliaryFields' => [
                    [
                        'key' => 'membership_number',
                        'label' => 'Membership #',
                        'value' => $customer->membership_number
                    ]
                ],
                'backFields' => [
                    [
                        'key' => 'total_points',
                        'label' => 'Total Points Earned',
                        'value' => number_format($customer->total_points)
                    ],
                    [
                        'key' => 'terms',
                        'label' => 'Terms & Conditions',
                        'value' => 'Visit our website for complete terms and conditions.'
                    ]
                ]
            ],
            'organizationName' => config('app.name'),
            'description' => 'Loyalty Card',
            'logoText' => config('app.name'),
            'foregroundColor' => 'rgb(255, 255, 255)',
            'backgroundColor' => 'rgb(0, 122, 255)',
            'labelColor' => 'rgb(255, 255, 255)',
        ];

        $this->last_updated = now();
        $this->save();

        return $this;
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
        return $this;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
