<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('adopted_children')->cascadeOnDelete();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('suffix')->nullable();
            $table->string('relationship')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('highest_grade')->nullable();
            $table->string('occupation')->nullable();
            $table->string('actual_weight')->nullable(); // Set as Varchar in ERD
            $table->string('nutrition_status')->nullable(); // Spelling from ERD
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_profiles');
    }
};
