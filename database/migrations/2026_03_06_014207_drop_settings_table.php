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
        // Drop the unused settings table
        // This table is not used anywhere in the codebase
        Schema::dropIfExists('settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create the table if migration is rolled back
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }
};
