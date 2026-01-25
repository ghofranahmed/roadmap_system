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
    Schema::create('announcements', function (Blueprint $table) {
        $table->id(); // معرف الإعلان
        $table->string('title'); // عنوان الإعلان
        $table->text('description'); // وصف الإعلان
        $table->timestamp('starts_at')->nullable(); // وقت بداية ظهور الإعلان
        $table->timestamp('ends_at')->nullable(); // وقت انتهاء الإعلان
        $table->string('link')->nullable(); // رابط الإعلان (اختياري)
        $table->timestamps(); // created_at و updated_at
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
