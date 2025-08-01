<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم الشعار');
            $table->string('file_path')->comment('مسار الملف');
            $table->string('original_name')->comment('الاسم الأصلي للملف');
            $table->string('mime_type')->comment('نوع الملف');
            $table->integer('size')->comment('حجم الملف بالبايت');
            $table->integer('width')->nullable()->comment('العرض بالبكسل');
            $table->integer('height')->nullable()->comment('الارتفاع بالبكسل');
            $table->boolean('is_active')->default(false)->comment('هل الشعار نشط');
            $table->boolean('is_default')->default(false)->comment('هل هو الشعار الافتراضي');
            $table->text('description')->nullable()->comment('وصف الشعار');
            $table->json('metadata')->nullable()->comment('بيانات إضافية');
            $table->timestamps();
            
            // فهرس للبحث السريع عن الشعار النشط
            $table->index(['is_active', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logos');
    }
};