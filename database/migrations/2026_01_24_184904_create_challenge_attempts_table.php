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
    Schema::create('challenge_attempts', function (Blueprint $table) {
        $table->id(); // معرف المحاولة
        $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete(); // علاقة بالتحدي
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // علاقة بالمستخدم
        $table->longText('submitted_code'); // الكود اللي الطالب كتبه
        $table->longText('execution_output')->nullable(); // مخرجات التنفيذ (stdout/stderr)
        $table->boolean('passed')->default(false); // هل نجح الطالب في التحدي
       
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_attempts');
    }
};
