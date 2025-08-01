<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyCard;
use App\Models\Transaction;
use App\Models\AppleWalletPass;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalletBridgeService
{
    protected $bridgeUrl;
    protected $bridgeSecret;

    public function __construct()
    {
        $this->bridgeUrl = config('loyalty.bridge_url');
        $this->bridgeSecret = config('loyalty.bridge_secret');
    }

    /**
     * تحديث نقاط العميل في البطاقة
     */
    public function updateCustomerPoints($customerId, $newPoints, $reason = 'Manual Update')
    {
        try {
            $customer = Customer::with('loyaltyCards')->findOrFail($customerId);
            $loyaltyCard = $customer->loyaltyCards->first();
            
            if (!$loyaltyCard) {
                throw new \Exception('Customer has no loyalty card');
            }

            // تحديث النقاط في قاعدة البيانات
            $oldPoints = $customer->available_points;
            $customer->update([
                'total_points' => $newPoints,
                'available_points' => $newPoints
            ]);

            // تسجيل المعاملة
            Transaction::create([
                'customer_id' => $customer->id,
                'type' => 'manual_update',
                'points' => $newPoints - $oldPoints,
                'description' => $reason,
                'balance' => $newPoints
            ]);

            // تحديث البطاقة عبر الجسر
            $this->updateWalletPass($loyaltyCard, $customer);

            Log::info("Customer points updated via bridge", [
                'customer_id' => $customer->id,
                'old_points' => $oldPoints,
                'new_points' => $newPoints,
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'message' => 'Points updated successfully',
                'old_points' => $oldPoints,
                'new_points' => $newPoints,
                'difference' => $newPoints - $oldPoints
            ];

        } catch (\Exception $e) {
            Log::error("Failed to update customer points", [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * إضافة نقاط للعميل
     */
    public function addPoints($customerId, $points, $reason = 'Points Earned')
    {
        try {
            $customer = Customer::with('loyaltyCards')->findOrFail($customerId);
            $loyaltyCard = $customer->loyaltyCards->first();
            
            if (!$loyaltyCard) {
                throw new \Exception('Customer has no loyalty card');
            }

            $oldPoints = $customer->available_points;
            $newPoints = $oldPoints + $points;

            // تحديث النقاط
            $customer->update([
                'total_points' => $customer->total_points + $points,
                'available_points' => $newPoints
            ]);

            // تسجيل المعاملة
            Transaction::create([
                'customer_id' => $customer->id,
                'type' => 'earned',
                'points' => $points,
                'description' => $reason
            ]);

            // تحديث البطاقة عبر الجسر
            $this->updateWalletPass($loyaltyCard, $customer);

            // إرسال إشعار push
            $this->sendPushNotification($loyaltyCard, $customer, $points, 'earned');

            Log::info("Points added via bridge", [
                'customer_id' => $customer->id,
                'points_added' => $points,
                'new_balance' => $newPoints,
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'message' => 'Points added successfully',
                'points_added' => $points,
                'new_balance' => $newPoints
            ];

        } catch (\Exception $e) {
            Log::error("Failed to add points", [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * استبدال نقاط العميل
     */
    public function redeemPoints($customerId, $points, $reason = 'Points Redeemed')
    {
        try {
            $customer = Customer::with('loyaltyCards')->findOrFail($customerId);
            $loyaltyCard = $customer->loyaltyCards->first();
            
            if (!$loyaltyCard) {
                throw new \Exception('Customer has no loyalty card');
            }

            if ($customer->available_points < $points) {
                throw new \Exception('Insufficient points');
            }

            $oldPoints = $customer->available_points;
            $newPoints = $oldPoints - $points;

            // تحديث النقاط
            $customer->update([
                'available_points' => $newPoints
            ]);

            // تسجيل المعاملة
            Transaction::create([
                'customer_id' => $customer->id,
                'type' => 'redeemed',
                'points' => -$points,
                'description' => $reason
            ]);

            // تحديث البطاقة عبر الجسر
            $this->updateWalletPass($loyaltyCard, $customer);

            // إرسال إشعار push
            $this->sendPushNotification($loyaltyCard, $customer, $points, 'redeemed');

            Log::info("Points redeemed via bridge", [
                'customer_id' => $customer->id,
                'points_redeemed' => $points,
                'new_balance' => $newPoints,
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'message' => 'Points redeemed successfully',
                'points_redeemed' => $points,
                'new_balance' => $newPoints
            ];

        } catch (\Exception $e) {
            Log::error("Failed to redeem points", [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * تحديث بيانات البطاقة عبر الجسر
     */
    protected function updateWalletPass($loyaltyCard, $customer)
    {
        try {
            $passData = [
                'pass_type_id' => 'pass.com.loyalty.customer',
                'serial_number' => $loyaltyCard->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'points' => $customer->available_points,
                'card_number' => $loyaltyCard->card_number,
                'level' => $this->calculateLevel($customer->available_points),
                'last_updated' => now()->toISOString()
            ];

            $response = Http::withHeaders([
                'X-Bridge-Secret' => $this->bridgeSecret,
                'Content-Type' => 'application/json'
            ])->post($this->bridgeUrl . '/update-pass', $passData);

            if ($response->successful()) {
                Log::info("Wallet pass updated via bridge", [
                    'card_id' => $loyaltyCard->id,
                    'customer_id' => $customer->id,
                    'new_points' => $loyaltyCard->points
                ]);
            } else {
                Log::warning("Failed to update wallet pass via bridge", [
                    'card_id' => $loyaltyCard->id,
                    'response' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Error updating wallet pass via bridge", [
                'card_id' => $loyaltyCard->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * إرسال إشعار push للعميل
     */
    protected function sendPushNotification($loyaltyCard, $customer, $points, $action)
    {
        try {
            $message = match($action) {
                'earned' => "تم إضافة {$points} نقطة لحسابك! رصيدك الحالي: {$customer->available_points} نقطة",
                'redeemed' => "تم استبدال {$points} نقطة من حسابك. رصيدك الحالي: {$customer->available_points} نقطة",
                default => "تم تحديث بطاقتك! رصيدك الحالي: {$customer->available_points} نقطة"
            };

            $notificationData = [
                'pass_type_id' => 'pass.com.loyalty.customer',
                'serial_number' => $loyaltyCard->id,
                'message' => $message,
                'action' => $action,
                'points' => $points,
                'balance' => $customer->available_points
            ];

            $response = Http::withHeaders([
                'X-Bridge-Secret' => $this->bridgeSecret,
                'Content-Type' => 'application/json'
            ])->post($this->bridgeUrl . '/push-notification', $notificationData);

            if ($response->successful()) {
                Log::info("Push notification sent via bridge", [
                    'card_id' => $loyaltyCard->id,
                    'action' => $action,
                    'points' => $points
                ]);
            } else {
                Log::warning("Failed to send push notification via bridge", [
                    'card_id' => $loyaltyCard->id,
                    'response' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Error sending push notification via bridge", [
                'card_id' => $loyaltyCard->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * حساب مستوى العميل بناءً على النقاط
     */
    protected function calculateLevel($points)
    {
        if ($points >= 1000) return 'Gold';
        if ($points >= 500) return 'Silver';
        if ($points >= 100) return 'Bronze';
        return 'New';
    }

    /**
     * جلب إحصائيات الجسر
     */
    public function getBridgeStatistics()
    {
        try {
            $response = Http::withHeaders([
                'X-Bridge-Secret' => $this->bridgeSecret
            ])->get($this->bridgeUrl . '/statistics');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to get bridge statistics'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * اختبار الاتصال بالجسر
     */
    public function testBridgeConnection()
    {
        try {
            $response = Http::withHeaders([
                'X-Bridge-Secret' => $this->bridgeSecret
            ])->get($this->bridgeUrl . '/test-connection');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Bridge connection failed'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * جلب سجلات الجسر
     */
    public function getBridgeLogs()
    {
        try {
            $response = Http::withHeaders([
                'X-Bridge-Secret' => $this->bridgeSecret
            ])->get($this->bridgeUrl . '/logs');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to get bridge logs'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 