<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that have no natural branch-bearing parent need their own
     * branch_id so the "branch" global scope can isolate them.
     */
    public function up(): void
    {
        foreach (['notices', 'resources', 'certificates'] as $table) {
            if (! Schema::hasColumn($table, 'branch_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('branch_id')
                        ->nullable()
                        ->after('institute_id')
                        ->constrained()
                        ->nullOnDelete();
                });
            }

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
        foreach (['certificates', 'resources', 'notices'] as $table) {
            if (Schema::hasColumn($table, 'branch_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('branch_id');
                });
            }
        }
    }
};
