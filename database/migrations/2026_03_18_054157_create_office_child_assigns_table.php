<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_child_assigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bns_id')->nullable()->constrained('barangay_nutrition_scholars')->nullOnDelete();
            $table->foreignId('adopted_id')->constrained('adopted_children')->cascadeOnDelete();
            $table->unsignedBigInteger('office_id')->nullable(); // Assuming 'offices' table exists elsewhere
            $table->date('assigned_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_child_assigns');
    }
};
