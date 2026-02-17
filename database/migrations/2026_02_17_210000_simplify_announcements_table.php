<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Copy content → description where description is empty/null
        DB::statement("
            UPDATE announcements
            SET description = content
            WHERE (description IS NULL OR description = '')
              AND content IS NOT NULL
              AND content != ''
        ");

        Schema::table('announcements', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['status']);
            $table->dropIndex(['publish_at']);

            // Drop foreign key is not needed for non-FK columns

            // Drop the targeting/scheduling columns
            $table->dropColumn([
                'content',
                'target_type',
                'target_rules',
                'publish_at',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->text('content')->nullable()->after('title');
            $table->enum('target_type', ['all', 'specific_users', 'inactive_users', 'low_progress'])
                  ->default('all')->after('type');
            $table->json('target_rules')->nullable()->after('target_type');
            $table->timestamp('publish_at')->nullable()->after('target_rules');
            $table->enum('status', ['draft', 'scheduled', 'published'])
                  ->default('draft')->after('publish_at');

            $table->index('status');
            $table->index('publish_at');
        });

        // Copy description back to content
        DB::statement("UPDATE announcements SET content = description");
    }
};

