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
        Schema::create('who_growth_standards', function (Blueprint $table) {
            $table->id();
            $table->string('indicator');    // 'wfa', 'hfa', 'wfh'
            $table->string('sex');          // 'male', 'female'
            $table->decimal('key_value', 6, 1); // age in months (wfa/hfa) or height cm (wfh)
            $table->decimal('l', 8, 6);
            $table->decimal('m', 8, 4);
            $table->decimal('s', 8, 6);
            $table->timestamps();

            $table->index(['indicator', 'sex', 'key_value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('who_growth_standards');
    }
};
