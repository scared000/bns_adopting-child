<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immunizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('adopted_children')->cascadeOnDelete();
            $table->string('vaccine_description');
            $table->date('dose_1')->nullable();
            $table->date('dose_2')->nullable();
            $table->date('dose_3')->nullable();
            $table->string('status')->default('incomplete');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immunizations');
    }
};
