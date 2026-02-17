<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->text('content')->after('title');
            $table->enum('type', ['general', 'technical', 'opportunity'])->default('general')->after('content');
            $table->enum('target_type', ['all', 'specific_users', 'inactive_users', 'low_progress'])->default('all')->after('type');
            $table->json('target_rules')->nullable()->after('target_type');
            $table->timestamp('publish_at')->nullable()->after('target_rules');
            $table->enum('status', ['draft', 'scheduled', 'published'])->default('draft')->after('publish_at');
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();

            $table->index('status');
            $table->index('type');
            $table->index('publish_at');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['publish_at']);
            $table->dropColumn(['content', 'type', 'target_type', 'target_rules', 'publish_at', 'status', 'created_by']);
        });
    }
};

