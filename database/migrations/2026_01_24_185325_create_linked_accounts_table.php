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

    Schema::create('linked_accounts', function (Blueprint $table) {
        $table->id(); // معرف الحساب المرتبط
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // علاقة بالمستخدم
        $table->string('provider'); // اسم الخدمة (google, github, facebook...)
        $table->string('provider_user_id'); // المعرف الفريد للمستخدم في الخدمة الخارجية
        $table->string('access_token')->nullable(); // التوكن للوصول
        $table->string('refresh_token')->nullable(); // التوكن لتجديد الوصول
        $table->timestamp('expires_at')->nullable();
        $table->unique(['provider', 'provider_user_id']); // وقت انتهاء التوكن
        $table->timestamps(); // created_at و updated_at
    });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('linked_accounts');
    }
};
