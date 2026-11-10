<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('asset_code', 100)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->date('distribution_date')->nullable();
            $table->date('return_date')->nullable();
            $table->string('status')->default('in_stock'); // in_stock, distributed, returned, damaged, lost
            $table->string('condition')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_assets');
    }
};
