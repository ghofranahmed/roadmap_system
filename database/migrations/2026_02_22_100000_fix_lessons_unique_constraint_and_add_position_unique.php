<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fix: Drop wrong UNIQUE(learning_unit_id) on lessons table
 *      (was preventing multiple lessons per learning unit).
 *
 * Add:  UNIQUE(learning_unit_id, position) — correct composite unique for lessons
 * Add:  UNIQUE(lesson_id, position)        — composite unique for sub_lessons
 *
 * Also normalizes existing positions to contiguous 1..N before applying constraints.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── 1) Drop the wrong UNIQUE on learning_unit_id alone ─────
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropUnique('lessons_learning_unit_id_unique');
        });

        // ─── 2) Drop the old regular composite index ────────────────
        // (learning_unit_id, position) — will be replaced by the unique index
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex(['learning_unit_id', 'position']);
        });

        // ─── 3) Normalize lessons positions per learning_unit ───────
        // Ensure no duplicate (learning_unit_id, position) pairs before adding unique
        $unitIds = DB::table('lessons')
            ->select('learning_unit_id')
            ->distinct()
            ->pluck('learning_unit_id');

        foreach ($unitIds as $unitId) {
            $lessons = DB::table('lessons')
                ->where('learning_unit_id', $unitId)
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            foreach ($lessons as $index => $lesson) {
                DB::table('lessons')
                    ->where('id', $lesson->id)
                    ->update(['position' => $index + 1]);
            }
        }

        // ─── 4) Add correct composite UNIQUE for lessons ────────────
        Schema::table('lessons', function (Blueprint $table) {
            $table->unique(['learning_unit_id', 'position'], 'lessons_unit_position_unique');
        });

        // ─── 5) Normalize sub_lessons positions per lesson ──────────
        $lessonIds = DB::table('sub_lessons')
            ->select('lesson_id')
            ->distinct()
            ->pluck('lesson_id');

        foreach ($lessonIds as $lessonId) {
            $subLessons = DB::table('sub_lessons')
                ->where('lesson_id', $lessonId)
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            foreach ($subLessons as $index => $sl) {
                DB::table('sub_lessons')
                    ->where('id', $sl->id)
                    ->update(['position' => $index + 1]);
            }
        }

        // ─── 6) Add composite UNIQUE for sub_lessons ────────────────
        Schema::table('sub_lessons', function (Blueprint $table) {
            $table->unique(['lesson_id', 'position'], 'sub_lessons_lesson_position_unique');
        });
    }

    public function down(): void
    {
        // Remove sub_lessons composite unique
        Schema::table('sub_lessons', function (Blueprint $table) {
            $table->dropUnique('sub_lessons_lesson_position_unique');
        });

        // Remove lessons composite unique
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropUnique('lessons_unit_position_unique');
        });

        // Restore the original regular index
        Schema::table('lessons', function (Blueprint $table) {
            $table->index(['learning_unit_id', 'position']);
        });

        // Restore the original wrong unique on learning_unit_id alone
        Schema::table('lessons', function (Blueprint $table) {
            $table->unique('learning_unit_id', 'lessons_learning_unit_id_unique');
        });
    }
};

