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
    Schema::create('roadmap_enrollments', function (Blueprint $table) {
        $table->id(); // معرف الاشتراك
        $table->foreignId('user_id')
        ->constrained('users')->cascadeOnDelete(); // علاقة بالمستخدم
        $table->foreignId('roadmap_id')
        ->constrained('roadmaps')->cascadeOnDelete(); // علاقة بالمسار
        $table->dateTime('started_at')->nullable(); // تاريخ البدء
        $table->dateTime('completed_at')->nullable();   // تاريخ الانتهاء
        $table->integer('xp_points')->default(0); // نقاط الخبرة
        // نسبة التقدم
        $table->enum('status', ['active', 'completed', 'paused'])->default('active'); // حالة الاشتراك
       
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roadmap_enrollments');
    }
};
