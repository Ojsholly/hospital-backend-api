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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->string('gateway')->nullable();
            $table->foreignUuid('user_id')->nullable();
            $table->foreignUuid('doctor_id')->nullable();
            $table->foreignUuid('appointment_id')->nullable();
            $table->decimal('amount', 16, 2);
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('status');
            $table->timestamps();

            $table->index(['user_id', 'doctor_id', 'appointment_id', 'reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
