<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(false);
            $table->string('title')->unique();
            $table->integer('max_weight_kg');
            $table->decimal('base_price');
            $table->decimal('price_per_kg');
            $table->decimal('price_per_km');
            $table->decimal('price_per_hour');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
