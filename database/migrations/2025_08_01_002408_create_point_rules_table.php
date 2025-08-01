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
        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('rule_type', ['fixed_per_amount', 'percentage', 'multiplier', 'fixed'])->default('fixed_per_amount');
            $table->integer('points_per_unit')->default(1);
            $table->string('currency', 3)->default('KD');
            $table->decimal('minimum_amount', 10, 2)->default(0);
            $table->integer('maximum_points')->nullable();
            $table->decimal('multiplier', 8, 2)->default(1.0);
            $table->json('tier_specific')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'valid_from', 'valid_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_rules');
    }
};
