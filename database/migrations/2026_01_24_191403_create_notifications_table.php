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
    Schema::create('notifications', function (Blueprint $table) {
        $table->id(); // معرف الإشعار
        $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete(); 
        // اختياري: لو فارغ = إشعار عام، لو فيه قيمة = إشعار مخصص

        $table->string('title'); // عنوان الإشعار
        $table->text('message'); // نص الإشعار
        $table->boolean('is_active')->default(true); // حالة الإشعار
        $table->timestamp('scheduled_at')->nullable(); // وقت الجدولة
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
