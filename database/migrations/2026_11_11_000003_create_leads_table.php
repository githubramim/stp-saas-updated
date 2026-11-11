<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_student_id')->nullable()->constrained('students')->nullOnDelete();

            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('source')->default('website');
            $table->string('status')->default('new');
            $table->string('interest')->nullable();
            $table->text('notes')->nullable();
            $table->text('lost_reason')->nullable();

            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('converted_at')->nullable();

            $table->timestamps();

            $table->index(['institute_id', 'status']);
            $table->index(['institute_id', 'source']);
            $table->index(['institute_id', 'next_follow_up_at']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
