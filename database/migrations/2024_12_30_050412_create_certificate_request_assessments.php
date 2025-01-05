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
        Schema::create('certificate_request_assessments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('certificate_request_id')->nullable();
            $table->bigInteger('assessor')->nullable();
            $table->json('element_properties')->nullable();
            $table->json('answers')->nullable();
            $table->json('assessments')->nullable();
            $table->enum('status', ['rejected','draft','request','not_passed_assessment','submission_revision','passed_assessment','not_passed_assessment_verification','assessment_revision','passed_assessment_verification'])->nullable();
            $table->longText('validation_notes')->nullable();
            $table->longText('rejected_note')->nullable();
            $table->integer('revision')->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_request_assessments');
    }
};
