<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('question_type')->default('written');
            $table->text('question');
            $table->text('option_a')->nullable();
            $table->text('option_b')->nullable();
            $table->text('option_c')->nullable();
            $table->text('option_d')->nullable();
            $table->string('correct_option')->nullable();
            $table->decimal('negative_marks', 8, 2)->default(0);
            $table->decimal('marks', 8, 2)->default(1);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['exam_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
