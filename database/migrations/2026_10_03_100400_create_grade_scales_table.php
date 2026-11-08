<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->decimal('grade_point', 3, 2);
            $table->string('grade_letter', 10);
            $table->string('remark', 100)->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'min_percentage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scales');
    }
};
