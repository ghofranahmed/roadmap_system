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
    Schema::create('admins', function (Blueprint $table) {
        $table->id(); // معرف المسؤول
        $table->string('admin_name'); // اسم المسؤول
        $table->string('email')->unique(); // البريد الإلكتروني مع تحقق فريد
        $table->string('password'); // كلمة المرور (bcrypt)
        $table->timestamps(); // created_at و updated_at
         $table->index('admin_name');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
