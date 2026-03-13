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
            $table->boolean('is_pedestrian')->default(false);
            $table->foreignId('vehicle_type_id')->nullable()->constrained()->restrictOnDelete();

            $table->string('vehicle_number', 20)->nullable();
            $table->string('vehicle_brand')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->string('license_number', 50)->nullable();

            $table->decimal('rating', 3, 2)->default(5.00);

            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(false);

            $table->string('application_status')->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['is_online', 'is_active']);
            $table->index('application_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_profiles');
    }
};
