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
        Schema::table('linked_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('linked_accounts', 'provider_email')) {
                $table->string('provider_email')->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('linked_accounts', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->after('provider_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('linked_accounts', function (Blueprint $table) {
            $table->dropColumn(['provider_email', 'avatar_url']);
        });
    }
};
