<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(false);
            $table->dateTime('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('is_online');
            $table->index('is_active');
            $table->index(['is_online', 'is_active']);
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
