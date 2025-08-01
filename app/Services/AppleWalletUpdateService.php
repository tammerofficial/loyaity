<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\AppleWalletPass;
use App\Models\WalletDeviceRegistration;
use App\Models\WalletDesignSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AppleWalletUpdateService
{
    /**
     * Force update customer's wallet pass with latest design and data
     */
    public function forceUpdateCustomerPass(Customer $customer)
    {
        try {
            // Find the customer's pass
            $pass = AppleWalletPass::where('customer_id', $customer->id)
                ->where('is_active', true)
                ->first();

            if (!$pass) {
                throw new \Exception('No active pass found for customer');
            }

            // Regenerate pass with latest design and data
            $this->regeneratePass($customer);
            
            // Send push notifications to all registered devices
            $devices = WalletDeviceRegistration::where('apple_wallet_pass_id', $pass->id)->get();
            $notifiedCount = 0;
            
            foreach ($devices as $device) {
                try {
                    $this->sendPushNotification($device, $pass);
                    $notifiedCount++;
                } catch (\Exception $e) {
                    Log::warning('Failed to send push notification', [
                        'device' => $device->device_library_identifier,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return [
                'success' => true,
                'pass_regenerated' => true,
                'devices_notified' => $notifiedCount,
                'total_devices' => $devices->count(),
                'customer' => [
                    'name' => $customer->name,
                    'points' => $customer->available_points,
                    'tier' => $customer->tier,
                    'membership_number' => $customer->membership_number
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Force update customer pass failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Regenerate pass file with latest design and data
     */
    private function regeneratePass(Customer $customer)
    {
        // Call the existing wallet pass generation endpoint
        $response = Http::timeout(30)->get(url("/admin/customers/{$customer->id}/wallet-pass"));
        
        if (!$response->successful()) {
            throw new \Exception('Failed to regenerate pass: HTTP ' . $response->status());
        }

        // Upload to remote server
        $passContent = $response->body();
        $filename = "loyalty_card_{$customer->membership_number}.pkpass";
        
        // Save to remote server via SSH
        $tempFile = storage_path("app/temp_{$filename}");
        file_put_contents($tempFile, $passContent);
        
        // Upload to remote server
        $uploadCommand = "sshpass -p 'aaaaaaaa' scp " . escapeshellarg($tempFile) . " alalawi310@192.168.8.143:/var/www/html/applecards/" . escapeshellarg($filename);
        exec($uploadCommand, $output, $returnCode);
        
        // Clean up temp file
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        
        if ($returnCode !== 0) {
            throw new \Exception('Failed to upload pass to server');
        }

        Log::info('Pass regenerated successfully', [
            'customer_id' => $customer->id,
            'filename' => $filename
        ]);
    }

    /**
     * Send push notification to device
     */
    private function sendPushNotification($device, $pass)
    {
        // This would integrate with Apple's Push Notification service
        // For now, we'll log the notification
        Log::info('Push notification sent', [
            'device' => $device->device_library_identifier,
            'pass_type' => $pass->pass_type_id,
            'serial_number' => $pass->serial_number
        ]);

        // TODO: Implement actual push notification
        // $this->pushService->sendUpdate($device->device_library_identifier, $pass->pass_type_id, $pass->serial_number);
    }

    /**
     * Update all customers' passes (bulk update)
     */
    public function updateAllPasses()
    {
        $customers = Customer::all();
        $results = [];
        
        foreach ($customers as $customer) {
            try {
                $result = $this->forceUpdateCustomerPass($customer);
                $results[] = [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'success' => true,
                    'devices_notified' => $result['devices_notified']
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'total_customers' => $customers->count(),
            'successful_updates' => count(array_filter($results, fn($r) => $r['success'])),
            'failed_updates' => count(array_filter($results, fn($r) => !$r['success'])),
            'results' => $results
        ];
    }
}
