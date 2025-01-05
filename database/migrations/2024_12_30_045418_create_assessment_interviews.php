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
        Schema::create('assessment_interviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('certificate_request_id')->nullable();
            $table->bigInteger('assessor_head')->nullable();
            $table->bigInteger('assessor_1')->nullable();
            $table->bigInteger('assessor_2')->nullable();
            $table->enum('interview_type', ['online', 'offline'])->nullable();
            $table->dateTime('schedule')->nullable();
            $table->string('location')->nullable();
            $table->string('notes')->nullable();
            $table->string('number_of_letter')->nullable();
            $table->json('photos_of_event')->nullable();
            $table->json('photos_of_attendance_list')->nullable();
            $table->json('name_of_participants')->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->enum('status', ['scheduling','not_pass','completed'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_interviews');
    }
};
