<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add unit_type only (is_active already exists in your project)
        Schema::table('learning_units', function (Blueprint $table) {
            $table->enum('unit_type', ['lesson', 'quiz', 'challenge'])
                ->nullable()
                ->after('title');
        });

        // Unique: (roadmap_id, position)
        Schema::table('learning_units', function (Blueprint $table) {
            $table->unique(['roadmap_id', 'position'], 'learning_units_roadmap_position_unique');
        });
    }

    public function down(): void
    {
        Schema::table('learning_units', function (Blueprint $table) {
            $table->dropUnique('learning_units_roadmap_position_unique');
            $table->dropColumn(['unit_type']);
        });
    }
};
