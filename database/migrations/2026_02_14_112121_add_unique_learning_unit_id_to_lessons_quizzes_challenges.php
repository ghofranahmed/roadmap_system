<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // lessons: كل unit عندها درس واحد فقط
        Schema::table('lessons', function (Blueprint $table) {
            $table->unique('learning_unit_id', 'lessons_learning_unit_id_unique');
        });

        // quizzes: كل unit عندها quiz واحد فقط
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unique('learning_unit_id', 'quizzes_learning_unit_id_unique');
        });

        // challenges: كل unit عندها challenge واحد فقط
        Schema::table('challenges', function (Blueprint $table) {
            $table->unique('learning_unit_id', 'challenges_learning_unit_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropUnique('lessons_learning_unit_id_unique');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropUnique('quizzes_learning_unit_id_unique');
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->dropUnique('challenges_learning_unit_id_unique');
        });
    }
};
