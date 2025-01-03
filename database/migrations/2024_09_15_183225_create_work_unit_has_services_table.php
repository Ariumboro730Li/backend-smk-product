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
        Schema::create('work_unit_has_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_unit_id')
                ->constrained('work_units')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('service_type_id')
                ->constrained('service_types')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_unit_has_services');
    }
};
