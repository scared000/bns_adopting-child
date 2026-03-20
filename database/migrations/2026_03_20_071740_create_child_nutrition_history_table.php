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
        Schema::create('child_nutrition_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('adopted_children')->cascadeOnDelete();
            $table->date('recorded_at');
            $table->integer('age_months');
            $table->decimal('weight_kg', 5, 2);
            $table->decimal('height_cm', 5, 2);
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_nutrition_history');
    }
};
