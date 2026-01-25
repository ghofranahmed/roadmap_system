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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id(); // معرف التحدي
            $table->foreignId('learning_unit_id')
            ->constrained('learning_units')->cascadeOnDelete(); // مرتبط بالوحدة التعليمية
            $table->string('title'); // عنوان التحدي
            $table->text('description')->nullable(); // وصف التحدي
            $table->integer('min_xp')->default(0); // أقل نقاط خبرة يمنحها التحدي
            $table->string('language'); // اللغة البرمجية (PHP, JS, Python...)
            $table->longText('starter_code')->nullable(); // الكود المبدئي
            $table->json('test_cases'); // حالات الاختبار (مخزنة كـ JSON)
            $table->boolean('is_active')->default(true); // حالة التحدي

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
