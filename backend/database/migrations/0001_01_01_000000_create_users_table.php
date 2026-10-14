<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One users table serves both mobile-app users (farmers, gardeners …)
     * and admin-panel staff. The `role` column decides what they may do.
     *
     * App users sign in with phone + PIN (stored hashed in `password`);
     * staff sign in to the admin panel with phone/email + password.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 15)->unique();          // 01XXXXXXXXX
            $table->string('email')->nullable()->unique();  // optional, staff mostly
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 20)->default('farmer')->index();
            $table->string('user_type', 20)->nullable();    // farmer, gardener, learner, extension_worker
            $table->unsignedBigInteger('district_id')->nullable()->index(); // FK added in geography migration
            $table->string('preferred_language', 2)->default('bn');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
