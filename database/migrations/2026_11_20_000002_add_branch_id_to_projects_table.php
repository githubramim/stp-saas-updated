<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Projects belong to one branch. Existing projects are assigned to their
     * institute's first branch so nothing disappears after the upgrade.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('institute_id')
                ->constrained()
                ->nullOnDelete();
        });

        DB::statement(
            'UPDATE projects SET branch_id = ('
            .' SELECT b.id FROM branches b'
            .' WHERE b.institute_id = projects.institute_id'
            .' ORDER BY b.id LIMIT 1'
            .') WHERE branch_id IS NULL AND institute_id IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
