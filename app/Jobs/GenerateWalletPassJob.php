<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

class GenerateWalletPassJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 60;
    public $tries = 3;
    
    protected $customerId;

    /**
     * Create a new job instance.
     */
    public function __construct($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $customer = Customer::find($this->customerId);
            
            if (!$customer) {
                Log::error("Customer not found for wallet pass generation: {$this->customerId}");
                return;
            }
            
            // Generate the pass by calling our wallet pass endpoint
            $response = Http::timeout(30)->get(url("/admin/customers/{$customer->id}/wallet-pass"));
            
            if ($response->successful()) {
                // Upload the generated pass to the remote server
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
                
                if ($returnCode === 0) {
                    Log::info("✅ Wallet pass generated successfully for customer {$customer->id}: {$filename}");
                } else {
                    Log::error("❌ Failed to upload wallet pass for customer {$customer->id}: " . implode("\n", $output));
                    throw new \Exception("Upload failed: " . implode("\n", $output));
                }
            } else {
                Log::error("❌ Failed to generate wallet pass for customer {$customer->id}: HTTP {$response->status()}");
                throw new \Exception("HTTP Error: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("❌ Exception generating wallet pass for customer {$this->customerId}: " . $e->getMessage());
            throw $e;
        }
    }
}