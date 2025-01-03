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
        Schema::create('monitoring_elements', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('title')->unique();
            $table->json('element_properties');
            $table->json('monitoring_elements');
            $table->json('additional_questions');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_elements');
    }
};
