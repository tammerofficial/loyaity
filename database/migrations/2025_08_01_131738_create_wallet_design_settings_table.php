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
        Schema::create('wallet_design_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('global'); // 'global' or 'customer'
            $table->unsignedBigInteger('customer_id')->nullable(); // null for global settings
            $table->string('organization_name')->default('Tammer Loyalty');
            $table->string('background_color')->default('#1e3a8a');
            $table->string('background_color_secondary')->default('#3b82f6');
            $table->string('text_color')->default('#ffffff');
            $table->string('label_color')->default('#ffffff');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['type', 'is_active']);
            $table->unique(['customer_id'], 'unique_customer_design');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_design_settings');
    }
};
