<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ask-an-expert questions & answers, and broadcast alerts.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->string('image_path')->nullable();
            $table->string('status', 12)->default('pending');   // pending, answered, closed
            $table->boolean('is_public')->default(false);       // expert may publish to community Q&A
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // the expert
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('severity', 10)->default('info');    // info, warning, danger
            $table->string('title_bn', 200);
            $table->string('title_en', 200);
            $table->text('body_bn');
            $table->text('body_en');
            $table->boolean('is_national')->default(true);      // false → only listed districts
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('alert_district', function (Blueprint $table) {
            $table->foreignId('alert_id')->constrained()->cascadeOnDelete();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->primary(['alert_id', 'district_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_district');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
    }
};
