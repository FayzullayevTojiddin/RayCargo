<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_profile_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // license_front, license_back, passport, vehicle_front, vehicle_back, vehicle_left, vehicle_right
            $table->string('file_path');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['courier_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_documents');
    }
};
