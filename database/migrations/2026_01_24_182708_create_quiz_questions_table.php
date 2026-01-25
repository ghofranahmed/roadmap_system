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
    {Schema::create('quiz_questions', function (Blueprint $table) { 
        $table->id(); // معرف السؤال 
        $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
         // علاقة بالكويز 
         $table->text('question_text'); // نص السؤال 
         $table->json('options'); // الخيارات (مخزنة كـ JSON) 
         $table->string('correct_answer'); // الإجابة الصحيحة 
         $table->integer('question_xp')->default(1); // النقاط للسؤال 
         $table->unsignedInteger('order')->default(1); // ترتيب السؤال داخل الكويز 
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
