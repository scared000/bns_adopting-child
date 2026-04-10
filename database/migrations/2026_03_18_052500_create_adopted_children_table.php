<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adopted_children', function (Blueprint $table) {
            $table->id();
            $table->string('municipality_id')->nullable();
            $table->string('barangay_id')->nullable();
            $table->foreign('municipality_id')->references('citymunCode')->on('municipalities');
            $table->foreign('barangay_id')->references('brgyCode')->on('barangays');
            $table->string('purok')->nullable();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('middlename')->nullable();
            $table->string('suffix')->nullable();
            $table->string('profile_path')->nullable();
            $table->date('birthdate')->nullable();
            $table->text('birthplace')->nullable();
            $table->string('sex')->nullable();
            $table->integer('age_months')->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->string('nutritional_status')->nullable();
            $table->string('underlying_cause')->nullable();
            $table->boolean('lcr_registered')->default(false);
            $table->boolean('breastfed')->default(false);
            $table->boolean('v_suplemented')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adopted_children');
    }
};
