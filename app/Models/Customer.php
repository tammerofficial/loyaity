<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Jobs\GenerateWalletPassJob;

class Customer extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'date_of_birth',
        'tier',
        'total_points',
        'available_points',
        'membership_number',
        'joined_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joined_at' => 'datetime',
        'total_points' => 'integer',
        'available_points' => 'integer',
    ];

    public function loyaltyCards()
    {
        return $this->hasMany(LoyaltyCard::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function appleWalletPasses()
    {
        return $this->hasMany(AppleWalletPass::class);
    }

    public function getTierAttribute($value)
    {
        return ucfirst($value);
    }

    public function earnPoints($amount, $description = null)
    {
        $this->increment('total_points', $amount);
        $this->increment('available_points', $amount);

        $this->transactions()->create([
            'type' => 'earned',
            'points' => $amount,
            'description' => $description ?: 'Points earned',
            'expires_at' => now()->addMonths(config('loyalty.points_expiry_months', 6)),
        ]);

        $this->updateTier();
        
        return $this;
    }

    public function redeemPoints($amount, $description = null)
    {
        if ($this->available_points < $amount) {
            throw new \Exception('Insufficient points');
        }

        $this->decrement('available_points', $amount);

        $this->transactions()->create([
            'type' => 'redeemed',
            'points' => -$amount,
            'description' => $description ?: 'Points redeemed',
        ]);

        return $this;
    }

    public function updateTier()
    {
        if ($this->total_points >= 10000) {
            $this->tier = 'vip';
        } elseif ($this->total_points >= 5000) {
            $this->tier = 'gold';
        } elseif ($this->total_points >= 1000) {
            $this->tier = 'silver';
        } else {
            $this->tier = 'bronze';
        }

        $this->save();
    }
    
    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Auto-generate wallet pass after creating customer
        static::created(function ($customer) {
            static::generateWalletPass($customer);
            
            // Send notification to customer about new account
            try {
                $customer->notify(new \App\Notifications\WalletPassCreatedNotification($customer));
            } catch (\Exception $e) {
                \Log::warning('Failed to send welcome notification to new customer', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
            }
        });
        
        // Auto-regenerate wallet pass after updating customer  
        static::updated(function ($customer) {
            // Only regenerate if membership_number, name, or tier changed
            if ($customer->isDirty(['membership_number', 'name', 'tier', 'total_points', 'available_points'])) {
                static::generateWalletPass($customer);
            }
        });
    }
    
    /**
     * Generate Apple Wallet pass file for customer (dispatch job)
     */
    public static function generateWalletPass($customer)
    {
        // Dispatch job to background queue for better performance
        GenerateWalletPassJob::dispatch($customer->id)->delay(now()->addSeconds(2));
    }
}
