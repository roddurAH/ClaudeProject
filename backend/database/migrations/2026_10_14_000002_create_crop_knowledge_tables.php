<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The agronomic knowledge base, maintained by experts in the admin panel
     * and downloaded by the app for offline use.
     *
     * Every user-facing text has a Bangla (_bn) and English (_en) column so the
     * app can switch language without another network request.
     */
    public function up(): void
    {
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name_bn', 80);
            $table->string('name_en', 80);
            $table->string('category', 20)->index();        // cereal, vegetable, fruit, pulse, oilseed, fibre, spice
            $table->string('icon', 16)->nullable();         // emoji fallback when no image
            $table->string('image_path')->nullable();
            $table->string('season', 20)->nullable();       // rabi, kharif1, kharif2, year_round
            $table->json('sowing_months')->nullable();      // [11, 12] = Nov–Dec
            $table->json('harvest_months')->nullable();
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->boolean('is_garden_friendly')->default(false); // suitable for rooftop / homestead
            $table->text('summary_bn')->nullable();
            $table->text('summary_en')->nullable();
            $table->longText('cultivation_bn')->nullable(); // one tip per line
            $table->longText('cultivation_en')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });

        // Crop calendar template. "day_offset" counts from the sowing /
        // transplanting date the farmer enters in "My Crops".
        Schema::create('crop_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('day_offset');
            $table->string('stage_type', 20)->default('other'); // land_prep, sowing, fertilizer, irrigation, weeding, pest_watch, harvest, other
            $table->string('title_bn', 150);
            $table->string('title_en', 150);
            $table->text('details_bn')->nullable();
            $table->text('details_en')->nullable();
            $table->timestamps();
            $table->index(['crop_id', 'day_offset']);
        });

        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);                      // pest, disease, nutrient, weed, physiological
            $table->string('affected_part', 20);             // leaf, stem, fruit, grain, root, whole
            $table->string('name_bn', 150);
            $table->string('name_en', 150);
            $table->string('severity', 10)->default('medium');
            $table->string('image_path')->nullable();
            $table->text('symptoms_bn');
            $table->text('symptoms_en');
            $table->text('prevention_bn')->nullable();       // cultural / IPM measures – shown first
            $table->text('prevention_en')->nullable();
            $table->text('organic_control_bn')->nullable();
            $table->text('organic_control_en')->nullable();
            $table->text('chemical_control_bn')->nullable(); // last resort, with safety note
            $table->text('chemical_control_en')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
            $table->index(['crop_id', 'affected_part']);
        });

        Schema::create('fertilizers', function (Blueprint $table) {
            $table->id();
            $table->string('key', 30)->unique();             // urea, tsp, mop, dap, gypsum, zinc, boron
            $table->string('name_bn', 80);
            $table->string('name_en', 80);
            $table->decimal('official_price_per_kg', 8, 2)->nullable(); // government farmer-level price
            $table->text('notes_bn')->nullable();
            $table->text('notes_en')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Recommended dose for medium-fertility soil, per hectare.
        Schema::create('crop_fertilizer_doses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fertilizer_id')->constrained()->cascadeOnDelete();
            $table->decimal('kg_per_hectare', 8, 2);
            $table->text('application_bn')->nullable();      // when / how to apply
            $table->text('application_en')->nullable();
            $table->timestamps();
            $table->unique(['crop_id', 'fertilizer_id']);
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('category', 20)->index();         // gardening, ipm, soil, safety, post_harvest, climate, market
            $table->string('audience', 20)->default('all');  // all, farmer, gardener
            $table->string('icon', 16)->nullable();
            $table->string('image_path')->nullable();
            $table->string('title_bn', 200);
            $table->string('title_en', 200);
            $table->text('summary_bn')->nullable();
            $table->text('summary_en')->nullable();
            $table->longText('body_bn');
            $table->longText('body_en');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
        Schema::dropIfExists('crop_fertilizer_doses');
        Schema::dropIfExists('fertilizers');
        Schema::dropIfExists('problems');
        Schema::dropIfExists('crop_stages');
        Schema::dropIfExists('crops');
    }
};
