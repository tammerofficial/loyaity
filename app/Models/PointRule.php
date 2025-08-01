<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PointRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'rule_type',
        'points_per_unit',
        'currency',
        'minimum_amount',
        'maximum_points',
        'multiplier',
        'tier_specific',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'points_per_unit' => 'integer',
        'minimum_amount' => 'decimal:2',
        'maximum_points' => 'integer',
        'multiplier' => 'decimal:2',
        'tier_specific' => 'json',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function calculatePoints($amount, $tier = null)
    {
        if (!$this->isValid()) {
            return 0;
        }

        if ($amount < $this->minimum_amount) {
            return 0;
        }

        $basePoints = 0;

        switch ($this->rule_type) {
            case 'fixed_per_amount':
                $basePoints = floor($amount / $this->points_per_unit);
                break;
            case 'percentage':
                $basePoints = floor($amount * $this->points_per_unit / 100);
                break;
            case 'multiplier':
                $basePoints = floor($amount * $this->multiplier);
                break;
            default:
                $basePoints = $this->points_per_unit;
        }

        // Apply tier-specific multipliers
        if ($tier && $this->tier_specific && is_array($this->tier_specific) && isset($this->tier_specific[$tier])) {
            $basePoints *= $this->tier_specific[$tier];
        }

        // Apply maximum points limit
        if ($this->maximum_points && $basePoints > $this->maximum_points) {
            $basePoints = $this->maximum_points;
        }

        return (int) $basePoints;
    }

    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->isBefore($this->valid_from)) {
            return false;
        }

        if ($this->valid_to && $now->isAfter($this->valid_to)) {
            return false;
        }

        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('valid_from')
                          ->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('valid_to')
                          ->orWhere('valid_to', '>=', $now);
                    });
    }
}
