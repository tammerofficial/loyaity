<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PointRule;

class PointRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = config('loyalty.default_rules');

        foreach ($rules as $rule) {
            PointRule::create($rule);
        }

        // Additional custom rules
        PointRule::create([
            'name' => 'Visit Bonus',
            'description' => 'Bonus points for store visits',
            'rule_type' => 'fixed',
            'points_per_unit' => 10,
            'tier_specific' => [
                'bronze' => 1,
                'silver' => 1.25,
                'gold' => 1.5,
                'vip' => 2.0,
            ],
        ]);

        PointRule::create([
            'name' => 'Weekend Multiplier',
            'description' => '2x points on weekends',
            'rule_type' => 'multiplier',
            'multiplier' => 2.0,
            'valid_from' => now()->startOfWeek()->addDays(5), // Friday
            'valid_to' => now()->endOfWeek()->subDay(), // Sunday
        ]);
    }
}
