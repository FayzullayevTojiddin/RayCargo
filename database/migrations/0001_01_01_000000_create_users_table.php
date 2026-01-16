<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('email')->unique()->nullable();
            $table->string('phone_number')->unique()->nullable();

            $table->string('image')->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            $table->enum('login_type', ['email', 'phone'])->default('email');

            $table->timestamp('last_login_at')->nullable();

            $table->enum('role', ['admin', 'courier', 'client'])->default('client');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');

            $table->string('lang')->default('en');

            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('status');
            $table->index('login_type');
            $table->index('last_login_at');
            $table->index('lang');
            $table->index(['role', 'status']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
