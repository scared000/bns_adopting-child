<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangay_nutrition_scholars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained('users')->nullOnDelete();
            $table->string('municipality_id')->nullable();
            $table->string('barangay_id')->nullable();
            $table->foreign('municipality_id')->references('citymunCode')->on('municipalities');
            $table->foreign('barangay_id')->references('brgyCode')->on('barangays');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('suffix')->nullable();
            $table->string('profile_path')->nullable();
            $table->string('barangay_code')->nullable();
            $table->string('barangay_name')->nullable();
            $table->string('purok')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangay_nutrition_scholars');
    }
};
