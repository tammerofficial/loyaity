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
        Schema::create('wallet_device_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('device_library_identifier'); // Device identifier from Apple
            $table->string('pass_type_identifier'); // Pass type ID
            $table->string('serial_number'); // Pass serial number
            $table->foreignId('apple_wallet_pass_id')->constrained('apple_wallet_passes')->onDelete('cascade');
            $table->string('push_token')->nullable(); // For push notifications
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('last_updated')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique registration per device per pass
            $table->unique(['device_library_identifier', 'pass_type_identifier', 'serial_number'], 'unique_device_pass');
            $table->index(['pass_type_identifier', 'serial_number']);
            $table->index(['device_library_identifier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_device_registrations');
    }
};
