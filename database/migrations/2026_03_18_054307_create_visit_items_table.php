<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('office_child_visits')->cascadeOnDelete();
            $table->text('Item_description')->nullable();
            $table->decimal('item_quantity', 8, 2)->nullable(); // Spelling from ERD
            $table->decimal('item_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_items');
    }
};
