<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // حذف index المرتبط بالعمود is_admin
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_is_admin_index');  // تأكد من اسم الـ index
        });
    }

    public function down(): void
    {
        // لو رجعت المايقريشن
        Schema::table('users', function (Blueprint $table) {
            $table->index('is_admin'); // إعادة إنشاء index للـ is_admin
        });
    }
};
