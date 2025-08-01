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
        Schema::table('wallet_design_settings', function (Blueprint $table) {
            $table->string('background_image_url')->nullable()->after('label_color');
            $table->integer('background_opacity')->default(50)->after('background_image_url'); // 0-100
            $table->boolean('use_background_image')->default(false)->after('background_opacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_design_settings', function (Blueprint $table) {
            $table->dropColumn(['background_image_url', 'background_opacity', 'use_background_image']);
        });
    }
};
