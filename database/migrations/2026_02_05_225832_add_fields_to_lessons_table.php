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
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('title')->after('learning_unit_id');
            $table->text('description')->nullable()->after('title');
            $table->unsignedInteger('position')->default(1)->after('description');
            $table->boolean('is_active')->default(true)->after('position');
            
            // إضافة timestamps إذا لم تكن موجودة
            if (!Schema::hasColumn('lessons', 'created_at')) {
                $table->timestamps();
            }
            
            // إضافة index لتحسين الأداء
            $table->index(['learning_unit_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'position', 'is_active']);
            $table->dropIndex(['learning_unit_id', 'position']);
        });
    }
};