<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('adopted_children')->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->string('type_of_marriage')->nullable();
            $table->string('monthly_income')->nullable();
            $table->string('source_income')->nullable();
            $table->boolean('phil_member')->default(false);
            $table->string('family_plan_method')->nullable();
            $table->boolean('have_electricity')->default(false);
            $table->string('water_source')->nullable();
            $table->string('toilet_facility')->nullable();
            $table->string('roofing')->nullable();
            $table->string('walls')->nullable();
            $table->string('flooring')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_statuses');
    }
};
