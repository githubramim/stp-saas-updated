<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('donor_name')->nullable()->after('description');
            $table->string('donor_type')->nullable()->after('donor_name');
            $table->decimal('budget_amount', 14, 2)->nullable()->after('donor_type');
            $table->string('mou_number', 100)->nullable()->after('budget_amount');
            $table->string('mou_file_path')->nullable()->after('mou_number');
            $table->date('start_date')->nullable()->after('mou_file_path');
            $table->date('end_date')->nullable()->after('start_date');
            $table->unsignedInteger('target_trainees')->nullable()->after('end_date');
            $table->string('coordinator_name')->nullable()->after('target_trainees');
            $table->string('coordinator_contact', 50)->nullable()->after('coordinator_name');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'donor_name', 'donor_type', 'budget_amount', 'mou_number', 'mou_file_path',
                'start_date', 'end_date', 'target_trainees', 'coordinator_name', 'coordinator_contact',
            ]);
        });
    }
};
