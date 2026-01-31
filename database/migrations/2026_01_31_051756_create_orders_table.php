<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete(null);
            $table->foreignId('courier_id')->constrained('users')->onDelete(null);
            $table->foreignId('vehicle_type_id')->constrained()->onDelete(null);
            $table->string('status')->default('created');
            $table->decimal('total_distance_km', 8, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
