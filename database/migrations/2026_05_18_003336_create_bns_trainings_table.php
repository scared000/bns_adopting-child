<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bns_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bns_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('date_attended')->nullable();
            $table->string('conducted_by')->nullable();
            $table->string('venue')->nullable();
            $table->unsignedSmallInteger('duration_hours')->nullable();
            $table->json('certificate')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bns_trainings');
    }
};
