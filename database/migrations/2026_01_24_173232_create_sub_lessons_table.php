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
        Schema::create('sub_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete(); // علاقة بالدرس الأساسي 
            $table->unsignedInteger('position')->default(1); 
            $table->text('description')->nullable();
            $table->timestamps();
            
    // Optional: Add an index for fast lookup on lesson_id
    $table->index('lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_lessons');
    }
};
