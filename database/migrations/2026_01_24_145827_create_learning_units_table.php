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
        Schema::create('learning_units', function (Blueprint $table) {
    $table->id();  // Primary Key
    $table->foreignId('roadmap_id')  // Foreign Key referencing the 'roadmaps' table
          ->constrained('roadmaps')  // Automatic referencing of 'roadmaps.id'
          ->onDelete('cascade');  // If a roadmap is deleted, all learning units linked to it will be deleted
    $table->string('title');  // Title of the learning unit
    $table->integer('position');  // Position of the learning unit in a specific roadmap (useful for ordering)
    $table->timestamps(); 
     // created_at and updated_at timestamps
    $table->index('roadmap_id');  // Index on roadmap_id
    $table->index('position');    // Index on position for ordering within the roadmap

});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_units');
    }
};
