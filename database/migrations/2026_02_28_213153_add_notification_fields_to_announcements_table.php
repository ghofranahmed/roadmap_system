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
        Schema::table('announcements', function (Blueprint $table) {
            // Add notification-related fields
            $table->boolean('send_notification')->default(false)->after('created_by');
            $table->enum('target_type', ['all', 'specific_users', 'inactive_users', 'low_progress'])
                  ->default('all')->after('send_notification');
            $table->json('target_rules')->nullable()->after('target_type'); // For specific_users: array of user IDs
            $table->enum('status', ['draft', 'published'])->default('draft')->after('target_rules');
            
            // Add indexes
            $table->index('status');
            $table->index('send_notification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['send_notification']);
            $table->dropColumn(['send_notification', 'target_type', 'target_rules', 'status']);
        });
    }
};
