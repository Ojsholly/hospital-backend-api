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
        Schema::create('doctors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->index();
            $table->string('specialty');
            $table->string('medical_license_number');
            $table->string('medical_school');
            $table->string('year_of_graduation');
            $table->text('biography')->nullable();
            $table->text('address');
            $table->unsignedFloat('consultation_fee')->default('0.00');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['specialty', 'medical_license_number', 'medical_school']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
