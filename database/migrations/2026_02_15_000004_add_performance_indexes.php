<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index for quiz_attempts lookups
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index(['user_id', 'quiz_id'], 'quiz_attempts_user_quiz_index');
            $table->index('created_at');
        });

        // Index for challenge_attempts lookups
        Schema::table('challenge_attempts', function (Blueprint $table) {
            $table->index(['user_id', 'challenge_id'], 'challenge_attempts_user_challenge_index');
            $table->index('created_at');
            $table->index(['challenge_id', 'user_id', 'execution_output'], 'challenge_attempts_active_lookup');
        });

        // Index for lesson_trackings
        Schema::table('lesson_trackings', function (Blueprint $table) {
            $table->index('last_updated_at');
        });

        // Index for roadmap_enrollments
        Schema::table('roadmap_enrollments', function (Blueprint $table) {
            $table->index('started_at');
            $table->index('status');
        });

        // Index for chat_messages pagination
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index(['chat_room_id', 'created_at'], 'chat_messages_room_created_index');
        });

        // Index for quiz_questions ordering
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->index(['quiz_id', 'order'], 'quiz_questions_quiz_order_index');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('quiz_attempts_user_quiz_index');
            $table->dropIndex('quiz_attempts_created_at_index');
        });

        Schema::table('challenge_attempts', function (Blueprint $table) {
            $table->dropIndex('challenge_attempts_user_challenge_index');
            $table->dropIndex('challenge_attempts_created_at_index');
            $table->dropIndex('challenge_attempts_active_lookup');
        });

        Schema::table('lesson_trackings', function (Blueprint $table) {
            $table->dropIndex('lesson_trackings_last_updated_at_index');
        });

        Schema::table('roadmap_enrollments', function (Blueprint $table) {
            $table->dropIndex('roadmap_enrollments_started_at_index');
            $table->dropIndex('roadmap_enrollments_status_index');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('chat_messages_room_created_index');
        });

        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropIndex('quiz_questions_quiz_order_index');
        });
    }
};

