<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['announcement_id']);
            $table->dropColumn('announcement_id');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('announcement_id')->nullable()->after('read_at')
                  ->constrained('announcements')->nullOnDelete();
        });
    }
};

