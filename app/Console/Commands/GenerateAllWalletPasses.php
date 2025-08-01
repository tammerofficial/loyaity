<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Jobs\GenerateWalletPassJob;

class GenerateAllWalletPasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:generate-all {--delay=5 : Delay between each job in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Apple Wallet passes for all existing customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting wallet pass generation for all customers...');
        
        $delay = (int) $this->option('delay');
        $customers = Customer::all();
        
        if ($customers->isEmpty()) {
            $this->warn('⚠️  No customers found.');
            return;
        }
        
        $this->info("📊 Found {$customers->count()} customers to process.");
        
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();
        
        foreach ($customers as $index => $customer) {
            // Dispatch job with increasing delay to avoid overwhelming the server
            $jobDelay = now()->addSeconds($index * $delay);
            GenerateWalletPassJob::dispatch($customer->id)->delay($jobDelay);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $totalTime = $customers->count() * $delay;
        $this->info("✅ {$customers->count()} wallet pass generation jobs dispatched!");
        $this->info("⏱️  Estimated completion time: " . gmdate('H:i:s', $totalTime));
        $this->info("📋 Monitor progress with: tail -f storage/logs/laravel.log");
        
        return 0;
    }
}