<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('department_code')->nullable();
            $table->string('ffunccod')->nullable();
            $table->string('office')->nullable();
            $table->string('short_name')->nullable();
            $table->boolean('within_capitol')->default(false)->nullable();
            $table->string('empl_id')->nullable();
            $table->string('designation')->nullable();
            $table->string('location')->nullable();
            $table->boolean('can_be_multiple_services')->default(false)->nullable();
            $table->string('assigned_character')->nullable();
            $table->string('queuing_ip_address')->nullable();
            $table->integer('window_count')->default(0)->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('office_id')->references('id')->on('offices')->nullOnDelete();
        });
        Schema::table('office_child_visits', function (Blueprint $table) {
            $table->foreign('office_id')->references('id')->on('offices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
        });
        Schema::table('office_child_visits', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
        });

        Schema::dropIfExists('offices');
    }
};
