<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('last_seen_at')->nullable()->after('last_login_at');
        });

        // Migrate existing data
        DB::statement('UPDATE users SET last_seen_at = cp.last_seen_at FROM courier_profiles cp WHERE users.id = cp.user_id AND cp.last_seen_at IS NOT NULL');
        DB::statement('UPDATE users SET last_seen_at = clp.last_seen_at FROM client_profiles clp WHERE users.id = clp.user_id AND clp.last_seen_at IS NOT NULL');

        Schema::table('courier_profiles', function (Blueprint $table) {
            $table->dropColumn(['last_seen_at', 'image']);
        });

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('courier_profiles', function (Blueprint $table) {
            $table->string('image')->nullable()->after('license_number');
            $table->dateTime('last_seen_at')->nullable()->after('is_active');
        });

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dateTime('last_seen_at')->nullable()->after('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });
    }
};
