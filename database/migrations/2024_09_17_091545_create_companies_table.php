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
            $table->uuid()->primary();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('phone_number')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('nib');
            $table->string('nib_file');
            $table->foreignId('province_id')
                ->constrained('provinces')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('city_id')
                ->constrained('cities')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('address');
            $table->string('company_phone_number');
            $table->string('pic_name');
            $table->string('pic_phone');
            $table->dateTime('request_date', $precision = 0)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->boolean('is_active')->default(0);
            $table->rememberToken();
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
