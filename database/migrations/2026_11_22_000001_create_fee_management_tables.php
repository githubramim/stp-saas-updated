<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BRD module #8 — Enrollment & Fee Management.
     *
     * Fee plans are reusable course/batch templates. A student fee plan is the
     * per-student snapshot (gross fee, adjustments, net payable) that owns the
     * installment schedule. Payments reconcile back to the existing ledger via
     * transactions, while reminders are logged per channel and due date.
     */
    public function up(): void
    {
        Schema::create('fee_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->after('institute_id')->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedTinyInteger('duration_months');
            $table->decimal('total_amount', 12, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['institute_id', 'branch_id', 'status']);
        });

        Schema::create('student_fee_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->after('institute_id')->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->unsignedTinyInteger('plan_months');
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('scholarship_amount', 12, 2)->default(0);
            $table->decimal('waiver_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->date('start_date');
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'branch_id', 'status']);
            $table->index(['student_id', 'status']);
        });

        Schema::create('fee_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->after('institute_id')->constrained()->nullOnDelete();
            $table->foreignId('student_fee_plan_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->string('reason');
            $table->string('reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_fee_plan_id', 'type']);
        });

        Schema::create('fee_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->after('institute_id')->constrained()->nullOnDelete();
            $table->foreignId('student_fee_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status')->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'branch_id', 'status']);
            $table->index(['student_fee_plan_id', 'sequence']);
            $table->index('due_date');
        });

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->after('institute_id')->constrained()->nullOnDelete();
            $table->foreignId('fee_installment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_fee_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['institute_id', 'branch_id', 'payment_date']);
        });

        Schema::create('fee_due_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->after('institute_id')->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_installment_id')->constrained()->cascadeOnDelete();
            $table->string('channel');
            $table->date('due_date');
            $table->string('status')->default('pending');
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->text('response')->nullable();
            $table->timestamps();

            $table->unique(['fee_installment_id', 'channel', 'due_date'], 'fee_reminders_unique');
            $table->index(['institute_id', 'branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_due_reminders');
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fee_installments');
        Schema::dropIfExists('fee_adjustments');
        Schema::dropIfExists('student_fee_plans');
        Schema::dropIfExists('fee_plans');
    }
};
