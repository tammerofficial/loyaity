<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Logo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'is_active',
        'is_default',
        'description',
        'metadata',
        'external_url'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'metadata' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    /**
     * الحصول على URL الشعار
     */
    public function getUrlAttribute(): string
    {
        // إذا كان هناك رابط خارجي، استخدمه
        if ($this->external_url) {
            return $this->external_url;
        }
        
        // وإلا استخدم الملف المحلي
        return Storage::url($this->file_path);
    }

    /**
     * الحصول على الشعار النشط الحالي
     */
    public static function getActiveLogo(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * الحصول على الشعار الافتراضي
     */
    public static function getDefaultLogo(): ?self
    {
        return self::where('is_default', true)->first();
    }

    /**
     * تفعيل شعار معين وإلغاء تفعيل الآخرين
     */
    public function activate(): bool
    {
        // إلغاء تفعيل جميع الشعارات الأخرى
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // تفعيل هذا الشعار
        return $this->update(['is_active' => true]);
    }

    /**
     * جعل شعار افتراضي وإلغاء الافتراضية من الآخرين
     */
    public function makeDefault(): bool
    {
        // إلغاء الافتراضية من جميع الشعارات الأخرى
        self::where('id', '!=', $this->id)->update(['is_default' => false]);
        
        // جعل هذا الشعار افتراضي
        return $this->update(['is_default' => true]);
    }

    /**
     * الحصول على حجم الملف بصيغة قابلة للقراءة
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * التحقق من صحة نوع الملف
     */
    public static function isValidImageType(string $mimeType): bool
    {
        $allowedTypes = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/svg+xml',
            'image/webp'
        ];
        
        return in_array($mimeType, $allowedTypes);
    }

    /**
     * حذف الملف عند حذف السجل
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($logo) {
            if (Storage::exists($logo->file_path)) {
                Storage::delete($logo->file_path);
            }
        });
    }
}