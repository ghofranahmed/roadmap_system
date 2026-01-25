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
        Schema::create('quizzes', function (Blueprint $table) {
             $table->id(); // معرف الكويز 
             $table->foreignId('learning_unit_id')
             ->constrained('learning_units')->cascadeOnDelete(); // علاقة بالوحدة التعليمية 
             $table->boolean('is_active')->default(true); // حالة الكويز (مفعل أو لا) 
             $table->integer('max_xp')->default(0); // أقصى نقاط خبرة يمنحها الكويز 
             $table->integer('min_xp')->default(0); // أقل نقاط خبرة يمنحها الكويز 
        $table->timestamps(); // created_at و updated_at 
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
