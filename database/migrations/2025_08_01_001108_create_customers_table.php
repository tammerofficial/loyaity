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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('tier', ['bronze', 'silver', 'gold', 'vip'])->default('bronze');
            $table->unsignedBigInteger('total_points')->default(0);
            $table->unsignedBigInteger('available_points')->default(0);
            $table->string('membership_number')->unique();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->index(['tier', 'total_points']);
            $table->index('membership_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
