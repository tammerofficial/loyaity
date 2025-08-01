<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WalletDesignSettings;

class WalletDesignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تحديث الإعدادات العامة أو إنشاؤها إذا لم تكن موجودة
        $globalSettings = WalletDesignSettings::where('type', 'global')->first();
        
        if (!$globalSettings) {
            WalletDesignSettings::create([
                'type' => 'global',
                'organization_name' => 'Tammer Loyalty',
                'background_color' => '#000000', // أسود
                'background_color_secondary' => '#1a1a1a', // رمادي داكن
                'text_color' => '#ffffff', // أبيض
                'label_color' => '#ffffff', // أبيض
                'background_image_url' => null,
                'background_opacity' => 50,
                'use_background_image' => false,
                'is_active' => true,
            ]);
        } else {
            $globalSettings->update([
                'background_color' => '#000000', // أسود
                'background_color_secondary' => '#1a1a1a', // رمادي داكن
                'text_color' => '#ffffff', // أبيض
                'label_color' => '#ffffff', // أبيض
            ]);
        }

        $this->command->info('✅ تم تحديث إعدادات تصميم البطاقة باللون الأسود');
    }
} 