<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Loyalty System Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the loyalty points system
    |
    */

    'points_per_kd' => (int) env('LOYALTY_POINTS_PER_KD', 1),
    'points_expiry_months' => (int) env('LOYALTY_POINTS_EXPIRY_MONTHS', 6),
    'default_currency' => env('LOYALTY_DEFAULT_CURRENCY', 'KD'),

    /*
    |--------------------------------------------------------------------------
    | Tier System
    |--------------------------------------------------------------------------
    |
    | Configuration for customer tier levels
    |
    */

    'tiers' => [
        'bronze' => [
            'name' => 'Bronze',
            'minimum_points' => 0,
            'multiplier' => 1.0,
            'color' => '#CD7F32',
        ],
        'silver' => [
            'name' => 'Silver',
            'minimum_points' => 1000,
            'multiplier' => 1.25,
            'color' => '#C0C0C0',
        ],
        'gold' => [
            'name' => 'Gold',
            'minimum_points' => 5000,
            'multiplier' => 1.5,
            'color' => '#FFD700',
        ],
        'vip' => [
            'name' => 'VIP',
            'minimum_points' => 10000,
            'multiplier' => 2.0,
            'color' => '#9B59B6',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Point Rules
    |--------------------------------------------------------------------------
    |
    | Default point earning rules
    |
    */

    'default_rules' => [
        [
            'name' => 'Purchase Points',
            'description' => 'Earn points for every purchase',
            'rule_type' => 'fixed_per_amount',
            'points_per_unit' => 1,
            'currency' => 'KD',
            'minimum_amount' => 1.0,
        ],
        [
            'name' => 'Birthday Bonus',
            'description' => 'Special birthday points',
            'rule_type' => 'fixed',
            'points_per_unit' => 100,
        ],
    ],
];
