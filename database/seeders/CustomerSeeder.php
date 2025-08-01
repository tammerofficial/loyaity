<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\LoyaltyCard;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample customers
        $customers = [
            [
                'name' => 'Ahmed Al-Kuwait',
                'email' => 'ahmed@example.com',
                'phone' => '+965-1234-5678',
                'date_of_birth' => '1990-05-15',
                'tier' => 'gold',
                'total_points' => 7500,
                'available_points' => 6500,
                'membership_number' => 'M2025000001',
                'joined_at' => now()->subMonths(8),
            ],
            [
                'name' => 'Fatima Al-Sabah',
                'email' => 'fatima@example.com',
                'phone' => '+965-9876-5432',
                'date_of_birth' => '1985-10-20',
                'tier' => 'vip',
                'total_points' => 15000,
                'available_points' => 12000,
                'membership_number' => 'M2025000002',
                'joined_at' => now()->subYear(),
            ],
            [
                'name' => 'Mohammad Hassan',
                'email' => 'mohammad@example.com',
                'phone' => '+965-5555-1234',
                'date_of_birth' => '1995-03-08',
                'tier' => 'silver',
                'total_points' => 2500,
                'available_points' => 2100,
                'membership_number' => 'M2025000003',
                'joined_at' => now()->subMonths(3),
            ],
            [
                'name' => 'Sara Al-Ahmad',
                'email' => 'sara@example.com',
                'phone' => '+965-7777-9999',
                'date_of_birth' => '1992-12-25',
                'tier' => 'bronze',
                'total_points' => 500,
                'available_points' => 450,
                'membership_number' => 'M2025000004',
                'joined_at' => now()->subMonth(),
            ],
        ];

        foreach ($customers as $customerData) {
            $customer = Customer::create($customerData);

            // Create loyalty card for each customer
            $loyaltyCard = $customer->loyaltyCards()->create([
                'card_number' => 'LC' . str_pad($customer->id, 8, '0', STR_PAD_LEFT),
                'qr_code' => 'QR' . $customer->id . time(),
                'barcode' => 'BC' . $customer->id . time(),
                'status' => 'active',
                'issued_at' => $customer->joined_at,
            ]);

            // Create some sample transactions
            $customer->transactions()->create([
                'loyalty_card_id' => $loyaltyCard->id,
                'type' => 'earned',
                'points' => $customer->total_points,
                'amount' => $customer->total_points, // Assuming 1 KD = 1 point
                'currency' => 'KD',
                'description' => 'Initial points from past purchases',
                'reference_number' => 'TXN' . time() . $customer->id,
                'expires_at' => now()->addMonths(6),
                'processed_at' => $customer->joined_at,
            ]);

            if ($customer->total_points > $customer->available_points) {
                $redeemed = $customer->total_points - $customer->available_points;
                $customer->transactions()->create([
                    'loyalty_card_id' => $loyaltyCard->id,
                    'type' => 'redeemed',
                    'points' => -$redeemed,
                    'description' => 'Points redeemed for rewards',
                    'reference_number' => 'RDM' . time() . $customer->id,
                    'processed_at' => now()->subWeeks(2),
                ]);
            }
        }
    }
}
