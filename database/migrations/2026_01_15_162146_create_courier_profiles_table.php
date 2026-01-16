<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->foreignId('vehicle_type_id')->constrained()->restrictOnDelete();
            $table->string('image')->nullable();

            $table->string('vehicle_number', 20);
            $table->string('license_number', 50);

            $table->decimal('rating', 3, 2)->default(5.00);

            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(false);

            $table->dateTime('last_seen_at')->nullable();

            $table->timestamps();

            $table->index(['is_online', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_profiles');
    }
};
