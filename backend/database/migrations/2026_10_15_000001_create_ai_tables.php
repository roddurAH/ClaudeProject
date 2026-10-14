<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AI crop doctor: a conversation is one plant problem (photo + follow-ups);
     * messages keep the photo, the structured answer and the token usage
     * (for the cost estimate in the admin panel).
     */
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('language', 2)->default('bn');
            $table->string('rating', 4)->nullable();            // up, down
            $table->string('feedback', 500)->nullable();
            $table->foreignId('escalated_question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->timestamps();
            $table->index(['rating', 'created_at']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role', 10);                         // user, assistant
            $table->text('text')->nullable();
            $table->string('image_path')->nullable();
            $table->string('context', 300)->nullable();         // crop, district, month as sent to the model
            $table->json('result')->nullable();                 // structured answer (assistant)
            $table->string('model', 60)->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('cache_read_tokens')->default(0);
            $table->unsignedInteger('cache_write_tokens')->default(0);
            $table->string('stop_reason', 30)->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamps();
            $table->index(['role', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
