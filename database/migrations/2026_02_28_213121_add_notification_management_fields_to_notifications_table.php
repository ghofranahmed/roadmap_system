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
        Schema::table('notifications', function (Blueprint $table) {
            // Add announcement_id (nullable FK) - optional linkage
            $table->foreignId('announcement_id')->nullable()->after('read_at')
                  ->constrained('announcements')->nullOnDelete();
            
            // Add priority field (low, medium, high)
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('announcement_id');
            
            // Add metadata JSON field for flexible data storage
            $table->json('metadata')->nullable()->after('priority');
            
            // Add indexes for efficient querying
            $table->index('announcement_id');
            $table->index('priority');
            $table->index(['user_id', 'read_at']); // For user notification queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['announcement_id']);
            $table->dropIndex(['announcement_id']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropColumn(['announcement_id', 'priority', 'metadata']);
        });
    }
};
