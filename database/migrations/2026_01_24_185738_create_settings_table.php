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
        Schema::create('settings', function (Blueprint $table) {
            $table->id(); // معرف الإعداد 
            $table->foreignId('modified_by_user_id')
            ->nullable()->constrained('users')
            ->nullOnDelete();   // أفضل من cascade لأن الإعدادات ما لازم تختفي لو الأدمن اتحذف
             $table->string('key'); // اسم الإعداد (مثلاً: site_name, default_language, theme)
            $table->text('value')->nullable(); 
            // قيمة الإعداد (مثلاً: "Roadmap System", "ar", "dark")
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
