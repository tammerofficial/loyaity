<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Logo;

class UpdateLogoToExternalUrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $externalUrl = 'https://hudaaljarallah.net/wp-content/uploads/thegem/logos/logo_192acb51c2313c84b0d3cab0253ca739_1x.png';
        
        try {
            // البحث عن الشعار النشط الحالي
            $activeLogo = Logo::where('is_active', true)->first();
            
            if ($activeLogo) {
                // تحديث الشعار ليستخدم الرابط الخارجي
                $activeLogo->update([
                    'external_url' => $externalUrl,
                    'name' => 'Huda Aljarallah Logo (External)',
                    'description' => 'الشعار من موقع Huda Aljarallah - رابط خارجي مباشر',
                    'metadata' => array_merge($activeLogo->metadata ?? [], [
                        'external_url' => $externalUrl,
                        'updated_at' => now()->toISOString(),
                        'source' => 'external_url'
                    ])
                ]);
                
                $this->command->info('✅ تم تحديث الشعار ليستخدم الرابط الخارجي');
                $this->command->info("🔗 الرابط: {$externalUrl}");
                $this->command->info("📝 الاسم: {$activeLogo->name}");
                
            } else {
                // إنشاء شعار جديد إذا لم يكن هناك شعار نشط
                $logo = Logo::create([
                    'name' => 'Huda Aljarallah Logo (External)',
                    'external_url' => $externalUrl,
                    'file_path' => null, // لا نحتاج لملف محلي
                    'original_name' => 'logo_192acb51c2313c84b0d3cab0253ca739_1x.png',
                    'mime_type' => 'image/png',
                    'size' => 0, // لا نعرف الحجم للرابط الخارجي
                    'width' => 164,
                    'height' => 164,
                    'description' => 'الشعار من موقع Huda Aljarallah - رابط خارجي مباشر',
                    'is_active' => true,
                    'is_default' => true,
                    'metadata' => [
                        'external_url' => $externalUrl,
                        'created_at' => now()->toISOString(),
                        'source' => 'external_url',
                        'original_dimensions' => [
                            'width' => 164,
                            'height' => 164
                        ]
                    ]
                ]);
                
                $this->command->info('✅ تم إنشاء شعار جديد بالرابط الخارجي');
                $this->command->info("🔗 الرابط: {$externalUrl}");
                $this->command->info("📝 الاسم: {$logo->name}");
            }
            
        } catch (\Exception $e) {
            $this->command->error('❌ حدث خطأ أثناء تحديث الشعار: ' . $e->getMessage());
        }
    }
} 