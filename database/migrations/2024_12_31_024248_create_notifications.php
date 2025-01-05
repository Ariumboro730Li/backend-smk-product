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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('topic')->nullable();
            $table->string('type')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('path_url')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->json('data')->nullable();
            $table->dateTime('delivery_at')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
