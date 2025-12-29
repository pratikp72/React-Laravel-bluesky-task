<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bluesky_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('handle')->unique();
            $table->string('service')->default('https://bsky.social');
            $table->string('status')->default('pending');
            $table->text('app_password');
            $table->string('did')->nullable();
            $table->longText('access_jwt')->nullable();
            $table->longText('refresh_jwt')->nullable();
            $table->timestamp('last_authenticated_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bluesky_accounts');
    }
};
