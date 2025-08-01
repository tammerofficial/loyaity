<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AppleWalletPushService;
use App\Models\WalletDeviceRegistration;
use App\Models\Customer;

class TestWalletPushNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wallet:test-push 
                            {--customer-id= : Test push for specific customer}
                            {--global : Test global push notifications}
                            {--status : Show push notification status}';

    /**
     * The console command description.
     */
    protected $description = 'Test Apple Wallet push notifications system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pushService = new AppleWalletPushService();

        if ($this->option('status')) {
            $this->showStatus($pushService);
            return;
        }

        // Validate configuration first
        $configValidation = $pushService->validateConfiguration();
        if ($configValidation !== true) {
            $this->error('❌ Push notifications configuration issues:');
            foreach ($configValidation as $error) {
                $this->error("   • $error");
            }
            return 1;
        }

        $this->info('✅ Push notifications configuration is valid');

        if ($this->option('global')) {
            $this->testGlobalPush($pushService);
        } elseif ($customerId = $this->option('customer-id')) {
            $this->testCustomerPush($pushService, $customerId);
        } else {
            $this->info('Available options:');
            $this->info('  --status         Show current status');
            $this->info('  --global         Test global push notifications');
            $this->info('  --customer-id=X  Test push for specific customer');
        }
    }

    private function showStatus(AppleWalletPushService $pushService)
    {
        $this->info('📊 Apple Wallet Push Notifications Status');
        $this->info('==========================================');

        // Configuration status
        $configValidation = $pushService->validateConfiguration();
        if ($configValidation === true) {
            $this->info('✅ Configuration: Valid');
        } else {
            $this->error('❌ Configuration: Invalid');
            foreach ($configValidation as $error) {
                $this->error("   • $error");
            }
        }

        // Device registrations
        $totalRegistrations = WalletDeviceRegistration::active()->count();
        $this->info("📱 Active device registrations: $totalRegistrations");

        if ($totalRegistrations > 0) {
            $byPassType = WalletDeviceRegistration::active()
                ->select('pass_type_identifier')
                ->groupBy('pass_type_identifier')
                ->selectRaw('pass_type_identifier, count(*) as count')
                ->get();

            $this->info('   By pass type:');
            foreach ($byPassType as $type) {
                $this->info("   • {$type->pass_type_identifier}: {$type->count} devices");
            }
        }

        // Recent activity
        $recentRegistrations = WalletDeviceRegistration::where('registered_at', '>', now()->subHours(24))
            ->count();
        $this->info("🕒 Registrations in last 24h: $recentRegistrations");
    }

    private function testGlobalPush(AppleWalletPushService $pushService)
    {
        $this->info('🌍 Testing global push notifications...');
        
        $registrations = WalletDeviceRegistration::active()->count();
        if ($registrations === 0) {
            $this->warn('⚠️  No active device registrations found');
            $this->info('💡 Add some passes to Apple Wallet first to test push notifications');
            return;
        }

        $this->info("📱 Found $registrations active device registrations");
        
        if ($this->confirm('Send test push notifications to all devices?')) {
            $notificationsSent = $pushService->notifyGlobalDesignUpdate();
            $this->info("✅ Sent $notificationsSent push notifications");
        }
    }

    private function testCustomerPush(AppleWalletPushService $pushService, $customerId)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            $this->error("❌ Customer with ID $customerId not found");
            return 1;
        }

        $this->info("👤 Testing push notifications for customer: {$customer->name}");

        $registrations = WalletDeviceRegistration::whereHas('appleWalletPass', function ($query) use ($customerId) {
            $query->where('customer_id', $customerId);
        })->active()->count();

        if ($registrations === 0) {
            $this->warn('⚠️  No active device registrations found for this customer');
            $this->info('💡 Customer needs to add their pass to Apple Wallet first');
            return;
        }

        $this->info("📱 Found $registrations active device registrations for this customer");

        if ($this->confirm('Send test push notification to this customer\'s devices?')) {
            $notificationsSent = $pushService->notifyCustomerPassUpdates($customerId);
            $this->info("✅ Sent $notificationsSent push notifications");
        }
    }
}