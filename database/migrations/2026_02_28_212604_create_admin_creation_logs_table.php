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
        Schema::create('admin_creation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('created_role'); // 'admin' or 'tech_admin'
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['creator_id', 'created_role', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_creation_logs');
    }
};
