<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('tam_sector')->nullable()->after('ntvqf_level');
            $table->string('tam_occupation')->nullable()->after('tam_sector');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['tam_sector', 'tam_occupation']);
        });
    }
};
