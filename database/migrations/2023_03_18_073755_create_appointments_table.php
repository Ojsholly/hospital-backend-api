<?php

use App\Enums\AppointmentStatusEnum;
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
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->foreignUuid('user_id')->references('id')->on('users');
            $table->foreignUuid('doctor_id')->references('id')->on('doctors');
            $table->dateTime('datetime');
            $table->text('reason');
            $table->text('symptoms');
            $table->text('diagnosis')->nullable();
            $table->text('prescription')->nullable();
            $table->text('comment')->nullable();
            $table->text('address');
            $table->decimal('price', 20, 2);
            $table->string('status')->default(AppointmentStatusEnum::PENDING);
            $table->timestamps();

            $table->index(['user_id', 'doctor_id', 'reference', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
