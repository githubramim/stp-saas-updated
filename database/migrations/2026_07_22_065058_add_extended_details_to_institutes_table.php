<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->string('eiin_no')->nullable()->after('code');
            $table->string('established_year')->nullable()->after('eiin_no');
            $table->string('website')->nullable()->after('email');
            $table->string('tagline')->nullable()->after('website');
            $table->text('description')->nullable()->after('tagline');
            $table->string('principal_name')->nullable()->after('description');
            $table->string('principal_phone')->nullable()->after('principal_name');
            $table->string('upazila')->nullable()->after('address');
            $table->string('district')->nullable()->after('upazila');
            $table->string('division')->nullable()->after('district');
            $table->string('cover_photo')->nullable()->after('logo');
            $table->string('facebook_url')->nullable()->after('cover_photo');
            $table->string('youtube_url')->nullable()->after('facebook_url');
            $table->string('linkedin_url')->nullable()->after('youtube_url');
        });
    }

    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn([
                'eiin_no', 'established_year', 'website', 'tagline',
                'description', 'principal_name', 'principal_phone',
                'upazila', 'district', 'division', 'cover_photo',
                'facebook_url', 'youtube_url', 'linkedin_url'
            ]);
        });
    }
};
