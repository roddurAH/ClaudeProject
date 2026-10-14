<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daily market prices. One row = one commodity in one market on one
     * date, for one price type (wholesale / retail).
     */
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->restrictOnDelete();
            $table->string('name_bn', 120);
            $table->string('name_en', 120);
            $table->string('type', 20)->default('wholesale'); // wholesale, retail, both
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('commodities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_bn', 120);
            $table->string('name_en', 120);
            $table->string('category', 20)->index();
            $table->string('unit', 10)->default('kg');        // kg, maund (40 kg), piece, hali (4 pcs), dozen, litre
            $table->string('icon', 16)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('market_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commodity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->date('price_date');
            $table->string('price_type', 10)->default('wholesale');
            $table->decimal('min_price', 10, 2);
            $table->decimal('max_price', 10, 2);
            $table->string('source', 120)->nullable();       // e.g. "DAM daily report", "Field survey"
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['commodity_id', 'market_id', 'price_date', 'price_type'], 'market_prices_unique_entry');
            $table->index(['market_id', 'price_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_prices');
        Schema::dropIfExists('commodities');
        Schema::dropIfExists('markets');
    }
};
