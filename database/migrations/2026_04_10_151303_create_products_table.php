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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Specs
            $table->string('material')->nullable();
            $table->string('fit_type')->nullable();
            $table->text('care_instructions')->nullable();

            // NEW: Gender Column for Section Filtering
            $table->enum('gender', ['male', 'female', 'unisex'])->default('unisex');

            // Pricing Defaults
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('compare_at_price', 10, 2)->nullable();

            // Metadata & Workflow
            $table->string('sku_prefix')->nullable();
            $table->foreignId('category_id')->constrained(); 
            $table->enum('status', ['draft', 'live', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);

            $table->softDeletes(); 
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};



