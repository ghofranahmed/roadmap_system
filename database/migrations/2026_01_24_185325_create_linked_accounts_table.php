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

    Schema::create('linked_accounts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->string('provider'); // google, github, etc.
        $table->string('provider_user_id'); // Provider's user ID
        $table->text('access_token')->nullable(); // Changed to text for longer tokens
        $table->text('refresh_token')->nullable(); // Changed to text for longer tokens
        $table->timestamp('expires_at')->nullable();
        $table->string('provider_email')->nullable(); // Store provider email
        $table->string('avatar_url')->nullable(); // Store provider avatar
        $table->timestamps();
        
        // Unique: same provider + provider_user_id can only exist once
        $table->unique(['provider', 'provider_user_id']);
        
        // Unique: same user can't link same provider twice
        $table->unique(['user_id', 'provider']);
        
        // Index for faster user lookups
        $table->index('user_id');
    });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('linked_accounts');
    }
};
