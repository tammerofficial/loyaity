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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('loyalty_card_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['earned', 'redeemed', 'expired', 'adjusted'])->default('earned');
            $table->integer('points');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('KD');
            $table->text('description')->nullable();
            $table->string('reference_number')->unique()->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();

            $table->index(['customer_id', 'type', 'processed_at']);
            $table->index(['expires_at', 'type']);
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
