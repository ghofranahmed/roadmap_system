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
        Schema::create('chat_messages', function (Blueprint $table) {
    $table->id(); // معرف الرسالة
    $table->foreignId('chat_room_id')->constrained('chat_rooms')->cascadeOnDelete(); // الغرفة المرتبطة
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // المرسل

    $table->text('content')->nullable(); // محتوى الرسالة (نص)
    $table->string('attachment')->nullable(); // مرفق (رابط ملف أو صورة)

    $table->timestamp('sent_at')->nullable(); // وقت الإرسال
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
