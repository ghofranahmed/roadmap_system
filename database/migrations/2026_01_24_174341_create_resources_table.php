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
        Schema::create('resources', function (Blueprint $table) { 
            $table->id(); // معرف المصدر 
        $table->foreignId('sub_lesson_id')
        ->constrained('sub_lessons')
        ->cascadeOnDelete();
         // علاقة بالدرس الفرعي 
         $table->string('title'); // عنوان المصدر 
         $table->enum('type', ['book', 'video',  'article']); // نوع المصدر  
        // الرابط الإلكتروني 
        $table->enum('language', ['ar', 'en'])->default('en'); // اللغة (عربي أو إنجليزي)
        $table->string('link');
        $table->timestamps(); // created_at و updated_at 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
