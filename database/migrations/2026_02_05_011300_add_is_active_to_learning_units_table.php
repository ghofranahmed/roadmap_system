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
    Schema::table('learning_units', function (Blueprint $table) {
        // نضيفه بعد العنوان، والقيمة الافتراضية true (نشط)
        $table->boolean('is_active')->default(true)->after('title');
    });
}

public function down(): void
{
    Schema::table('learning_units', function (Blueprint $table) {
        $table->dropColumn('is_active');
    });
}
};
