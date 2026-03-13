<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courier_profiles', function (Blueprint $table) {
            $table->foreignId('vehicle_type_id')->nullable()->change();
            $table->string('vehicle_number', 20)->nullable()->change();
            $table->string('license_number', 50)->nullable()->change();

            $table->boolean('is_pedestrian')->default(false)->after('user_id');
            $table->string('vehicle_brand')->nullable()->after('vehicle_number');
            $table->string('vehicle_model')->nullable()->after('vehicle_brand');
            $table->string('vehicle_color')->nullable()->after('vehicle_model');

            $table->string('application_status')->default('pending')->after('is_active');
            $table->text('rejection_reason')->nullable()->after('application_status');
        });
    }

    public function down(): void
    {
        Schema::table('courier_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'is_pedestrian',
                'vehicle_brand',
                'vehicle_model',
                'vehicle_color',
                'application_status',
                'rejection_reason',
            ]);

            $table->foreignId('vehicle_type_id')->nullable(false)->change();
            $table->string('vehicle_number', 20)->nullable(false)->change();
            $table->string('license_number', 50)->nullable(false)->change();
        });
    }
};
