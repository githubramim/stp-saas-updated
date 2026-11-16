<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'students', 'transactions'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('institute_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        // Every existing record falls back to its institute's first branch,
        // which the previous migration guarantees to exist ("Branch 1").
        foreach (['users', 'students', 'transactions'] as $table) {
            DB::statement(sprintf(
                'UPDATE %s SET branch_id = ('
                .' SELECT b.id FROM branches b'
                .' WHERE b.institute_id = %s.institute_id'
                .' ORDER BY b.id LIMIT 1'
                .') WHERE branch_id IS NULL AND institute_id IS NOT NULL',
                $table,
                $table
            ));
        }
    }

    public function down(): void
    {
        foreach (['transactions', 'students', 'users'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }
    }
};
