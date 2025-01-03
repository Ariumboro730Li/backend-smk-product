<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->foreignId('directorate_id')->nullable()
                ->constrained('directorates')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('work_unit_level_1_id')
                ->nullable()
                ->constrained('work_units')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('work_unit_level_2_id')
                ->nullable()
                ->constrained('work_units')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->enum('level', ['Level 1', 'Level 2', 'Level 3']);
            $table->foreignId('province_id')
                ->constrained('province')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('city_id')
                ->constrained('cities')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('address');
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_units');
    }
};
