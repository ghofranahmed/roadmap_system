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
        Schema::table('chatbot_settings', function (Blueprint $table) {
            // Remove unused model_name column
            // This field was validated but never used in LLM providers
            $table->dropColumn('model_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_settings', function (Blueprint $table) {
            // Re-add column if migration is rolled back
            $table->string('model_name')->nullable();
        });
    }
};
