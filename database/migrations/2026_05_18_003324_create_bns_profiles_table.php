<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bns_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // I. Personal Information
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable();
            $table->string('municipality_id')->nullable()->after('province');
            $table->string('barangay_id')->nullable()->after('municipality_id');
            $table->date('date_of_birth');
            $table->string('place_of_birth');
            $table->enum('sex', ['male', 'female']);
            $table->enum('civil_status', ['single', 'married', 'widowed', 'separated']);
            $table->enum('educational_attainment', [
                'elementary_graduate',
                'high_school_level',
                'high_school_graduate',
                'college_level',
                'college_graduate',
                'vocational',
                'post_graduate',
            ]);
            $table->string('contact_number')->nullable();
            $table->string('email_or_facebook')->nullable();
            $table->text('home_address');

            // II. Service Records
            $table->date('date_started')->nullable();
            $table->unsignedSmallInteger('years_of_service')->default(0);
            $table->string('bns_id_number')->nullable();
            $table->decimal('monthly_honorarium', 10, 2)->nullable();

            //IV. Documents (file paths stored as JSON array)
            $table->json('pds_document')->nullable();
            $table->json('appointment_order')->nullable();
            $table->json('oath_of_office')->nullable();
            $table->json('certificate_of_training')->nullable();
            $table->json('psa_birth_certificate')->nullable();
            $table->json('diploma_or_tor')->nullable();
            $table->json('service_record')->nullable();

            //  Meta
            $table->enum('status', ['active', 'inactive', 'resigned'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bns_profiles');
    }
};
