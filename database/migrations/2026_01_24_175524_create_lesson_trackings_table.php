<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{    
       public function up(): void
{
    Schema::create('lesson_trackings', function (Blueprint $table) {
        $table->id(); // معرف تتبع الدرس
        $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete(); // علاقة بالدرس
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // علاقة بالمستخدم
       $table->boolean('is_complete')->default(false);

        $table->timestamp('last_updated_at')->nullable(); // تاريخ آخر تعديل
        $table->timestamps(); // created_at و updated_at
    });
}

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_trackings');
    }
};
