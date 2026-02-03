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
    {Schema::table('roadmap_enrollments', function (Blueprint $table) {
        $table->timestamps();  
        $table->unique(['user_id', 'roadmap_id']);
    });
       
      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
