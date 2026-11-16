<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Institutes without any branch automatically get "Branch 1" so every
        // record (staff, students, transactions) always has a branch to belong to.
        foreach (DB::table('institutes')->orderBy('id')->get(['id']) as $institute) {
            $hasBranch = DB::table('branches')
                ->where('institute_id', $institute->id)
                ->exists();

            if (! $hasBranch) {
                DB::table('branches')->insert([
                    'institute_id' => $institute->id,
                    'name' => 'Branch 1',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
