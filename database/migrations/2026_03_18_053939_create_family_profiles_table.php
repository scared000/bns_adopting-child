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
            $table->enum('type', ['mother', 'father', 'fam_member'])->default('mother');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('suffix')->nullable();
            $table->string('relation')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('educational_attainment')->nullable();
            $table->string('occupation')->nullable();
            $table->string('fam_member_fullname')->nullable();
            $table->string('fam_member_actual_weight')->nullable(); // Set as Varchar in ERD
            $table->string('fam_member_nutrition_status')->nullable(); // Spelling from ERD
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_profiles');
    }
};
