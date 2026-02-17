<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->default('general')->after('message');
            $table->timestamp('read_at')->nullable()->after('type');
            $table->foreignId('announcement_id')->nullable()->after('read_at')
                  ->constrained('announcements')->nullOnDelete();

            $table->index('type');
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['announcement_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['read_at']);
            $table->dropColumn(['type', 'read_at', 'announcement_id']);
        });
    }
};

