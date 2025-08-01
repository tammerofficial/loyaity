<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Logo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logoUrl = 'https://hudaaljarallah.net/wp-content/uploads/thegem/logos/logo_192acb51c2313c84b0d3cab0253ca739_1x.png';
        
        try {
            // Download the logo from the URL
            $response = Http::timeout(30)->get($logoUrl);
            
            if ($response->successful()) {
                // Generate unique filename
                $fileName = 'huda_aljarallah_logo_' . Str::uuid() . '.png';
                $filePath = 'logos/' . $fileName;
                
                // Store the logo in storage
                Storage::put('public/' . $filePath, $response->body());
                
                // Get image dimensions
                $imageInfo = getimagesizefromstring($response->body());
                $width = $imageInfo ? $imageInfo[0] : null;
                $height = $imageInfo ? $imageInfo[1] : null;
                
                // Deactivate all existing logos
                Logo::where('is_active', true)->update(['is_active' => false]);
                
                // Create new logo record
                $logo = Logo::create([
                    'name' => 'Huda Aljarallah Logo',
                    'file_path' => $filePath,
                    'original_name' => 'logo_192acb51c2313c84b0d3cab0253ca739_1x.png',
                    'mime_type' => 'image/png',
                    'size' => strlen($response->body()),
                    'width' => $width,
                    'height' => $height,
                    'description' => 'الشعار الجديد من موقع Huda Aljarallah',
                    'is_active' => true,
                    'is_default' => true,
                    'metadata' => [
                        'source_url' => $logoUrl,
                        'downloaded_at' => now()->toISOString(),
                        'original_dimensions' => [
                            'width' => $width,
                            'height' => $height
                        ]
                    ]
                ]);
                
                $this->command->info('✅ تم إضافة الشعار الجديد بنجاح');
                $this->command->info("📁 مسار الملف: {$filePath}");
                $this->command->info("📏 الأبعاد: {$width}x{$height}");
                $this->command->info("🔗 المصدر: {$logoUrl}");
                
            } else {
                $this->command->error('❌ فشل في تحميل الشعار من الرابط');
                $this->command->error("HTTP Status: " . $response->status());
            }
            
        } catch (\Exception $e) {
            $this->command->error('❌ حدث خطأ أثناء إضافة الشعار: ' . $e->getMessage());
        }
    }
} 