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
        Schema::table('users', function (Blueprint $table) {
            // Add role column (enum: user, admin, tech_admin)
            $table->enum('role', ['user', 'admin', 'tech_admin'])->default('user')->after('email');
            
            // Add notification preference
            $table->boolean('is_notifications_enabled')->default(true)->after('role');
        });

        // Migrate existing is_admin to role
        \DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_notifications_enabled']);
        });
    }
};
