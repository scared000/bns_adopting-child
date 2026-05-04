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
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('brgyCode')->unique();
            $table->string('brgyDesc');
            $table->string('regCode')->nullable();
            $table->string('provCode')->nullable();
            $table->string('citymunCode')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('municipality_id')->references('citymunCode')->on('municipalities')->nullOnDelete();
            $table->foreign('barangay_id')->references('brgyCode')->on('barangays')->nullOnDelete();
        });

        Schema::table('adopted_children', function (Blueprint $table) {
            $table->foreign('municipality_id')->references('citymunCode')->on('municipalities')->nullOnDelete();
            $table->foreign('barangay_id')->references('brgyCode')->on('barangays')->nullOnDelete();
        });

        Schema::table('barangay_nutrition_scholars', function (Blueprint $table) {
            $table->foreign('municipality_id')->references('citymunCode')->on('municipalities')->nullOnDelete();
            $table->foreign('barangay_id')->references('brgyCode')->on('barangays')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['municipality_id']);
            $table->dropForeign(['barangay_id']);
        });

        Schema::table('adopted_children', function (Blueprint $table) {
            $table->dropForeign(['municipality_id']);
            $table->dropForeign(['barangay_id']);
        });

        Schema::table('barangay_nutrition_scholars', function (Blueprint $table) {
            $table->dropForeign(['municipality_id']);
            $table->dropForeign(['barangay_id']);
        });

        Schema::dropIfExists('barangays');
    }
};
