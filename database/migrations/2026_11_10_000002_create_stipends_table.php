<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stipends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('stipend'); // stipend, allowance, transport, food
            $table->string('period', 7); // YYYY-MM
            $table->decimal('amount', 10, 2);
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable(); // bank, bkash, nagad, cash
            $table->string('bank_name')->nullable();
            $table->string('account_no')->nullable();
            $table->string('transaction_ref')->nullable();
            $table->string('status')->default('pending'); // pending, paid, cancelled
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'project_id', 'period']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stipends');
    }
};
