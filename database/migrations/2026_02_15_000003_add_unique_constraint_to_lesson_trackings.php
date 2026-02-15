<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_trackings', function (Blueprint $table) {
            $table->unique(['user_id', 'lesson_id'], 'lesson_trackings_user_lesson_unique');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_trackings', function (Blueprint $table) {
            $table->dropUnique('lesson_trackings_user_lesson_unique');
        });
    }
};

