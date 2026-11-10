<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('employer_name');
            $table->string('job_title')->nullable();
            $table->string('employment_type')->nullable(); // full_time, part_time, self_employed, apprenticeship
            $table->decimal('monthly_salary', 10, 2)->nullable();
            $table->date('placement_date')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_number', 50)->nullable();
            $table->string('status')->default('placed'); // placed, retained, dropped
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_placements');
    }
};
