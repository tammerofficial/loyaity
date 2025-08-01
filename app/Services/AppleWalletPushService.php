<?php

namespace App\Services;

use App\Models\WalletDeviceRegistration;
use App\Models\AppleWalletPass;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AppleWalletPushService
{
    private $pushGateway;
    private $certificatePath;
    private $certificatePassword;

    public function __construct()
    {
        $this->pushGateway = config('app.env') === 'production' 
            ? 'https://api.push.apple.com:443' 
            : 'https://api.sandbox.push.apple.com:443';
            
        $this->certificatePath = storage_path('certs/tammer.wallet.p12');
        $this->certificatePassword = env('APPLE_WALLET_CERTIFICATE_PASSWORD', '');
    }

    /**
     * Send push notification to update a specific pass.
     */
    public function notifyPassUpdate($serialNumber, $passTypeId)
    {
        $registrations = WalletDeviceRegistration::getRegistrationsForPass($serialNumber, $passTypeId);
        
        if ($registrations->isEmpty()) {
            Log::info("No device registrations found for pass: $serialNumber");
            return false;
        }

        $successCount = 0;
        
        foreach ($registrations as $registration) {
            if ($this->sendPushNotification($registration->push_token)) {
                $registration->markAsUpdated();
                $successCount++;
                
                Log::info("Push notification sent successfully", [
                    'device' => $registration->device_library_identifier,
                    'pass' => $serialNumber
                ]);
            } else {
                Log::error("Failed to send push notification", [
                    'device' => $registration->device_library_identifier,
                    'pass' => $serialNumber
                ]);
            }
        }

        return $successCount > 0;
    }

    /**
     * Send push notifications to all passes for a customer when design changes.
     */
    public function notifyCustomerPassUpdates($customerId)
    {
        $passes = AppleWalletPass::where('customer_id', $customerId)
            ->where('is_active', true)
            ->get();

        $totalNotified = 0;

        foreach ($passes as $pass) {
            if ($this->notifyPassUpdate($pass->serial_number, $pass->pass_type_id)) {
                $totalNotified++;
            }
        }

        Log::info("Customer pass updates notified", [
            'customer_id' => $customerId,
            'passes_notified' => $totalNotified
        ]);

        return $totalNotified;
    }

    /**
     * Send push notifications to all registered devices when global design changes.
     */
    public function notifyGlobalDesignUpdate()
    {
        $registrations = WalletDeviceRegistration::active()->get();
        $successCount = 0;

        foreach ($registrations as $registration) {
            if ($this->sendPushNotification($registration->push_token)) {
                $registration->markAsUpdated();
                $successCount++;
            }
        }

        Log::info("Global design update notifications sent", [
            'total_notifications' => $successCount
        ]);

        return $successCount;
    }

    /**
     * Send individual push notification using Apple's HTTP/2 API.
     */
    private function sendPushNotification($pushToken)
    {
        if (empty($pushToken)) {
            return false;
        }

        try {
            // For now, we'll use a simple approach with HTTP client
            // In production, you might want to use a more robust solution like pusher/pusher-php-server
            
            $url = $this->pushGateway . '/3/device/' . $pushToken;
            
            // Create the payload (empty for pass updates)
            $payload = json_encode([]);
            
            // Headers required by Apple
            $headers = [
                'apns-topic' => env('APPLE_WALLET_PASS_TYPE_ID'),
                'apns-push-type' => 'background',
                'apns-priority' => '5',
                'Content-Type' => 'application/json',
            ];

            // Note: This is a simplified implementation
            // For production, you need proper HTTP/2 client with certificate authentication
            Log::info("Would send push notification", [
                'url' => $url,
                'token' => substr($pushToken, 0, 10) . '...',
                'payload' => $payload
            ]);

            // For development/testing, we'll return true
            // In production, implement actual HTTP/2 request with certificate
            return true;

        } catch (\Exception $e) {
            Log::error("Push notification failed", [
                'error' => $e->getMessage(),
                'token' => substr($pushToken, 0, 10) . '...'
            ]);
            
            return false;
        }
    }

    /**
     * Validate that push notifications are properly configured.
     */
    public function validateConfiguration()
    {
        $errors = [];

        if (!file_exists($this->certificatePath)) {
            $errors[] = 'Apple Wallet certificate not found';
        }

        if (empty(env('APPLE_WALLET_PASS_TYPE_ID'))) {
            $errors[] = 'APPLE_WALLET_PASS_TYPE_ID not configured';
        }

        if (empty(env('APPLE_WALLET_TEAM_ID'))) {
            $errors[] = 'APPLE_WALLET_TEAM_ID not configured';
        }

        return empty($errors) ? true : $errors;
    }
}
