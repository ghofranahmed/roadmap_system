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
    Schema::create('quiz_attempts', function (Blueprint $table) {
        $table->id(); // معرف المحاولة
        $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete(); // علاقة بالكويز
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // علاقة بالمستخدم
        $table->json('answers'); // الإجابات المقدمة (مخزنة كـ JSON)
        $table->integer('score')->default(0); // مجموع النقاط المكتسبة
        $table->boolean('passed')->default(false); // حالة النجاح أو الرسوب
       
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
