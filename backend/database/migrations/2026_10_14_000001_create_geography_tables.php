<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bangladesh administrative geography: 8 divisions, 64 districts.
     * District coordinates are used for weather forecasts and for picking
     * the nearest market when the user has not chosen one.
     */
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 60)->unique();
            $table->string('name_bn', 60);
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name_en', 60)->unique();
            $table->string('name_bn', 60);
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
        });
        Schema::dropIfExists('districts');
        Schema::dropIfExists('divisions');
    }
};
