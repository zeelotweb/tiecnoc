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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g., TNC-A1B2C3
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('pending'); // pending, paid, failed, refunded
            $table->decimal('total_amount', 10, 2);
            $table->string('stripe_session_id')->nullable()->index();
            $table->json('metadata')->nullable(); // Store browser/guest info
            $table->timestamps();
        });
    }

   /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
