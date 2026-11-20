<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HR & Employee Management (BRD module #22): joining date, salary
     * structure, leave balance, employee attendance, payroll and performance.
     */
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employee_code', 30)->nullable();
            $table->date('joining_date')->nullable();
            $table->string('employment_type', 30)->default('full_time');
            $table->string('department')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('house_allowance', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->decimal('other_allowance', 12, 2)->default(0);
            $table->unsignedInteger('annual_leave_entitlement')->default(12);
            $table->string('emergency_contact', 50)->nullable();
            $table->string('nid', 50)->nullable();
            $table->unsignedTinyInteger('performance_rating')->nullable();
            $table->text('performance_notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['institute_id', 'status']);
        });

        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 20)->default('present'); // present|late|half_day|absent|leave
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['institute_id', 'date']);
        });

        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('leave_type', 30)->default('casual'); // casual|sick|earned|unpaid|other
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days', 5, 1)->default(1);
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending'); // pending|approved|rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'status']);
        });

        // Backfill one HR profile per existing staff member. Basic salary comes
        // from the saved monthly_salary so the payroll page is useful on day one.
        foreach (DB::table('users')->whereNotNull('institute_id')->orderBy('id')->get() as $index => $user) {
            DB::table('employee_profiles')->insert([
                'institute_id' => $user->institute_id,
                'user_id' => $user->id,
                'employee_code' => 'EMP-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'joining_date' => $user->created_at ? substr((string) $user->created_at, 0, 10) : now()->toDateString(),
                'employment_type' => 'full_time',
                'basic_salary' => $user->monthly_salary ?? 0,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leaves');
        Schema::dropIfExists('employee_attendances');
        Schema::dropIfExists('employee_profiles');
    }
};
