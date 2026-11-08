<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('course_code')->nullable()->unique()->after('name_bn');
            $table->string('ntvqf_level')->nullable()->after('course_code');
            $table->integer('duration_hours')->nullable()->after('duration');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['course_code', 'ntvqf_level', 'duration_hours']);
        });
    }
};
