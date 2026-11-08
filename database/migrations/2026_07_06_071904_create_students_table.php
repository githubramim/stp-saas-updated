<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();

            // Personal
            $table->string('name_en');
            $table->string('name_bn')->nullable();
            $table->string('father_name_en');
            $table->string('father_name_bn')->nullable();
            $table->string('mother_name_en');
            $table->string('mother_name_bn')->nullable();
            $table->string('contact');
            $table->string('email')->nullable();
            $table->date('dob');
            $table->string('gender');
            $table->string('religion')->nullable();
            $table->string('nationality')->default('Bangladeshi');
            $table->string('blood_group')->nullable();
            $table->string('id_type');
            $table->string('id_no');
            $table->string('occupation')->nullable();
            $table->string('guardian_contact');
            $table->string('guardian_relation');

            // Address
            $table->string('present_village')->nullable();
            $table->string('present_road')->nullable();
            $table->string('present_po')->nullable();
            $table->string('present_upazila')->nullable();
            $table->string('present_district')->nullable();
            $table->string('present_division')->nullable();
            $table->string('perm_village')->nullable();
            $table->string('perm_road')->nullable();
            $table->string('perm_po')->nullable();
            $table->string('perm_upazila')->nullable();
            $table->string('perm_district')->nullable();
            $table->string('perm_division')->nullable();

            // Education
            $table->string('edu_degree')->nullable();
            $table->string('edu_institute')->nullable();
            $table->year('edu_year')->nullable();
            $table->string('edu_cgpa')->nullable();
            $table->string('edu_address')->nullable();

            // Documents
            $table->string('photo_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('nid_path')->nullable();

            // Status
            $table->string('status')->default('applied');
            $table->string('reference_no')->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
