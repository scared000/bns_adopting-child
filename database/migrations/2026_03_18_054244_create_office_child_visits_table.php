<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_child_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_type')->default('bns_visit');
            $table->foreignId('office_assign_id')->nullable()->constrained('office_child_assigns')->cascadeOnDelete();
            $table->foreignId('office_id')->nullable();
            $table->foreignId('adopted_id')->nullable()->constrained('adopted_children')->cascadeOnDelete();
            $table->foreignId('bns_id')->nullable()->constrained('barangay_nutrition_scholars')->cascadeOnDelete();
            $table->date('visit_date')->nullable();
            $table->text('visit_address')->nullable();
            $table->json('visit_documentation')->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_child_visits');
    }
};
