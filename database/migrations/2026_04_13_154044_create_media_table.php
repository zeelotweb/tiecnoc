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




Schema::create('media', function (Blueprint $table) {
    $table->id();
    $table->morphs('mediable'); // Creates mediable_id and mediable_type
    $table->string('disk')->default('public');
    $table->string('path');
    $table->string('collection'); // e.g., 'variant.front', 'variant.back'
    
    $table->enum('status', ['uploaded', 'processing', 'ready', 'failed'])
          ->default('uploaded');

    $table->json('meta')->nullable();
    $table->unsignedInteger('order')->default(0);
    $table->string('tag')->nullable();
    
    // ⚡ ADD THESE TWO COLUMNS HERE
    $table->string('type')->nullable(); 
    $table->string('thumbnail')->nullable(); 

    $table->timestamps();

    $table->index(['collection']);
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
