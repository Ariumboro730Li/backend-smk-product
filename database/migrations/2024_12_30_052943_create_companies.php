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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('nib')->nullable();
            $table->string('nib_file')->nullable();
            $table->bigInteger('province_id')->nullable();
            $table->bigInteger('city_id')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('company_phone_number')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_phone')->nullable();
            $table->dateTime('request_date')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->string('remember_token')->nullable();
            $table->date('established')->nullable();
            $table->boolean('exist_spionam')->default(false)->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
